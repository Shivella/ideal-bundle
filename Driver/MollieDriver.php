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
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @param string $key
     * @param string $description
     */
    public function __construct($key, $description)
    {
        $this->description = $description;

        $this->mollie = new Mollie_API_Client;
        $this->mollie->setApiKey($key);
    }

    /**
     * @return Bank[]
     */
    public function getBanks()
    {
        $bankList = [];

        foreach ($this->mollie->issuers->all() as $issuer) {
            $bankList[] = new Bank($issuer->id, $issuer->name);
        }

        return $bankList;
    }

    /**
     * @param Bank   $bank
     * @param float  $amount
     * @param string $returnUrl
     *
     * @return RedirectResponse
     */
    public function execute(Bank $bank, $amount, $returnUrl)
    {
        $payment = $this->mollie->payments->create([
            "amount"      => $amount,
            "description" => $this->description,
            "redirectUrl" => $returnUrl,
            "method" => Mollie_API_Object_Method::IDEAL,
            "issuer" => $bank->getId(),
        ]);

        file_put_contents($this->getFile(), $payment->id);

        return new RedirectResponse($payment->getPaymentUrl());
    }

    /**
     * @return bool
     */
    public function confirm()
    {
        try {
            $key = file_get_contents($this->getFile());
            if (false === $key) {
                return false;
            }

            return $this->mollie->payments->get($key)->isPaid();
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return string
     */
    private function getFile()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . session_id();
    }
}
