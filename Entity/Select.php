<?php
namespace Maxmode\GeneratorBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Maxmode\GeneratorBundle\Admin\ClassGenerator;

class Select
{
    /**
     * @var EntityManager
     */
    protected $_entityManager;

    /**
     * @var ClassGenerator
     */
    protected $_classGenerator;

    /**
     * @param $entityClass
     *
     * @throws \Exception
     */

    /**
     * @param string $entityClass
     *
     * @return bool
     * @throws \Exception
     */
    public function validateClass($entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \Exception('Specified class "' . $entityClass . '" does not found');
        }
        if ($this->getEntityHasAdmin($entityClass)) {
            throw new \Exception('Specified class "' . $entityClass . '" already has admin class "'
                . $this->_classGenerator->getAdminClassNameByEntityName($entityClass) . '"');
        }
        $this->_entityManager->getClassMetadata($entityClass);

        return true;
    }

    /**
     * Check if entity class already has admin class
     *
     * @param string $entityClass
     *
     * @return bool
     */
    public function getEntityHasAdmin($entityClass)
    {
        return class_exists($this->_classGenerator->getAdminClassNameByEntityName($entityClass));
    }

    /**
     * @return array
     */
    public function getEntityList()
    {
        $entityList = array();
        /** @var ClassMetadata $entityMetadata */
        foreach ($this->_entityManager->getMetadataFactory()->getAllMetadata() as $entityMetadata) {
            $entityClass = $entityMetadata->getName();
            if (!$this->getEntityHasAdmin($entityClass)) {
                $entityList[] = $entityClass;
            }
        }
        //todo: throw exception if there is no available entities in the system

        return $entityList;
    }

    /**
     * @param string $entityClass
     *
     * @return array
     */
    public function getEntityFields($entityClass)
    {
        return $this->_entityManager->getClassMetadata($entityClass)->getFieldNames();
    }

    /**
     * @return string|null
     */
    public function getFirstEntity()
    {
        $entityList = $this->getEntityList();
        if (isset($entityList[0])) {
            return $entityList[0];
        }
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    /**
     * @param ClassGenerator $classGenerator
     */
    public function setClassGenerator(ClassGenerator $classGenerator)
    {
        $this->_classGenerator = $classGenerator;
    }
}
