<?php

namespace Eight\PageBundle\Widget;

class Footer extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'label',
            'html_classes',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:footer.html.twig';
    }

    public function getName()
    {
        return 'footer';
    }
}