<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Tests\Model;

use Usoft\IDealBundle\Model\Bank;

/**
 * Class BankTest.
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class BankTest extends \PHPUnit_Framework_TestCase
{
    public function testBank()
    {
        $bank = new Bank('identifier', 'awesomebank');

        $this->assertSame('identifier', $bank->getId());
        $this->assertSame('awesomebank', $bank->getName());
    }
}
