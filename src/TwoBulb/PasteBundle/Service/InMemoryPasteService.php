<?php

namespace TwoBulb\PasteBundle\Service;

class InMemoryPasteService implements PasteService {

  private $log;
  private $db;

  public function __construct($log) {
    $log->info("InMemoryPasteService::__construct()");
    $this->db = array();
  }

  /**
   * @return array
   */
  public function fetchMany($limit, $offset) {
    $p = new Paste();
    return $p->setId(1)->setContent("blah");
  }

  /**
   * @param string $id
   * @return Paste
   */
  public function fetchOne($id) {
    return $this->db[$id];
  }

  private function generateId() {
    return 1;
  }

  /**
   * @param Paste $paste 
   * @return Paste just-stored object
   */
  public function store(Paste $paste) {
    $id = $this->generateId();
    $paste->setId($id);
    $this->db[$paste->getId()] = $paste;
    return $paste;
  }

}
