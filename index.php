<? 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("../../lib/db.php");  // connects to MySQL database, this file is located outside of repository

define("_root", "");
include_once("lib/var.php");
include_once("lib/lib.php");
include_once("lib/tpl.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("parts/backstage.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$_ENV->ver = 314;

$Title = "GLife3";
$H1 = "";
$zabst = $_GET
  ?
  "<div style='font-style:normal;'>&#9432; <a href='$_self'>What is Glife?</a></div>"
  :
  "
  <b>GLife is a cellular automata platform based on WebGL2.</b><br>
  Universal logic: represents both Conway's Game of Life and Langton's Ant as particular examples.<br>
  2D/3D: multi-layer grids.<br>
  Cross-platfom: works in (almost every) browser; independent of hardware; doesn't need any software installation.<br>
  Insanely fast: GPU is ~1000 times faster than CPU due to parallel computing.<br>
  Open-source: <a href='https://github.com/eruditor/glife3' class=ext>github</a>.<br>
  It's primary target is the search of artificial life — evolving self-repairing self-replicating structures.<br>
  &rarr; <a href='$_self?glife=Harbinger&fseed=186356772&LF=100&maxfps=300'>First emergent appearance of something vaguely resembling Artificial Life in Cellular Automata</a><br>
  &rarr; <a href='$_self?glife=Plexus&fseed=2779294873&maxfps=300'>Second one</a><br>
";
// ?family=Conway3D&gl_named=Aphrodite&nmuta=100&rseed=1936799038&fseed=186356772&LF=100&maxfps=300
$zzt = "";
$zpubd = "2020-10-01";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ROUTING
    if($_GET['glife'])               { include("parts/show.php");        }
elseif($_GET['view']=='gallery')     { include("parts/gallery.php");     }
elseif($_GET['view']=='catalog')     { include("parts/catalog.php");     }
elseif($_GET['view']=='stadium')     { include("parts/stadium.php");     }
elseif($_GET['view']=='manufacture') { include("parts/manufacture.php"); }
elseif($_GET['gl_run']) {
  $gr_id = intval($_GET['gl_run']);  if(!$gr_id) die("#r84238237432");
  $gr = mysql_o("SELECT * FROM rr_glifetriruns WHERE id='$gr_id'");  if(!$gr) die("#r84238237433");
  $Title = "Run #$gr->id";
  $H1 = "Run #$gr->id";
  $zzt .= GlifeBigInfo("gl.id='$gr->gl_id'", " AND gr.id='$gr_id'");
  $zzt .= GLifeJS($gr->gl_id, ['fseed'=>$gr->fseed]);
}
else {
  $zzt .= GLifeJS();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$gltitle = "GLife";

$page->title = "Alife: $gltitle".($Title?": ".strip_tags($Title):"")." – ERUDITOR.RU";

$H1 = "" // <a href='/k/?alife'>Alife</a> &rarr; 
      . ($_GET?"<a href='$_self'>$gltitle</a><sup>".sprintf("%.2lf", $_ENV->ver/100)."</sup>":"$gltitle <span>v".sprintf("%.2lf", $_ENV->ver/100)."</span>")
      . ($H1?" &rarr; $H1":"");

$page->z .= "
  <h1>$H1</h1>
  
  <div class=zabst>
    $zabst
  </div>
  
  <div class=zzt>
    $zzt
  </div>
  
  <div class=zpubd>$zpubd</div>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// to be refactored...
$page->z = "
  <style>
    HTML, BODY {min-width:1200px;}
    CANVAS {vertical-align:top; background:#ccc; cursor:crosshair;}
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    SUP {color:#aaa; font-weight:normal; vertical-align:middle; position:relative; font-size:50%; bottom:0.6em;}
    
    .valtop, .valtop TD {vertical-align:top;}
    .hlp {cursor:help;}
    .nrrw {font-family:arial narrow, arial; font-stretch:condensed;}
    
    #GLifeCont INPUT[type=text] {padding:0px 1px;  margin:1px 1px;}
    
    #glifeStatTB {border:solid 2px #ddd; margin:0 0 10px 0;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right; vertical-align:top;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
    #glifeStatTB .tal {text-align:left;}
    TABLE.nrrw TD, TABLE.nrrw TH {font-family:arial narrow, arial!important; font-stretch:condensed!important;}
    
    #SavedListTB TD, #SavedListTB TH {font:normal 11px/13px arial; padding:1px 3px; vertical-align:top;}
    #SavedListTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd; text-align:left; font-weight:bold;}
    #SavedListTB TD INPUT {font:normal 11px/11px arial; padding:0;}
    #SavedListTB TD.tar {text-align:right;}
    #SavedListTB TD.nrrw {font-family:arial narrow, arial; font-stretch:condensed;}
  </style>
" . $page->z;

$_ENV->isMobile = false;

MakePage();

?>