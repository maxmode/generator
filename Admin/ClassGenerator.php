<?php
namespace Maxmode\GeneratorBundle\Admin;

/**
 * Representation of generated admin class
 *
 * @package Maxmode\GeneratorBundle\Admin
 */
class ClassGenerator
{
    /**
     * @var string
     */
    protected $_entityClass;

    /**
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->_entityClass = $entityClass;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    /**
     * Return admin class name
     *
     * @return string
     */
    public function getAdminClassName()
    {
        return $this->getAdminClassNameByEntityName($this->getEntityClass());
    }

    /**
     * Return admin class name by entity class name
     *
     * @param string $entityClass
     *
     * @return string
     */
    public function getAdminClassNameByEntityName($entityClass)
    {
        return str_replace('\Entity\\', '\Admin\\', $entityClass) . 'Admin';
    }

    /**
     * @return string
     */
    public function getAdminFileName()
    {
        //todo: check on windows
        $r = new \ReflectionClass($this->getEntityClass());

        return str_replace(array(
            DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . $r->getShortName() . '.php'
        ), array(
            DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . $r->getShortName() . 'Admin.php'
        ), $r->getFileName());
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getGeneratedCode()
    {
        $adminClass = $this->getAdminClassName();
        if (class_exists($adminClass)) {
            throw new \Exception('Impossible to generate. Class ' . $adminClass . ' already exists.');
        }
        //create class
        $namespaceParts = explode('\\', $adminClass);
        if (isset($namespaceParts[0]) && $namespaceParts[0] == '') {
            array_shift($namespaceParts);
        }
        $className = array_pop($namespaceParts);
        $namespace = implode('\\', $namespaceParts);

        //todo: use template to store file source
        $classCode = <<<CLASS
<?php
namespace $namespace;

/**
 * Admin user interface for {$this->getEntityClass()}
 */
class $className
{

}

CLASS;
        return $classCode;
    }

}