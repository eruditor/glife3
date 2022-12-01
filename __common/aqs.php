<?

class AQs {
  
  public $AQs = [];        // array of queries
  public $nttl = 0;        // SQL_CALC_FOUND_ROWS -> SELECT FOUND_ROWS()
  public $shwn = 0;        // mysql_num_rows / count
  public $PP = 0;          // pagination: items per page
  public $LL = 0;          // pagination: current page
  public $LP = 0;          // pagination: LL*PP, shift for LIMIT
  public $inx = 0;         // GET-command: executing queries
  public $show_aqs = 0;    // GET-command: displaying queries
  public $arn = 0;         // GET-command: auto-pagination
  public $xqlmt = 0;       // GET-command: query execution limit
  public $anyway = 1;      // GET-command: show nav/paginat for empty AQs
  public $paginat = '';    // page pagination content
  public $nav = '';        // show_aqs/inx buttons
  public $SQ = '';         // inx/showaqs output
  public $div = "<div id=aux1></div>";        // div for js
  public $allow_edit = false; // allow inx from admin rights
  public $time0 = 0;
  public $time1 = 0;
  public $notransact = false;  // no TRANSACTION and COMMIT
  public $hugetransact = false;  // no autocommit and unique_checks
  public $timeo = 3000;  // timeout, ms
  
  const myinsid = "@@MYINSID@@";
  
  function __construct($allow_edit = false, $pp = 10000, $prms=[]) {
    $this->allow_edit = $allow_edit===true ? true : false;
    $this->PP       = intval($_GET['pp']) ?: $pp;
    $this->LL       = intval($_GET['ll']);
    $this->LP = $this->LL * $this->PP;
    $this->inx      = intval($_GET['inx']);  if($allow_edit!==true) $this->inx = 0;
    $this->show_aqs = intval($_GET['show_aqs']);
    $this->arn      = intval($_GET['arn']);
    $this->xqlmt    = intval($_GET['xqlmt']);
    $this->anyway   = isset($_GET['anyway']) ? intval($_GET['anyway']) : (isset($prms['anyway']) ? $prms['anyway'] : 1);
    $this->timeo    = intval($_GET['timeo'])*1000 ?: 3000;
    $this->time0 = microtime(true);
    $this->notransact = $prms['notransact'] ? true : false;
    $this->hugetransact = $prms['hugetransact'] ? true : false;
    if($this->hugetransact) $this->notransact = true;
  }
  
  function RunAQs() {
    $this->time1 = microtime(true);
    $this->SQ = '';
    
    $aqs_counters = $this->nttl || $this->shwn ? false : true;
    if($this->AQs && $aqs_counters) {
      $this->SQ .= "<div id='wrndiv'>aqs_counters!</div>";
    }
    
    if($this->inx>0 && $this->AQs) {
      if($this->allow_edit!==true) die("access denied");
      
      if($this->hugetransact) { IUD("SET autocommit=0");  IUD("SET unique_checks=0"); }
      
      if($aqs_counters) { $this->nttl = count($this->AQs);  $this->shwn = 0; }
      $inxlog = '';  $nnv = 0;  $last_id = 0;
      if(!$this->notransact) IUD("START TRANSACTION");
      try {
        $ii = 0;  $tt = microtime(true);
        foreach($this->AQs as $n=>$v) {
          if($this->xqlmt && $ii>=$this->xqlmt) break;
          if($aqs_counters) {
            if($n<$this->LP || $n>$this->LP+$this->PP) continue;
            $this->shwn ++;
          }
          if($last_id && strpos($v, self::myinsid)!==false) {
            $v = str_replace(self::myinsid, $last_id, $v);
          }
          
          IUD($v);
          $nv = mysql_affected();
          if(strpos($v, "# KEEP MYINSID")!==false) $last_id = mysql_id();
          
          $inxlog .= "$v :: $nv<br>\n";  $nnv += $nv;
          $ii++;
          if(!$this->notransact && ($ii%100==99 || microtime(true)-$tt>10)) { // split to avoid locks
            IUD("COMMIT");
            if($ii%1000==999) sleep(1);
            IUD("START TRANSACTION");  $tt = microtime(true);
            $inxlog .= "<hr><b>NEW TRANSACTION</b><br>\n";
          }
        }
        if(!$this->notransact) IUD("COMMIT");
      }
      catch(Exception $e) {
        IUD("ROLLBACK");
        die("<br><div id='errdiv'>Database error!</div>".print_var($e,['release'])."<hr>");
      }
      
      if($this->hugetransact) { IUD("SET unique_checks=1");  IUD("COMMIT"); }
      
      $this->SQ .= "<hr>inxlog: $nnv (aqs: ".count($this->AQs).")<br>\n";
      $this->SQ .= "time_php: ".sprintf("%.2lf", $this->time1-$this->time0)." s<br>\n";
      $this->SQ .= "time_aqs: ".sprintf("%.2lf", microtime(true)-$this->time1)." s<br>\n";
      if(!$_GET['noinxlog']) $this->SQ .= "<div class=CommonDIV>$inxlog</div>";
    }
    
    if($_GET['show_aqs'] && $this->AQs) {
      if($aqs_counters) { $this->nttl = count($this->AQs);  $this->shwn = 0; }
      $s = '';
      foreach($this->AQs as $n=>$v) {
        if($aqs_counters) {
          if($n<$this->LP || $n>$this->LP+$this->PP) continue;
          $this->shwn ++;
        }
        $v6 = mb_substr($v,0,6);
        $v = SPCQA($v);
            if($v6=="INSERT") $ucl = "g";
        elseif($v6=="UPDATE") $ucl = "b";
        elseif($v6=="REPLAC") $ucl = "c";
        elseif($v6=="DELETE") $ucl = "r";
        else                  $ucl = "";
        if($this->xqlmt && $n>=$this->xqlmt) $v = "<s>$v</s>";
        $s .= "<div class=aqs$ucl>$v</div>\n";
      }
      $this->SQ .= "time_php: ".sprintf("%.2lf", $this->time1-$this->time0)." s<br>\n";
      $this->SQ .= "time_aqs: ".sprintf("%.2lf", microtime(true)-$this->time1)." s<br>\n";
      $this->SQ .= "<hr>AQs: $this->LP - ".($this->LP+$this->PP)." / ".count($this->AQs)."\n";
      $this->SQ .= "<div class=CommonDIV align=left>$s</div><hr>";
    }
    
    $qq = '';  foreach($_GET as $n=>$v) {  if($n=="ll" || $n=="arn") continue;  $qq .= ($qq?"&":"") . "$n=$v"; }
    
    if($_GET['arn']>0 && ($this->shwn>=$this->PP || $this->LP+$this->shwn<$this->nttl)) {
      $arn1 = intval($_GET['arn']) - 1;
      $href = "?$qq".($qq?"&":"")."ll=".($this->LL+1)."&arn=$arn1";
      if(!$_GET['cli']) $this->SQ .= "
        <script>
        o1=document.getElementById('aux1');
        window.onload=function() {
          o1.innerHTML='DONE';
          var t=setTimeout('Redir2NextOne()', $this->timeo);
        }
        function Redir2NextOne() {
          o1.innerHTML='NEXT ONE ...';
          window.location.href='$href';
        }
        </script>
      ";
      $_ENV->cli_out = $href;
    }
    else {
      $_ENV->cli_out = "finished";
    }
    
    if($_GET['show_aqs']) $_ENV->cli_out .= "\n" . html_entity_decode(strip_tags($this->SQ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    
    if($this->nttl || $this->anyway) {
      $this->paginat = "
        <div style='background:#f4f4f4; margin:8px 0;'><table cellspacing=0 id=CommonTB2 align=center><tr>
        <td width=120 align=left  >".($this->LP>0?"<a href='?$qq".($this->LL>1 ? ($qq?"&":"")."ll=".($this->LL-1) : "")."'>«« prev $this->PP</a>":"")."</td>
        <td width=160 align=center><b>".($this->shwn>0?($this->LP+1):$this->LP)."</b>–<b>".($this->LP+$this->shwn)."</b> из <b>".($this->nttl?:"?")."</b> ".($aqs_counters?"(aqs)":"")."</td>
        <td width=120 align=right >".($this->LP+$this->shwn<$this->nttl || $this->anyway?"<a href='?$qq".($qq?"&":"")."ll=".($this->LL+1)."'>next ".($this->nttl&&$this->LP+$this->PP+$this->shwn>=$this->nttl?($this->nttl-$this->LP-$this->shwn):$this->PP)." »»</a>":"")."</td>
        </tr></table></div>
      ";
    }

    if($this->AQs) {
      $u = $_SERVER['REQUEST_URI'];  $u .= $_GET ? "&" : "?";
      $s1 = "show_aqs";  if(!$_GET['show_aqs']) $s1 = "<a href='".$u."show_aqs=1'>$s1</a>";
      $s2 = "inx";  if(!$_GET['inx'] && $this->allow_edit) $s2 = "<a href='".$u."inx=1' onclick='return confirm(`Sure, inx?`) ? true : false;'>$s2</a>";
      $this->nav .= "
        <br><hr>
        <table><tr>
        <td><table class='BlackTB' width=100 style='background:#ff8;'><tr><td class=tac>$s1</td></tr></table><td width=20>&nbsp;</td>
        <td><table class='BlackTB' width=100 style='background:#f80;'><tr><td class=tac>$s2</td></tr></table>
        </tr></table>
        <hr>
      ";
    }
  }
  
  
  function View($s='') {
    return "
      <div style='font:normal 13px/15px Arial narrow, Arial;'>
        $this->div
        $this->paginat
        $this->SQ
        $s
        $this->nav
        $this->paginat
      </div>
    ";
  }
}
