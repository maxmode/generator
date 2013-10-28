<?php

namespace Maxmode\GeneratorBundle\Doctrine\Entity;

use Doctrine\ORM\EntityManager;

/**
 * Representation of single Doctrine entity
 */
class Item
{
    /**
     * @var string
     */
    private $itemClass;

    /**
     * @var EntityManager
     */
    private $_entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_entityManager;
    }

    /**
     * @param string $itemClass
     */
    public function setItemClassName($itemClass)
    {
        $this->itemClass = $itemClass;
    }

    /**
     * @return string
     */
    public function getItemClassName()
    {
        return $this->itemClass;
    }

    /**
     * List of field names
     *
     * @return array
     */
    public function getEntityFields()
    {
        return $this->getEntityManager()->getClassMetadata($this->getItemClassName())->getFieldNames();
    }

    /**
     * Type of field
     *
     * @param string $fieldName
     *
     * @return \Doctrine\DBAL\Types\Type|string
     */
    public function getFieldType($fieldName)
    {
        return $this->getEntityManager()->getClassMetadata($this->getItemClassName())->getTypeOfField($fieldName);
    }
}
