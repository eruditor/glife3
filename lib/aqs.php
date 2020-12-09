<?

// AQs class (c) Eruditor Group

class AQs {
  
  public $AQs = [];        // array of queries
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
          if($ii%100==99 || microtime(true)-$tt>10) { // split to avoid locks
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
        <td width=120 align=left  >".($this->LP>0?"<a href='$_self?$qq".($qq?"&":"")."ll=".($this->LL-1)."'>ЂЂ prev $this->PP</a>":"")."</td>
        <td width=160 align=center><b>".($this->shwn>0?($this->LP+1):$this->LP)."</b>Ц<b>".($this->LP+$this->shwn)."</b> из <b>$this->nttl</b> ".($aqs_counters?"(aqs)":"")."</td>
        <td width=120 align=right >".($this->LP+$this->shwn<$this->nttl?"<a href='$_self?$qq".($qq?"&":"")."ll=".($this->LL+1)."'>next ".($this->LP+$this->PP+$this->shwn>=$this->nttl?($this->nttl-$this->LP-$this->shwn):$this->PP)." її</a>":"")."</td>
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

?>