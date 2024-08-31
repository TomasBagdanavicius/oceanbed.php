<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Relationships\RelationshipPerspective;
use LWP\Components\Datasets\Interfaces\DatasetInterface;

abstract class AbstractContainer
{
    public Relationship $relationship;
    public RelationshipPerspective $perspective;
    public RelationshipPerspective $the_other_perspective;


    //

    public function getRelationship(): ?Relationship
    {

        if (!$this->relationship_name) {
            return null;
        }

        $this->relationship ??= $this->dataset->database->getRelationship($this->relationship_name);

        return $this->relationship;
    }


    //

    public function getPerspective(): ?RelationshipPerspective
    {

        if (!$this->relationship_name) {
            return null;
        }

        if (!isset($this->perspective)) {
            $build_options = $this->getBuildOptions();
            $relationship = $this->getRelationship();
            $this->perspective = $relationship->getPerspectiveFromBuildOptions($build_options, $this->dataset);
        }

        return $this->perspective;
    }


    //

    public function getTheOtherPerspective(): ?RelationshipPerspective
    {

        if (!$this->relationship_name) {
            return null;
        }

        if (!isset($this->the_other_perspective)) {
            $build_options = $this->getBuildOptions();
            $relationship = $this->getRelationship();
            $this->the_other_perspective = $relationship->getTheOtherPerspectiveFromBuildOptions($build_options, $this->dataset);
        }

        return $this->the_other_perspective;
    }


    //

    public function getTheOtherDataset(): ?DatasetInterface
    {

        if (!$this->relationship_name) {
            return null;
        }

        return $this->getTheOtherPerspective()->dataset;
    }
}
