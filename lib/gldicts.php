<?

class glDicts {
  
  static $families = [], $famnames = [];
  static $nonmutated = [];
  
  
  static function GetFamilies($byname=false) {
    if(!self::$families) {
      $res = mysql_query("SELECT * FROM rr_glfamilies");
      while($r = mysql_fetch_object($res)) {
        self::$families[$r->id] = $r;
        self::$famnames[$r->name] = $r;
      }
    }
    return $byname ? self::$famnames : self::$families;
  }
  
  
  static function GetFamily($idnm) {
    if(!self::$families) self::GetFamilies();
    return is_numeric($idnm) ? self::$families[$idnm] : self::$famnames[$idnm];
  }
  
  
  static function GetNonmutated($gl) {
    if(!isset(self::$nonmutated[$gl->family_id][$gl->notaset])) {
      self::$nonmutated[$gl->family_id][$gl->notaset]
        = mysql_o("SELECT * FROM rr_glifetris WHERE family_id='".MRES($gl->family_id)."' AND notaset='".MRES($gl->notaset)."' AND mutaset=''");
    }
    return self::$nonmutated[$gl->family_id][$gl->notaset];
  }
  
  
  static function GetGL4Notaset($notaset) {
    if(is_numeric($notaset))             $fld = "id";
    elseif(strpos($notaset,":")===false) $fld = "named";
    else                                 $fld = "notaset";
    return mysql_o("SELECT * FROM rr_glifetris WHERE $fld='".MRES($notaset)."'");
  }
  
  
  static function GetFD($gl) {
    $FD = 0;
    if($gl->notaset) {
      $FD = count(explode(",", $gl->notaset));
    }
    elseif($gl->records) {
      $json = json_decode($gl->records);
      if(is_array($json->livecells)) $FD = count($json->livecells);
    }
    elseif($gl->family_id) {
      $fm = glDicts::GetFamily($gl->family_id);
      if($fm->FD) $FD = $fm->FD;
    }
    return $FD;
  }
  
}

?>