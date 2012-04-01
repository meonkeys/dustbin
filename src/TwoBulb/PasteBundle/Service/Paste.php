<?php

namespace TwoBulb\PasteBundle\Service;

class Paste {

  private $id;
  private $content;

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  public function getContent() {
    return $this->content;
  }

  public function setContent($content) {
    $this->content = $content;
    return $this;
  }

}
