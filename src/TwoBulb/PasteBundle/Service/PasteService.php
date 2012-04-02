<?php

namespace TwoBulb\PasteBundle\Service;

interface PasteService {

  function fetchOne($id);

  function fetchMany($limit, $offset);

  function store(Paste $paste);
  
  function deleteOne($id);
  
  function deleteAll();
}
