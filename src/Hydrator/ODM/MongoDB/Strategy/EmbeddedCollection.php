<?php

namespace Phpro\DoctrineHydrationModule\Hydrator\ODM\MongoDB\Strategy;

use Doctrine\Common\Collections\Collection;
use Doctrine\Instantiator\Instantiator;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Hydrator\NamingStrategy\NamingStrategyInterface;

/**
 * Class PersistentCollection.
 */
class EmbeddedCollection extends AbstractMongoStrategy
{
    /**
     * @var NamingStrategyInterface
     */
    protected $namingStrategy;

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
     * @param mixed $value
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public function extract($value)
    {
        // Embedded Many
        if (!($value instanceof Collection)) {
            throw new \Exception('Embedded collections should be a doctrine collection.');
        }

        $mapping = $this->getClassMetadata()->fieldMappings[$this->getCollectionName()];
        $result = array();
        if ($value) {
            foreach ($value as $index => $object) {
                $hydrator = $this->getDoctrineHydrator();
                if ($this->namingStrategy) {
                    $hydrator->setNamingStrategy($this->namingStrategy);
                }
                $result[$index] = $hydrator->extract($object);

                // Add discrimator field if it can be found.
                if (isset($mapping['discriminatorMap'])) {
                    $discriminatorName = array_search(get_class($object), $mapping['discriminatorMap']);
                    if ($discriminatorName) {
                        $result[$index][$mapping['discriminatorField']] = $discriminatorName;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @return array|Collection|mixed
     */
    public function hydrate($value)
    {
        $mapping = $this->metadata->fieldMappings[$this->collectionName];
        $targetDocument = $mapping['targetDocument'];
        $discriminator = isset($mapping ['discriminatorField']) ? $mapping ['discriminatorField'] : false;
        $discriminatorMap = isset($mapping['discriminatorMap']) ? $mapping['discriminatorMap'] : array();

        $result = array();
        if ($value) {
            foreach ($value as $key => $data) {
                // Use configured discriminator as discriminator class:
                if ($discriminator && is_array($data)) {
                    if (isset($data[$discriminator]) && isset($discriminatorMap[$data[$discriminator]])) {
                        $targetDocument = $discriminatorMap[$data[$discriminator]];
                    }
                }

                $result[$key] = $this->hydrateSingle($targetDocument, $data);
            }
        }

        return $this->hydrateCollection($result);
    }

    /**
     * Note: do not use EmbeddedField strategy. Discriminators will not work.
     *
     * @param $targetDocument
     * @param $document
     *
     * @return object
     */
    protected function hydrateSingle($targetDocument, $document)
    {
        if (is_object($document)) {
            return $document;
        }

        $instantiator = new Instantiator();
        $object = $instantiator->instantiate($targetDocument);

        $hydrator = $this->getDoctrineHydrator();
        if ($this->namingStrategy) {
            $hydrator->setNamingStrategy($this->namingStrategy);
        }
        $hydrator->hydrate($document, $object);

        return $object;
    }
}
