<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Driver;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param string $routeName
     *
     * @return RedirectResponse
     */
    public function execute(Bank $bank, $amount, $routeName);

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function confirm(Request $request);
}
