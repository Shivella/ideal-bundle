<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Tests\Event;

use Usoft\IDealBundle\Event\PaymentFailedEvent;

/**
 * Class PaymentFailedEvent
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class PaymentFailedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testPaymentFailedEvent()
    {
        $date = new \DateTime('01-01-2015 01:00:10');
        $event = new PaymentFailedEvent($date, 12.99, 'test-id', 'status-failed');

        $this->assertSame($date, $event->getDateTime());
        $this->assertSame(12.99, $event->getTotalAmount());
        $this->assertSame('test-id', $event->getPaymentId());
        $this->assertSame('status-failed', $event->getStatus());
    }
}
