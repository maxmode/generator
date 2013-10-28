<?php
namespace Maxmode\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;

use Maxmode\GeneratorBundle\Generator\AdminClass;
use Maxmode\GeneratorBundle\Generator\Translation;
use Maxmode\GeneratorBundle\Doctrine\Entity\Item;

/**
 * ServicesGenerator is responsible for generating services definition
 */
class Services
{
    /**
     * @var Item
     */
    protected $_entityItem;

    /**
     * @var AdminClass
     */
    protected $_classGenerator;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_group = 'default';

    /**
     * @var string
     */
    protected $_servicesFile;

    /**
     * @var string
     */
    protected $_currentCode = '';

    /**
     * @var TimedTwigEngine
     */
    protected $_templating;

    /**
     * @var Translation
     */
    protected $_translator;

    /**
     * @param TimedTwigEngine $twig
     */
    public function setTemplating(TimedTwigEngine $twig)
    {
        $this->_templating = $twig;
    }

    /**
     * @return TimedTwigEngine
     */
    public function getTemplating()
    {
        return $this->_templating;
    }

    /**
     * @return AdminClass
     */
    public function getClassGenerator()
    {
        return $this->_classGenerator;
    }

    /**
     * @param AdminClass $classGenerator
     */
    public function setClassGenerator(AdminClass $classGenerator)
    {
        $this->_classGenerator = $classGenerator;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * @param string $groupName
     */
    public function setGroup($groupName)
    {
        $this->_group = $groupName;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->_filesystem;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem($filesystem)
    {
        $this->_filesystem = $filesystem;
    }

    /**
     * Performs code generation
     */
    public function generate()
    {
        if ($this->getFilesystem()->exists($this->getServicesDefinitionFile())) {
            $this->_currentCode = file_get_contents($this->getServicesDefinitionFile());
        }

        $this->getFilesystem()->dumpFile(
            $this->getServicesDefinitionFile(),
            $this->getGeneratedCode()
        );
    }

    /**
     * @return string
     */
    public function getServicesDefinitionFile()
    {
        if (!$this->_servicesFile) {
            $this->_servicesFile = preg_replace(
                '#/Admin/.+#',
                '/Resources/config/services.xml',
                $this->getClassGenerator()->getAdminFileName()
            );
        }

        return $this->_servicesFile;
    }

    /**
     * @param string $fileName
     */
    public function setServicesDefinitionFile($fileName)
    {
        $this->_servicesFile = $fileName;
    }

    /**
     * @return string
     */
    protected function getAdminServiceId()
    {
        $dotDelimited = str_replace('\\', '.', $this->getClassGenerator()->getAdminClassName());
        $withoutAdmin = preg_replace('#([a-z])Admin#', '\1', $dotDelimited);

        return strtolower($withoutAdmin);
    }

    /**
     * @return string
     */
    protected function getAdminServiceClassId()
    {
        return $this->getAdminServiceId() . '.class';
    }

    /**
     * @return string
     */
    public function getGeneratedCode()
    {
        $parameters = null;
        $services = null;
        if ($this->_currentCode) {
            $xmlElement = simplexml_load_string($this->_currentCode);
            /** @var \SimpleXMLElement $tag */
            foreach ($xmlElement as $tag) {
                if ($tag->getName() == 'parameters') {
                    $matches = array();
                    if (preg_match('#<parameters>(.+)\n\s*</parameters>#ms', $this->_currentCode, $matches)
                        && isset($matches[1])) {
                        $parameters = $matches[1];
                    }
                } elseif ($tag->getName() == 'services') {
                    $matches = array();
                    if (preg_match('#<services>(.+)\n\s*</services>#ms', $this->_currentCode, $matches)
                        && isset($matches[1])) {
                        $services = $matches[1];
                    }
                }
            }
        }

        return $this->getTemplating()->render('MaxmodeGeneratorBundle:Sonata:Admin/services.xml.twig', array(
            'parameters' => $parameters,
            'parameterKey' => $this->getAdminServiceClassId(),
            'adminClass' => $this->getClassGenerator()->getAdminClassName(),
            'services' => $services,
            'serviceId' => $this->getAdminServiceId(),
            'group' => $this->getGroup(),
            'entityClass' => $this->getEntityItem()->getItemClassName(),
            'adminCaption' => $this->_translator->getAdminClassKey($this->getClassGenerator()->getAdminClassName())
        ));
    }

    /**
     * @param Translation $translation
     */
    public function setTranslation(Translation $translation)
    {
        $this->_translator = $translation;
    }

    /**
     * @param \Maxmode\GeneratorBundle\Doctrine\Entity\Item $entityItem
     */
    public function setEntityItem($entityItem)
    {
        $this->_entityItem = $entityItem;
    }

    /**
     * @return \Maxmode\GeneratorBundle\Doctrine\Entity\Item
     */
    public function getEntityItem()
    {
        return $this->_entityItem;
    }
}
