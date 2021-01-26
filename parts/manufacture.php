<?

$page->bread[] = ["Manufacture", "?view=manufacture"];

$page->zabst .= "
  “Manufacture” is where glifes are automatically produced.<br>
  Setting random physics rules for a newborn universe, running it's dynamics calculations and then analyzing if the result is somehow interesting.<br>
  (At ~1000 fps it takes about 1 second to process one set of rules.)<br>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$aRgeoms = [18=>"Moore", 182=>"3D Moore", 142=>"3D von Neumann"];
$aRsymms = [85=>"8-rotation+parity", 47=>"4-rotation-vector"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_GET['autore'])) {
  if($_GET['family']) {
    $fm = glDicts::GetFamily($_GET['family']);  if(!$fm) die("incorrect family");
    $FD = intval($fm->FD ?: $_GET['FD']);
    $page->bread[] = ["$fm->name (FD=$FD)", "&family=$fm->name&FD=$FD"];
    $page->z .= GLifeJS('random', ['FD'=>$FD]);
  }
  elseif($_GET['nota']) {
    $nota = $_GET['nota'];  if(!isCorrectVar($nota)) dierr("#598720384");
    $nmuta = intval($_GET['nmuta']);  if(!$nmuta) dierr("zero nmuta");
    $page->bread[] = ["$nota ($nmuta mutations)", "&nota=$nota&nmuta=$nmuta"];
    $page->z .= GLifeJS($nota);
  }
  elseif($_GET['glid']) {
    $glid = $_GET['glid'];  if(!isCorrectID($glid)) die("#780234615");
    $page->bread[] = ["$glid (refielding)", "&glid=$glid"];
    $page->z .= GLifeJS($glid);
  }
  else {
    die("incorrect parameters");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  $famopts = "<option value=''>";
  foreach(glDicts::GetFamilies() as $r) $famopts .= "<option value='$r->name' data-fd='$r->FD'>$r->name";
  
  $namopts = "<option value=''>";
  foreach(glDicts::GetFamilies() as $r) $namopts .= "<option value='anyrand_$r->name'>Any random from $r->name";
  $res = mysql_query("SELECT * FROM rr_glifetris WHERE named<>'' AND mutamd5='' ORDER BY family_id, named");
  while($r = mysql_fetch_object($res)) $namopts .= "<option value='".SPCQA($r->named)."'>".glDicts::GetFamily($r->family_id)->name.": ".SPCQA($r->named);
  
  $famsel = "onchange='var fd=this.options[this.selectedIndex].getAttribute(`data-fd`); var fdinp=document.getElementById(`glfdinp`); fdinp.value=fd>0?fd:3; fdinp.disabled=fd>0?true:false;'";
  
  $speeds = [1001, 300, 60];
  $speedsel = '';  foreach($speeds as $speed) $speedsel .= "<option value='$speed'>$speed";
  $speedsel = "<select name='maxfps'>$speedsel</select>";
  
  $page->z .= "
    <h2>Random search for extra-universial life</h2>
    <table><tr>
    <td valign=top>
      <form method=GET action=''>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Iterating family</th></tr>
        <tr><td>family</td><td class=tal><select name='family' style='width:140px' $famsel>$famopts</select></td></tr>
        <tr><td>FD    </td><td class=tal><input type=number name='FD' value='3' min='1' max='8' id='glfdinp'></td></tr>
        <tr><td>speed </td><td class=tal>$speedsel <input type=submit value=' OK ' style='float:right;'></td></tr>
        </table>
      </form>
    </td><td width=30>&nbsp;</td>
    <td valign=top>
      <form method=GET action=''>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Mutating known glife</th></tr>
        <tr><td>named </td><td class=tal><select name='nota'>$namopts</select></td></tr>
        <tr><td>nmuta </td><td class=tal><input type=text name='nmuta' value='100' size=8></td></tr>
        <tr><td>speed </td><td class=tal>$speedsel <input type=submit value=' OK ' style='float:right;'></td></tr>
        </table>
      </form>
    </td><td width=30>&nbsp;</td>
    <td valign=top>
      <form method=GET action=''>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Refielding initial layout</th></tr>
        <tr><td>gl_id </td><td class=tal><input type=text name='glid' value='' size=16></td></tr>
        <tr><td>      </td><td class=tal><br></td></tr>
        <tr><td>speed </td><td class=tal>$speedsel <input type=submit value=' OK ' style='float:right;'></td></tr>
        </table>
      </form>
    </td>
    </tr></table><hr>
    
    <table><tr>
    <td valign=top>
      <form method=GET action=''>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal><span class=gr>Unclassified</span> (not working yet)</th></tr>
        <tr><td><span title='' class=hlp>FD</span></td><td class=tal><input type=text name='FD' value='3' size=2></td></tr>
        <tr><td><span title='' class=hlp>RB</span></td><td class=tal><input type=text name='RB' value='2' size=2></td></tr>
        <tr><td>nmuta </td><td class=tal><input type=text name='nmuta' value='100' size=8></td></tr>
        <tr><td>      </td><td><input type=submit value=' OK ' disabled></td></tr>
        </table>
      </form>
    </td>
    </tr></table>
  ";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>