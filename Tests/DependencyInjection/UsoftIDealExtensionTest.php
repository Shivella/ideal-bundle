<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Tests\DependencyInjection;

use Usoft\IDealBundle\DependencyInjection\UsoftIDealExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * Class UsoftIDealExtensionTest
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class UsoftIDealExtensionTest extends AbstractExtensionTestCase
{
    public function testAfterLoadingTheCorrectParameterHasBeenSet()
    {
        $this->load(array('mollie' => array('key' => 'secret-key')));

        $this->assertContainerBuilderHasParameter('mollie_key', 'secret-key');
        $this->assertContainerBuilderHasParameter('mollie_description', 'mollie ideal payment');
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new UsoftIDealExtension(),
        ];
    }
}
