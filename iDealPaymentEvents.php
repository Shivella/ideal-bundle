<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle;

/**
 * Class IDealPaymentEvents
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
final class IDealPaymentEvents
{
    /**
     * The PAYMENT_PLACED event occurs when the payment process executes.
     *
     * @Event("Usoft\IDealBundle\Event\PaymentCreatedEvent")
     */
    const PAYMENT_PLACED = 'ideal.payment_placed.event';
}
