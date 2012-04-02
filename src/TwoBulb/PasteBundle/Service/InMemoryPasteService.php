<?php

namespace TwoBulb\PasteBundle\Service;

class InMemoryPasteService implements PasteService {

  /** @var \Symfony\Bridge\Monolog\Logger */
  private $log;
  private $lockfile;
  private $shmid;
  private $shm_key = 0xBABA1792;
  private $shm_size;

  private function lock() {
    if (!flock($this->lockfile, LOCK_EX))
      throw new \Exception('error locking');
  }

  private function unlock() {
    if (!flock($this->lockfile, LOCK_UN))
      throw new \Exception('error unlocking');
  }
  
  private function persist() {
    $data = serialize($this->db);
      $result = shmop_write($this->shmid, $data, 0);
      if (FALSE === $result)
        throw new \Exception('error writing shared memory');
  }

  public function __construct($log) {
    $this->log = $log;
    $this->lockfile = tmpfile();
    $this->lock();
    $this->shm_size = 10 * 1024 * 1024;
    $this->shmid = @shmop_open($this->shm_key, "a", 0666, 0);
    if ($this->shmid) {
      shmop_close($this->shmid);
      $this->shmid = shmop_open($this->shm_key, "c", 0666, $this->shm_size);
      $data = shmop_read($this->shmid, 0, $this->shm_size);
      try {
        $this->db = unserialize($data);
      } catch (\Exception $e) {
        throw new \Exception('unserialize failed. Key already in use?', 500, $e);
      }
    } else {
      $this->shmid = shmop_open($this->shm_key, "c", 0666, $this->shm_size);
      $this->db = array();
      $this->persist();
    }
    $this->shmid = $this->shmid;
    $this->unlock();
  }

  public function __destruct() {
    $this->log->debug("InMemoryPasteService::__destruct()");
    if (!fclose($this->lockfile))
      $this->log->warn('failed to close lockfile');
    shmop_close($this->shmid);
  }

  /**
   * @return array
   */
  public function fetchMany($limit, $offset) {
    $this->lock();
    $p = $this->db;
    $this->unlock();
    return $p;
  }

  /**
   * @param string $id
   * @return Paste
   */
  public function fetchOne($id) {
    $this->lock();
    $p = $this->db[$id];
    $this->unlock();
    return $p;
  }

  private function generateId() {
    if (count($this->db) < 1) {
      return 1;
    } else {
      return max(array_keys($this->db)) + 1;
    }
  }

  /**
   * @param Paste $paste 
   * @return Paste just-stored object
   */
  public function store(Paste $paste) {
    $this->lock();
    $id = $this->generateId();
    $paste->setId($id);
    $paste->setCreatedTimestamp(new \DateTime());
    $this->db[$paste->getId()] = $paste;
    $this->persist();
    $this->unlock();
    return $paste;
  }
  public function deleteAll() {
    $this->lock();
    $this->db = array();
    $this->persist();
    $this->unlock();
  }

  public function deleteOne($id) {
    $this->lock();
    unset($this->db[$id]);
    $this->persist();
    $this->unlock();
  }

}
