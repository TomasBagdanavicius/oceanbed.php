<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Common;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Datasets\Interfaces\DatasetManagerInterface;
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Components\Model\ModelCollection;
use LWP\Components\Datasets\Exceptions\UpdateEntryException;
use LWP\Components\Datasets\Exceptions\DatasetUpdateException;
use LWP\Components\Constraints\Violations\InDatasetConstraintViolation;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\detachElements;

abstract class AbstractDatasetUpdateManager implements DatasetManagerInterface
{
    use DatasetManagerTrait;
    use DatasetStoreManagerTrait;


    public readonly RelationalPropertyModel $model;


    public function __construct(
        public readonly AbstractDatasetStoreHandle $store_handle,
        protected ?DatasetManagementProcessInterface $process = null
    ) {

        $this->model = $store_handle->containers->getModel();
    }


    //

    abstract public function getUpdateEntryHandlerClassName(): string;


    //

    public static function getActionTypeDefinitionDataArrays(): array
    {

        return [
            'update' => [
                'data' => [
                    'type' => 'array',
                    'required' => true,
                    'description' => "Data structure",
                ],
            ],
        ];
    }


    //

    public function singleFromArray(
        string $container_name,
        string|int|float $field_value,
        array $data,
        array $private_data = [],
        bool $commit = true
    ): array {

        $dataset = $this->store_handle->dataset;
        $this->store_handle->containers->assertUniqueContainer(
            $container_name,
            "Single entry can be updated only by an unique container (%s is not unique)"
        );

        $select_handle = $dataset->getSelectHandle();
        $fetch_manager = $dataset->getFetchManager();
        $data_server_context = $fetch_manager->getSingleByUniqueContainer($select_handle, $container_name, $field_value);
        $result = $data_server_context->getDatasetResult();

        if ($result->count() === 0) {
            throw new EntryNotFoundException(sprintf(
                "Entry was not found: looked for value \"%s\" in container \"%s\"",
                $field_value,
                $container_name
            ));
        }

        $data_server_context_class_name = $select_handle->getDataServerContextClassName();

        /* Build a separate data server context with a new data model. This allows to populate raw dataset data with trusted access. */
        $my_data_server_context = new $data_server_context_class_name(
            $fetch_manager,
            // Used as template – will be cloned inside.
            $this->model,
            $data_server_context->result
        );

        $model = $my_data_server_context->getModel();
        $property_collection = $model->getPropertyCollection();

        /* Issue: set virtual properties as not required for update operation. Example: in Users, user_password and user_password_repeat are required in create operation, but are not required when one wants to update a few other columns. It looks like it's not possible to remove virtual properties from model, because, for instance, in Relationship Nodes relationship_type_code virtual property is used in `findMatch` operation below. */
        foreach ($property_collection as $property) {
            if ($dataset->containers->isVirtualContainer($property->property_name)) {
                $property->setRelationalRequired(null);
            }
        }

        ['process' => $process, 'inherited_process' => $inherited_process] = $this->getProcess($commit);
        $update_entry_handler_class_name = $this->getUpdateEntryHandlerClassName();
        $entry_handler = new $update_entry_handler_class_name($data, $model, $this, $private_data, $process);
        $entry_handler->addRelatedData($model);
        $validation_result = $entry_handler->getValidationResult();

        // Validation failed
        if (!empty($validation_result['__index']['error_count'])) {

            if (!$inherited_process) {
                $process->terminate();
            }

            return [
                'status' => DatasetActionStatusEnum::ERROR->value,
                'data' => $validation_result
            ];
        }

        $match = $fetch_manager->findMatch(
            $select_handle,
            $model,
            return_array: true,
            exclude_prime: true,
            model_for_default_case: $entry_handler->getModel(),
            required_when_not_available: false,
            /* If containers whose changed values do not intersect with containers participating in main unique case, don't include this case, because in update context it's a "no change" condition. */
            compare_main_unique_case_participants: array_keys($entry_handler->getChangedProperties())
        );

        // Match found
        if ($match) {

            $representational_name = $dataset->getRepresentationalName();
            $primary_container_name = $dataset->getPrimaryContainerName();

            return [
                'status' => DatasetActionStatusEnum::FOUND->value,
                'data' => [
                    $representational_name => [
                        $model->{$primary_container_name} => [
                            'status' => DatasetActionStatusEnum::FOUND->value,
                            $primary_container_name => $match[$primary_container_name],
                            'data' => $model->getValues(),
                        ],
                    ],
                ],
            ];
        }

        try {

            $update_data = $entry_handler->updateMain();

        } catch (UpdateEntryException $exception) {

            if (!$inherited_process) {
                $process->terminate();
            }

            return [
                'status' => DatasetActionStatusEnum::ERROR->value,
                'error_message' => $exception->getMessage(),
            ];
        }

        if ($commit && !$inherited_process && !$process->isTerminated()) {
            $process->commit();
        }

        return $update_data;
    }


    //

    public function manyFromArray(ConditionGroup $condition_group, array $data, bool $commit = true): array
    {

        /* If any of the values in data array violated unique field value constraint, it's better to determine that before running through each entry, because that error will be detected for all entries. */

        $model = $this->store_handle->getModel();
        $this->store_handle->setupDatasetConstraintsInModel($model);
        $model->setMass($data);

        $property_collection = $model->getPropertyCollection();

        foreach ($property_collection as $property) {

            if ($property->hasErrors()) {

                $class_names = $property->getViolationCollection()->getKeys();

                if (in_array(InDatasetConstraintViolation::class, $class_names)) {

                    throw new DatasetUpdateException(sprintf(
                        "Property \"%s\" contains dataset error",
                        $property->property_name
                    ));
                }
            }
        }

        $match_sensitive_containers = $this->store_handle->containers->getMatchSensitiveContainers();
        $unique_containers_in_data = array_intersect($match_sensitive_containers, array_keys($data));

        if ($unique_containers_in_data) {
            throw new \Exception(sprintf(
                "Unique flagged containers must not be used in data payload when updating multiple entries; found %s",
                ('"' . implode('", ', $unique_containers_in_data) . '"')
            ));
        }

        $dataset = $this->store_handle->dataset;
        $select_handle = $dataset->getSelectHandle();
        $fetch_manager = $dataset->getFetchManager();
        $data_server_context = $fetch_manager->getByConditionGroup($select_handle, $condition_group);
        $database_result = $data_server_context->getDatasetResult();
        $database_result_count = $database_result->count();

        if ($database_result_count > $dataset::MAX_UPDATE_ENTRIES) {
            throw new \RangeException(sprintf(
                "The number of entries to be updated (%d) exceeds the maximum number allowed (%d)",
                $database_result_count,
                $dataset::MAX_UPDATE_ENTRIES
            ));
        }

        [
            'process' => $process,
            'inherited_process' => $inherited_process,
        ] = $this->getProcess($commit);
        $data_server_context_class_name = $select_handle->getDataServerContextClassName();
        $compound_result = [];

        // Reconstruct to upgrade from "AllDataModel" to "RelationalPropertyModel", which is represented by "relational_model" property.
        $data_server_context = new $data_server_context_class_name(
            $fetch_manager,
            // Used as template – will be cloned inside.
            $this->model,
            $data_server_context->result
        );

        $data_model_collection = new ModelCollection();
        $entry_handlers = [];
        $data_model_iterator = $data_server_context->getIterator();
        $update_entry_handler_class_name = $this->getUpdateEntryHandlerClassName();
        $models_for_default_case = [];

        foreach ($data_model_iterator as $data_model) {

            $entry_handler = new $update_entry_handler_class_name($data, $data_model, $this, process: $process);
            $validation_result = $entry_handler->getValidationResult();

            // Validation failed
            if (!empty($validation_result['__index']['error_count'])) {

                unset($validation_result['__index']);
                $representational_name = $dataset->getRepresentationalName();
                $primary_container_name = $dataset->getPrimaryContainerName();

                Common::addToUniversalPayload($compound_result, [
                    'data' => [
                        $representational_name => [
                            $data_model->{$primary_container_name} => [
                                'status' => DatasetActionStatusEnum::ERROR->value,
                                'data' => $validation_result,
                            ],
                        ],
                    ],
                ]);

                // Valid data model
            } else {

                $entry_handlers[] = $entry_handler;
                $name = array_key_last($entry_handlers);
                $data_model_collection->set($name, $data_model);
                $models_for_default_case[$name] = $entry_handler->getModel();
            }
        }

        // Unless not all entries were invalid.
        if ($data_model_collection->count() !== 0) {

            $matches_sql_result = $fetch_manager->findMatches(
                $select_handle,
                $data_model_collection,
                models_for_default_case: $models_for_default_case,
                required_when_not_available: false
            );

            // Some or all entries match.
            if ($matches_sql_result) {

                $representational_name = $dataset->getRepresentationalName();

                foreach ($matches_sql_result as $match_data) {

                    /* Capture Recursive Common Table Expressions index. It is a reliable way to determine which entries in the set have in fact been matched. */
                    $rcte_id = $match_data['rcte_id'];
                    unset($match_data['rcte_id']);

                    $assc_model = $data_model_collection->get($rcte_id);
                    $primary_container_name = $dataset->getPrimaryContainerName();

                    Common::addToUniversalPayload($compound_result, [
                        'data' => [
                            $representational_name => [
                                $assc_model->{$primary_container_name} => [
                                    'status' => DatasetActionStatusEnum::FOUND->value,
                                    $primary_container_name => $match_data[$primary_container_name],
                                    'data' => $assc_model->getValues(),
                                ],
                            ],
                        ],
                    ], preserve_keys: true);

                    $data_model_collection->remove($rcte_id);
                    unset($entry_handlers[$rcte_id]);
                }
            }

            // Any data remaining - if all entries were found, this will be empty by now.
            if ($data_model_collection->count() !== 0) {

                foreach ($entry_handlers as $entry_handler) {

                    try {

                        Common::addToUniversalPayload(
                            $compound_result,
                            $entry_handler->updateMain()
                        );

                    } catch (UpdateEntryException $exception) {

                        $data_model = $entry_handler->getModel();

                        #todo: error policy
                        // Error and continue.
                        if (1 === 1) {

                            $representational_name = $dataset->getRepresentationalName();
                            $primary_container_name = $dataset->getPrimaryContainerName();

                            Common::addToUniversalPayload($compound_result, [
                                'data' => [
                                    $representational_name => [
                                        $data_model->{$primary_container_name} => [
                                            'status' => DatasetActionStatusEnum::ERROR->value,
                                            'error_message' => $exception->getMessage(),
                                            'data' => $data_model->getValues(),
                                        ],
                                    ],
                                ],
                            ], preserve_keys: true);

                            // Error and exit.
                        } else {

                            throw new DatasetUpdateException(
                                $exception->getMessage(),
                                code: $exception->getCode(),
                                previous: $exception
                            );
                        }
                    }
                }
            }
        }

        if ($commit && !$inherited_process && !$process->isTerminated()) {
            $process->commit();
        }

        return $compound_result;
    }
}
