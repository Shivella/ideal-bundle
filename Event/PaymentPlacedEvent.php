<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Event;

use Mollie_API_Object_Payment;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PaymentPlacedEvent.
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class PaymentPlacedEvent extends Event
{
    /** @var Mollie_API_Object_Payment */
    private $payment;

    /**
     * @param Mollie_API_Object_Payment $payment
     */
    public function __construct(Mollie_API_Object_Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Mollie_API_Object_Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
