<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Usoft\IDealBundle\Model\Bank;

/**
 * Class IDealType.
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
            'banks', ChoiceType::class, [
                'choices'   => $this->getBankList($options['data']),
                'required'  => true,
            ]
        );

        $builder->add('save', SubmitType::class, []);
    }

    /**
     * @param Bank[] $banks
     *
     * @return array
     */
    private function getBankList($banks)
    {
        $list = [];
        foreach ($banks as $bank) {
            $list[$bank->getName()] = $bank->getId();
        }

        return $list;
    }
}
