<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Driver;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Usoft\IDealBundle\Model\Bank;

/**
 * Interface IDealInterface
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
interface IDealInterface
{
    /**
     * @return Bank[]
     */
    public function getBanks();

    /**
     * @param Bank   $bank
     * @param float  $amount
     * @param string $returnUrl
     *
     * @return RedirectResponse
     */
    public function execute(Bank $bank, $amount, $returnUrl);

    /**
     * @return bool
     */
    public function confirm();
}
