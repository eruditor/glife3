<? 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("../../lib/db.php");  // connects to MySQL database, this file is located outside of repository

define("_root", "");
include_once("lib/var.php");
include_once("lib/lib.php");
include_once("lib/tpl.php");

include_once("parts/backstage.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->bread[] = ["GLife", "./", "<sup>".sprintf("%.2lf", $_ENV->ver/100)."</sup>"];

$page->zabst = $_GET
  ?
  "<div style='font-style:normal;'>&#9432; <a href='./'>What is Glife?</a></div>"
  :
  "
  <b>GLife is a cellular automata platform based on WebGL2.</b>
  <ul>
  <li> Universal logic: represents both
       <a href='?glife=Game+of+Life&FW=1200&FH=700&LF=80&maxfps=300'>Conway's Game of Life</a> and
       <a href='?glife=Langtons+Ant&LF=1&maxfps=300'>Langton's Ant</a>
       as particular examples.
  <li> 2D/3D: multi-layer grids.
  <li> Cross-platfom: works in (almost every) browser; independent of hardware; doesn't need any software installation.
  <li> Insanely fast: GPU is ~1000 times faster than CPU due to parallel computing.
  <li> Open-source: <a href='https://github.com/eruditor/glife3' class=ext>github</a>.
  </ul>
  It's primary target is the search of artificial life (alife) — evolving self-repairing self-replicating structures.<br>
  &rarr; <a href='?glife=Harbinger&fseed=186356772&LF=100'>First emergent appearance of something vaguely resembling Artificial Life in Cellular Automata</a><br>
  &rarr; <a href='?glife=Plexus&fseed=2779294873'>Second one</a>.
         <a href='?gl_run=727595'>Third one</a>.
         <a href='?gl_run=839157'>#4</a>.
         <a href='?gl_run=1023439&maxfps=300'>#5</a>.
         <a href='?gl_run=1023442&maxfps=300'>#6</a>.
         <br>
  "
;

$page->zpubd = "2020-10-01";

// ROUTING /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if($_GET['glife'])               { include("parts/show.php");        }
elseif($_GET['view']=='gallery')     { include("parts/gallery.php");     }
elseif($_GET['view']=='catalog')     { include("parts/catalog.php");     }
elseif($_GET['view']=='stadium')     { include("parts/stadium.php");     }
elseif($_GET['view']=='manufacture') { include("parts/manufacture.php"); }
elseif($_GET['gl_run']) {
  $gr_id = intval($_GET['gl_run']);  if(!$gr_id) die("#r84238237432");
  $gr = mysql_o("SELECT * FROM rr_glifetriruns WHERE id='$gr_id'");   if(!$gr) die("#r84238237433");
  $gl = mysql_o("SELECT * FROM rr_glifetris WHERE id='$gr->gl_id'");  if(!$gl) die("#r84238237434");
  $page->bread[] = ["Run #$gr->id", "?gl_run=$gr->id"];
  $page->z .= GlifeBigInfo($gl, " AND gr.id='$gr_id'");
  $page->z .= GLifeJS($gl->id, ['rseed'=>$gr->rseed, 'fseed'=>$gr->fseed, 'FW'=>$gr->FW, 'FH'=>$gr->FH, 'LF'=>$gr->LF]);
}
else {
  $page->z .= GLifeJS();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

MakePage();

?>