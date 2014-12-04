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
                'choices' => array_map(function($bank) {
                    /** @var Bank $bank */

                    return array($bank->getId() => $bank->getName());
                }, $options['data']),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ideal';
    }
}
