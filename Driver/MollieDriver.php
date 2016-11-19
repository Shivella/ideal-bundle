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
use Symfony\Component\Filesystem\Filesystem;
use Usoft\IDealBundle\Event\PaymentFailedEvent;
use Usoft\IDealBundle\Event\PaymentPlacedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Usoft\IDealBundle\Event\PaymentSuccessEvent;
use Usoft\IDealBundle\Exceptions\RequestTokenNotFoundException;
use Usoft\IDealBundle\PaymentEvents;
use Usoft\IDealBundle\Model\Bank;
use Usoft\IDealBundle\Exceptions\BankLoaderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Usoft\IDealBundle\Exceptions\IDealExecuteException;

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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $description;

    /**
     * @param Mollie_API_Client        $client
     * @param RouterInterface          $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param Filesystem               $filesystem
     * @param string                   $key
     * @param string                   $description
     */
    public function __construct(
        Mollie_API_Client        $client,
        RouterInterface          $router,
        EventDispatcherInterface $eventDispatcher,
        Filesystem               $filesystem,
        $key,
        $description
    )
    {
        $this->description     = $description;
        $this->router          = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->filesystem      = $filesystem;

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

        try {
            $redirectUrl = $this->router->generate($routeName, ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (\Exception $exception) {
            throw new IDealExecuteException('Router not configured or does not exist');
        }

        try {
            $payment = $this->mollie->payments->create([
                'amount'      => $amount,
                'description' => $this->description,
                'redirectUrl' => $redirectUrl,
                'method'      => Mollie_API_Object_Method::IDEAL,
                'issuer'      => $bank->getId(),
            ]);

            $this->filesystem->dumpFile($this->getFilePath($token), $payment->id);
            $this->eventDispatcher->dispatch(PaymentEvents::PAYMENT_PLACED, new PaymentPlacedEvent(new \DateTime('now'), $amount, $payment->id, $payment->status));

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
        $token = $request->get('token');

        if ($token === null) {
            throw new RequestTokenNotFoundException('The token is not passed in the url');
        }

        if (false === $this->filesystem->exists($this->getFilePath($token))) {
            throw new RequestTokenNotFoundException('The token file cannot be found');
        }

        try {
            $key = file_get_contents($this->getFilePath($token));
            $payment = $this->mollie->payments->get($key);

            if ($payment->isPaid() === true) {

                $this->eventDispatcher->dispatch(PaymentEvents::PAYMENT_SUCCESS, new PaymentSuccessEvent(new \DateTime('now'), $payment->amount, $payment->id, $payment->status));
                $this->filesystem->remove($this->getFilePath($token));

                return true;
            }

            $this->eventDispatcher->dispatch(PaymentEvents::PAYMENT_FAILED, new PaymentFailedEvent(new \DateTime('now'), $payment->amount, $payment->id, $payment->status));

            return false;

        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $token
     *
     * @return string
     */
    private function getFilePath($token)
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $token . '.txt';
    }
}
