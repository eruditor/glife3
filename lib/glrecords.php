<?

class glRecords {
  
  const Dark   = "999";  // zero-bad value
  const Red    = "faa";  // very low
  const Orange = "fda";  // low
  const Yellow = "ff8";  // so-so
  const Light  = "eee";  // unknown
  const Green  = "afa";  // good
  const Cyan   = "8ff";  // excellent
  const Pink   = "8f8";  // too much
  const Grey   = "ccc";  // overmuch
  
  static $rank4bgc = [
    self::Dark   => -40,
    self::Red    => -20,
    self::Orange => -10,
    self::Yellow =>  -1,
    self::Light  =>   0,
    self::Green  =>  10,
    self::Cyan   =>  20,
    self::Pink   =>  -5,
    self::Grey   => -30,
  ];
  
  static $bgc4records = [
    'fillin'   => [
          2 => self::Red,
          5 => self::Orange,
         10 => self::Yellow,
         30 => self::Green,
         50 => self::Pink,
        999 => self::Grey,
    ],
    'spread'   => [
          5 => self::Red,
         10 => self::Orange,
         30 => self::Yellow,
         60 => self::Green,
         80 => self::Cyan,
        100 => self::Pink,
        999 => self::Grey,
    ],
    'variat'   => [
          1 => self::Dark,
          5 => self::Red,
         10 => self::Orange,
         50 => self::Yellow,
        100 => self::Green,
        999 => self::Cyan,
    ],
    'stopped_nturn' => [
       1000 => self::Red,
       4000 => self::Orange,
       5000 => self::Green,
      10000 => self::Cyan,
      99999 => self::Light,
    ],
    'orga_num' => [
          0 => self::Dark,
         50 => self::Red,
        100 => self::Orange,
        200 => self::Green,
        400 => self::Cyan,
        800 => self::Green,
       1000 => self::Pink,
    9999999 => self::Grey,
    ],
    'orga_sum' => [
          0 => self::Dark,
       5000 => self::Red,
      10000 => self::Orange,
      20000 => self::Green,
      40000 => self::Cyan,
      60000 => self::Green,
      80000 => self::Pink,
    9999999 => self::Grey,
    ],
    'orga_avg' => [
          1 => self::Dark,
         20 => self::Red,
         40 => self::Orange,
         60 => self::Green,
        100 => self::Cyan,
        140 => self::Pink,
    9999999 => self::Grey,
    ],
    'orga_z'   => [
          1 => self::Red,
          2 => self::Cyan,
          3 => self::Green,
          9 => self::Grey,
    ],
    'rating'   => [
         60 => self::Cyan,
         80 => self::Green,
        100 => self::Yellow,
        110 => self::Light,
        120 => self::Orange,
        140 => self::Red,
        160 => self::Grey,
        999 => self::Dark,
    ],
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
    
    $FD = glDicts::GetFD($gl);
    
    $json_orgasum = $json->orga_sum;
    if($FD>=3) $json_orgasum[0] = -1;  // not counting z=0 in orgasum for many-layer case
    $gl->orgasum = max($json_orgasum);
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