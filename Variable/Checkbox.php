<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;

class Checkbox extends AbstractVariable
{
    public function buildForm($builder, $name, $config, $variable = null)
    {
        $builder
            ->add($name, 'checkbox', array(
                'data' => $variable ? ($variable->getContent() ? true : false) : false,
                ))
            ;
    }
    public function getName()
    {
        return 'checkbox';
    }
}
