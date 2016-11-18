<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Tests\Driver;

use Mollie_API_Client;
use Mollie_API_Exception;
use Mollie_API_Object_Issuer;
use Mollie_API_Object_Method;
use Mollie_API_Resource_Issuers;
use Mollie_API_Resource_Payments;
use Mollie_API_Object_Payment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Usoft\IDealBundle\Driver\MollieDriver;
use Usoft\IDealBundle\PaymentEvents;
use Usoft\IDealBundle\Model\Bank;

/**
 * Class MollieDriverTest
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class MollieDriverTest extends \PHPUnit_Framework_TestCase
{
    /** @var MollieDriver */
    private $mollieDriver;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Client */
    private $mollieAPIClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Bank */
    private $bank;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Resource_Payments */
    private $payments;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Resource_Issuers */
    private $issuers;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Object_Payment */
    private $paymentObject;

    /** @var \PHPUnit_Framework_MockObject_MockObject|RouterInterface */
    private $router;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Request */
    private $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Filesystem */
    private $filesystem;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Filesystem */
    private $issuer;

    public function setUp()
    {
        $this->mollieAPIClient = $this->createMollie_API_ClientMock();
        $this->payments = $this->createMollie_API_Resource_PaymentsMock();
        $this->issuers = $this->createMollie_API_Resource_IssuersMock();
        $this->paymentObject = $this->createMollie_API_Object_PaymentMock();
        $this->router = $this->createRouterInterfaceMock();
        $this->eventDispatcher = $this->createEventDispatcherInterfaceMock();
        $this->bank = $this->createBankMock();
        $this->request = $this->createRequestMock();
        $this->filesystem = $this->createFilesystemMock();
        $this->issuer = $this->createMollie_API_Object_IssuerMock();

        $this->mollieDriver = new MollieDriver($this->mollieAPIClient, $this->router, $this->eventDispatcher, $this->filesystem, 'test_secret_key', 'awesome test');
    }

    public function testGetBanks()
    {
        $this->mollieAPIClient->issuers = $this->issuers;

        $this->issuers->expects($this->once())
            ->method('all')
            ->willReturn([$this->issuer]);

        $this->assertTrue(is_array($this->mollieDriver->getBanks()));
    }

    /**
     * @expectedException \Usoft\IDealBundle\Exceptions\BankLoaderException
     */
    public function testGetBanksException()
    {
        $this->mollieAPIClient->issuers = $this->issuers;

        $this->issuers->expects($this->once())
            ->method('all')
            ->willThrowException(new \Exception());

        $this->mollieDriver->getBanks();
    }

    public function testExecute()
    {
        $this->router->expects($this->once())
            ->method('generate')
            ->with('confirm_route')
            ->willReturn('http://www.awesome-app.com/foo/bar?token=foobar');

        $this->mollieAPIClient->payments = $this->payments;

        $this->bank->expects($this->once())
            ->method('getId')
            ->willReturn(666);

        $this->paymentObject->expects($this->once())
            ->method('getPaymentUrl')
            ->willReturn('https://www.mollie.nl/pay-the-money-bitch');

        $this->payments->expects($this->once())->method('create')->with(
            [
                "amount"      => 12.43,
                "description" => 'awesome test',
                "redirectUrl" => 'http://www.awesome-app.com/foo/bar?token=foobar',
                "method"      => Mollie_API_Object_Method::IDEAL,
                "issuer"      => 666,
            ]
        )->willReturn($this->paymentObject);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(PaymentEvents::PAYMENT_PLACED);

        $this->assertInstanceOf(
            RedirectResponse::class,
            $this->mollieDriver->execute($this->bank, 12.43, 'confirm_route')
        );
    }

    /**
     * @expectedException \Usoft\IDealBundle\Exceptions\IDealExecuteException
     * @expectedExceptionMessage Router not configured or does not exist
     */
    public function testExecuteRouteNotFoundException()
    {
        $this->router->expects($this->once())
            ->method('generate')
            ->with('confirm_route')
            ->willThrowException(new RouteNotFoundException);

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->mollieDriver->execute($this->bank, 12.43, 'confirm_route');
    }

    /**
     * @expectedException \Usoft\IDealBundle\Exceptions\IDealExecuteException
     *
     * @throws \Usoft\IDealBundle\Exceptions\IDealExecuteException
     */
    public function testExecuteException()
    {
        $this->router->expects($this->once())
            ->method('generate')
            ->with('confirm_route')
            ->willReturn('http://www.awesome-app.com/foo/bar?token=foobar');

        $this->mollieAPIClient->payments = $this->payments;

        $this->bank->expects($this->once())
            ->method('getId')
            ->willReturn(666);

        $this->payments->expects($this->once())->method('create')->with(
            [
                "amount"      => 12.43,
                "description" => 'awesome test',
                "redirectUrl" => 'http://www.awesome-app.com/foo/bar?token=foobar',
                "method"      => Mollie_API_Object_Method::IDEAL,
                "issuer"      => 666,
            ]
        )->willThrowException(new \Exception());

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->mollieDriver->execute($this->bank, 12.43, 'confirm_route');
    }

    public function testConfirm()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->with('token')
            ->willReturn('awesome-token');

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awesome-token' . '.txt')
            ->willReturn(true);

        $this->mollieAPIClient->payments = $this->payments;

        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awesome-token' . '.txt', 'test');

        $this->payments->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn($this->paymentObject);

        $this->paymentObject->expects($this->once())
            ->method('isPaid')
            ->willReturn(true);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(PaymentEvents::PAYMENT_SUCCESS);

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awesome-token' . '.txt');

        $this->assertTrue($this->mollieDriver->confirm($this->request));
    }

    /**
     * @expectedException \Usoft\IDealBundle\Exceptions\RequestTokenNotFoundException
     */
    public function testConfirmTokenNotSet()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->with('token')
            ->willReturn(null);

        $this->filesystem->expects($this->never())
            ->method('exists');

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->mollieDriver->confirm($this->request);
    }

    /**
     * @expectedException \Usoft\IDealBundle\Exceptions\RequestTokenNotFoundException
     */
    public function testConfirmFileNotExists()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->with('token')
            ->willReturn('test');

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->mollieDriver->confirm($this->request);
    }

    public function testConfirmFalse()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->with('token')
            ->willReturn('awesome-token');

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awesome-token' . '.txt')
            ->willReturn(true);

        $this->mollieAPIClient->payments = $this->payments;

        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awesome-token' . '.txt', 'test');

        $this->payments->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn($this->paymentObject);

        $this->paymentObject->expects($this->once())
            ->method('isPaid')
            ->willReturn(false);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(PaymentEvents::PAYMENT_FAILED);

        $this->filesystem->expects($this->never())
            ->method('remove');

        $this->assertFalse($this->mollieDriver->confirm($this->request));
    }


    public function testConfirmException()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->with('token')
            ->willReturn('awesome-token');

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awesome-token' . '.txt')
            ->willReturn(true);

        $this->mollieAPIClient->payments = $this->payments;

        $this->payments->expects($this->once())
            ->method('get')
            ->with('test')
            ->willThrowException(new Mollie_API_Exception());

        $this->assertFalse($this->mollieDriver->confirm($this->request));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Client
     */
    private function createMollie_API_ClientMock()
    {
        return $this->getMockBuilder(Mollie_API_Client::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Resource_Issuers
     */
    private function createMollie_API_Resource_IssuersMock()
    {
        return $this->getMockBuilder(Mollie_API_Resource_Issuers::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Resource_Payments
     */
    private function createMollie_API_Resource_PaymentsMock()
    {
        return $this->getMockBuilder(Mollie_API_Resource_Payments::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Object_Payment
     */
    private function createMollie_API_Object_PaymentMock()
    {
        return $this->getMock(Mollie_API_Object_Payment::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    private function createRouterInterfaceMock()
    {
        return $this->getMock(RouterInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private function createEventDispatcherInterfaceMock()
    {
        return $this->getMock(EventDispatcherInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function createRequestMock()
    {
        return $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    private function createFilesystemMock()
    {
        return $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Mollie_API_Object_Issuer
     */
    private function createMollie_API_Object_IssuerMock()
    {
        return $this->getMock(Mollie_API_Object_Issuer::class);
    }

}
