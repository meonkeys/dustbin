<?php

namespace TwoBulb\PasteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

  /**
   * @Route("/get/{id}")
   * @Template()
   */
  public function getAction($id) {
    $pasteService = $this->getPasteService();
    return array('paste' => $pasteService->fetchOne($id));
  }

  /**
   * @Route("/new")
   * @Template()
   */
  public function newAction(Request $request) {
    $pasteService = $this->getPasteService();
    $paste = new Paste();
    $form = $this->createFormBuilder($paste)
            ->add('content', 'textarea', array('label' => ' ', 'attr' => array('cols' => 80, 'rows' => 24)))
            ->getForm();
    if ($request->getMethod() == 'POST') {
      $form->bindRequest($request);

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
    return array();
  }

}
