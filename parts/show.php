<?

include_once("backstage.php");

$families = GetFamilies();

$gl_id = intval($_GET['gl_id']);
$gl_name = MRES($_GET['gl_name']);

    if($gl_name) { $q = "named='$gl_name'";  $isnamed = true;  }
elseif($gl_id)   { $q = "id='$gl_id'";       $isnamed = false; }
else die("nothing to show");

$gl = mysql_o("SELECT * FROM rr_glifetris WHERE $q");  if(!$gl) die("no gl found");

$fm = $families[$gl->family_id];
$FD = intval($fm->FD ?: $_GET['FD']);

$Title = SPCQA($gl->named ?: $gl->notaset);
$H1 = $Title;

$zzt .= GlifeBigInfo($gl);

$zzt .= GLifeJS($gl->notaset, ['family'=>$fm->name, 'FD'=>$FD]);

?>