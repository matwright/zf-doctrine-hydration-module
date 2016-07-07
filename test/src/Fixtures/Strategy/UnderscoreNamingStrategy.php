<?php

namespace PhproTest\DoctrineHydrationModule\Fixtures\Strategy;

use Zend\Hydrator\NamingStrategy\NamingStrategyInterface;

class UnderscoreNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Zend\Hydrator\NamingStrategy\NamingStrategyInterface::hydrate()
     */
    public function hydrate($name)
    {
        return trim($name, '_');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Zend\Hydrator\NamingStrategy\NamingStrategyInterface::extract()
     */
    public function extract($name)
    {
        return '_'.$name.'_';
    }
}
