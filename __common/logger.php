<?

class Logger {
  
  ////////////////////////////////////////////////////////////////////////////////////////////////
  
  static $table = '';
  static function Table() {
    if(!self::$table) self::$table = CFG::init()->db('prefix') . "chlogs";
    return self::$table;
  }
  
  static function Log($entype, $entid, $var, $val0='', $val1='') {
    IUD("INSERT INTO ".self::Table()." SET ".mysql_set(['entype'=>$entype, 'entid'=>$entid, 'var'=>$var, 'val0'=>$val0, 'val1'=>$val1]));
    return mysql_affected();
  }
  
  static $delayed = [];
  static function Delayed($entype, $entid, $var, $val0='', $val1='') {
    self::$delayed[] = [$entype, $entid, $var, $val0, $val1];
  }
  static function Flush() {
    $logged = 0;
    foreach(self::$delayed as $v) {
      $logged += self::Log($v[0], $v[1], $v[2], $v[3], $v[4]);
    }
    self::$delayed = [];
    return $logged;
  }
  
  ////////////////////////////////////////////////////////////////////////////////////////////////
  
  static function WriteEvent($set) {
    $set['ip'] = $set['ip'] ?: $_ENV->ip;
    if(!isset($set['user_id']) && Auth::$user) $set['user_id'] = Auth::$user->id;
    IUD("INSERT INTO rr_evlogs SET ".mysql_set($set));
  }
  
  ////////////////////////////////////////////////////////////////////////////////////////////////
  
}
