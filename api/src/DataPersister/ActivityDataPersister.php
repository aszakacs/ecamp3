<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Activity;
use App\Entity\ContentNode;
use App\Entity\ContentNode\ColumnLayout;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDataPersister implements ContextAwareDataPersisterInterface {
    public function __construct(private ContextAwareDataPersisterInterface $dataPersister, private EntityManagerInterface $entityManager) {
    }

    public function supports($data, array $context = []): bool {
        return ($data instanceof Activity) && $this->dataPersister->supports($data, $context);
    }

    /**
     * @param Activity $data
     *
     * @return object|void
     */
    public function persist($data, array $context = []) {
        $data->camp = $data->category->camp;

        if ('post' === ($context['collection_operation_name'] ?? null)) {
            if (!isset($data->category?->rootContentNode)) {
                throw new \UnexpectedValueException('Property rootContentNode of provided category is null. Object of type '.ContentNode::class.' expected.');
            }

            $rootContentNode = new ColumnLayout();
            $data->setRootContentNode($rootContentNode);

            // deep copy from category root node
            $rootContentNode->copyFromPrototype($data->category->rootContentNode);
        }

        return $this->dataPersister->persist($data, $context);
    }

    public function remove($data, array $context = []) {
        return $this->dataPersister->remove($data, $context);
    }
}
