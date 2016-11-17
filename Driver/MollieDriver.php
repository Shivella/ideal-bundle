<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Driver;

use Mollie_API_Client;
use Mollie_API_Object_Method;
use Usoft\IDealBundle\Event\PaymentPlacedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Usoft\IDealBundle\IDealPaymentEvents;
use Usoft\IDealBundle\Model\Bank;
use Usoft\IDealBundle\Exceptions\BankLoaderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Usoft\IDealBundle\Exceptions\IDealExecuteException;
use Usoft\IDealBundle\Exceptions\InvalidPaymentKeyException;

/**
 * Class MollieDriver
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class MollieDriver implements IDealInterface
{
    /** @var Mollie_API_Client */
    private $mollie;

    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $description;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param Mollie_API_Client        $client
     * @param RouterInterface          $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $key
     * @param string                   $description
     */
    public function __construct(
        Mollie_API_Client $client,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        $key,
        $description
    )
    {
        $this->description     = $description;
        $this->router          = $router;
        $this->eventDispatcher = $eventDispatcher;

        $client->setApiKey($key);
        $this->mollie = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getBanks()
    {
        $bankList = [];
        try {
            $issuers = $this->mollie->issuers->all();
            foreach ($issuers as $issuer) {
                $bankList[] = new Bank($issuer->id, $issuer->name);
            }
            return $bankList;
        } catch (\Exception $exception) {
            throw new BankLoaderException('Cannot load issuers from mollie');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Bank $bank, $amount, $routeName)
    {
        $token = md5(uniqid('mollie_'));
        $redirectUrl = $this->router->generate($routeName, ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            $payment = $this->mollie->payments->create([
                "amount"      => $amount,
                "description" => $this->description,
                "redirectUrl" => $redirectUrl,
                "method"      => Mollie_API_Object_Method::IDEAL,
                "issuer"      => $bank->getId(),
            ]);

            file_put_contents($this->getFile($token), $payment->id);

            $this->eventDispatcher->dispatch(IDealPaymentEvents::PAYMENT_PLACED, new PaymentPlacedEvent($payment));

            return new RedirectResponse($payment->getPaymentUrl());
        } catch (\Exception $exception) {
            throw new IDealExecuteException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function confirm(Request $request)
    {
        try {
            $key = file_get_contents($this->getFile($request->get('token')));

            return $this->mollie->payments->get($key)->isPaid();
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $token
     *
     * @return string
     *
     * @throws InvalidPaymentKeyException
     */
    private function getFile($token)
    {
        try {
            return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $token;
        } catch (\Exception $exception) {
            throw new InvalidPaymentKeyException('Cannot resolve payment key');
        }
    }
}
