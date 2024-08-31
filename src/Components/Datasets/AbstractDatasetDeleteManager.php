<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Datasets\Interfaces\DatasetManagerInterface;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;
use LWP\Components\Datasets\Exceptions\DeleteEntryException;

class AbstractDatasetDeleteManager implements DatasetManagerInterface
{
    use DatasetManagerTrait;
    use DatasetStoreManagerTrait;


    public function __construct(
        public readonly AbstractDatasetStoreHandle $store_handle,
        protected ?DatasetManagementProcessInterface $process = null
    ) {

    }


    //

    public function deleteByPrimaryContainer(int|string $field_value, bool $commit = true): array
    {

        return $this->deleteOne(
            $this->store_handle->dataset->getPrimaryContainerName(),
            $field_value,
            $commit
        );
    }


    //

    public function deleteOne(string $container_name, string|int|float $field_value, bool $commit = true): array
    {

        [
            'process' => $process,
            'inherited_process' => $inherited_process,
        ] = $this->getProcess($commit);
        $dataset = $this->store_handle->dataset;
        $this->store_handle->containers->assertUniqueContainer($container_name);
        $result = [];
        $empty_or_error = false;

        try {

            $dataset_result = $dataset->deleteEntry($container_name, $field_value);

        } catch (EntryNotFoundException) {

            $result['status'] = DatasetActionStatusEnum::EMPTY->value;
            $empty_or_error = true;

        } catch (DeleteEntryException) {

            $result['status'] = DatasetActionStatusEnum::ERROR->value;
            $empty_or_error = true;
        }

        if ($empty_or_error && !$inherited_process) {
            $process->terminate();
        }

        if ($commit && !$inherited_process && !$process->isTerminated()) {
            $process->commit();
        }

        if (isset($dataset_result)) {

            $result['status'] = DatasetActionStatusEnum::SUCCESS->value;
            $result['data'] = [
                'result' => $dataset_result,
            ];
        }

        return $result;
    }


    //

    public static function getActionTypeDefinitionDataArrays(): array
    {

        return [
            'delete' => [
                'data' => [
                    'type' => 'array',
                    'description' => "Data structure",
                ]
            ],
        ];
    }


    // Deletes any number of entries

    public function deleteBy(ConditionGroup $condition_group, bool $commit = true, bool $track_by_primary = false): array
    {

        [
            'process' => $process,
            'inherited_process' => $inherited_process,
        ] = $this->getProcess($commit);
        $dataset = $this->store_handle->dataset;
        $result = [];
        $empty_or_error = false;

        if ($track_by_primary) {

            $primary_container_name = $dataset->getPrimaryContainerName();
            $fetch_manager = $dataset->getFetchManager();
            $select_handle = $dataset->getSelectHandle([$primary_container_name]);
            $data_server_context = $fetch_manager->getByConditionGroup($select_handle, $condition_group);
            $dataset_select_result = $data_server_context->getDatasetResult();
            $condition_group->unsetStringifyReplacer();

            if ($dataset_select_result->count() !== 0) {

                $found = [];
                foreach ($dataset_select_result as $key => $value) {
                    $found[] = $value[$primary_container_name];
                }

                $result['found'] = $found;

            } else {
                $result['status'] = DatasetActionStatusEnum::EMPTY->value;
                $empty_or_error = true;
            }
        }

        if (!isset($result['status'])) {

            try {

                $dataset_result = $dataset->deleteByConditionObject($condition_group);

            } catch (EntryNotFoundException) {

                $result['status'] = DatasetActionStatusEnum::EMPTY->value;
                $empty_or_error = true;

            } catch (DeleteEntryException) {

                $result['status'] = DatasetActionStatusEnum::ERROR->value;
                $empty_or_error = true;
            }
        }

        if ($empty_or_error && !$inherited_process) {
            $process->terminate();
        }

        if ($commit && !$inherited_process && !$process->isTerminated()) {
            $process->commit();
        }

        if (isset($dataset_result)) {

            $result['status'] = DatasetActionStatusEnum::SUCCESS->value;
            $result['data'] = [
                'result' => $dataset_result,
            ];
        }

        // Determine which found entries where actually deleted.
        if (!empty($result['found'])) {

            $data_server_context = $fetch_manager->filterByValues($select_handle, $primary_container_name, $result['found']);
            $dataset_select_result = $data_server_context->getDatasetResult();

            // Some entries have not been deleted.
            if ($dataset_select_result->count() !== 0) {

                $result['failed'] = [];
                foreach ($dataset_select_result as $key => $value) {
                    $result['failed'][] = $value[$primary_container_name];
                }
            }
        }

        return $result;
    }
}
