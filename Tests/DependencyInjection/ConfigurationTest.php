<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Usoft\IDealBundle\DependencyInjection\Configuration;
use Usoft\IDealBundle\DependencyInjection\UsoftIDealExtension;

/**
 * Class ConfigurationTest
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    /**
     * @test
     */
    public function testIt_converts_extension_elements_to_extensions()
    {
        $expectedConfiguration = [
            'mollie' => [
                'key' => 'secret-key',
                'description' => 'mollie ideal payment',
            ]
        ];

        $sources = [__DIR__ . '/Fixtures/config.yml'];

        $this->assertProcessedConfigurationEquals($expectedConfiguration, $sources);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtension()
    {
        return new UsoftIDealExtension();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }
}
