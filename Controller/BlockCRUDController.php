<?php

namespace Eight\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Eight\PageBundle\Entity\Content;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlockCRUDController extends CRUDController
{
    /**
     * Upate page last updated for caching invalidation
     */
    protected function updatePage($page)
    {
        $page->setUpdatedAtValue();
        $this->container->get('doctrine')->getManager()->flush();
    }

    public function appendAction(Request $request)
    {
        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $static = $request->get('is_static') === 'true' ? true : false;

        if (!$static) {
            $subject = $this->get('doctrine')->getRepository($request->get('subject'))->find($request->get('id'));
        } else {
            $subject = $page;
        }

        if (false === $this->admin->isGranted('EDIT', $subject)) {
            throw new AccessDeniedException();
        }

        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);

        $block = $this->get('helper.page')->append($request->get('subject'), $request->get('id'), $request->get('name'), $request->get('slot_label'), $static);

        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true),
            'form' => $this->get('page.renderer')->appendForm($block, false), // recursive form false
            ));
    }

    public function removeAction(Request $request)
    {
        $block = $this->get('eight.blocks')->find($request->get('block_id'));

        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $request->get('block_id')));
        }

        if (false === $this->admin->isGranted('EDIT', $block)) {
            throw new AccessDeniedException();
        }

        $manager = $this->get('doctrine')->getManager();
        $manager->remove($block);
        $manager->flush();

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK'
            ));
    }

    public function enableAction(Request $request)
    {
        $block = $this->get('eight.blocks')->find($request->get('block_id'));

        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $request->get('block_id')));
        }

        if (false === $this->admin->isGranted('EDIT', $block)) {
            throw new AccessDeniedException();
        }

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);

        $block->setEnabled(true);
        $manager = $this->get('doctrine')->getManager();
        $manager->flush();

        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true),
            ));
    }

    public function disableAction(Request $request)
    {
        $block = $this->get('eight.blocks')->find($request->get('block_id'));

        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $request->get('block_id')));
        }

        if (false === $this->admin->isGranted('EDIT', $block)) {
            throw new AccessDeniedException();
        }

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);

        $block->setEnabled(false);
        $manager = $this->get('doctrine')->getManager();
        $manager->flush();

        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true),
            ));
    }

    public function updateAction()
    {
        $request = $this->getRequest();

        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());

        /**
         * @var Eight\PageBundle\Entity\Block
         * Our block being updated.
         */
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $this->get('page.renderer')->setCurrentPage($page);

        $form = $this->get('page.renderer')->createFormForBlock($object);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            if ($request->get('enable') == 'true') {
                $object->setEnabled(true);
            }

            foreach ($data as $name => $value) {
                $prev = $object->getContent($name);
                $type = $this->get('widget.provider')->getContentType($object->getName(), $name);

                // if there is no value nor images attached, skip or delete if existing.
                // TODO: find a way to delete images
                if (empty($value)) {
                    if ($prev && ($type != 'image' && $type != 'file')) {
                        $this->get('doctrine')->getManager()->remove($prev);
                    }

                    continue;
                }

                if (!$prev) {
                    $prev = new Content();
                    $prev->setBlock($object);
                    $prev->setName($name);
                    $prev->setType($type);

                    $this->get('doctrine')->getManager()->persist($prev);
                }

                $config = $this->get('widget.provider')->getConfigFor($object->getName(), $name);
                $this->get('variable.provider')->get($prev->getType())->saveValue($prev, $value, $config);
            }

            $this->get('doctrine')->getManager()->flush();

            $this->updatePage($page);
        }

        $page->setEditMode();

        $this->get('doctrine')->getManager()->clear();

        $block = $this->get('eight.blocks')->find($request->get($this->admin->getIdParameter()));

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true), // edit mode true
            'form' => $this->get('page.renderer')->appendForm($block, false), // recursive form false
            ));
    }

    public function reorderAction(Request $request)
    {
        $this->get('helper.page')->reorder($request->get('ids'));

        if ($request->get('page_id')) {
            $page = $this->get('eight.pages')->find($request->get('page_id'));
            $this->updatePage($page);
        }

        return new JsonResponse(array(
            'status' => 'OK'
            ));
    }
}