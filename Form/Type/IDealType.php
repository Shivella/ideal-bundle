<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Usoft\IDealBundle\Model\Bank;

/**
 * Class IDealType
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class IDealType extends abstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'banks', 'choice', array(
                'choices' => $this->getBankList($options['data']),
                'required'  => true,
            )
        );
    }

    /**
     * @param Bank[] $banks
     *
     * @return array
     */
    private function getBankList($banks)
    {
        $list = array();
        foreach ($banks as $bank) {
            $list[$bank->getId()] = $bank->getName();
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ideal';
    }
}
