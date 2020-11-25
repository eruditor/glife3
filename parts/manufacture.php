<?

$H1 = "Manufacture";

$families = GetFamilies();
$famnames = GetFamilies(true);
$named = GetNamed();

$aRgeoms = [18=>"Moore", 182=>"3D Moore", 142=>"3D von Neumann"];
$aRsymms = [85=>"8-rotation+parity", 47=>"4-rotation-vector"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_GET['autore'])) {
  if(false) {
    // todo
  }
  elseif($_GET['family']) {
    $fm = $famnames[$_GET['family']];  if(!$fm) die("incorrect family");
    
    $FD = intval($fm->FD || $_GET['FD']);
    
    $maxfps = intval($_GET['maxfps'] ?: 1001);
    
    $H1 .= " &rarr; Family=" . $fm->name . " (FD=$FD)";
    
    $zzt .= GLifeJS('random', ['FD'=>$FD, 'maxfps'=>$maxfps]);
  }
  elseif($_GET['nota']) {
    $zzt .= GLifeJS($_GET['nota'], ['maxfps'=>$maxfps]);
  }
  else {
    die("incorrect parameters");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  $famopts = "<option value='0'>";  foreach($families as $r) $famopts .= "<option value='$r->name' data-fd='$r->FD'>$r->name";
  $namopts = "<option value=''>";   foreach($named    as $r) $namopts .= "<option value='".SPCQA($r->named)."'>".SPCQA($r->named);
  $famsel = "onchange='var fd=this.options[this.selectedIndex].getAttribute(`data-fd`); var fdinp=document.getElementById(`glfdinp`); fdinp.value=fd>0?fd:3; fdinp.disabled=fd>0?true:false;'";
  
  $speeds = [1001, 300, 60];
  $speedsel = '';  foreach($speeds as $speed) $speedsel .= "<option value='$speed'>$speed";
  $speedsel = "<select name='maxfps'>$speedsel</select>";
  
  $zzt .= "
    <h2>Random search for extra-universial life</h2>
    <table><tr>
    <td valign=top>
      <form method=GET action='$_self'>
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
    </td><td width=30>&nbsp;</td>
    <td valign=top>
      <form method=GET action='$_self'>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Iterating family</th></tr>
        <tr><td>family</td><td class=tal><select name='family' $famsel>$famopts</select></td></tr>
        <tr><td><span title='' class=hlp>FD</span></td><td class=tal><input type=number name='FD' value='3' min='1' max='8' id='glfdinp'></td></tr>
        <tr><td>speed</td><td class=tal>$speedsel</td></tr>
        <tr><td>      </td><td><input type=submit value=' OK '></td></tr>
        </table>
      </form>
    </td><td width=30>&nbsp;</td>
    <td valign=top>
      <form method=GET action='$_self'>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Mutating known glife</th></tr>
        <tr><td>named </td><td class=tal><select name='nota'>$namopts</select></td></tr>
        <tr><td>nmuta </td><td class=tal><input type=text name='nmuta' value='100' size=8></td></tr>
        <tr><td>speed</td><td class=tal>$speedsel</td></tr>
        <tr><td>      </td><td><input type=submit value=' OK '></td></tr>
        </table>
      </form>
    </td>
    </tr></table>
  ";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>