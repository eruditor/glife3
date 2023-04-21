<?

class glDicts {
  
  static $families = [], $famnames = [];
  static $nonmutated = [];
  
  
  static function GetFamilies($byname=false) {
    if(!self::$families) {
      $res = MQ("SELECT * FROM rr_glfamilies");
      while($r = mysqli_fetch_object($res)) {
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
    if(!isset(self::$nonmutated[$gl->family_id][$gl->notamd5])) {
      self::$nonmutated[$gl->family_id][$gl->notamd5]
        = mysql_o("SELECT * FROM rr_glifetris WHERE family_id='".MRES($gl->family_id)."' AND notamd5='".MRES($gl->notamd5)."' AND mutamd5=''");
    }
    return self::$nonmutated[$gl->family_id][$gl->notamd5];
  }
  
  
  static function GetGL4Notaset($notaset, $fm_id=0) {
    if(is_numeric($notaset))             $fld = "id";
    elseif(strpos($notaset,":")!==false) $fld = "notaset";
    elseif(strlen($notaset)==32)         $fld = "notamd5";
    else                                 $fld = "named";
    return mysql_o("SELECT * FROM rr_glifetris WHERE $fld='".MRES($notaset)."'".($fm_id?" AND family_id='$fm_id'":"")."");
  }
  
  
  static function GetFD($gl) {
    $FD = 0;
    $fm = glDicts::GetFamily($gl->family_id);
    if($fm && $fm->FD<0) {
      $FD = $fm->FD;
    }
    elseif($gl->FD) {
      $FD = $gl->FD;
    }
    elseif($gl->notaset) {
      $FD = count(explode(",", $gl->notaset));
    }
    elseif($gl->mutaset) {
      $FD = count(explode("\n", $gl->mutaset));
    }
    elseif($gl->records) {
      $json = json_decode($gl->records);
      if(is_array($json->livecells)) $FD = count($json->livecells);
    }
    elseif($gl->family_id) {
      if($fm->FD) $FD = $fm->FD;
    }
    return $FD;
  }
  
}

?>