<?php
namespace Maxmode\GeneratorBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Maxmode\GeneratorBundle\Generator\AdminClass as ClassGenerator;

/**
 * Class Seeking for entities
 */
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
     * @param string $entityClass
     *
     * @return string entity class on success
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

        return $entityClass;
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
     * @throws \Exception
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
        if (!$entityList) {
            throw new \Exception("There is no doctrine entities without admin class in the system");
        }

        return $entityList;
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
