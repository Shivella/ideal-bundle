<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PaymentEvent
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
abstract class PaymentEvent extends Event
{
    /** @var \DateTime */
    private $dateTime;

    /** @var float */
    private $totalAmount;

    /** @var string */
    private $paymentId;

    /** @var string */
    private $status;

    /**
     * @param \DateTime $dateTime
     * @param float     $totalAmount
     * @param string    $paymentId
     * @param string    $status
     */
    public function __construct(\DateTime $dateTime, $totalAmount, $paymentId, $status)
    {
        $this->dateTime    = $dateTime;
        $this->totalAmount = $totalAmount;
        $this->paymentId   = $paymentId;
        $this->status      = $status;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
