<?php

namespace Eight\PageBundle\Widget;

class Label extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'label' => array()
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:label.html.twig';
    }

    public function getName()
    {
        return 'label';
    }
}