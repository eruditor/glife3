<?

class glRecords {
  
  static $bgc4records = [
    'fillin' => [
        2 => "faa",
        5 => "fda",
       10 => "ff8",
       30 => "afa",
      999 => "ccc",
    ],
    'spread' => [
        5 => "faa",
       10 => "fda",
       30 => "ff8",
       60 => "afa",
       80 => "8ff",
      100 => "eee",
      999 => "ccc",
    ],
    'variat' => [
        1 => "999",
        5 => "faa",
       10 => "fda",
       50 => "ff8",
      100 => "afa",
      999 => "8ff",
    ],
    'stopped_nturn' => [
       1000 => "faa",
       4000 => "fda",
       5000 => "afa",
      10000 => "8ff",
      99999 => "eee",
    ],
    'orga_num' => [
        0 => "999",
       50 => "faa",
      100 => "fda",
      200 => "afa",
      400 => "8ff",
      800 => "afa",
      9999999 => "ccc",
    ],
    'orga_sum' => [
          0 => "999",
       5000 => "faa",
      10000 => "fda",
      20000 => "afa",
      40000 => "8ff",
      60000 => "afa",
      9999999 => "ccc",
    ],
    'orga_avg' => [
        1 => "eee",
       20 => "faa",
       40 => "fda",
       60 => "afa",
      100 => "8ff",
      140 => "afa",
      9999999 => "ccc",
    ],
    'orga_z' => [
      1 => "faa",
      2 => "8ff",
      3 => "fda",
      9 => "ccc",
    ],
    'rating' => [
       60 => "8ff",
       80 => "afa",
      100 => "eee",
      110 => "ccc",
      120 => "fda",
      140 => "faa",
      160 => "ccc",
      999 => "999",
    ],
  ];
  
  static $rank4bgc = [
    "999" => -50,
    "ccc" => -30,
    "faa" => -20,
    "fda" => -10,
    "ccc" =>  -5,
    "eee" =>   0,
    "afa" =>  10,
    "8ff" =>  20,
  ];
  
  static function Bgc4Records($k, $v) {
    $bgc = '';
    if(!self::$bgc4records[$k]) return "fff";
    foreach(self::$bgc4records[$k] as $x=>$c) {
      $bgc = $c;
      if($v<$x) break;
    }
    return $bgc;
  }
  
  static function RecordsSpan($r, $k) {
    $ko = property_exists($r, $k) ? $k : str_replace("_", "", $k);  // hack for inexact naming in object fields
    return "<span style='background:#".self::Bgc4Records($k, $r->$ko)."'>".($r->$ko)."</span>";
  }
  
  static function EnrichOrgaRatings(&$gl) {
    if(!$gl->records) return false;
    
    $json = json_decode($gl->records) ?: [];
    
    if(!property_exists($gl, 'orgasum')) {  // if glife not select'ed (only gliferun), rebuild it's value
      $json_orgasum = $json->orga_sum;
      $json_orgasum[0] = -1;  // not counting z=0 in orgasum (see js for this logic)
      $gl->orgasum = max($json_orgasum);
    }
    
    $gl->orga_sum = $gl->orgasum;
    
    if($json->orga_sum) {
      $gl->orga_z = array_search($gl->orgasum, $json->orga_sum);
      $gl->orga_num = $gl->orga_z!==false ? $json->orga_num[$gl->orga_z] : -1;
      $gl->orga_avg = $gl->orga_num>0 ? round($gl->orgasum / $gl->orga_num) : -1;
      
      $gl->rating = 100;
      foreach(['stopped_nturn', 'orga_z', 'orga_sum', 'orga_avg'] as $k) {
        $bgc = self::Bgc4Records($k, $gl->$k);
        $gl->rating -= self::$rank4bgc[$bgc];
      }
      if($gl->rating<=0) $gl->rating = 1;  // decreasing rating is to optimize MySQL ASC/DESC INDEX usage
    }
    else {
      $gl->rating = -999;
    }
  }
  
}

?>