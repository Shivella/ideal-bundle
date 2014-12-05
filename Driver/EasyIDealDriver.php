<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Driver;

use Usoft\IDealBundle\Exceptions\BankLoaderException;
use Usoft\IDealBundle\Exceptions\IDealExecuteException;
use Usoft\IDealBundle\Model\Bank;
use Usoft\IDealBundle\Providers\EasyIdeal;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class EasyIDealDriver
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class EasyIDealDriver implements IDealInterface
{
    /** @var EasyIdeal */
    private $easyIdeal;

    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->easyIdeal = EasyIdeal::CreateInstance(
            $this->config['id'],
            $this->config['key'],
            $this->config['secret']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBanks()
    {
        $bankApiList = $this->easyIdeal->Ideal_getBanks();

        if ( ! count($bankApiList)) {
            throw new BankLoaderException('Cannot load bank list from API');
        }

        return array_map(function($bank) {
            return new Bank($bank['Id'], $bank['Name']);
        }, $bankApiList);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Bank $bank, $amount, $returnUrl)
    {
        $options = [
            'Amount'=> $amount,
            'Currency'=> 'EUR',
            'Description'=> $this->config['description'],
            'Bank'=> $bank->getId(),
            'Return'=> $returnUrl,
        ];

        try {
            $easyIdealUrl = $this->easyIdeal->Ideal_execute($options);
        } catch (\Exception $message) {
            throw new IDealExecuteException($message->getMessage());
        }

        file_put_contents($this->getFile(), json_encode(array(
            'TransactionID'=> $this->easyIdeal->GetLastTransactionId(),
            'TransactionCode'=> $this->easyIdeal->GetLastTransactionCode(),
        )));

        return new RedirectResponse($easyIdealUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function confirm()
    {
        $key = json_decode(file_get_contents($this->getFile()), true);
        if ( ! $key) {
            return false;
        }
        unlink($this->getFile());

        return $this->easyIdeal->getTransactionStatus([
            'TransactionID'=> $key['TransactionID'],
            'TransactionCode'=> $key['TransactionCode']
        ]);
    }

    /**
     * @return string
     */
    private function getFile()
    {
        return join(DIRECTORY_SEPARATOR, strip(sys_get_temp_dir(), DIRECTORY_SEPARATOR), session_id());
    }
}
