<?

// dbi = database id (0, 1, 2, ...)
// default dbi is 0 (section [db] in CFG)
// each db-function has it's purpose (purp)
// if dbi is explicitly specified -> we use it
// elseif purp is explicitly specified -> we use dbi, corresponding to this purp (from DB::$purps)
// else -> use current default dbi from DB::$dbi (which can be changed from anywhere outside)

class DB {
  
  static $DBs = [], $dbs = [];  // DBs - connected; dbs - existing in CFG
  static $dbi = null;  // current default dbi
  static $purps = ['sel'=>0, 'upd'=>0, 'log'=>0];  // db purposes: sel=read, upd=write, log, etc. Set in Init()
  static $inited = false;
  static $last_mq = null;  // mq for previous operation
  static $last_dbi = 0;  // dbi for previous operation
  static $profiling = false;
  
  
  static function Init() {
    if(self::$inited) return;
    self::$inited = true;
    for($i=0; $i<=9; $i++) {  // does anyone need more than 10 databases?..
      $db_i = "db".($i?:"");
      if(!CFG::init()->$db_i('host')) continue;
      self::$dbs[$i] = $i;
      $purp = CFG::init()->$db_i('purp');
      if($purp) self::$purps[$purp] = $i;
    }
  }
  
  
  static function Connect($i) {
    if(!self::$inited) self::Init();
    $i = intval($i);
    $db_i = "db".($i?:"");
    if(!CFG::init()->$db_i('host')) dierr("no host for $db_i");
    self::$DBs[$i] = new mysqli(
      CFG::init()->$db_i('host'),
      CFG::init()->$db_i('user'),
      CFG::init()->$db_i('pass'),
      CFG::init()->$db_i('name'),
      3306
    );
    $collation = CFG::init()->$db_i('collation');
    if($collation) self::$DBs[$i]->query("SET NAMES $collation");
    self::$dbi = $i;
    return self::$DBs[$i];
  }
  
  
  static function Override($purps) {
    if(!self::$inited) self::Init();
        if(is_int(  $purps)) { foreach(self::$purps as $purp=>$dbi) self::$purps[$purp] = $purps; }
    elseif(is_array($purps)) { foreach(      $purps as $purp=>$dbi) self::$purps[$purp] = $dbi;   }
  }
  
  
  static function I($dbi=null, $purp=null) {
    if(is_null($dbi)) {
      if(!is_null($purp)) $dbi = self::$purps[$purp];
      if(is_null($dbi)) $dbi = self::$dbi;
    }
    if(!self::$DBs[$dbi]) self::Connect($dbi);
    return $dbi;
  }
  
  
  static function Switch($i) {
    if(!self::$DBs[$i]) {
      self::Connect($i);
      if(!self::$DBs[$i]) dierr("unable to switch to DB $i");
    }
    self::$dbi = $i;
  }
  
  
  static function SetProfiling() {
    self::$profiling = true;
    foreach(self::$dbs as $dbi) {
      MQ("SET profiling=1", $dbi);
      MQ("SET profiling_history_size=200", $dbi);
    }
  }
  
  
  static function GetProfiling($formatter) {
    $ret = '';
    foreach(self::$dbs as $dbi) {
      $dbprofiles = mysql_all("SHOW PROFILES", $dbi);
      foreach($dbprofiles as $k=>$v) $dbprofiles[$k]->Duration = sprintf("%.1lf", 1000 * $v->Duration) . " ms";
      $ret .= $formatter($dbprofiles) . "<hr>";
    }
    return $ret;
  }
  
  
  static $gcml_set = false;
  static function SetGCML() {
    if(self::$gcml_set) return false;
    self::$gcml_set = true;
    foreach(self::$dbs as $dbi) {
      MQ("SET SESSION group_concat_max_len = 1000000", $dbi);
    }
  }
  
}

// CONNECT ////////////////////////////////////////////////////////////////////////////////////////////////

DB::Connect(0);  // default DB connection is obligatory

// MYSQL QUERY ////////////////////////////////////////////////////////////////////////////////////////////////

function MQ($q, $dbi=null, $purp='upd') {
  DB::$last_dbi = DB::I($dbi, $purp);
  DB::$last_mq = DB::$DBs[DB::$last_dbi]->query($q);
  if($_ENV->dev===true) { $e = mysql_err();  if($e) echo "<div id='wrndiv'>$q<br>$e</div>"; }
  return DB::$last_mq;
}

function mysql_err() {
  return DB::$DBs[DB::$last_dbi]->error;
}

// SELECT ////////////////////////////////////////////////////////////////////////////////////////////////

function mysql_num($mq) {
  return mysqli_num_rows($mq);
}

function mysql_o($q, $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = null;  if(!$mq) return $ret;
  return mysqli_fetch_object($mq);
}

function mysql_r($q, $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = null;  if(!$mq) return $ret;
  $row = mysqli_fetch_row($mq);
  return $row ? $row[0] : null;
}

function mysql_all($q, $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = [];  if(!$mq) return $ret;
  while($r = mysqli_fetch_object($mq)) $ret[] = $r;
  return $ret;
}

function mysql_allr($q, $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = [];  if(!$mq) return $ret;
  while($r = mysqli_fetch_row($mq)) $ret[] = $r[0];
  return $ret;
}

function mysql_allo($q, $k='id', $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = [];  if(!$mq) return $ret;
  while($r = mysqli_fetch_object($mq)) $ret[$r->$k] = $r;
  return $ret;
}

function mysql_allkv($q, $k, $v, $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = [];  if(!$mq) return $ret;
  while($r = mysqli_fetch_object($mq)) $ret[$r->$k] = $r->$v;
  return $ret;
}

function mysql_all2d($q, $k1, $k2='', $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = [];  if(!$mq) return $ret;
  while($r = mysqli_fetch_object($mq)) {
    if($k2) $ret[$r->$k1][$r->$k2] = $r;
    else    $ret[$r->$k1][]        = $r;
  }
  return $ret;
}

function mysql_allkv2d($q, $k1, $k2='', $v, $dbi=null) {
  $mq = MQ($q, $dbi, 'sel');
  $ret = [];  if(!$mq) return $ret;
  while($r = mysqli_fetch_object($mq)) {
    if($k2) $ret[$r->$k1][$r->$k2] = $r->$v;
    else    $ret[$r->$k1][]        = $r->$v;
  }
  return $ret;
}

// UPDATE ////////////////////////////////////////////////////////////////////////////////////////////////

function IUD($q, $dbi=null) {
  return MQ($q, $dbi, 'upd');
}

function mysql_affected() {
  return DB::$DBs[DB::$last_dbi]->affected_rows;
}

function mysql_id() {
  return DB::$last_mq ? DB::$DBs[DB::$last_dbi]->insert_id : null;
}

function mysql_bulk($aq, $dbi=null) {  // see also: AQs and Bulker
  if(!$aq || !is_array($aq)) return false;
  $naff = 0;
  foreach($aq as $q) {
    MQ($q, $dbi, 'upd');
    $naff += mysql_affected();
  }
  return $naff;
}

// QUERY STRING ////////////////////////////////////////////////////////////////////////////////////////////////

function MRES($s) { return DB::$DBs[DB::$dbi]->real_escape_string($s); }

function mysql_in($arr) { return implode(',', array_map(function($id) { return "'".MRES($id)."'"; }, $arr)); }
function mysql_in_int($arr) { return implode(',', array_map('intval', $arr)); }
function mysql_in_keys($arr) { return mysql_in(array_keys($arr)); }
function mysql_in_keys_int($arr) { return mysql_in_int(array_keys($arr)); }

function mysql_ids($arr, $k='id') {
  $ret = [];
  foreach($arr as $a) {
    $ret[intval($a->$k)] = 1;
  }
  return $ret;
}

function mysql_where($o) {
  $wh = '';
  foreach($o as $k=>$v) $wh .= ($wh?" AND ":"") . "`".MRES($k)."`='".MRES($v)."'";
  return $wh;
}

function mysql_set($o) {
  $set = '';
  foreach($o as $k=>$v) $set .= ($set?", ":"") . "`".MRES($k)."`='".MRES($v)."'";
  return $set;
}

function mysql_set_notnull($o) {
  if(!$o) return '';
  return mysql_set(array_filter((array)$o, function($v) {return isset($v);} ));
}

function mysql_set_null($o) {
  $set = '';
  foreach($o as $k=>$v) $set .= ($set?", ":"") . "`".MRES($k)."`=".(is_null($v) ? "NULL" : "'".MRES($v)."'");
  return $set;
}

// BULKER ////////////////////////////////////////////////////////////////////////////////////////////////

function mysql_upd($new, $old, $key='id', $ignore=[]) {
  $ret = [];
  if($new->$key<>$old->$key) return false;
  if(!is_array($ignore)) $ignore = [];
  foreach($new as $k=>$v) {
    if(in_array($k, $ignore)) continue;
    if($v==$old->$k && is_null($v)==is_null($old->$k)) continue;
    $ret[$k] = $v;
  }
  return $ret;
}


function Bulker1($new, $tbl, $key='id', $prms=[]) {  // $new array must be indexed by $key
  $aqs = [];
  
  $whr = $and = $plu = '';
  if($prms['fixed']) {
    $whr = mysql_where($prms['fixed']);  $and = " AND $whr";
    $set = mysql_set(  $prms['fixed']);  $plu = ", $set";
  }
  
  if(isset($prms['old'])) {
    $old = $prms['old'];
  }
  elseif($prms['fixed']) {
    $old = mysql_allo("SELECT * FROM $tbl WHERE $whr", $key, 0);
  }
  else {
    if(isset($prms['min_id']) && isset($prms['max_id'])) {
      $min_id = intval($prms['min_id']);
      $max_id = intval($prms['max_id']);
    }
    else {
      $min_id = $max_id = 0;  // careful here! bulker can fail to delete some borderline records
      foreach($new as $w) {
        if(!is_numeric($w->$key)) dierr("Bulker1: non-numeric key value");
        if(!$min_id || $min_id>$w->$key) $min_id = $w->$key;
        if(!$max_id || $max_id<$w->$key) $max_id = $w->$key;
      }
    }
    $old = mysql_allo("SELECT * FROM $tbl WHERE $key BETWEEN $min_id AND $max_id", $key, 0);
  }
  
  foreach($new as $w) {
    $o = $old[$w->$key];
    if($o) {
      $upd = mysql_upd($w, $o, $key, $prms['ignore']);
      if($upd) $aqs[] = "UPDATE $tbl SET ".mysql_set_null($upd)." WHERE $key='".MRES($o->$key)."'$and LIMIT 1";
    }
    else {
      $aqs[] = "INSERT INTO $tbl SET ".mysql_set_null($w).$plu;
    }
  }
  
  foreach($old as $o) {
    if(!$new[$o->$key]) $aqs[] = "DELETE FROM $tbl WHERE $key='".MRES($o->$key)."'$and LIMIT 1";
  }
  
  if($prms['inx']) {
    return mysql_bulk($aqs);
  }
  else {
    return $aqs;
  }
}


function Bulker3($old, $new, $tbl, $keys, $prms=[]) {
  $key0 = $keys[0];  $key1 = $keys[1];  $key2 = $keys[2];  if(!$key0 || !$key1 || !$key2) return false;
  if($prms['status_key']) {
    $status_key = MRES($prms['status_key']);  $status_del = intval($prms['status_del']);
  }
  else { $status_key = $status_del = ''; }
  
  $all = [];
  foreach($old as $k0=>$v0) foreach($v0 as $k1=>$v1) foreach($v1 as $k2=>$r) if(!$status_key || $r->$status_key<>$status_del) { if(!isset($all[$k0][$k1][$k2])) $all[$k0][$k1][$k2] = (object)[];  $all[$k0][$k1][$k2]->old = $r; }
  foreach($new as $k0=>$v0) foreach($v0 as $k1=>$v1) foreach($v1 as $k2=>$r) if(!$status_key || $r->$status_key<>$status_del) { if(!isset($all[$k0][$k1][$k2])) $all[$k0][$k1][$k2] = (object)[];  $all[$k0][$k1][$k2]->new = $r; }
  
  $AQs = $qlog = [];  
  $logs0 = [];
  $logs1 = [];
  foreach($all as $k0=>$v0) {
    foreach($v0 as $k1=>$v1) {
      if(isset($prms['allow2edit']) && !array_key_exists($k1, $prms['allow2edit'])) continue;
      foreach($v1 as $k2=>$r) {
        // components of future iuds
        $q4k0 = "`".MRES($key0)."`='".MRES($k0)."'";
        $q4k1 = "`".MRES($key1)."`='".MRES($k1)."'";
        $q4k2 = "`".MRES($key2)."`='".MRES($k2)."'";
        $q4data = '';
        if($r->new) foreach($r->new as $n=>$v) {
          if($n==$key0 || $n==$key1 || $n==$key2) continue;
          if($n==$status_key && $r->old && $r->old->$n<$status_del && $r->old->$n<$v) continue;  // not rewriting super-deleted records
          if(!$r->old || $v!=$r->old->$n) {
            if($r->old && $prms['skipupd'] && in_array($n,$prms['skipupd'])) continue;
            if($r->old && $prms['acurupd'] && $prms['acurupd'][$n] && !FarDifferent($v, $r->old->$n, $prms['acurupd'][$n])) continue;
            $q4data .= ($q4data?", ":"") . "`".MRES($n)."`='".MRES($v)."'";
            if($prms['logs']) {
              $logs0["$n($k1,$k2)"] = $r->old->$n;
              $logs1["$n($k1,$k2)"] = $v;
            }
          }
        }
        
        $query = '';
        if(!$r->new && !$r->old) {
          $qlog['xxx'] ++;
        }
        elseif($r->new && !$r->old) {
          if($status_key && $old[$k0][$k1][$k2]) $query = "UPDATE $tbl SET $q4data WHERE $q4k0 AND $q4k1 AND $q4k2";  // для всех включенных записей значение поля status_key должно приходить в $new
          else $query = "INSERT INTO $tbl SET $q4k0, $q4k1, $q4k2".($q4data?", $q4data":"")."";  // предполагаем, что в $old приходят все записи, в т.ч. с отключенным status_key
          $qlog['ins'] ++;
        }
        elseif($r->old && !$r->new) {
          if($status_key) $query = "UPDATE $tbl SET `$status_key`='$status_del' WHERE $q4k0 AND $q4k1 AND $q4k2";
          else            $query = "DELETE FROM $tbl WHERE $q4k0 AND $q4k1 AND $q4k2";
          $qlog['del'] ++;
          $logs0["DEL($k1,$k2)"] = 0;
          $logs1["DEL($k1,$k2)"] = 1;
        }
        elseif($q4data) {
          $query = "UPDATE $tbl SET $q4data WHERE $q4k0 AND $q4k1 AND $q4k2";
          $qlog['upd'] ++;
        }
        else {
          $qlog['non'] ++;
        }
        
        if($query) $AQs[] = $query;
      }
    }
  }
  
  $inxlog = '';
  if(!$prms['noinx']) {
    $inxlog = mysql_bulk($AQs);
  }
  
  $ret = (object)['qlog'=>$qlog, 'AQs'=>$AQs, 'inxlog'=>$inxlog];
  if($prms['logs']) { $ret->logs0 = $logs0;  $ret->logs1 = $logs1; }
  return $ret;
}

////////////////////////////////////////////////////////////////////////////////////////////////