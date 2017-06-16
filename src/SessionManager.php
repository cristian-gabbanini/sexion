<?php

namespace Begenius\Session;

class SessionManager
{
  private $_providers = [];  
  
  public function __construct()
  {
    $providers = func_get_args();   
  
    foreach ($providers as $provider) {
    
      session_set_save_handler($provider, true);     
      $this->_providers[] = $provider;
    }
  }
  
  public function start()
  {
    session_start();
  }
  
  public function get($key)
  {
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return null;
  }
  
  public function put($key, $value)
  {  
    $_SESSION[$key] = $value;
  }
  
  public function clean()
  {    
    $this->_providers[0]->gc(time() - (60 * 60));
  }
}