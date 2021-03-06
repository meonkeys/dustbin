<?php

namespace TwoBulb\PasteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use TwoBulb\PasteBundle\Service\Paste;

/**
 * @Route("/p");
 */
class PasteController extends Controller {

  /**
   * @return \TwoBulb\PasteBundle\Service\PasteService
   */
  private function getPasteService() {
    return $this->get('twobulb_paste_service');
  }

  private function addLineNumbers(Paste $p) {
    $lineNum = 1;
    $p->setContent(preg_replace_callback('/^/m', function($match) use (&$lineNum) {
                      $thisLineNum = $lineNum;
                      $lineNum++;
                      $l = str_pad((string) $thisLineNum, 3, ' ', STR_PAD_LEFT);
                      return "$l  $match[0]";
                    }, $p->getContent()));
    return $p;
  }

  /**
   * @Route("/get/{id}")
   * @Template()
   */
  public function getAction($id) {
    $pasteService = $this->getPasteService();
    $p = $this->addLineNumbers($pasteService->fetchOne($id));
    return array('paste' => $p);
  }

  /**
   * @Route("/edit/{id}")
   * @Template("TwoBulbPasteBundle:Paste:get.html.twig" )
   */
  public function editAction($id) {
    return $this->forward('TwoBulbPasteBundle:Paste:new', array('id' => $id));
  }

  /**
   * GET without id starts a new paste. With id edits an existing paste. POST
   * always creates a new paste.
   * @Route("/new/{id}", defaults={"id"=NULL})
   * @Template()
   */
  public function newAction(Request $request, $id) {
    $pasteService = $this->getPasteService();
    if ($request->getMethod() === 'POST' && $id) {
      throw new \Exception('Something fishy going on. Stop that.');
    }
    $paste = $id ? $pasteService->fetchOne($id) : new Paste();
    // FIXME: label is ' ' to suppress label. Hide with CSS instead?
    $form = $this->createFormBuilder($paste)
            ->add('content', 'textarea', array('label' => ' ', 'attr' => array('cols' => 80, 'rows' => 24)))
            ->getForm();
    if ($request->getMethod() == 'POST') {
      $form->bindRequest($request);

      // TODO: add validation markup to Paste object
      // TODO: make sure content is not null (or image was uploaded)
      if ($form->isValid()) {
        $paste = $pasteService->store($paste);

        return $this->redirect($this->generateUrl('twobulb_paste_paste_get', array('id' => $paste->getId())));
      }
    }
    return array('form' => $form->createView());
  }

  /**
   * @Route("/list")
   * @Template()
   */
  public function listAction() {
    $pasteService = $this->getPasteService();
    return array('pastes' => $pasteService->fetchMany(NULL, NULL));
  }

  /**
   * @Route("/delete/{id}")
   * @Template()
   */
  public function deleteAction($id) {
    $pasteService = $this->getPasteService();
    $pasteService->deleteOne($id);
    return array('oldId' => $id);
  }

  /**
   * @Route("/deleteAll")
   * @Template()
   */
  public function deleteAllAction() {
    $pasteService = $this->getPasteService();
    $pasteService->deleteAll();
    return array();
  }

}
