<?php

namespace App\DataPersister;

use App\DataPersister\Util\AbstractDataPersister;
use App\DataPersister\Util\DataPersisterObservable;
use App\Entity\Activity;
use App\Entity\BaseEntity;
use App\Entity\ContentNode\ColumnLayout;

class ActivityDataPersister extends AbstractDataPersister {
    public function __construct(
        DataPersisterObservable $dataPersisterObservable
    ) {
        parent::__construct(
            Activity::class,
            $dataPersisterObservable,
        );
    }

    /**
     * @param Activity $data
     */
    public function beforeCreate($data): BaseEntity {
        $data->camp = $data->category->camp;

        if (!isset($data->category?->rootContentNode)) {
            throw new \UnexpectedValueException('Property rootContentNode of provided category is null. Object of type '.ContentNode::class.' expected.');
        }

        $rootContentNode = new ColumnLayout();
        $data->setRootContentNode($rootContentNode);

        // deep copy from category root node
        $rootContentNode->copyFromPrototype($data->category->rootContentNode);

        return $data;
    }
}
