<?

class Validator {
  
  static function isVarcharID($v_id) {
    return preg_match("`[^a-zA-Z0-9_\.\-:]`", $v_id) ? false : true;
  }
  
  static function isSymbolic($v_id) {
    return preg_match("`[^a-zA-Z0-9_\.\-:;,@$\n]`", $v_id) ? false : true;
  }
  
  static function isInteger($v) {
    if($v==='') return true;
    $int = intval($v);
    return is_numeric($v) && $v==$int ? true : false;
  }
  
  static function isJson($v) {
    if(!is_string($v)) return false;
    @json_decode($v);
    return json_last_error() ? false : true;
  }
  
  static function isJson0($v) {
    if(is_object($v)) return true;
    return self::isJson($v);
  }
  
  static function isDateTime($v) {
    if(!is_string($v)) return false;
    $t = strtotime($v);
    return $t ? true : false;
  }
  
  static function ValVar($field, $v, $format) {
    $err = '';
    
    list($name, $type, $isreq, $maxlen) = $format;
    
    if($isreq && !isset($v)) $err .= "required $field\n";
    
    if(!$isreq && !$v) {
      if($type=='int') $v = 0;
    }
    
        if($type=='strid') { if(!self::isVarcharID($v)) $err .= "incorrect $field\n"; }
    elseif($type=='intid') { if(!self::isInteger(  $v)) $err .= "incorrect $field\n"; }
    elseif($type=='int'  ) { if(!self::isInteger(  $v)) $err .= "incorrect $field\n"; }
    elseif($type=='json' ) { if(!self::isJson(     $v)) $err .= "incorrect $field\n"; }
    elseif($type=='dt'   ) { if(!self::isDateTime( $v)) $err .= "incorrect $field\n"; }
    elseif($type=='str'  ) { if(!is_string(        $v)) $err .= "incorrect $field\n"; }
    elseif($type=='chkbx') {  }
    else { $err .= "#8781936451"; }
    
    if(is_array($maxlen)) { if(!isset($maxlen[$v])) $err .= "invalid $field\n"; }
    elseif($maxlen>0 && $maxlen<>999 && !$err && strlen($v)>$maxlen) $err .= "max_length($field) = $maxlen\n";
    
    return $err;
  }
  
  static function CleanInput($input, $formats) {
    $ret = [];  $err = '';
    
    foreach($formats as $field=>$format) {
      list($name, $type, $isreq, $maxlen) = $format;
      
      if(!isset($input[$field]) && $type<>'chkbx') continue;
      
      $err .= self::ValVar($field, $input[$field], $format);
      
      $ret[$field] = $input[$field];
      
          if($type=='json')  $ret[$field] = json_decode($ret[$field]);
      elseif($type=='dt')    $ret[$field] = date("Y-m-d H:i:s", strtotime($ret[$field]));
      elseif($type=='chkbx') $ret[$field] = $ret[$field] ? 1 : 0;
    }
    
    if($err) dierr(trim($err));
    
    return $ret ? (object)$ret : $ret;
  }
  
}