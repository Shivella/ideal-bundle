<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Driver;

use Mollie_API_Client;
use Usoft\IDealBundle\Model\Bank;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class MollieDriver
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class MollieDriver implements IDealInterface
{
    /** @var array */
    private $config;

    /** @var Mollie_API_Client */
    private $mollie;

    /** @var */

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->mollie = new Mollie_API_Client;
        $this->mollie->setApiKey($config['key']);
    }

    /**
     * @return Bank[]
     */
    public function getBanks()
    {
        return array_map(function($bank) {
            return new Bank($bank->id, $bank->name);
        }, $this->mollie->issuers->all());
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
        $payment = $this->mollie->payments->create(array(
            "amount"      => $amount,
            "description" => $this->config['description'],
            "redirectUrl" => $returnUrl,
            "method"      => 'ideal',
            "issuer"      => $bank->getId(),
        ));

        file_put_contents($this->getFile(), $payment->id);

        return new RedirectResponse($payment->getPaymentUrl());
    }

    /**
     * @return bool
     */
    public function confirm()
    {
        $key = file_get_contents($this->getFile());
        if ( ! $key) {
            return false;
        }
        unlink($this->getFile());

        return $this->mollie->payments->get($key);
    }

    /**
     * @return string
     */
    private function getFile()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . session_id();
    }
}
