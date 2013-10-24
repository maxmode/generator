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
class EditTest extends WebTestCase
{
    /**
     * Check that "new" page rendered correctly
     *
     * @param string $url
     * @param string $caption
     * @param array  $fields
     *
     * @dataProvider newFormDataProvider
     */
    public function testNewPage($url, $caption, $fields)
    {
        $client = static::createClient();
        $page = $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $page->filter('a:contains("' . $caption . '")')->count(),
            'Page does not contain link with "' . $caption . '"');

        foreach ($fields as $expectedField) {
            $this->assertGreaterThan(0, $page->filter('input[name*="' . $expectedField['id'] . '"]')->count(),
                'Input with name "' . $expectedField['id'] . '" was not found');
            $this->assertGreaterThan(0, $page->filter('label:contains("' . $expectedField['caption'] . '")')->count(),
                'Label contains "' . $expectedField['caption'] . '" was not found');
        }

    }

    /**
     * @return array
     */
    public function newFormDataProvider()
    {
        return array(
            'carrot' => array(
                'url' => 'http://localhost/admin/testvendor/test/carrot/create',
                'caption' => 'test_vendor.test_bundle.admin.carrot',
                'fields' => array(
                    'id' => array(
                        'id' => 'id',
                        'caption' => 'test_vendor.test_bundle.admin.carrot.fields.id',
                    ),
                    'length' => array(
                        'id' => 'length',
                        'caption' => 'test_vendor.test_bundle.admin.carrot.fields.length',
                    ),
                    'color' => array(
                        'id' => 'color',
                        'caption' => 'test_vendor.test_bundle.admin.carrot.fields.color',
                    )
                ),
            )
        );
    }
}
