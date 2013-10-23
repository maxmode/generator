<?php
namespace Maxmode\GeneratorBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Check that admin panel works based on generated code
 *
 * @group functional
 */
class TestCase extends WebTestCase
{
    /**
     * @var string
     */
    protected $_dashboardUrl = '/admin/dashboard';

    /**
     * Check that entity is present on dashboard
     *
     * @param string $entityCaption
     *
     * @dataProvider dashboardDataProvider
     */
    public function testDashboard($entityCaption)
    {
        $client = static::createClient();
        $client->request('GET', $this->_dashboardUrl);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains($entityCaption, $client->getResponse()->getContent());
    }

    /**
     * @return array
     */
    public function dashboardDataProvider()
    {
        return array(
            'case1' => array(
                'entityCaption' => 'testvendor.testbundle.admin.carrot',
            )
        );
    }
}
