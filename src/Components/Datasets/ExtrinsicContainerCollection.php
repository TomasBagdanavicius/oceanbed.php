<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Collectable;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Common\Enums\ReadWriteModeEnum;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class ExtrinsicContainerCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct(
            $data,
            element_filter: function (
                mixed $element,
                null|int|string $key
            ): true {

                if (!($element instanceof ExtrinsicContainer)) {
                    throw new InvalidMemberException(sprintf(
                        "Collection %s accepts elements of class %s",
                        self::class,
                        ExtrinsicContainer::class
                    ));
                }

                return true;
            },
            obtain_name_filter: function (mixed $element): ?string {

                return $element->container_name;
            }
        );
    }


    //

    public function createNewMember(array $params = []): Collectable
    {


    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }


    //

    public function findByBuildOptions(array $build_options, DatasetInterface $dataset, ReadWriteModeEnum $type = ReadWriteModeEnum::READ): ?ExtrinsicContainer
    {

        if ($this->count() === 0) {
            return null;
        }

        $condition_group = new ConditionGroup();
        $condition_group->add(new Condition('relationship', $build_options['relationship']));
        $condition_group->add(new Condition('extrinsic_container_name', $build_options['property_name']));
        $condition_group->add(new Condition('dataset_association_type', strtolower($type->name)));

        $filtered_collection = $this->matchConditionGroup($condition_group);

        if ($filtered_collection->count() === 0) {
            return null;
        }

        foreach ($filtered_collection as $extrinsic_container) {
            $extrinsic_container_build_options = $extrinsic_container->getBuildOptions();
            // Default build options
            if ($extrinsic_container_build_options == $build_options) {
                return $extrinsic_container;
            }
        }

        $database = $this->getFirst()->dataset->database;
        $my_relationship = $database->getRelationship($build_options['relationship']);
        $my_has_perspective = isset($build_options['perspective']);
        $my_has_which = isset($build_options['which']);
        $my_perspective_position = $my_which = null;

        foreach ($filtered_collection as $extrinsic_container) {

            $has_perspective_position = ($extrinsic_container->perspective_position !== null);
            $perspective_match = false;

            if (!$has_perspective_position && !$my_has_perspective) {
                $perspective_match = true;
            } elseif ($my_has_perspective && $has_perspective_position) {
                $perspective_match = ($build_options['perspective'] == $extrinsic_container->perspective_position);
            } else {
                $my_perspective_position ??= $build_options['perspective'] ?? $my_relationship->getPerspectiveFromBuildOptions($build_options, $dataset)->position;
                $perspective_match = (
                    ($has_perspective_position && $extrinsic_container->perspective_position === $my_perspective_position)
                    || $extrinsic_container->getPerspective()->position === $my_perspective_position
                );
            }

            if (!$perspective_match) {
                continue;
            }

            $has_which = ($extrinsic_container->which !== null);
            $which_match = false;

            if (!$has_which && !$my_has_which) {
                $which_match = true;
            } elseif ($my_has_which && $has_which) {
                $which_match = ($build_options['which'] == $extrinsic_container->which);
            } else {
                $my_which ??= $build_options['which'] ?? $my_relationship->getTheOtherPerspectiveFromBuildOptions($build_options, $dataset)->position;
                $which_match = (
                    ($has_which && $extrinsic_container->which === $my_which)
                    || $extrinsic_container->getTheOtherPerspective()->position === $my_which
                );
            }

            if ($perspective_match && $which_match) {
                return $extrinsic_container;
            }
        }

        return null;
    }
}
