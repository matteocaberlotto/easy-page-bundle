<?php

namespace Eight\PageBundle\Provider;

use Eight\PageBundle\Entity\Page;
use Doctrine\Common\Collections\ArrayCollection;

class Widget
{
    protected $container;

    protected $_widgets = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function get($name)
    {
        if (isset($this->_widgets[$name])) {
            return $this->_widgets[$name];
        }

        throw new \Exception("Widget \"{$name}\" has not been defined or has not been added properly.");
    }

    public function addWidget($widget)
    {
        $this->_widgets[$widget->getName()] = $widget;
    }

    public function all()
    {
        return $this->_widgets;
    }

    public function getDefaultVariables($name)
    {
        $vars = array();

        foreach ($this->_widgets[$name]->getVars() as $name => $config) {

            if (!isset($config['type'])) {
                $config['type'] = 'label';
            }

            $vars[$name] = $this->container->get('variable.provider')->get($config['type'])->getDefaultValue($config);
        }

        return $vars;
    }

    public function getVariables($name)
    {
        return $this->_widgets[$name]->getVars();
    }

    public function getContentType($widget_name, $variable_name)
    {
        $widget = $this->get($widget_name);

        foreach ($this->getVariables($widget_name) as $name => $variable) {
            if ($name == $variable_name) {
                return isset($variable['type']) ? $variable['type'] : '';
            }
        }

        return false;
    }

    public function editable($block)
    {
        foreach ($this->_widgets[$block->getName()]->getVars() as $name => $config) {
            if (!isset($config['edit']) || $config['edit'] !== false) {
                return true;
            }
        }

        return false;
    }
}