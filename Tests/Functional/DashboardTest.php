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
class DashboardTest extends WebTestCase
{
    /**
     * @var string
     */
    protected $_dashboardUrl = '/admin/dashboard';

    /**
     * Check that entity is present on dashboard
     *
     * @param array $entityCaptions
     * @param array $links
     *
     * @dataProvider dashboardDataProvider
     */
    public function testDashboard($entityCaptions, $links)
    {
        $client = static::createClient();
        $page = $client->request('GET', $this->_dashboardUrl);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        foreach ($entityCaptions as $caption) {
            $this->assertContains($caption, $client->getResponse()->getContent());
        }

        foreach ($links as $expectedLink) {
            $this->assertGreaterThan(0, $page->filter('a:contains("' . $expectedLink['name'] . '")')->count());
            $link = $page->selectLink($expectedLink['name'])->link();
            $this->assertEquals($expectedLink['url'], $link->getUri());
        }
    }

    /**
     * @return array
     */
    public function dashboardDataProvider()
    {
        return array(
            'case1' => array(
                'entityCaptions' => array(
                    'carrot' => 'test_vendor.test_bundle.admin.carrot',
                ),
                'links' => array(
                    'carrot list' => array(
                        'name' => 'link_list',
                        'url' => 'http://localhost/admin/testvendor/test/carrot/list',
                    ),
                    'carrot add' => array(
                        'name' => 'link_add',
                        'url' => 'http://localhost/admin/testvendor/test/carrot/create',
                    )
                ),
            )
        );
    }
}
