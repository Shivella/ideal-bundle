<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle;

/**
 * Class PaymentEvents
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
final class PaymentEvents
{
    /**
     * The PAYMENT_PLACED event occurs when the payment process executes.
     *
     * @Event("Usoft\IDealBundle\Event\PaymentCreatedEvent")
     */
    const PAYMENT_PLACED = 'ideal.payment_placed.event';

    /**
     * The PAYMENT_SUCCESS event occurs when the payment process succeed.
     *
     * @Event("Usoft\IDealBundle\Event\PaymentSuccessEvent")
     */
    const PAYMENT_SUCCESS = 'ideal.payment_success.event';

    /**
     * The PAYMENT_FAILED event occurs when the payment failed.
     *
     * @Event("Usoft\IDealBundle\Event\PaymentFailedEvent")
     */
    const PAYMENT_FAILED = 'ideal.payment_failed.event';
}
