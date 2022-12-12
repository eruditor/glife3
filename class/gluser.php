<?

class glUser {
  
  static $user = null;
  
  
  static function GetFromCookies() {
    //if($_ENV->route && $_ENV->route->xget['_hsh']=='_set_once') glUser::_set_once();
    
    $uid = intval($_COOKIE['gl3_user_id']);  if(!$uid) return false;
    $hsh = MRES($_COOKIE['gl3_user_ha']);    if(!$hsh) return false;
    
    self::$user = mysql_o("SELECT * FROM rr_glusers WHERE id='$uid' AND hsh='$hsh'");
    if(self::$user) unset(self::$user->hsh);
    
    return self::$user;
  }
  
  
  static function _set_once() {
    $u = mysql_o("SELECT * FROM rr_glusers WHERE id='1'");
    setcookie("gl3_user_id", $u->id,  time()+365*24*60*60, "/", "", false, true);
    setcookie("gl3_user_ha", $u->hsh, time()+365*24*60*60, "/", "", false, true);
  }
  
}