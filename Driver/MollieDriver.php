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
    /** @var string */
    private $description;

    /** @var Mollie_API_Client */
    private $mollie;

    /**
     * @param Mollie_API_Client $client
     * @param string            $key
     * @param string            $description
     */
    public function __construct(Mollie_API_Client $client, $key, $description)
    {
        $this->description = $description;
        $client->setApiKey($key);
        $this->mollie = $client;
    }

    /**
     * @return Bank[]
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
     * @param Bank   $bank
     * @param float  $amount
     * @param string $returnUrl
     *
     * @return RedirectResponse
     *
     * @throws IDealExecuteException
     */
    public function execute(Bank $bank, $amount, $returnUrl)
    {
        try {
            $payment = $this->mollie->payments->create([
                "amount"      => $amount,
                "description" => $this->description,
                "redirectUrl" => $returnUrl,
                "method"      => Mollie_API_Object_Method::IDEAL,
                "issuer"      => $bank->getId(),
            ]);

            file_put_contents($this->getFile(), $payment->id);

            return new RedirectResponse($payment->getPaymentUrl());
        } catch (\Exception $exception) {
            throw new IDealExecuteException($exception->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function confirm()
    {
        try {
            $key = file_get_contents($this->getFile());

            return $this->mollie->payments->get($key)->isPaid();
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return string
     *
     * @throws InvalidPaymentKeyException
     */
    private function getFile()
    {
        try {
            return sys_get_temp_dir() . DIRECTORY_SEPARATOR . session_id();
        } catch (\Exception $exception) {
            throw new InvalidPaymentKeyException('Cannot resolve payment key');
        }
    }
}
