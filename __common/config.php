<?

if (!class_exists('CFG', false)) :
  
  class CFG {
    private $_config = array();
    
    private function __construct() {
      $this->_config = parse_ini_file(
        $_SERVER['DOCUMENT_ROOT'] . "/../configs/global.ini",
        true
      );
    }
    
    public static function init() {
      static $instance = null;
      if (is_null($instance)) {
        $instance = new CFG();
      }
      return $instance;
    }
    
    public function __call($name, $args) {
      $result = null;
      if (!empty($args)) {  // parse parameters  in section
        $args = array_shift($args);
        if (isset($this->_config[$name][$args])) {
          $result = $this->_config[$name][$args];
        }
      }
      else {  // parse parameters outside of section or sections themselves
        if (array_key_exists($name, $this->_config)) {
          $result = $this->_config[$name];
        }
      }
      return $result;
    }

  }
  
endif;
