<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Activity;
use App\Entity\ContentNode\ColumnLayout;
use App\Entity\ContentType;
use App\State\Util\AbstractPersistProcessor;
use App\Util\EntityMap;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template-extends AbstractPersistProcessor<Activity>
 */
class ActivityCreateProcessor extends AbstractPersistProcessor {
    public function __construct(
        ProcessorInterface $decorated,
        private EntityManagerInterface $em
    ) {
        parent::__construct($decorated);
    }

    /**
     * @param Activity $data
     */
    public function onBefore($data, Operation $operation, array $uriVariables = [], array $context = []): Activity {
        if (!isset($data->category?->rootContentNode)) {
            throw new \UnexpectedValueException('Property rootContentNode of provided category is null. Object of type '.ColumnLayout::class.' expected.');
        }
        if (!is_a($data->category->rootContentNode, ColumnLayout::class)) {
            throw new \UnexpectedValueException('Property rootContentNode of provided category is of wrong type. Object of type '.ColumnLayout::class.' expected.');
        }

        $data->camp = $data->category->camp;
        $rootContentNodePrototype = $data->category->rootContentNode;

        if (isset($data->copyActivitySource)) {
            // CopyActivity Source is set -> copy it's content (rootContentNode)
            $rootContentNodePrototype = $data->copyActivitySource->rootContentNode;
        }

        $rootContentNode = new ColumnLayout();
        $rootContentNode->contentType = $this->em
            ->getRepository(ContentType::class)
            ->findOneBy(['name' => 'ColumnLayout'])
        ;
        $data->setRootContentNode($rootContentNode);

        // deep copy from category root node
        $entityMap = new EntityMap();
        $rootContentNode->copyFromPrototype($rootContentNodePrototype, $entityMap);

        return $data;
    }
}
