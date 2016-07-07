<?php

namespace Phpro\DoctrineHydrationModule\Hydrator\ODM\MongoDB\Strategy;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Hydrator\NamingStrategy\NamingStrategyInterface;

/**
 * Class PersistentCollection.
 */
class EmbeddedField extends AbstractMongoStrategy
{
    /**
     * @var NamingStrategyInterface
     */
    public $namingStrategy;

    /**
     * @param ObjectManager           $objectManager
     * @param NamingStrategyInterface $namingStrategy
     */
    public function __construct(ObjectManager $objectManager = null, $namingStrategy = null)
    {
        parent::__construct($objectManager);

        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param NamingStrategyInterface $namingStrategy
     */
    public function setNamingStrategy(NamingStrategyInterface $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param object $value
     *
     * @return mixed
     */
    public function extract($value)
    {
        if (!is_object($value)) {
            return $value;
        }
        $hydrator = $this->getDoctrineHydrator();
        if ($this->namingStrategy) {
            $hydrator->setNamingStrategy($this->namingStrategy);
        }

        return $hydrator->extract($value);
    }

    /**
     * @param mixed $value
     *
     * @return array|mixed
     */
    public function hydrate($value)
    {
        $mapping = $this->metadata->fieldMappings[$this->collectionName];
        $targetDocument = $mapping['targetDocument'];

        if (is_object($value)) {
            return $value;
        }

        $instantiator = new Instantiator();
        $object = $instantiator->instantiate($targetDocument);

        $hydrator = $this->getDoctrineHydrator();
        if ($this->namingStrategy) {
            $hydrator->setNamingStrategy($this->namingStrategy);
        }
        $hydrator->hydrate($value, $object);

        return $object;
    }
}
