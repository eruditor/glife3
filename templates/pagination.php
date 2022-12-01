<?

class Pagination {
  
  public $PP = 100;
  public $LL = 0;
  public $LP = 0;
  public $shwn = 0;
  public $nttl = 0;
  
  
  function __construct($pp=100) {
    $this->PP = intval($_GET['pp']) ?: $pp;
    $this->LL = intval($_GET['ll']);
    $this->LP = $this->LL * $this->PP;
    if($this->LL) Page::$bread[] = ["page #$this->LL", "&ll=$this->LL"];
  }
  
  
  function Count($res) {
    $this->shwn = mysql_num($res);
    $this->nttl = mysql_r("SELECT FOUND_ROWS()");
  }
  
  
  static function href_get($al, $prms=[]) {
    $get = '';
    if($prms['only_ll']) {
      $get = $al['ll'] ? "ll=".intval($al['ll']) : '';
    }
    else {
      foreach($al as $k=>$v) {
        $get .= ($get?"&":"") . $k;
        if($v!=='') $get .= "=" . $v;
      }
    }
    return "./" . ($get ? "?$get" : "");
  }
  
  
  function Draw() {
    if($this->nttl<=$this->PP) return "";
    
    $al = [];  foreach($_GET as $k=>$v) $al[$k] = $v;
    
    $prev = $next = '';
    if($this->LL>0) {
      $al['ll'] = $this->LL - 1;  if(!$al['ll']) unset($al['ll']);
      $prev = "<a href='" . self::href_get($al, ['only_ll'=>true]) . "'>&#10094; prev</a>";
    }
    if($this->shwn>=$this->PP) {
      $al['ll'] = $this->LL + 1;
      $next = "<a href='" . self::href_get($al, ['only_ll'=>true]) . "'>next &#10095;</a>";
    }
    
    $s4nttl = $this->nttl ? "/ $this->nttl" : "";
    
    $s = "
      <td width=160 style='text-align:right;' >$prev</td>
      <td width=240 style='text-align:center;'>— &nbsp; ".($this->LP+1)."..".($this->LP+$this->shwn)." $s4nttl &nbsp; —</td>
      <td width=160 style='text-align:left;'  >$next</td>
    ";
    echo "<br><div align=center><table><tr>$s</tr></table></div>";
  }
  
}
