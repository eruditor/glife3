<? define("_root","../../");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("../../lib/db.php");  // connects to MySQL database, this file is located outside of repository

include_once("lib/var.php");
include_once("lib/lib.php");
include_once("lib/tpl.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("parts/backstage.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$otitle = "GLife Service";
$h1 = "";
$zabst = "Service scripts. Mostly single-time usage.";
$zzt = "";
$zpubd = "2020-06-08";

$page->meta .= "<meta name=\"robots\" content=\"noindex\">";

$_tm0 = microtime(true);

// AQs class (c) Eruditor Group
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class AQs {
  
  public $AQs = [];        // array of queries
  public $APs = [];        // array of preps for pparsing
  public $nttl = 0;        // SQL_CALC_FOUND_ROWS -> SELECT FOUND_ROWS()
  public $shwn = 0;        // mysql_num_rows
  public $PP = 0;          // pagination: items per page
  public $LL = 0;          // pagination: current page
  public $LP = 0;          // pagination: LL*PP, shift for LIMIT
  public $inx = 0;         // GET-command: executing queries
  public $show_aqs = 0;    // GET-command: displaying queries
  public $arn = 0;         // GET-command: auto-pagination
  public $xqlmt = 0;       // GET-command: query execution limit
  public $paginat = '';    // page pagination content
  public $cache_prep = []; // invalidate prep cache
  public $allow_edit = false; // allow inx from admin rights
  
  function __construct($allow_edit = false, $pp = 10000) {
    $this->allow_edit = $allow_edit===true ? true : false;
    $this->PP       = intval($_GET['pp']) ?: $pp;
    $this->LL       = intval($_GET['ll']);
    $this->LP = $this->LL * $this->PP;
    $this->inx      = intval($_GET['inx']);  if($allow_edit!==true) $this->inx = 0;
    $this->show_aqs = intval($_GET['show_aqs']);
    $this->arn      = intval($_GET['arn']);
    $this->xqlmt    = intval($_GET['xqlmt']);
  }
  
  function RunAQs() {
    global $_self, $page;

    $this->SQ = '';
    
    $aqs_counters = $this->nttl ? false : true;
    if($this->AQs && $aqs_counters) {
      $this->SQ .= "<div class='wardiv'>aqs_counters!</div>";
    }
    
    if($this->inx>0 && $this->AQs) {
      if($this->allow_edit!==true) die("access denied");
      if($aqs_counters) { $this->nttl = count($this->AQs);  $this->shwn = 0; }
      $inxlog = '';  $nnv = 0;
      mysql_query("START TRANSACTION");
      try {
        $ii = 0;  $tt = microtime(true);
        foreach($this->AQs as $n=>$v) {
          if($this->xqlmt && $ii>=$this->xqlmt) break;
          if($aqs_counters) {
            if($n<$this->LP || $n>$this->LP+$this->PP) continue;
            $this->shwn ++;
          }
          mysql_query("$v");  $nv = mysql_affected_rows();
          $inxlog .= "$v :: $nv<br>";  $nnv += $nv;
          $ii++;
          if($ii%100==99 || microtime(true)-$tt>10) { // разбиваем транзакцию на куски, чтобы избежать долгих блокировок
            mysql_query("COMMIT");
            if($ii%1000==999) sleep(1);
            mysql_query("START TRANSACTION");  $tt = microtime(true);
            $inxlog .= "<hr><b>NEW TRANSACTION</b><br>";
          }
        }
        mysql_query("COMMIT");
      }
      catch (Exception $e) {
        mysql_query("ROLLBACK");
        $page->main .= "<br><div class=errdiv>ќшибка базы данных!</div>".print_pre($e,1,-1)."<hr>";
        MakePage();  exit();
      }
      $this->SQ .= "<hr>inxlog: $nnv:<br>";
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
        $v6 = substr($v,0,6);
            if($v6=="INSERT") $ucl = "g";
        elseif($v6=="UPDATE") $ucl = "y";
        elseif($v6=="DELETE") $ucl = "r";
        else                  $ucl = "";
        if($this->xqlmt && $n>=$this->xqlmt) $v = "<s>$v</s>";
        $s .= "<u id=aqs$ucl>$v</u><br>";
      }
      $this->SQ .= "
        <style>
          #aqs, #aqsg, #aqsy, #aqsr {text-decoration:none;}
          #aqs  {color:#000;}
          #aqsg {color:#060;}
          #aqsy {color:#007;}
          #aqsr {color:#800;}
        </style>
        <hr>
        AQs: $this->LP - ".($this->LP+$this->PP)." / ".count($this->AQs)."
        <div class=CommonDIV align=left>$s</div>
        <hr>
      ";
    }
    
    $qq = '';  foreach($_GET as $n=>$v) {  if($n=="ll" || $n=="arn") continue;  $qq .= ($qq?"&":"") . "$n=$v"; }
    
    if($_GET['arn']>0 && ($this->shwn>=$this->PP || $this->LP+$this->shwn<$this->nttl)) {
      $arn1 = intval($_GET['arn']) - 1;
      $this->SQ .= "
        <script>
        o1=document.getElementById('aux1');
        window.onload=function() {
          o1.innerHTML='DONE';
          var t=setTimeout('Redir2NextOne()', 3000);
        }
        function Redir2NextOne() {
          o1.innerHTML='NEXT ONE ...';
          window.location.href='$_self?$qq".($qq?"&":"")."ll=".($this->LL+1)."&arn=$arn1';
        }
        </script>
      ";
    }
    
    if($this->nttl) {
      $this->paginat = "
        <table cellspacing=0 id=CommonTB2><tr>
        <td width=120 align=left  >".($this->LP>0?"<a href='$_self?$qq".($qq?"&":"")."ll=".($this->LL-1)."'>ЂЂ предыдущие $this->PP</a>":"")."</td>
        <td width=160 align=center><b>".($this->shwn>0?($this->LP+1):$this->LP)."</b>Ц<b>".($this->LP+$this->shwn)."</b> из <b>$this->nttl</b> ".($aqs_counters?"(aqs)":"")."</td>
        <td width=120 align=right >".($this->LP+$this->shwn<$this->nttl?"<a href='$_self?$qq".($qq?"&":"")."ll=".($this->LL+1)."'>следующие ".($this->LP+$this->PP+$this->shwn>=$this->nttl?($this->nttl-$this->LP-$this->shwn):$this->PP)." її</a>":"")."</td>
        </tr></table>
      ";
    }

    if($this->AQs) {
      $s1 = "show_aqs";  if(!$_GET['show_aqs']) $s1 = "<a href='".$_SERVER['REQUEST_URI']."&show_aqs=1'>$s1</a>";
      $s2 = "inx";  if(!$_GET['inx'] && $this->allow_edit) $s2 = "<a href='".$_SERVER['REQUEST_URI']."&inx=1' onclick='return confirm(`Sure, inx?`) ? true : false;'>$s2</a>";
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
}

$AQ = new AQs(_local==="1" ? true : false);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['upd_orgasum']) {  // recalc glifetriruns.orgasum, see comments with "undeservingly too large" in Analysis
  $res = mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM rr_glifetriruns WHERE orgasum>0 LIMIT $AQ->LP,$AQ->PP");
  $AQ->shwn = mysql_num_rows($res);
  $AQ->nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysql_fetch_object($res)) {
    $json = json_decode($r->records);  if(!$json) continue;
    $orgasums = $json->orga_sum ?: [];
    $max = 0;
    foreach($orgasums as $z=>$orgasum) {
      if($z==0) continue;
      if($max<$orgasum) $max = $orgasum;
    }
    if($max<>$r->orgasum) $AQ->AQs[] = "UPDATE rr_glifetriruns SET orgasum='$max' WHERE id='$r->id'";
  }
  $AQ->RunAQs();
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->title = "GL_SERVICE Ц ERUDITOR.RU";
$h1 = "GL_SERVICE &rarr; $stitle";

$zzt .= "<div>time: " . round(microtime(true) - $_tm0, 3) . "s</div>";

$page->z .= "
  <style>
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    #glifeStatTB {border:solid 2px #ddd; margin-top:3px;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
  </style>
";

$page->z .= "
  <h1>$h1</h1>
  
  <div class=zabst>
    $zabst
  </div>
  
  <div class=zzt style='font:normal 11px/11px Lucida Console, Monaco, Monospace;'>
    <div id=aux1></div>
    
    $AQ->paginat
    
    $AQ->SQ
    
    $zzt
    
    $AQ->nav
    
    $AQ->paginat
  </div>
  
  <div class=zpubd>$zpubd</div>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

MakePage();

?>