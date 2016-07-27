<?php

namespace Eight\PageBundle\Helper;

use Eight\PageBundle\Entity\Block;
use Eight\PageBundle\Entity\Page as PageEntity;

class Page
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function append($subject, $id, $name, $template, $type)
    {
        $block = new Block();
        $parent = $this->container->get('doctrine')->getRepository($subject)->find($id);

        switch ($subject) {
            case 'Eight\PageBundle\Entity\Page':
                $block->setPage($parent);
                break;
            case 'Eight\PageBundle\Entity\Block':
                $block->setBlock($parent);
                break;
            default:
                throw new \Exception("Class {$subject} not supported");
                break;
        }

        $nextSeq = $this->container->get('eight.blocks')->getNextPosition($subject, $id, $type);

        $block->setName($name);
        $block->setLayout($template);
        $block->setType($type);
        $block->setSeq($nextSeq);

        $manager = $this->container->get('doctrine')->getManager();
        $manager->persist($block);
        $manager->flush();
    }

    public function reorder($ids)
    {
        $index = 0;

        foreach ($ids as $id) {
            $next = $this->container->get('eight.blocks')->find($id);
            $next->setSeq($index);
            $index++;
        }

        $this->container->get('doctrine')->getManager()->flush();
    }
}