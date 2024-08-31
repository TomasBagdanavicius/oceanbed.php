<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Common;
use LWP\Common\Conditions\Condition;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Datasets\Interfaces\DatasetManagerInterface;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;
use LWP\Components\Model\ModelCollection;
use LWP\Components\Datasets\Exceptions\CreateEntryException;

abstract class AbstractDatasetCreateManager implements DatasetManagerInterface
{
    use DatasetManagerTrait;
    use DatasetStoreManagerTrait;


    public function __construct(
        public readonly AbstractDatasetStoreHandle $store_handle,
        protected ?DatasetManagementProcessInterface $process = null
    ) {

    }


    //

    abstract public function getCreateEntryHandlerClassName(): string;


    //

    abstract public function manyFromArray(array $data, bool $commit = true): array;


    //

    public static function getActionTypeDefinitionDataArrays(): array
    {

        return [
            'create' => [
                'data' => [
                    'type' => 'array',
                    'required' => true,
                    'description' => "Data structure",
                ]
            ],
        ];
    }


    //

    public function buildRelationalPropertyModel(): RelationalPropertyModel
    {

        #review: do I want to move this to `StoreHandle->getModel()`
        $model = RelationalPropertyModel::fromDefinitionCollectionSet($this->store_handle->getDefinitionCollectionSet());
        $this->store_handle->dataset->setupModelPopulateCallbacks($model);
        $this->store_handle->setupDatasetConstraintsInModel($model);
        $model->setErrorHandlingMode($model::COLLECT_ERRORS);

        return $model;
    }


    //

    public function singleFromArray(array $data, bool $commit = true): array
    {

        ['process' => $process, 'inherited_process' => $inherited_process] = $this->getProcess($commit);
        $create_entry_handler_class_name = $this->getCreateEntryHandlerClassName();
        $entry_handler = new $create_entry_handler_class_name($data, $this, $process);
        $entry_handler->addRelatedData();
        // Validate after adding related data, because it can fill in some required containers
        $validation_result = $entry_handler->getValidationResult();

        // Validation failed.
        if (!empty($validation_result['__index']['error_count'])) {

            // Don't terminate if it's an inherited process, which will handle itself
            if (!$inherited_process) {
                $process->terminate();
            }

            unset($validation_result['__index']);

            return [
                'status' => DatasetActionStatusEnum::ERROR->value,
                'error_name' => 'validation_error',
                'data' => $validation_result,
            ];
        }

        $dataset = $this->store_handle->dataset;
        $model = $entry_handler->getModel();
        $select_handle = $dataset->getPrimaryContainerSelectHandle();
        $match = $dataset->getFetchManager()->findMatch($select_handle, $model, return_array: true);

        // Match not found
        if (!$match) {

            try {

                $create_id = $entry_handler->createMain();

            } catch (CreateEntryException $exception) {

                if (!$inherited_process) {
                    $process->terminate();
                }

                return [
                    'status' => DatasetActionStatusEnum::ERROR->value,
                    'error_message' => $exception->getMessage()
                ];
            }

            if ($commit && !$inherited_process && !$process->isTerminated()) {
                $process->commit();
            }

            // Match found
        } else {

            $data = $match;
            $primary_container_name = $dataset->getPrimaryContainerName();
            $representational_name = $dataset->getRepresentationalName();

            $entry_handler->addToResultPayload([
                'status' => DatasetActionStatusEnum::SUCCESS->value,
                'data' => [
                    $representational_name => [
                        [
                            'status' => DatasetActionStatusEnum::FOUND->value,
                            $primary_container_name => $data[$primary_container_name],
                            'data' => [...$entry_handler->getData(), ...$match],
                        ],
                    ],
                ],
            ]);
        }

        return $entry_handler->getResultPayload();
    }


    //

    protected function manyFromArrayMake(array $data, bool $commit = true, bool $flatten = false): array
    {

        $compound_result
            = $all_data
            = [];
        // All submitted data cases.
        $i
            // Index for valid, non-duplicate case.
            = $distinct_i
            = 0;
        // $i => duplicate condition group for that case
        $duplicate_conditions_set
            // $i => $i of the original case
            = $duplicate_references
            // $distinct_i => $i
            = $distinct_to_data_index_map
            = [];
        $create_entry_handler_class_name = $this->getCreateEntryHandlerClassName();
        $model_collection = new ModelCollection();
        $dataset = $this->store_handle->dataset;
        $select_handle = $dataset->getPrimaryContainerSelectHandle();
        $fetch_manager = $dataset->getFetchManager();
        $representational_name = $dataset->getRepresentationalName();
        $primary_container_name = $dataset->getPrimaryContainerName();
        [
            'process' => $process,
            'inherited_process' => $inherited_process,
        ] = $this->getProcess($commit);

        foreach ($data as $data_array) {

            $entry_handler = new $create_entry_handler_class_name($data_array, $this, $process);
            $entry_handler->addRelatedData();
            $validation_result = $entry_handler->getValidationResult();

            // Invalid data model.
            if (!empty($validation_result['__index']['error_count'])) {

                unset($validation_result['__index']);
                $representational_name = $dataset->getRepresentationalName();

                Common::addToUniversalPayload($compound_result, [
                    'data' => [
                        $representational_name => [
                            $i => [
                                'status' => DatasetActionStatusEnum::ERROR->value,
                                'data' => $validation_result,
                            ],
                        ],
                    ],
                ], preserve_keys: true);

                // Valid data model.
            } else {

                $model = $entry_handler->getModel();
                // Look for duplicates amongst provided data.
                $duplicate_found = false;

                if ($duplicate_conditions_set) {

                    foreach ($duplicate_conditions_set as $ref_i => $condition_group) {

                        if ($condition_group->matchModel($model)) {
                            $duplicate_found = $ref_i;
                            break;
                        }
                    }
                }

                $probe_duplicate_conditions = $dataset->buildDefaultUniqueCase($model);

                if ($probe_duplicate_conditions) {
                    $duplicate_conditions_set[$i] = $probe_duplicate_conditions;
                }

                // Duplicate not found
                if ($duplicate_found === false) {

                    Common::addToUniversalPayload(
                        $compound_result,
                        $entry_handler->getResultPayload()
                    );

                    $all_data[] = $entry_handler->getData();

                    $model_collection->add($model);

                    $distinct_to_data_index_map[$distinct_i] = $i;
                    $distinct_i++;

                    // Duplicate found
                } else {

                    $duplicate_references[$i] = $duplicate_found;
                }
            }

            $i++;
        }

        $matches_result = $fetch_manager->findMatches($select_handle, $model_collection, iterate_as_array: true);

        // Matches found.
        if ($matches_result) {

            foreach ($matches_result as $match_data) {

                /* Capture Recursive Common Table Expressions index. It references the distinct index. */
                $rcte_id = $match_data['rcte_id'];
                unset($match_data['rcte_id']);

                $result_data = array_merge($all_data[$rcte_id], $match_data);

                // Reference to the data set index.
                $data_set_id = $distinct_to_data_index_map[$rcte_id];

                Common::addToUniversalPayload($compound_result, [
                    'data' => [
                        $representational_name => [
                            $data_set_id => [
                                'status' => DatasetActionStatusEnum::FOUND->value,
                                $primary_container_name => $match_data[$primary_container_name],
                                'data' => $result_data,
                            ],
                        ],
                    ],
                ], preserve_keys: true);

                // Checks if there are any duplicates of the found entry.
                if ($duplicate_references && in_array($data_set_id, $duplicate_references)) {

                    // There can be multiple duplicate references to the found entry.
                    foreach ($duplicate_references as $data_set_id_of_duplicate => $reference_index) {

                        if ($reference_index === $data_set_id) {

                            Common::addToUniversalPayload($compound_result, [
                                'data' => [
                                    $representational_name => [
                                        $data_set_id_of_duplicate => [
                                            'status' => DatasetActionStatusEnum::FOUND->value,
                                            $primary_container_name => $match_data[$primary_container_name],
                                            'data' => $result_data,
                                        ],
                                    ],
                                ],
                            ], preserve_keys: true);

                            unset($duplicate_references[$data_set_id_of_duplicate]);
                        }
                    }
                }

                unset($all_data[$rcte_id]);
            }
        }

        $has_successes = false;
        $has_failures = false;

        // Any data remaining - if all entries were found, this will be empty by now.
        if ($all_data) {

            $all_data_count = count($all_data);

            $add_to_compound = function (
                int|string $key,
                int|false $auto_increment = false,
            ) use (
                $all_data,
                &$compound_result,
                $representational_name,
                $distinct_to_data_index_map,
                $primary_container_name,
                &$duplicate_references,
            ) {

                Common::addToUniversalPayload($compound_result, [
                    'data' => [
                        $representational_name => [
                            $distinct_to_data_index_map[$key] => [
                                'status' => DatasetActionStatusEnum::SUCCESS->value,
                                $primary_container_name => (($auto_increment !== false)
                                    ? $auto_increment
                                    : $all_data[$key][$primary_container_name]),
                                'data' => $all_data[$key],
                            ],
                        ],
                    ],
                ], preserve_keys: true);

                // Checks if there are any duplicates of the inserted entry.
                if ($duplicate_references && in_array($distinct_to_data_index_map[$key], $duplicate_references)) {

                    // There can be multiple duplicates references to the inserted entry.
                    foreach ($duplicate_references as $data_set_id_of_duplicate => $reference_index) {

                        if ($reference_index === $distinct_to_data_index_map[$key]) {

                            Common::addToUniversalPayload($compound_result, [
                                'data' => [
                                    $representational_name => [
                                        $data_set_id_of_duplicate => [
                                            'status' => DatasetActionStatusEnum::FOUND->value,
                                            $primary_container_name => (($auto_increment !== false)
                                                ? $auto_increment
                                                : $all_data[$key][$primary_container_name]),
                                            'data' => $all_data[$key],
                                        ],
                                    ],
                                ],
                            ], preserve_keys: true);

                            unset($duplicate_references[$data_set_id_of_duplicate]);
                        }
                    }
                }

            };

            /* Locks ALL loosely unique columns. */

            $condition = new Condition('unique', 'loose');
            $loosely_unique_definition_array = $this->store_handle->getReusableDefinitionCollectionSet()->matchCondition($condition)->toArray();

            if ($loosely_unique_definition_array) {
                $dataset->lockContainersForUpdate(array_keys($loosely_unique_definition_array));
            }

            if ($flatten) {

                /* Group data by matching columns. This is required, because each piece of the request data (entry request) will not necesserally have identical set of keys (containers). */

                $container_groups = [];

                foreach ($all_data as $index => &$data) {

                    $containers = array_keys($data);
                    $group_found = false;

                    if ($loosely_unique_definition_array) {
                        // Gives unique names to loosely unique field values
                        $data = $dataset->solveUniqueContainers($data, $loosely_unique_definition_array);
                    }

                    if ($container_groups) {

                        foreach ($container_groups as &$container_group) {

                            if (
                                // Optimization.
                                count($container_group['containers']) === count($containers)
                                // All values are identical, but their order is not important.
                                && array_diff($container_group['containers'], $containers) == array_diff($containers, $container_group['containers'])
                            ) {

                                $container_group['keys'][] = $index;
                                $container_group['data_flattened'] = array_merge($container_group['data_flattened'], array_values($data));
                                $group_found = true;
                                break;
                            }
                        }
                    }

                    if (!$group_found) {

                        $container_groups[] = [
                            'keys' => [
                                $index,
                            ],
                            'containers' => $containers,
                            'data_flattened' => array_values($data),
                        ];
                    }
                }

            } else {

                foreach ($all_data as $index => &$data) {

                    if ($loosely_unique_definition_array) {
                        // Gives unique names to loosely unique field values
                        $data = $dataset->solveUniqueContainers($data, $loosely_unique_definition_array);
                    }

                    $created = false;

                    if ($process->auto_commitment || $process->commitment) {

                        try {
                            $create_data = $dataset->createEntry($data, product: false);
                            $created = true;
                        } catch (\Exception) {
                            $created = false;
                        }

                    } else {

                        $created = true;
                    }

                    if ($created) {
                        $add_to_compound($index);
                        $has_successes = true;
                    } else {
                        $has_failures = true;
                    }
                }
            }

            if ($flatten) {

                $auto_increment = null;

                foreach ($container_groups as $container_group_data) {

                    try {

                        $dataset->createFromContainerGroupData($container_group_data);

                        if (!$auto_increment) {
                            /* This is more reliable then fetching max key value and then adding 1 to it, because when transactions are involved, there might be an uncommitted transaction before this. */
                            $auto_increment = $this->store_handle->dataset->server->getLastInsertId();
                        }

                    } catch (CreateEntryException $exception) {

                        $has_failures = true;

                        if (!$inherited_process) {
                            $process->terminate();
                        }

                        throw $exception;
                    }

                    $all_data_keys = $container_group_data['keys'];

                    foreach ($all_data_keys as $data_key) {

                        $add_to_compound($data_key, $auto_increment);

                        if ($auto_increment !== false) {
                            $auto_increment++;
                        }
                    }
                }
            }

            // All entries were found
        } else {

            $compound_result['status'] = DatasetActionStatusEnum::FOUND->value;
        }

        if ($has_failures) {
            $compound_result['status'] = DatasetActionStatusEnum::ERROR->value;
        } else {
            $compound_result['status'] = DatasetActionStatusEnum::SUCCESS->value;
        }

        if ($process->commitment && !$inherited_process && !$process->isTerminated()) {
            $process->commit();
        }

        return $compound_result;
    }
}
