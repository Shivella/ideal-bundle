<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Tests\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Usoft\IDealBundle\Driver\IDealInterface;
use Usoft\IDealBundle\Form\Type\IDealType;
use Usoft\IDealBundle\Model\Bank;

/**
 * Class IDealTypeTest
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class IDealTypeTest extends TypeTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|IDealInterface */
    private $mollieDriver;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Bank */
    private $bank;

    protected function setUp()
    {
        $this->mollieDriver = $this->createMollieDriverMock();
        $this->bank = $this->createBankMock();

        parent::setUp();
    }


    protected function getExtensions()
    {
        return [
            new PreloadedExtension([new IDealType($this->mollieDriver)], []),
        ];
    }


    public function testSubmitValidData()
    {
        $formData = ['bank' => 'test-bank'];

        $this->mollieDriver->expects($this->once())
            ->method('getBanks')
            ->willReturn([$this->bank]);

        $form = $this->factory->create(IDealType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals([], $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IDealInterface
     */
    private function createMollieDriverMock()
    {
        return $this->getMock(IDealInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Bank
     */
    private function createBankMock()
    {
        return $this->getMockBuilder(Bank::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
