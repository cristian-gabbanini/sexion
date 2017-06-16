<?php

namespace Sexion\Handlers;

class DoctrineDBAL implements \SessionHandlerInterface
{
  private $_db;
  private $_session_key;
  private $_table_name;
  private $_lifetime;
  
  public function __construct($table_name, $database, $lifetime)
  {      
    $this->_db = $database;    
    $this->_table_name = $table_name;
    $this->_lifetime = $lifetime;
    $this->_session_key = crypt('WERI349!;"£:', session_id());
  }
  
  public function close() 
  {
    // Nop 
  }

  public function destroy($key) 
  {
    $this->_db->delete($this->_table_name)
        ->where('SESSKEY = ?')
        ->bindValue(1, $key)
        ->execute();
  }

  public function gc($maxlifetime)
  {    
    $this->_db->builder()
        ->delete($this->_table_name)
        ->where('EXPIRY < ?')
        ->setParameter(0, $maxlifetime);        
  
    $stm = $this->_db->prepare('OPTIMIZE TABLE ' . $this->_table_name);     
    $stm->execute();
  }

  public function open($save_path, $session_name) 
  {
    // Nop
  }

  public function read($key) 
  {
    $row = $this->_db->builder()
        ->select('DATA')
        ->from($this->_table_name)
        ->where('SESSKEY = ?')
        ->setParameter(0, $key)
        ->setMaxResults(1)
        ->execute()
        ->fetch();
         
    return unserialize($row['DATA']);
  }

  public function write($key, $val) 
  {       
    $existing_session = $this->_db->builder()
        ->select('1')
        ->from($this->_table_name)
        ->where('SESSKEY = ?')
        ->setParameter(0, $key)
        ->execute()
        ->fetch();
      
    if (!$existing_session) {      
      $this->_db->insert($this->_table_name,[
          'EXPIRY'  => time() + $this->_lifetime,
          'DATA' => serialize($val),
          'SESSKEY' => $key,
          'EXPIREREF' => 'erererr'
      ]);
    } else {
      $this->_db->update($this->_table_name, [
        'EXPIRY'  => time() + $this->_lifetime,
        'DATA' => serialize($val),
        'EXPIREREF' => 'erererr'
      ], [
        'SESSKEY' => $key
      ]);
    }
  }

}