<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.0.1
*/namespace
Adminer;const
VERSION="6.0.1";error_reporting(24575);set_error_handler(function($Yc,$ad){return!!preg_match('~^Undefined (array key|offset|index)~',$ad);},E_WARNING|E_NOTICE);$Dd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Dd||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$El=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($El)$$X=$El;}}$_COOKIE=array_filter($_COOKIE,'is_scalar');if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Tb=adminer()->credentials();$I=Driver::connect($Tb[0],$Tb[1],$Tb[2]);return(is_object($I)?$I:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$Gf=substr($u,-1);return
str_replace($Gf.$Gf,$Gf,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
idx($_a,$x,$k=null){return($_a&&array_key_exists($x,$_a)?$_a[$x]:$k);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|number|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
text_type(){return'char|text'.(JUSH=="sql"?'|enum|set':'');}function
is_searchable(array$m,array$X){if(!isset($m["privileges"]["where"]))return
false;$U=$m["type"];$Aj=$X["val"];$Qa='binary$|bytea|raw|image|bfile|^vector$'.(JUSH=="mssql"?'|^timestamp$':'|^bit').(JUSH=="oracle"?'|^blob|^long|rowid':'');if(preg_match("~$Qa~",$U))return
false;if(preg_match(number_type(),$U)){$fh='-?\d+(\.\d+)?';return(bool)preg_match('~^'.$fh.(preg_match('~IN$~',$X["op"])?"( *, *$fh)*":'').'$~',$Aj);}if(preg_match('~^(small)?date|^timestamp~',$U))return(bool)preg_match('~^\d+-\d+-\d+~',$Aj);if(preg_match('~^time~',$U))return(bool)preg_match('~^\d+:\d+~',$Aj);if(preg_match('~^bool~',$U)||(JUSH=="mssql"&&$U=="bit"))return(bool)preg_match('~^(t|f|true|false|[01])$~i',$Aj);return
true;}function
remove_slashes(array$bm,$Dd=false){$I=array();foreach($bm
as$x=>$X)$I[stripslashes($x)]=(is_array($X)?remove_slashes($X,$Dd):($Dd?$X:stripslashes($X)));return$I;}function
bracket_escape($u,$Ja=false){static$ll=array(':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5');return
strtr($u,($Ja?array_flip($ll):$ll));}function
url_escape($Q){static$ll=array();if(!$ll){$ll=array(' '=>'+');foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$cb)$ll[$cb]=sprintf('%%%02X',ord($cb));for($s=0;$s<256;$s++){if($s<32||$s>126)$ll[chr($s)]=sprintf('%%%02X',$s);}}return
strtr((string)$Q,$ll);}function
min_version($em,$bg="",$g=null){$g=connection($g);$Pj=$g->server_info;if($bg&&preg_match('~([\d.]+)-MariaDB~',$Pj,$A)){$Pj=$A[1];$em=$bg;}return$em&&version_compare($Pj,$em)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_set($Ch,$Y){return(function_exists('ini_set')?\ini_set($Ch,$Y):false);}function
ini_bool($bf){$X=ini_get($bf);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($bf){$X=ini_get($bf);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
max_input_vars($J,$Rh){$fg=(int)ini_get("max_input_vars");return($fg?(int)floor(($fg-$Rh)/$J):0);}function
max_input_vars_error(){$bf="max_input_vars";return
sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"<b>$bf = ".ini_get($bf)."</b>");}function
sid(){static$I;if($I===null)$I=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$I;}function
set_password($dm,$N,$V,$E){$_SESSION["pwds"][$dm][$N][$V]=($_COOKIE["adminer_key"]&&is_string($E)?array(encrypt_string($E,$_COOKIE["adminer_key"])):$E);}function
get_password(){$I=get_session("pwds");if(is_array($I))$I=($_COOKIE["adminer_key"]?decrypt_string($I[0],$_COOKIE["adminer_key"]):false);return$I;}function
get_val($G,$m=0,$Eb=null){$Eb=connection($Eb);$H=$Eb->query($G);if(!is_object($H))return
false;$J=$H->fetch_row();return($J?$J[$m]:false);}function
get_vals($G,$d=0){$I=array();$H=connection()->query($G);if(is_object($H)){while($J=$H->fetch_row())$I[]=$J[$d];}return$I;}function
get_key_vals($G,$g=null,$Sj=true){$g=connection($g);$I=array();$H=$g->query($G);if(is_object($H)){while($J=$H->fetch_row()){if($Sj)$I[$J[0]]=$J[1];else$I[]=$J[0];}}return$I;}function
get_rows($G,$g=null,$l="<p class='error'>"){$Eb=connection($g);$I=array();$H=$Eb->query($G);if(is_object($H)){while($J=$H->fetch_assoc())$I[]=$J;}elseif(!$H&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.adminer()->error()."\n";return$I;}function
unique_array($J,array$w){foreach($w
as$v){if(preg_match("~^(PRIMARY|UNIQUE)$~",$v["type"])&&!$v["partial"]){$I=array();foreach($v["columns"]as$x){if(!isset($J[$x]))continue
2;$I[$x]=$J[$x];}return$I;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$I=array();foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$yd=$m["type"];$nf=$m&&(is_blob($m)||preg_match('~binary~',$yd));$I[]=$d.($nf&&!is_utf8($X)?" = ".driver()->quoteBinary($X):(JUSH=="sql"&&$yd=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$m["full_type"])?"::jsonb = ".q($X)."::jsonb":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($yd,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($m,q($X)))))));if(JUSH=="sql"&&preg_match('~char|text~',$yd)&&preg_match("~[^ -@]~",$X))$I[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$I[]=escape_key($x)." IS NULL";return
implode(" AND ",$I);}function
where_columns(array$n){$I=array();foreach((array)$_GET["null"]as$x)$I[$x]=true;foreach((array)$_GET["where"]as$x=>$X){$x=bracket_escape($x,true);foreach($n
as$C=>$m){if($x==$C||strpos($x,idf_escape($C))!==false)$I[$C]=true;}}return$I;}function
where_check($X,array$n=array()){parse_str($X,$fb);remove_slashes(array(&$fb));return
where($fb,$n);}function
where_link($s,$d,$Y,$_h="="){$xh=($Y!==null?$_h:"IS NULL");return"&where[$s][col]=".url_escape($d).($xh!=first(adminer()->operators())?"&where[$s][op]=".url_escape($xh):"")."&where[$s][val]=".url_escape($Y);}function
convert_fields(array$e,array$n,array$M=array()){$I="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$Aa=convert_field($n[$x]);if($Aa)$I
.=", $Aa AS ".idf_escape($x);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),array(";"=>"%3B",","=>"%2C"));}function
cookie($C,$Y,$Qf=2592000){header("Set-Cookie: $C=".rawurlencode($Y).($Qf?"; expires=".gmdate("D, d M Y H:i:s",time()+$Qf)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($C=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
get_url($Ml,$Lb){$http_response_header=null;$Zc=array();set_error_handler(function($Yc,$l)use(&$Zc){$Zc[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$l);return
true;});$I=file_get_contents($Ml,false,$Lb);restore_error_handler();$ve=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);return
array($I,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($ve,0,''),$A)?$A[1]:''),(array)$ve,($I===false?implode("\n",$Zc):''),);}function
get_settings($Ob){parse_str($_COOKIE[$Ob],$Tj);return$Tj;}function
get_setting($x,$Ob="adminer_settings",$k=null){return
idx(get_settings($Ob),$x,$k);}function
save_settings(array$Tj,$Ob="adminer_settings"){$Y=http_build_query($Tj+get_settings($Ob));cookie($Ob,$Y);$_COOKIE[$Ob]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==PHP_SESSION_NONE))session_start();}function
stop_session($Md=false){$Pl=ini_bool("session.use_cookies");if(!$Pl||$Md){session_write_close();if($Pl&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($dm,$N,$V,$j=null){$Ll=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($dm=='mssql'||$dm=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$Ll,$A);return"$A[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($dm!="server"||$N!=""?url_escape($dm)."=".url_escape($N)."&":"")."username=".url_escape($V).($j!=""?"&db=".url_escape($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Xf,$B=null){if($B!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Xf!==null?$Xf:$_SERVER["REQUEST_URI"]))][]=$B;}if($Xf!==null){if($Xf=="")$Xf=".";header("Location: $Xf");exit;}}function
query_redirect($G,$Xf,$B,$Xi=true,$hd=true,$sd=false,$Yk=""){if($hd){$nk=microtime(true);$sd=!connection()->query($G);$Yk=format_time($nk);}$gk=($G?adminer()->messageQuery($G,$Yk,$sd):"");if($sd){adminer()->error
.=adminer()->error().$gk.script("messagesPrint();")."<br>";return
false;}if($Xi)redirect($Xf,$B.$gk);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($G){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$G:(preg_match('~;$~',$G)?"DELIMITER ;;\n$G;\nDELIMITER ":$G).";");return
connection()->query($G);}function
apply_queries($G,array$T,$bd='Adminer\table'){foreach($T
as$R){if(!queries("$G ".$bd($R)))return
false;}return
true;}function
queries_redirect($Xf,$B,$Xi){$Ri=implode("\n",Queries::$queries);$Yk=format_time(Queries::$start);return
query_redirect($Ri,$Xf,$B,$Xi,false,!$Xi,$Yk);}function
format_time($nk){return
sprintf('%.3f s',max(0,microtime(true)-$nk));}function
relative_uri($Ll=''){return
preg_replace_callback('~^[^?]*~',function($A){return
str_replace(":","%3A",$A[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',($Ll?:$_SERVER["REQUEST_URI"])));}function
remove_from_uri($Yh=""){return
substr(preg_replace("~(?<=[?&])($Yh".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($C,$hc=false){$_d=$_FILES[$C];if(!$_d)return
null;foreach($_d
as$x=>$X)$_d[$x]=(array)$X;$I=array();foreach($_d["error"]as$x=>$l){if($l)return$l;$o=$_d["name"][$x];$gl=$_d["tmp_name"][$x];$Jb=file_get_contents($hc&&preg_match('~\.gz$~',$o)?"compress.zlib://$gl":$gl);if($hc){$nk=substr($Jb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$nk))$Jb=iconv("utf-16","utf-8",$Jb);elseif($nk=="\xEF\xBB\xBF")$Jb=substr($Jb,3);}$I[]=array($o,$Jb);}return$I;}function
get_file($x,$hc=false,$nc=""){$Cd=get_files($x,$hc);if(!is_array($Cd))return$Cd;$I='';foreach($Cd
as$_d){$Jb=$_d[1];$I
.=$Jb;if($nc)$I
.=(preg_match("($nc\\s*\$)",$Jb)?"":$nc)."\n\n";}return$I;}function
upload_error($l){$ng=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($ng?" ".sprintf('Maximum allowed file size is %sB.',$ng):""):'File does not exist.');}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$S,$x){$X=idx($S,$x,'?');if(!is_numeric($X))return
h($X);if($X<0)return'?';$xa=($x=="Rows"&&(JUSH=="sqlite"||$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));return($xa?"~ ":"").format_number($X);}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$td=false){$I=table_status($R,$td);return($I?reset($I):array("Name"=>$R));}function
column_foreign_keys($R){$I=array();foreach(adminer()->foreignKeys($R)as$p){foreach($p["source"]as$X)$I[$X][]=$p;}return$I;}function
fields_from_edit(){$I=array();foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$C=bracket_escape($x,true);$I[$C]=array("field"=>$C,"full_type"=>"","type"=>"","privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>true,"auto_increment"=>($C==driver()->primary),);}return$I;}function
dump_headers($Ge,$Kg=false){$I=adminer()->dumpHeaders($Ge,$Kg);$Th=$_POST["output"];if($Th!="text"||$I=="tar"){$Ab=($Th!="text"&&$Th!="file"&&preg_match('~^[0-9a-z]+$~',$Th)?".$Th":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Ge).".$I$Ab");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$I;}function
dump_csv(array$J){$wl=$_POST["format"]=="tsv";foreach($J
as$x=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($wl?'\t':'[,;]|^$').'~',$X))$J[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($wl?"\t":";")),$J)."\r\n";}function
parse_csv($Wb,$Ij){$I=array();preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Wb,$dg);foreach($dg[0]as$J){preg_match_all("~((?>\"[^\"]*\")+|[^$Ij]*)$Ij~",$J.$Ij,$eg);$I[]=$eg[1];}return$I;}function
csv_value($X){return(preg_match('~^".*"$~s',$X)?str_replace('""','"',substr($X,1,-1)):$X);}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($o){if(is_link($o))return;$q=@fopen($o,"c+");if(!$q)return;@chmod($o,0660);if(!flock($q,LOCK_EX)){fclose($q);return;}return$q;}function
file_write_unlock($q,$ac){rewind($q);fwrite($q,$ac);ftruncate($q,strlen($ac));file_unlock($q);}function
file_unlock($q){flock($q,LOCK_UN);fclose($q);}function
first(array$_a){return
reset($_a);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$q=file_open_lock($o);if(!$q)return'';$I=stream_get_contents($q);if(!$I){$I=rand_string();file_write_unlock($q,$I);}else
file_unlock($q);return$I;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($X,$_,array$m,$Wk){if(is_array($X)){$I="";if(array_filter($X,'is_array')==array_values($X)){$zf=array();foreach($X
as$W)$zf+=array_fill_keys(array_keys($W),null);foreach(array_keys($zf)as$xf)$I
.="<th>".h($xf);foreach($X
as$W){$I
.="<tr>";foreach(array_merge($zf,$W)as$Wl)$I
.="<td>".select_value($Wl,$_,$m,$Wk);}}else{foreach($X
as$xf=>$W)$I
.="<tr>".($X!=array_values($X)?"<th>".h($xf):"")."<td>".select_value($W,$_,$m,$Wk);}return"<table>$I</table>";}if(!$_)$_=adminer()->selectLink($X,$m);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$X=driver()->value($X,$m);$I=adminer()->editVal($X,$m);if($I!==null){if(!is_utf8($I))$I="\0";elseif($Wk!=""&&is_shortable($m))$I=shorten_utf8($I,max(0,+$Wk));else$I=h($I);}return
adminer()->selectVal($I,$_,$m,$X);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($Pc){$Ca='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Cc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$pi="$Ca+(\\.$Ca+)*@($Cc?\\.)+$Cc";return
is_string($Pc)&&preg_match("(^$pi(,\\s*$pi)*\$)i",$Pc);}function
is_url($Q){$Cc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($Cc?\\.)+$Cc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$m){return!preg_match('~'.number_type().'|date|time|year~',$m["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)\]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$A)?array($A[4].$A[5],$A[2].$A[8]):array($N,''));}function
count_rows($R,array$Z,$of,array$ee){$G=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($of&&(JUSH=="sql"||count($ee)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$ee).")$G":"SELECT COUNT(*)".($of?" FROM (SELECT 1$G GROUP BY ".implode(", ",$ee).") x":$G));}function
slow_query($G){$j=adminer()->database();$Zk=adminer()->queryTimeout();$Yj=driver()->slowQuery($G,$Zk);$g=null;if(!$Yj&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$_f=get_val(connection_id(),0,$g);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$_f&token=".get_token()."'); }, 1000 * $Zk);");}}ob_flush();flush();$I=@get_key_vals(($Yj?:$G),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$I;}function
get_token(){$Ui=rand(1,1e6);return($Ui^$_SESSION["token"]).":$Ui";}function
verify_token(){list($hl,$Ui)=explode(":",$_POST["token"]);return($Ui^$_SESSION["token"])==$hl&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"));}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Q,$tc=""){$va=array_flip(str_split(compress_alphabet()));$y=strlen($Q);$Zl=($y?13*($y-1)/2-$va[$Q[0]]:0);$Qa="";$kj=0;$lj=0;for($s=1;$s<$y;$s+=2){$kj=($kj<<13)+$va[$Q[$s]]*93+$va[$Q[$s+1]];$lj+=13;while($lj>=8&&$Zl>=8){$lj-=8;$Zl-=8;$Qa
.=chr($kj>>$lj);$kj&=(1<<$lj)-1;}}if($Qa=="")return"";if($tc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,array('dictionary'=>$tc)),$Qa,ZLIB_FINISH);return($tc==""&&function_exists('gzinflate')?gzinflate($Qa):inflate($Qa,$tc));}function
inflate($Qa,$tc=""){$Nf=array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258);$Of=array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0);$xc=array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577);$zc=array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13);$I=$tc;$F=0;do{$Ed=inflate_bits($Qa,$F,1);$U=inflate_bits($Qa,$F,2);if(!$U){$F=($F+7)&~7;$y=inflate_bits($Qa,$F,16);$F+=16;$I
.=substr($Qa,$F>>3,$y);$F+=$y<<3;}else{if($U==1){$Vf=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$_c=array_fill(0,30,5);}else{$Uf=inflate_bits($Qa,$F,5)+257;$yc=inflate_bits($Qa,$F,5)+1;$Fh=array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);$_g=array_fill(0,19,0);$zg=inflate_bits($Qa,$F,4)+4;for($s=0;$s<$zg;$s++)$_g[$Fh[$s]]=inflate_bits($Qa,$F,3);$Ag=inflate_table($_g);$Pf=array();while(count($Pf)<$Uf+$yc){$yk=inflate_symbol($Qa,$F,$Ag);if($yk==16)$Pf=array_merge($Pf,array_fill(0,inflate_bits($Qa,$F,2)+3,end($Pf)));elseif($yk==17)$Pf=array_merge($Pf,array_fill(0,inflate_bits($Qa,$F,3)+3,0));elseif($yk==18)$Pf=array_merge($Pf,array_fill(0,inflate_bits($Qa,$F,7)+11,0));else$Pf[]=$yk;}$Vf=array_slice($Pf,0,$Uf);$_c=array_slice($Pf,$Uf);}$Wf=inflate_table($Vf);$Bc=inflate_table($_c);while(($yk=inflate_symbol($Qa,$F,$Wf))!=256){if($yk<256)$I
.=chr($yk);else{$y=$Nf[$yk-257]+inflate_bits($Qa,$F,$Of[$yk-257]);$Ac=inflate_symbol($Qa,$F,$Bc);$mh=strlen($I)-$xc[$Ac]-inflate_bits($Qa,$F,$zc[$Ac]);for($s=0;$s<$y;$s++)$I
.=$I[$mh+$s];}}}}while(!$Ed);return($tc==""?$I:substr($I,strlen($tc)));}function
inflate_bits($Qa,&$F,$Qb){$I=0;for($s=0;$s<$Qb;$s++){$I+=((ord($Qa[$F>>3])>>($F&7))&1)<<$s;$F++;}return$I;}function
inflate_table(array$Pf){$R=array();$pb=0;for($Ra=1;$Ra<=max($Pf);$Ra++){foreach($Pf
as$yk=>$y){if($y==$Ra){$R[$Ra][$pb]=$yk;$pb++;}}$pb<<=1;}return$R;}function
inflate_symbol($Qa,&$F,array$R){$pb=0;$Ra=0;do{$pb=($pb<<1)+inflate_bits($Qa,$F,1);$Ra++;}while(!isset($R[$Ra][$pb]));return$R[$Ra][$pb];}function
script($ck,$kl="\n"){return"<script".nonce().">$ck</script>$kl";}function
script_src($Ml,$kc=false){return"<script src='".h($Ml)."'".nonce().($kc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($cd,$me,$ya=null){$za=array();foreach(array_slice(func_get_args(),2)as$X)$za[]=json_encode($X,256);return" data-on$cd='".str_replace(array('&','<',"'"),array('&amp;','&lt;','&#039;'),"$me(".implode(", ",$za).")")."'";}function
input_hidden($C,$Y=""){return"<input type='hidden' name='".h($C)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace(array('&','<','"',"'","\0"),array('&amp;','&lt;','&quot;','&#039;','&#0;'),$Q);}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($C,$Y,$ib,$Df="",$c="",$nb="",$Ff=""){$I="<input type='checkbox' name='$C' value='".h($Y)."'".($ib?" checked":"").($Df==""&&$nb?" class='$nb'":"").($Ff?" aria-labelledby='$Ff'":"").$c.">";return($Df!=""?"<label".($nb?" class='$nb'":"").">$I".h($Df)."</label>":$I);}function
optionlist($Dh,$Fj=null,$Ql=false){$I="";foreach($Dh
as$xf=>$W){$Eh=array($xf=>$W);if(is_array($W)){$I
.='<optgroup label="'.h($xf).'">';$Eh=$W;}foreach($Eh
as$x=>$X)$I
.='<option'.($Ql||is_string($x)?' value="'.h($x).'"':'').($Fj!==null&&($Ql||is_string($x)?(string)$x:$X)===$Fj?' selected':'').'>'.h($X);if(is_array($W))$I
.='</optgroup>';}return$I;}function
html_select($C,array$Dh,$Y="",$c="",$Ff=""){static$Df=0;$Ef="";if(!$Ff&&substr($Dh[""],0,1)=="("){$Df++;$Ff="label-$Df";$Ef="<option value='' id='$Ff'>".h($Dh[""]);unset($Dh[""]);}return"<select name='".h($C)."'".($Ff?" aria-labelledby='$Ff'":"")."$c>".$Ef.optionlist($Dh,$Y)."</select>";}function
html_radios($C,array$Dh,$Y="",$Ij=""){$I="";foreach($Dh
as$x=>$X)$I
.="<label><input type='radio' name='".h($C)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$Ij";return$I;}function
confirm($B=""){return
on('click','confirmClick',$B?:'Are you sure?');}function
print_fieldset($t,$Mf,$hm=false){echo"<fieldset><legend>","<a href='#fieldset-$t' class='toggle'>$Mf</a>","</legend>","<div id='fieldset-$t'".($hm?"":" class='hidden'").">\n";}function
bold($Ta,$nb=""){return($Ta?" class='active $nb'":($nb?" class='$nb'":""));}function
js_escape($Q){return
str_replace("<","\\x3C",addcslashes($Q,"\r\n'\\"));}function
js_escape_re($Q){return
addcslashes(preg_quote($Q,"/"),"\r\n");}function
pagination_href($D){return
remove_from_uri("page|next").($D?"&page=$D".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($D,$Xb){return" ".($D==$Xb?($D?"<b>".($D+1)."</b>":$D+1):'<a href="'.h(pagination_href($D)).'">'.($D+1)."</a>");}function
hidden_fields(array$Ni,array$Ke=array(),$Di=''){$I=false;foreach($Ni
as$x=>$X){if(!in_array($x,$Ke)){if(is_array($X))hidden_fields($X,array(),$x);else{$I=true;echo
input_hidden(($Di?$Di."[$x]":$x),$X);}}}return$I;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
on_upload_progress(&$Kl){$Kl=(ini_bool("session.upload_progress.enabled")&&ini_get("session.upload_progress.name")?rand_string():"");return($Kl?on('submit','uploadProgress',ME."upload=$Kl",SESSION_NAME."=$Kl"):"");}function
file_input($c,$kj=""){$hg="max_file_uploads";$ig=ini_get($hg);$ng="upload_max_filesize";$og=ini_bytes($ng);$Ai=ini_bytes("post_max_size");if($Ai&&$Ai<$og){$ng="post_max_size";$og=$Ai;}$pg=ini_get($ng);return(ini_bool("file_uploads")?"<input type='file'$c".on('change','fileChange',(int)$ig,sprintf('Increase %s.',"$hg = $ig"),$og,sprintf('Increase %s.',"$ng = $pg")).">$kj":'File uploads are disabled.');}function
enum_input($U,$c,array$m,$Y,$Sc=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$dg);$Di=($m["type"]=="enum"?"val-":"");$ib=(is_array($Y)?in_array("null",$Y):$Y===null);$I=($m["null"]&&$Di?"<label><input type='$U'$c value='null'".($ib?" checked":"")."><i>$Sc</i></label>":"");foreach($dg[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$ib=(is_array($Y)?in_array($Di.$X,$Y):$Y===$X);$I
.=" <label><input type='$U'$c value='".h($Di.$X)."'".($ib?' checked':'').'>'.h(adminer()->editVal($X,$m)).'</label>';}return$I;}function
input(array$m,$Y,$r,$Ha=false,$Il=false){$C=h(bracket_escape($m["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r)$r="json";$vf=($r=="json"||preg_match('~^jsonb?$~',$m["full_type"]));if($vf&&$Y!=''&&(JUSH!="pgsql"||$m["type"]!="json")&&(is_array($Y)||!$_POST["save"]))$Y=json_encode(is_array($Y)?$Y:json_decode($Y),128|64|256);$jj=(JUSH=="mssql"&&$Il&&$m["auto_increment"]);if($jj&&!$_POST["save"])$r=null;$Yd=(isset($_GET["select"])||$jj?array("orig"=>'original'):array())+adminer()->editFunctions($m);$Xc=driver()->enumLength($m);if($Xc){$m["type"]="enum";$m["length"]=$Xc;}$c=" name='fields[$C]".($m["type"]=="enum"||$m["type"]=="set"?"[]":"")."'".($Ha?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$R=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($Yd[""])."<td>".adminer()->editInput($R,$m,$c,$Y);else{$oe=(in_array($r,$Yd)||isset($Yd[$r]));$Fd=0;foreach($Yd
as$x=>$X){if($x===""||!$X)break;$Fd++;}echo(count($Yd)>1?"<select name='function[$C]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($Yd,$r===null||$oe?$r:"")."</select>":h(reset($Yd)))."<td".($Fd&&count($Yd)>1?on('input','skipOriginal',$Fd):"").">";$df=adminer()->editInput($R,$m,$c,$Y);if($df!="")echo$df;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked":"")."$c value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$c,$m,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$C'>";elseif($vf)echo"<textarea$c cols='50' rows='12' class='jush-json'>".h($Y).'</textarea>';elseif(($Vk=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$Y)){if($Vk&&JUSH!="sqlite")$c
.=" cols='50' rows='12'";else{$K=min(12,substr_count($Y,"\n")+1);$c
.=" cols='30' rows='$K'";}echo"<textarea$c>".h($Y).'</textarea>';}else{$zl=driver()->types();$qg=(!preg_match('~int~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A)?((preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0)):($zl[$m["type"]]?$zl[$m["type"]]+($m["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$m["type"]))$qg+=7;echo"<input".((!$oe||$r==="")&&preg_match('~^'.int_type().'$~',$m["type"])&&!preg_match('~\[]~',$m["full_type"])?" type='number'":"")." value='".h($Y)."'".($qg?" data-maxlength='$qg'":"").(preg_match('~char|binary~',$m["type"])&&$qg>20?" size='".($qg>99?60:40)."'":"")."$c>";}echo
adminer()->editHint($R,$m,$Y),(count($Yd)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$m){$u=bracket_escape($m["field"]);$r=idx($_POST["function"],$u);if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($r=="NULL")return"NULL";if(is_blob($m)&&ini_bool("file_uploads")){$_d=get_file("fields-$u");if(!is_string($_d))return
false;return
driver()->quoteBinary($_d);}$Y=idx($_POST["fields"],$u);if($Y===null)return
false;if($m["type"]=="enum"||driver()->enumLength($m)){$Y=idx($Y,0);if($Y=="orig"||!$Y)return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($m["auto_increment"]&&$Y=="")return
null;if($m["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
adminer()->processInput($m,$Y,$r);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Hj="<ul>\n";foreach(table_status('',true)as$R=>$S){$C=adminer()->tableName($S);if(isset($S["Engine"])&&$C!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$H=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$H||$H->fetch_row()){$Ji="<a href='".h(ME."select=".url_escape($R)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$C</a>";echo"$Hj<li>".($H?$Ji:"<p class='error'>$Ji: ".adminer()->error())."\n";$Hj="";}}}echo($Hj?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($Vk,$Vj=0){return
on('mouseover','helpMouseover',$Vk,$Vj).on('mouseout','helpMouseout');}function
on_help_value($ej="",$ij=""){return
on('mouseover','helpValueMouseover',$ej,$ij).on('mouseout','helpMouseout');}function
edit_form($R,array$n,$J,$Il,$l='',$G='',$Yk=''){$Dk=adminer()->tableName(table_status1($R,true));page_header(($Il?'Edit':'Insert'),$l,array("select"=>array($R,$Dk)),$Dk);adminer()->editRowPrint($R,$n,$J,$Il,$G,$Yk);if($J===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Nc=false;$om=($Il&&!isset($_GET["select"])?where_columns($n):array());$Mb=(count($om)!=count($n));if(!$Mb)$om=array();if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$Ha=!$_POST;foreach($n
as$C=>$m){echo"<tr".($om[$C]?on('change','whereChange'):"")."><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($C));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$fj))$k=$fj[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($J!==null?($J[$C]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($J[$C])?implode(",",$J[$C]):(is_bool($J[$C])?+$J[$C]:$J[$C])):(!$Il&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);if(($Il&&!isset($m["privileges"]["update"]))||$m["generated"])echo"<td class='function'><td>".select_value($Y,'',$m,null);else{$Nc=true;$r=($_POST["save"]?idx($_POST["function"],bracket_escape($C),""):($Il&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Il&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ha!==false)$Ha=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ha,$Il);if($Ha)$Ha=false;}}if(!fields($R)&&driver()->primary!="")echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($Nc){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"])&&$Mb){$uc=($om&&($l!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($Il?'Save and continue editing':'Save and insert next')."' title='Ctrl+Shift+Enter'$uc".($Il?on('click','ajaxForm','Saving…'):"").">\n";}}echo($Il?"<input type='submit' name='delete' value='".'Delete'."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
repeat_pattern($pi,$y){return
str_repeat("$pi{0,65535}",$y/65535)."$pi{0,".($y%65535)."}";}function
shorten_utf8($Q,$y=80,$uk=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);return
h($A[1]).$uk.(isset($A[2])?"":"<i>…</i>");}function
icon($Fe,$C,$Ee,$bl,$c=""){return"<button ".($C?"type='submit' name='$C'":"draggable='true' tabindex='-1'")." title='".h($bl)."' class='icon icon-$Fe".($C?"":" jsonly")."'$c><span>$Ee</span></button>";}function
copy_icon(){$Pb='Copy';return"<a href='' class='jsonly icon-copy' title='$Pb'><span>$Pb</span></a>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('*c0=@iDWB2P?H*{U)^:;B/4!N2Ch9&hJv;rrHHN,,V&KA"nRfwb9E:tfItOm[T$"DXBX~p!.VU_tTHo)6Y?9q/$mNiohTvI>+a<Y{uWk}`:y,3U4,>E(&Rg+L!L
o2PEgsnloQe<:k0oib.Mj<,:^!s
zL
u)CIc01D]MByKv5]ERUpyZdlppD_9oR;P9hVvq0j@^d:4.VmF0)NWe(|3H6L6o0Ws>bwAO`m9rG2Wt;hhRg!_Vf4_nB|@W/dk68Q<R1B`1Dm8:;z,2
U)U-,adrWa=3ExJ-QhN)_F%%ndS%Ly!><d61_"qU8+_TBHX@<(PwklcY!u8hbgk-
[;Rh`$j<M_/v1L?D1XOE!*3"aaCtLgr:&VCx-w"#!BYQEeE<?Ub!+aqVSz#me-7jAZ&6o[("Z/yYlM,,wx$LBFo4W,*sEWn"i_Dff8!3g>THyt3FDVLmGrq+phsrc9%K8ON1DXS`8$MRiPoh2TUidX#(7Q"{]y^3OK78@
M)W+3D81"Xj{qO"5CA8MS-J<&Xp_$*vN4|H#V:%`!.1
M+_Qf
`08<KQReU[2vX<g$^DbKIY*I/x
~,"#:J<<j(P[w8x_[QP`MX*51&AacRfmtF~1idOxM:15UQ]83D1aNR|ozOBHe8j34;O0RJ4YKp
E}#/y/De`cUg=;L9"ubwq!e5xFb4]pIeWpJ+]mJ$C}gDN;ZPE}3?;lR(K}z$7d1s-+vTIb(&1oZf;ND&DmhH
FOmYM
:5g$~pF%`*@4N]#@(O9`7y^$+`_ZCee5*+jIBh"5F
NwDj_!KW?!^.S^t]Y/)HK!um+_t:F7/>nk.4D=`Jk0Clho;IVM6:CSv^L;xB5uOq56C%O3B!ficBp/KWq)3qA)(sYQgvgp6<``h/x`L>N([yIQli+hUjv[[JHB1.BTXl+:d/!,zG&w?O}SzpV1`V4"Qu4Rz9@Egf)lmuP.Aiy_&`|8f+]FT7;uC=uf2$E9bgx1C8>#q8,hG8v@4I<I@(l
v>.
:YL5-(r"`K6.~f&P^Myh7>[z!x^f0AMi6xzDMd73~7Z+)5KR8=MA`b[nn"uUvEiuh_Rfa,^Qq1nBjy,Kh`CTX.wqQ1404HL=}>FjM^S:NKdKxX9hq6.hRIwn)Gt8osSL0u|l~B4/Sb^CG,cE^>Z3Hx|E5E{)uQ=J:RSnj9.,:Cx"::K(cXR
7!#MVx=^O"bN]yRVe_HFP99cSu[0y9l_pC!D
(N=,E14cE_FOQ4R,(D#>YD6+2pR/VrcJmW-iU-dl8e)A]=-RdIIZ4gwunIHQ1XBL_M#NE7DYF
NpUw%|k?cB/A/=3of|MsQ_iwT)DE)g@?30(ib#9;Y+`i<et(_M$eNd2E8//^OAwvQ4"U93&T`

4fB)KLP@Se+3.w
&e$|&;T<<
s7mC)6Q!3I;tCB@~T]Kw`0YX@F.n[CIQr~qJOj&A9&0Bn+WqTPT|k"O?=](`H)rC:C=d?0pzFYd(E@%bbi@P6j#)OQUC&+f-3+1FcZ.hg|<odB*#,-(G_;woYyY|GWh+_Hq.44TA`Edl+EUeeR6yV:H!D;RGedp#wJ#b%A[lB=Ifg_);]
y:_oKqH;VX9:?gc/P;d5X/T<b{g{OW1V>y[!Cu+%Z{DE[KNknQT/Jmp*&hZ-)et0h&VK/SPyg!,03;4A"~#62Z-N0VSB2(D(?/-Nr2qMLlq._crv+GfrNyE"$_W[g%34NR/{Y>FNog!1-it]?w.IH,_TmX#(_h+BUmhIn!5_
f<
<
x4=DsHYS@?2$^GRCbPx+l{5wem:Bk(d~#/hD#GHL&+DB1W<@!5Xq*#b>#"%W-2[nuo]J^=<t?CtFNLX^e_rF%o.)iR2wO*U!.DMk^AS"a$Fgd**`]RR6WEWzuY2;X[_JRaDqf7gnwSn>cC",R?x>K$Cx;kLcAPp
;4?M1]eW=Dd]Z~:oE|R";R+Y-L9I_;=&N;D~OO+s5dOjJ9ux>+eniq`wc2AfcXktmq3q.`H(;"b$!UD/YDo#$PI0dYV{BN],D=#(GUVzv^SbCH&!@|Z+S{o+v@<znTX%JxoK?l-E]BPP"`-)Yc,f=o]mO>rk?TnOmVi7Ec>.?|
19?m{
TZKdL!-BZ<O".
s+0(}nl.r82Z?U*fR<Zw49fU]tI$
9B,K4^5w[CaYp7rc-i#vm8Lw?}GUd]mL/_lG1]>L%NNH)P0bIim$d^&@YjyE?a&QcraXpWDe$.q^QZQjxhZ
bNR},ActZ}q"<0nu:,vQJq[WT@"M"eDHhIt9+i0.bfX:uMJFSo#Y(lJ676xE9d68q7lEfW1@),NP<je?!|F4b!517)m
x9Lar;e*diQ+u
ZT)@cy$/#O+Gv5K>*G<@23Xi_uN&,pd8TM
,XXdbFnVf]a`LxaS[<HR<3e!)MB[Q0P[w
!-MFKJ&2aVZ($f[bVS/m7i/CpZ"`y>-K&ZymMfS0JM6T/Y$&1>:"c63yZOmOqpH
<wUYgg7vJ2N9$N)3AKK<@1[n)y:Tl7@b@7.n%p:+3ddJy*0kY<MrA=CKSR.)6S~Ktrr_PHHf,Av^d,+f)C<tKm_Ava0KTs"*Uo#8[c|$guw7xw2s#_ScNgL4]vb)|x]yFG^fwZ@z)=nh-ug8#R%KS7cgo/{1-:4yiwjsywhF5!9ocMn?LB}oDB7XwfcfhK|bk5i7#ccm@Anyml[yg4cKsk~
X@yz!K}Mc:mb~EwAN"-WSa[q{O@Teflrpr^"pT7[0pkL+stz#soJ8lo>$&+M2xrbmw2BO+!GP<bG{YgS,3V
jo07hOdffL~8X6iBDE6]!J
],GMWsh`ml^i/NH^=+vYZZwZ*H=L3DK-rsIz>k$gp@uJBB2O63Ez0k+|P^hWHpUT,7Z/yzqMadT_U|
I&{[hRt>>i(kn#ZU#p!9z

^!f1GQjAs>%^r{86s,j_wgd?V6-:chvEK_#1v<,9wwsG<gM%9HiqZ
32:+#)c>sArZ&R#4@6p,COs9U,uC/I>+L%X/DQq6#u9Tn5HK>c8@)u`]poz)nPtkrv>noRP+s-61Fu:{6NUGR8?=JF_>Jb+yfLMG(&Dk,__$pB3|$b"3+^/G?ei0NPGnntjA^8t}tnhrJVBbV0IE=Id>bblad!nkq,
MpzBIrIhoo(rdc@FH0EC7.[:Cp(]+ol$kZlQdcG_0F%Qk]D8`m1PiY&t{=>mw]Y=AA]lgUEi9F"bsQ(;l=gnQ"lrwCthbg^#wd&:6]Cm=Q-<[UA;=x-UY[J:^a@mVkSjjH|<-k5OVZ|uwV5X=a(U;j$OUKR`KY>N,/~[|(r/c.r8ip!#O[$n
S1VIxh@0KKo+(ovB/;^tZ9-J
Mgk#7pNK3#$[{j$n_CO[r[i8/GM6xs!6{4Cg^E*A|,T)j6QQ8`Z/-8"%?=f0hJ<@ROf-wtSr|k5"-pKHrk9AuZ/8lkvXT>^][bg0?m-jv41:J[?6ri024s^;;qb(Zg~D^yAEgW#!HlRk_:EH}RCa2@xDWJ?6E?#3PA69bnJ`kfu0IDFV0jrB*)5xUq;,B+Fh)[!TqvcXCZaURXM1shnu^!2[y<pNK6Xx5kph56*xCx?EE^Jr[8ay1@<h}34w!$d[~.<]iuq,<C8K=00)(&tm"?3UXD^rN57uKf%hTAPa_lZiO+]Ix7vZTko0v6{RG`Y/70C)dnHt<2qxr$WpyXPr{ACS`?d^bU`<8]9yb7Vy[4twKycWKuVV}1,8O`hl0,=KN`y_vFU0~g<Yekq92?(Bsy#)B`ulH+U$`a7P0:]&i-u=O#(_+e@ACi0IAP;RFw/2c!}PX?0N+oFn}3^Z:dM+cuRko+}(RrhTmY7"@&UJ.HJW#]ZP2>HpZLV=SbZu9iiK(y02[[863NgF"CzIzE<T@1{[>;=De`gP,@wnm&:,#3@hV8
"k&"YA>,yfhwNk
M4<CxBHL`p&Iq23!!a,$^ka+mw(J(7)0O($O[bEMXx5h#cUD&;";.d+S7EUaT<uU5>*K`!-Z8@Jq3#8jw]SvmbV#0qe6UXmyAxShwmy`i-JU_
Gu%ivGkHu(_88t$&B-=NPXH"-:DI)p<0??N!weQ+W0oR]g~hW-MZy
)Eo1".&)vZ"4T"(fQ=V
}an+Re3EgLm:-hdohpG)
y*n:RYd7Krx@>PqZY0:P-;fa)L/Rc=uw4=l-@nEG[<t~n%4G)a&jaBy^#_5Iw~(3BTjO@|^?4J*oYSP{&:D?h_*M^QGLGG)D8:Y7jELo%;F(^lTSZUL{Nzc{W9L8U+9t&SYS@!DatruEJilUD4.H);rdiox=MWQT*z,(Y{;W4,MeNwpW4/
u_4ukG]2"Gl._
@Y/@m95%,wze9[#gp9:B##HcLFf+-7r"iG,JK3T_u7W`mM!^Ow.yF`(;ofa7/y.x~wA');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string(')OsbOb3V?!K0U*,j#-$TY2N&[`b!>wsTd_N`GuxPN9GOol*1@VDLlh_fdc430fu#lZ-r!f<.+=s=X(J2e>*"$r2geZo4@leYjQ1%,Ya^fK)KWrns9HN3Za[M&Ua[o)7sBH/u8kXg}4drw:$n$88?$
q.DLTGX#<D1t"V<MYp_Ma&R!lNy=^42%5+QTJ"M_zEIVt2b&@<iW5HXxa7"+HENrVp[-(?;l^q7O9Hb]:Sr
,WOw[;eXJ3/AYxWiY8v=afr;mm
2j7~=*!Bp~Z"dLH|e`)gkNjaXDNCg,tOd/Bee9aAhUna-ZLB;OF8<%r2e1x*xX$ZiG_Ot<kzJ%FMb$)(Q`hL2F*U3b$cI[XzX_yVm!=X`6&,RA>7e!9gn|F:S?FGgzw]+AWONX6E]$Hu$5^-Av"t[SRPD-dDP9jn"tZoFsSBWi!U
]MxVmGbSp6ix~D-FZ7DoJXY/zE9!l0/]_ZhqV=[.*yn"zS|U3V:p0%cK5pT+2_?0*<"/w-9$DgzF7#yWi<W,3"4>QoJftal+Tm>(PeM9JHTs;vxkWm9$<A7*iHsBl8Ig]>qQ38jy4P@0/ej$G,X[`Y>gf_|8q*^2Dnu#YI<#>h+;DK|$/DDimVm(m`WCVEYX1jS%84q"FCpAaU/4Yf
Q<ovd>ujL>jlSK$ADUHDsn1a>o@
;@5f]$+ZQNcbu-^=v>xaijt5[sMndunEa-5T28EWI"G!j1uhd)s:ch9c-:STXv8Dq82x=D]meVP[+d`LIY+k0"G?9H47
NBubq<z`![Z&|@7?P6j_[UcU{fnW0X^j_=5(,s<ii_zJS27M>X{xnK3M[W-rsA0k}H{mrK*vZ2&pNC@DA0;NWwLj&)j-eg5PfwA;O70]r,58hd_Eqn{Y@Ws+We9XpZFh)z(-@LIrbPy8da(hAcZV#?1X}E7dx7tw`28WL.XVqgdV!&yvq?3hO5.EHdr-kP>4[llRl9i0C+sj[+"u^v6Y#jXxd');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('(c4]`nsZ51ptW"t=*e+fx8n;ZVpb*]5K9W0*X6<mF3cq94l$pSe;A:p"0[OR1M@Fe528)9BGzmIM4eqOXr
t-kGb7>xmUy,#qQ
Qi[%Bgn%?$*5y~:g3LZc=(@..96q]HnPQU;tmMa!b`B<0fGbMY#w$7y8<aLKliH1BiPJN|sh#~w?%N@k_UbGv&u_n?ogSa)**=ywVcjY?Ktf=!"EY$n#y44}_oJTFj?$5.jmJP#55l_&((HbO/c,1?yNW:XSA4V).+97wNo.GcWVUP@$0%Em_Tp&h^C$ckooJjrXD{.|Ews"l1_B>$X)U)FYUs-SMBe`E<.1w]=xN?+w:atRAu9@28?cFy6t0nVm=*PMYYT5c:pQ0`UuR^"GtbY%YF>6BUD}x@)A^,D[.qZthMKtOz=(pI`auj/O1Jk%HeF%q2;XksE`/(cTMgj)`{.1@_;~Q;jS[7L2t<Q)1OMYXmBt_Pm6rH41m1m1w*Xrv;7<jlw-N%1VnggNm",]yz*-q,4gXmszw_t7u)7*!+V3p($:y-4!X]E.WlaX(n=4YfX&nmY<nYqz*X]B3[x&26,cda&+tvYGpYraYMoASiO>WVTSl2E(OUN%pJ#,xrYI_ZIfi/E*^0Kw-GrEi}Z-b_sC8kgWwG5tu*?rmkZDwC3LGv0aC%sv(=mr`u?H>{<D%;jFe0&G[?yarP[l:w"]GrFY8WWaEsjWD?%4g:@L:lKiCci-GHrF;Mm:F912#:bK2I%OkTR$WTA%`wpr@.+r9A5YC/L/E69vki^G(@a.apfZ*,AU!)t:*4g1<n$J0r:y9Z=|Cfcm8".,8BCpH?2T"mGMXII^$-`
87**P^:)u
k&/ff,WfVk^=Z5)INMeH)L2LK#+4&%avx>1rm6t!D,?CM[
vKlhK1M^6R5ZafEBRfCH!s2s89pE[yix:0<]pjSEtL^,m=iG%:;UkP|7Z?U:wqHDPQ|&E@>0Xlxb2W@Uz#x*yPK(-S)g!NJ&Bi
,vccMpKkkyQewf^uDsh)AI>!b2G2Ux1`F`4Blk,[H47:s{D+2knk&]_$`5+wo(pT^{(<U<:rC~>HZ&GG?oFL7!/nYsIKI3Hse>L|F3S&r1Bmrf3EHGCk3?gbVv!]PQ0I"jNTjX@(pev5"`HRyFwm
1H?gHgB"&_+hZ,}"%EVROBp-S(zur6Yc|N}ciPtYCY.Jzdf-v6ev&vX>e#AY1HTxDCUu-_x[RS,/P-br8<{ZNRJGUT^)StkdFYLploZh:oKx2b"%?kwwh=7CZXa(X^mLWJxXve:jE/D6GICdQ<!G<i<O#qW[2>qU0jb5GL49o]L%:H|JH
P!!*t
%9P:ClE?9nbSQo,!-Ip%0-m=[6gO&9mWIIHU6M-77E)<VwCQ(Nrq
AGP+CF,uL6;vXHY;"`8/ElB)
q?>4:c{I%7|!nf)i>gLYbnEyh*x*/*gwnowQWFs$PXb:Y6?lLp~`L-XALvv@%0Z9A0;>bOq%,3d;e"5"cVIs<h`:Msu$-Ri-ZjFFQ*T6Gj@OJVZh:]1[P<&YCj;KRIhR@3n5d%M/*F^e;b{IT78mDf55
m+?T!@VheIk>8v<e+)?bZ@9(d>JKRTL>8oAK;!r"f#XH,cp5Y%5N9l0|<?PV[6*Rg~@}sKAKVAW$(Tn:%:7rsO>E8c<2xSoq
yLSG&!lF*<GvX/@"^dB>ex}D^*zs+h@udp6=K&}0ivV.~8OfgXq>Z3feK7i%av1!sdGJ4O-qC]C<4Ow
H8PMyI&;nrTr5b|94:lbLKNT?,jCS:Am_(m"4Hcs<yvPye/J!a@g*1&?d2;K:ut5[7|x0$Ae>ckXR1u<r(QGeVmJDz%$)HEX%JY2)Y2w`2m8LG0oj_O,T5
fYeA?G_TD=lkmJbZ&K*+rW6:kT;f"l;xgTu
[VCSJ!8zx(ZfY/]M;Qt@C}6t8M^V-y0>N(:p
ZWvJ9>o.dre.N]4@}-vI2;Sgp4^(d$BO$/lNiXTfVHY>j,"sbT#69v`S-iq.a./w+&x)|1BQsTG_g$_]A:/"l4Z]B_ecPKGLku+6;NTvoU+-*47WaN-<0#E$%"j9-]CXLgPn.Y~31p`KXhkh=Af(.BtWvB<lg@pC=/.IjMLdt$V$&49q>QBg/:@igTQCo^R/7`/VmCN><6{6TB1)6ll/*7yLK/h]dE~^VkTSAXD<Dj)]`(jEr77!yN0&@&F0dLUIt6mfCd|R+kuG2yHU[OIHq#YaS&u7,[dBn+
vpX3=v1I!Lg%k)tyCMS9DnkXx3H42V6tk(azrZRLR5F8836s7GDw/ia
=SP?gS9p".6$PEe+KZsh8dC/C?S]5d2aR#d+`mrV1JYyx]6<u1S>lkU]i{HWFDx-c0[Na?BZ/Do:E#qrOe.
k/"+6SDC#_&FHj*(YLxqeICmV&Q(oa1]6iuy#S=erqcz9NIO6#=_rrSQ8I%L(ZwZmxx=+0&jMbnMqylVT9s8##Zs@vA_Ig[zm`Igbx
7Pb/r!)IBNr)g!?>B&G^DbmPk2B6?c>A|@+X{i&1D.~MfbML8*yp>`J]@i[ykiuBt.d#0]HU>/"l,JgbOq,U;SJ;*%fp,Di3p.dE]cQW](C;33pp_^($9Oz9EDQUoS51:eLt^x;.ZII5C&oJLK=/qQgAfXpC>?rDAbv;)$1.M
"&wfo/zPi7h3uXhB-c|Xl&8=;44G_ZD9~6I>Ts-F$f*9ZeLT*$ad22.Z+clP
C6.8F5FenWh^[wK?_|l/16BdKybmhuR0ZnQv1m7ZT%NL3,A$F`/d+I?heklRwB:x.*m>-*rQ8O?ydvPzq{%u#5HaKB#@5g3)v(I?"K
Zv)1:ViVIrsrr-m4Aj?+{poG<,d46>,geSr/pncK?CU1(NJRC*JRQ(VR!)4v@Y=
g"lP:MM[riZs1i2/+Fhi9t..Iyz@Ff8L$K?vS!+RL0zKTIk&8HLd~_aFhD_2KuS+w,"`6o~[7Scxio?+I6)j"NG2$f54Jr;-fY[ku!O2m8Ao9q]ALiN#c8xw@6Q=J%+?P]MsK(RT|ubj02>TIgsTW^AJ{qAyW@_LGdCeme[YVj<Yiir7j8,!gt
&,PbF[
2sV?w;wW_3ca8w@%J-EW`+SX6&}olP~$Cd!$B5u8[Gyg!O{d/dq1Ba/#!UBaD#K
nvfZ(@A!1&sT,@mOi04:n9LSk<@l6rk#8ak:.h{T"5N:}*CDm9H_-j:GsuVSuNJx2?~"-s(0,)d^5PY^[snXE,]=+)Fv}XF044mD(I-<gI/71G
=l`h
1S$BUygwvG;[U[&9j*RRP8$v9M:ocgfD+K{_?_L8{P|+E8B@}0z=`N7U
9g!"_[308`61[9nIQ"!KmQ#+N_-<mu]d*oZBRt11wxZ~a^9Pd/>h@]l3VsJdt@vY95cpgK[z7yG^LEYO&[,:315Rg~bepw;qO4dD>`F76,%2hvua`"$ig9O]b&T9yTQi
CS3B5LrbX(zMq9zmUld[U`X_,"JA<%<SA9rv53MdJW*1X_eQ8[%l-+9:In&&BB=Ur%T-Uey.4KR>O.jCI-Zp.g1
0[8+c<ke:Ahh(>jS2O8k5L$w9M<KvaDxsaZlKV_Z!fhMp+.n,y}:[.SL=+0(b]T4Jb(]ZQ{a2x%Cp^K-NJ*pDA@v]2/Dt/K167[j9y1[f8]^;dbHC2p`]3{T$ioCS.Kf4rEIDB(RDuL$1<uFS^rY5n!F_U4.LYvD~66f~PPV9>A]w.f7gd9a<8-NuA7.5"1Z/f7j7K5`E)DZ+yJN}.lR~#;5.2[tSX:q:d805hBZL2/G2IGpv%4UIcJ_R>C]9$(uUe:i0tgLb!y?
b(K&[-cX]J^T+re0Ji#,;b/-AcpQlc[oR*nJ"UccBF:rx@-
c[mVDmqwB0`ro-73sZ@^x:c/]-+vB;T{4:x/4zb5vP)p/rv~CH>18aJ{2[[$>h0GK/O+>a:t9NE;FLx0rqw@^%5Hd&x#vAj0*//
a__KIZRVq4XF[k<]N>$]hrcn_yO~4AHGqq#VGk,kPy17tPJB2fWt
7x|GF#%8rxD
6FQQ%#TfUfw;4)rC1!XSVu[T(?;VMRv%Tt;rgQGA;+c?jrV)M2?,!g#KW5aGlX<;pWp=!qR=01.I]V{-$FQQgv6A
%52ZZP"kDy_)%A3!lLJ:ZEs9Ow1d)y:B!;%aaD772hwI]68L!8H@=kh_^y$e/a_~<5BlpOF,J,#B-cI[#=.6S9=*?lU9T7={Y,Z.]=hIljYkdl@OjJ5N@cDY3NUPZ)`Mb1=5=M4-^M&@=1y>7K6s[P"NO#ql$(G#nXM?@S<%,6XG.(8IT1[43YU]m__D1urqdCL3bxvl/{kG3/2rcdqY$)"Radh*v53V73rmRLO7ow.87G2$1ZM%CWLYN?yPv0vRbnw7-WXUtP*p:U*IC+VGIa2kcBX^*;
uPSS<=3[]hsY$9+T<
>GC0VCcx_qaKd,dm&wK%KF]T<@rt|<}lUEewI#/k]iq5mY22z5AGogJ=c01s~(yC
SmMe)d7#if;9hf+$6&E`YwGH0"t*eHR4kIW~19o#dY)=oU?dD1`@YuR<koMX9rdpU`jLMMVaH7Ci3z+{Cc@J5Q<45m2=yF^8T$0"L6J%GE7Uq|IMktC]9.4JjO9F&:8z8W]G#xO+mCQ%jTgWbMS">"@
pKV|_VL/O=b[NMLKt%MIq]v5HNs=82`fY)aUECQ
5lxZ]582f+DpNarDjgk$OGj$y4hqw~ZJH%fh4ZpF4}F&K/YaIQHNw8)~KRGS4-^<L=qIt<>8(W1f6VpcW4"R;zAW`W1en2_!NsW7D!7QFm5hjVVOf@A.Xz;CHH&mDUJj9j1<U`nlbH&84rRZg;2"cA.|2vg>ZueJ+#Na3iF6l;2pm:@!GUj1)oZP-z0u%=.Uo4A5
~n0Q^ai^YQcV3`rB6I_)!VnV399sz_^d,@Bq1;bh64X*CG4HQs3vLFca.5T0Z!2Y-uRI-["DaCDKbbgECul[;csN]_Ey0,RB5
Pm6n!-,:[o0^FZEtMp-"Jtk?SOi4^eUb.]Qj_s2ujifT7JyVMgRs5dk#yLUUL83hYfUv;f7"GI9sFC=MCJ//iP7gD(ZLNP6p+Bcu=!X"#]iorAt+d>t,.s`W!Krw>W.WErw$3tt(gh]y"dB;UTX6&HH8WbM^~$7F(q{t"18h_1K=[UgR*0Y^J(vaH/kl?1JNQ[c@y2i_yl`?}JH_2Uh;RWOOL&hl9ihqZ35.2tMZudaAiPP`L$bL>4zTW@:^if-"U]g%di{/I5&cwlhj(x7aFUnC6Y9n]h^ev4|?Z4p&Ivd*nc@]C:yG=$o
NfH[.ioSaHegA3~3ay:u6KxD9aoY#@5M~PIt@<LC<ak_ze>t$JJGt%0k76PG<)<EfE,U,IX3}v!:THoFtk.;v5#p9uQB,@~@OSMy[,E/oS}B7J`<S$P-%SCh~;Na)AdX,cp&zWJ?A]!+R>TPm)NUH[+!t6/IfH*Rr]DpgDP&aCq^mmz6{g`/VLAv6j[K`HuslRZh=`S@*>%D@lj/~r=C&Ufd7>:SdZ6;*]SPbM3rv*vX.
(FiCICLD2!7
iRaCu
LXNHeQ!dk..:D1oiTkyrAO{?=R/<>&Jk@ku9
SGX*l,KIlE9B2OOl&@KWTpIQW*poq26IK*x,Jz`vgK#*#Ck34>/Tll]-thO2ys5"tc2g+7K|h;BC.boA`yMm?Bm^S7b>;a#Z%8#:2eW!I>#)-hy{#LhJxG?X0]s,*%(7$M_6AKd*=*EkJ#[DQrWqgQ[W9#T@+hOknMxexrv~8"82MvrP,CpFsMG,LNEkj3w#8fy#Btsh6|Ui*r%2aoq$Q"8JUyeNd?o?u1Uic{Me?{+1Ekt0?m/B(r/?wf<B]s/3hkPu[s,s@Wm.1md;Gam9#x<d4Ir[6-QZf8@PX!90.0D]*wrgIt_=fOehTG4y(UiD1Z9Xe>i->z?n`?#X337>B3Y]8QuK8>E|^h%g>x6_Zcu:Tm8}*uaur,4Du"2@J$(tpDWl!)Y_EP);&M_/_6)U8d]@2{dy5Oz#Hz1v)i`Y94mhM|:A#lyw^FZ0JSxW&p<42De2P|5}de;/JL`/Z?5Uh
*mXgA-pO:1+.uGbCLs;iJ3Zev_>FEI_BZ&f.Am.>/]E<jbRb)07K@w,adoWyAU2}2Vd_@z[eXpM>vE(prv@D32uv3x#o8MDDmO*/>RP(,m_3j^iz"e<@$JLxuv1?^")f3q.0l2yn"j"AFo]i5]9}1pn!`^nj4AFPVB]NU3*c5QEEJE(@!+T!b.BNFF?mfBsd79OyZe
aka944t5M#MS}G]k(0KO680r+d1T.&6LlO2N])/8Zrz=hl7<cI5V_s
L
`AcDsJi:@T<y;CuWH)KyQHGn=R[/t,lL-$]?y^&I9A`F"?2VY=QT(FloWr^3!e!lR`>.Z6Ha23"q4FL/5TKe&d9RH]DmV6%<C>mnsebK9P+w!a?Svkj>n`HBw8fgbyYIkb=8oWpt*MZ9Z!]27.1sp;CHrik`VxiJEe=!tnC5sFy|;2B+Ue"r[1,$20!ie4"BVmhj_iHvPZ.(9;akk.D4q6TQ.}6,^}lrQWQ$R!+
/F({6j_uvDSI*O`4MI-_:9Zrn{bwBnpK2LPJB$i)IiNgC
@50WIy5?wtat)&&%%xE@rOHjmp7O[Eaq=}N2]IxGI
o][Z5FZu0AXgCSfn@IE2?wa!SlKklr[+Oa(YDX)!tIUEkQIR+_Ui[-_bAzi#+Xa|AEQps;[sJH
$^uh/H(@+h4@F[^9`M,"&#^pt
EgwPaGJc4Onr(V>6YvJ<r*V>jf%wNL|n9wDYf
<D
Pq0)u8cU:OuGS!iZ@"-?.eUksaI==#_@eSF76j?P!r#ydHf&]e8{B~p*Lp:nHR3,CW%FCbR4Xq
%7qsKkZ.KKu&vMrv-Lsf6>*QhYu?%qX+a?m!;BC
nnxSI"5q$8kO19TpwN;DXA)yu>}uS@1fdFX0D)Sda0gde>E[b@K+TC++V6|hK-tx*e?q^;rb,CGZX9RDr8IRdECcbmmw"IT"yFY0gr60X[<%HDsh_3q0X=jW.:Yl]G;^iLP:]_MNt`Q8M(XO1vpTM8I(pSLJh;pv"370P,S`A_SpUGb>IB0"^$M#%wbZ_=djJdemd&F,,8NEr@T4`O|kJ[c-5h8Al5qAkw:&*K<+rdo$FA
,5kLHX?788Ybxkl5Wlqw6w1DeUCj2m
D3gH>R`[7aV
@<[#E(GJ=7W(zY
4?R1_0JrZ;63>[-F(bU8Yuo_+sG*hd]MW%IhUhsok(bN45@"B0cyG%mwM6:m<1Di^_.t8A$&cH+I#[_!VVp8n_7jOLnWvZ2(=~TxPbap)z&uQ8b=VsEG;.e8A*j{<<15wr7QcvSG:^E1Z{a
/(]6TtG?jV^s1_[*viX?h6xKr>g+,Rw`8~NlXAdTS{&e`)-}(."sC9)`R_1%trHGmvT*]Xs72SbxNVLg%S`N]FBV!&yp-:[wb,uWbtYU;qFrdt2=xsOCkia5[sie--3O(&6MnUi:qb<*4M%|%u.t);,K`o@[[iq)a#LUV3#z<&hLJGrK9%&tf;SdfA@Y@7&yBOn:%L`]L7lsaMsVh#,[i/<7nJ$0;z_t."Ib.pC#Fm4@CMs[!Fi~i"[Gs8M04[3`iwOS,KD2,/$ZwH2$r4xrVVj2wOvD/:)pW-hub{0UOF>"Q}5/I?d{m`]^5!L#>ls/
;M
q}k~etVzE8&y7u6[,}^5)ID;/C<y0)SoF%hyN/>S>bJ9DsM)Fc9,o50Y<@S+k~]Ho];b%j-cq|*?[dT0j*]^joITx#n}SDybuvx]
>ukH?G%,U-S>t^>"T5chmVek@Aev]YV#4vx"co+vpWoi&l~3$_ZJf"N0?@BpPY"Z,]+_4ISx{K>iW&.8Wrjr?lDv}V*?w5o5w89mUbHEEgEL^nCo[`q>TX8">Wj;}?g.
/mg52/B,(Zk[$tH
x,<)RN@q*M,CY{#=j^RWOQC;l/&HYXJSw>ck;$Uc9Wb/jwDq1&^?*z.50R&z!H7^=XS#[o:17}&%5;M`-74`7
v
3deblxV^R27g2pl^nghb95eqoxT|(b7+81]a$lNFH
Da*3o$EN/"iC8!R&G*R7D!)Ux8XlBD1>P8LHD]E5M~]fNb9b)~x&uU:@jGOZhB^PD4^@L
(r,`c0nuS*idc59H%N&WDghd$5t*3|>du0us,S0QLr3a_W4iO&`#Z43XdROry7D8A1PVwDk]@GkPJ(^w>br43#IW&f%kTb
5QOEGCF:{&@X)Tp]#Tswm&}-*w!m|3vB#Eg8;>h@tqVk];%L(!g]:Iwq*tX)HQr
84r$9,<;Rbj(c2BOw"=s(L&tW/L-a+m!YvcQ`?#je=e4X=g
C4at-
)MDmofI]@Ob2~TS[8EAVwhomg-y%:VmFRR-3OYA=iNXe[Lc%0Uza!RMlt(9H!gfjA$c1Cy+IQ`K^&f{bZD"^u3sJ$H&QCOlDU"L!wbx0Cf5NXkS`4)AyKnCG>EVvFl11gv-]4awq6:rAR"=[/cg%
x3VRF2kl3WCOGv6m"@8FDi$Z!KMgIEu4GHwGNnVh_c]T9*Y3#?2."{"<SA[_;.J;)iZRiYr%WNQ}=$E7E-AU`pF8o"f!ZZ!W>&E{YR<{!LfNonCX`p:%G>^p`lkwSut2+u&U7}e;s"K3R.Ocgs/!X1Zmy+3u5v&my,%
W"4`
*#UFq9y/(LS?&enjH;,oV^az%[lmbY@)XSe3$@NEe!$+rog,APWNMAE73>ibeDT!P[OLd&(&u8Q(=hDfpKjaqx!(Fq(+)_trik+G<B=ZzN5S)SH:i0CiSp1"kP|-_+$.9*>gp.S*=7eX]r]1sJL[;ma`l$R7Pd,h?/o1su0%hBl8n)Wc8k?w|<.Qf>=N4h&,VA}S1K2TW.8OZ1M
"dcTW4wc]HV`Cn-r8syx,QJI>>vINc"vvi+teT?,w*(FAckxCD4];5f<<]/eGuf@iQ@UgxWSq&Mh`lP!LQx/JTd:bg;r]Q0^>+O+o!#g%f5^}@`9/F/20@J9xA$3^)C^#x2i7S_gKlb5zbbl?pAyIGMMH/<-CT6$Pbk#p1RM8kvDPG*H]8l-v[Ae-"zf8[dMgvLKl/^#k-^Va>{w{"&9;.h6N"UR=$>L#;RAm4j!q2J,I3I%Zy1mH.C9o:7Dw-L&_,k3U8zjE("l(7hg}p0B},4`|6cy-qx_i>YYy(tbLYCLX`AeygWj)5!C.e<t9I;)-2Rp*I]
N=2K6l_o|q<EcX0$s8XO3KpWb!F7^b[g3qw$-C8`BaYC>M^p@Z/Q~>9PA?7A1UHVkDews(0HWaeUv;DJ/6}
{:ME,X/O#R7Fynw"bYIbq"&_F2@An$gh`qe:b-Z[.Ayh*GC0io.XQ?Yx(a9W-jO_uJaw^VX-(w?c!`U]x%@_@BiGT-HrY)Xm0tq"4M5Gn-cmAs~^Y-u`PD|05McY*mD%Pb(l`>0l
NLHSaU1Wj7YF/m#n#{e%?Wft6j_[f>p|LGx*bRI`t`nHP2wL8rEf*tYZa$1>r:e%7_E`W=Z3e1S`al/J(q1t`d`@&?pz$]-nFCr*xVieUC,c;6Wjt3>Q4&D~unS#kw,QBB25Y*U-&a/*+PK)]b"?+*j5:vaBQT(#Q:IiD3R]Q9!:k6iX+T`oI#,2DHPS
o.!eGpM+`&f
NfI+?i{hKNhD/v!`r4P?kgk))n9ILb%@r-9Xh*LL"sto.tqc%F@.TYxsI3KKH-1dC.8h@6^Bt(((PH3Rn!yaJJH_GbSHaBSl}/0*,s:aF%pb%b77lsb=r9PX&bPpz;>WW4V`>k7h^kjy<T[J>RPHpR.pmVi8rU98@VpAG#1q7nSJ!2/;B_PdwHyO:EBS>K=;B(/8%_-X!3=Z6;25ZF.gxs,!"Y__y:^l`M2J3NkbIbBr,-ep7-T?GXl(vjPY8@N25SpY<hnQ`Kw*148C^"L%{xp,7
EMpogj@gW0vmaN;X3aoP)5^gIGf]x$&lTpN[mHD)G`=(#WU;=VB,LT,#0Tf<a1A!
snQGO[-,!YZX
)L81:-ad0^LY6yeMHqbc>5ZhmW>l{7DXB#hN#et.U)k6jtuhE-BQ6Y3#Eq/:xIAZ#`[;5^>c"73qlTg:M?%<eP.,f;&=mqDT/PcU<h!gTRH?AL.XGZTx12q@WB
0o_>5`SI2,Far8R`*;UuKNlnvb=qEG@~V.h^E,o<SyT`&7(}X9s"0}[xo,aGDFtm>yA|^|JYV=lFry5s-XT7>C;/6?75pO[e4#E?`NYYN:M#t.u..6I+ip*4IZa~fi;t*X(p4"+dZg)Q0y.p4:Ndm2^p:ZE-(@$2bg7#<;2"W|9EPA&hlKYF1<mI*{+}w^O1b30eE/]~m#G2D3nDZfmw,3F^:OsHkw2]swQL>SU`x`2{2}5f&~FL*#BD
`Aeg(#VV6Gq/]vz5+(
J_E|
+cDM1?xXas/v
z(&cn|mIlLN6N~FA@>z#bEd^xt-f>TPk2dY@Pu^Zl|*.#BnU![JC&~w[yy7Id#HM2(S]BRmBs4V4j}@H@eNk&9.68P(eVN1DT|gf1J]"xo3M81Q&,v^I:_m~D.Zux>Nn`qKsn[W|BgXR-55Hy2r@n?XmXYd,kpxJnr:f={m55(=vc@NXGib5!?s%P5tClNAb&-hp7(6WtA=#5=Kmt4b
dbM>+COBJ1V=s/&bS1O@8n;FbTWM3yAufg=T+3TzCk``F!`}=6OmWJCEwpqz_r+o+4q,,|n-a.DUQP,0P{kcoAh)j`-;]yP+&7tL=Dh``!+u(I/C`BQm#o[r@Q&ORSla6?#O8GtI9V:"K%RYp4^."tuI(f2-h(-k6Kk@z),6H(`PJ}#;w?ITXkchRwjEeF6O@H&h+SbMa;c}v*NWc1NsRYtt6d<_GZ&23T2fz&v~T$l6cG:!g~GQ+GBt@F6>:mJ(4@2]QK`8&,q~`J"I_K0)!*vbYw4^Q
5,N6bng@)f/|+,ME`S-+$5#ZvQEH=""5-Byy#.HLf8AP%PX^ooAs*v0UM=S]i>Z/?yGDens3g:5pvBTFh&XS?*s/PgL_itx"Qc;aZXsn7sQexxJB&^"R>JIFUFZWLwa2poH*^p?/+D)lMd;Hz%Kb=GL;Fi!Mv@`@jiQscMOxq^<Kr2`xr)Re?V6B+dwZ?+0rt@e~2^-]nPhFU<(SR:GvF%K0UEf]o8K!^;?<nX$(M?-sdF+`]FU<XZ.2RV2!+tC`%2QOn<xdP"u;+`<ni?C]lb_Uo7;fNz6l3tSiCndy%r0n,p+{:@/VQkM+YY>b?O3fOBX_HgB[Y~nytM;c^uRq!#E"e7N-3jLu
;VI*iSWo@#z74aHUWN$5Ae3pE?B/R[~pvck%*<>GjvkUO6+<x!zJ(s|pE5WP0y%MRTuu/Im%u)yv;
>JmnU"d!yu
!f_je5y&GmX-uR*&2R==f~,kEoq.L!7cQ$vC^B27ln(5w^k`CNwsf/DhmpSJ^k05%6`Af0T{/66-2
A.=%Ay?awi?j:_#9Ln
&$JDZHpMm3#//h7(vehN[P^lS!V=#?x*.p/it[X0zl>v[(edj6)e6#)p![fHG%Xtc%Z#,Aj*{I~<.!w31a3.3R]3<q{)pW(ii(9)|9qmMcT#D
_EjWOyB>ckls!V;]{0},eesMVSu[ie
-qdmZ&Md2|HvuZg"D/ZA%tKk[H_]5YQoA[h2`l2x!e=.x^+N.rS}Ilebss6W3hX4AL<R9=UzX(L$m4*c_XxrLK,-:rs=2Um~"q.|N#^qw=4x&VPwID8&Zy.QEk
gt"o-9a`!+UsQLn8Q-"CdfEe9tFR;`>mp]|LNQ~QP#
or,@nb`Jt{Wi`&?X^u.V<G=#Dq]mW@X78_)]ZtK|JLU1mkS1SFjqe:(sABYvb<N(^e5aq_MujJ(ZGx%2i]S3-2KZgMypu4:Fe41O;CG"K!pk7b5Pb-ZHI+!w*EQwbc2r4Sn*9OkxDPk`K:kLFlSZ"0/]HY?Gj5/32}x}"4imyEHN^Z3|t3h"G6Tu5+ff4a-T(Rdzej=
>P1c,>th9H6
U<8Ow.r6fx[Hk(0Rr^+b`k&.7@@OMW:ceDrw?#i%UtWqN[m@aFO1R^v2fiM}<hxd)dDmyR^LW7bSn"UDcxAI2&oF6e3ze{/[Ac:Q8N]JI)]#(oQxb$o3+8f#vu&_pl#PO}T&0~e```-ZYi-j=,N:kvx8ClPBgPv<K<Srul5ARawoX(D$<`C!$WbGN!J
Dbj|D|!}#[d?6T3
=x0to?ROEawT6klea?ft;+
:K"SX<
Av3^y]eq2LA6$X8,a3?B#{p~RB9nlAXFstZ:[&8en-fDU5(#:f
:o9Bb$B[?WEaxJcRYr;BeB)&thsm~O4E!MUx!YdMfek"*eQP8?ZSFcLWD,u;3x4C?QX:x&a%Zs6jwq@:O%c0K!{8|"{NTSgC%q/1;"6r?50SOmB8|3X^d)21D]@GmDo/&wsn"Y}YeZh-X<,)XCp*)F:Wh2Fy>b,"*14
QJ~h<n8&Yt|@^;2XypTc&4`;dlFeBj3;1m`#&V)hXSa<x>].#9c6/Z#XmFC2FJSqXXrnn
fp2
E2Kolq{=7N}ih$O(phw<<f<qkRC_g3L+C7~P*p
GZQQSj6,&wDEf|M#=0TWxHh>nz"Uwh[-sDcJZ?52t`57+Uq;jqA;6eH}VUpLcWF[Jj#WGd]]^^OL@1%G@,=41](X([EDul(l-^?tVV/F6ZIl<}7ovdvP^A5/LP+ux0$FEsf[ZV[uby0;@GgQRWwi7>O:A}x,:b-c/AUdDV4v>;A+"z(TNgCUki_ebp!UK&[;;e48owPa#*mT^i-H*)CEEWoA;}w,^mkA*^D1jBWC%"3(QY+sa2hyjl%P-<QW7^!i48.L:Q/K4>SQTQL[(!hQe99EZu=QFv"3i5&GOp5,e4RLcbUE&ZKt3uY#R*:pR6&T-.Y)y]$)Nu`p0<jDc:)-)l1dyO0R&2R`ErK.JiOoF`4!u!5rj!qoi[aE,K#lByC#"LG,]_999r"T;!:mK%Mnsf&u+->ogGBu.Xlnt51LAz$,8_IX^l<(%(7="1Um$~9!@tJ9)Bv0p{HUW`=CwDs8.Zw{^_>[UtB+taXwtY7dxfh--~eKMTke4(nwa[>:M#)Rkjp.6`EhDi"_A&,3*@7Be0/[QnO`,q2T2T$cCYymS>aI
R,{la[5<`A3Rz#+
4WjAUTz9cST`609eJpA^@4?bJ6F:y)H5Ia!jNNUjm;=N:[HEW,7oxR9L{@U]NO7`r,+t`VT?YiJVKWf`vK6c``fyz4{jj(JPeJC+RI.RxsVlNG-T(t{/LK5yCjRLhx;=;!Ow94>^;^dq.[j5Th<H0n4cGm=u23tA=QOAdiQcE');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string(')hk^;xqDE.!v]yOYu=(i
Y)vs-KL22cTG!n"$h%T*V$&4/u<1-IIwU*8#JP`gsN$1g@6[stXya!^Sy)jW?D](JXw9HStw:`r/M`2pKZwzg3N$:QH#e"4tutpAp-7|E)c
l2yvhh%sdsumvY1&rnK|j1B-yt[j?(S=bZyMYQ,MY5T2;Tu$rbz#j5!#l[aqAfHbcBM&+~xdunM$gwyN$!%y-;WU$fcAq(7tu:e3TOXyrmB}1xB95$J.D9G$lYcP*!K[>2*Yof;_)H6;sbZ<I[XHRFD)ctKmHTnr^U
ij-V|Mq>![hvbcsvmDb9!R6yjqS7QPUnd&ne>)0fp1UA`z(O8v6
;wb3YoY>.R|Oi.4yoQ6L_V+#Ot
qg)k_(]8rcr:y]UcGpYE[W7|uT5AtR(av^I]c:4i:/jm9OZE6-.3o"k<v*iSd<t@B?l|v^l9HQ04x0Z%/fdA$HIyyVc64K?LlCFrCZQr"DCSSVy
SpLT@@&8Qlq`aj+|)t)+eh-uwS,xMA(4Out:,pLUwi<lXFZFJ8a
`g[4BX1vX`8X2$k
@zZg=JVaO;q`oB)->-=3s3IgyeRn-"3/-a+dq",
pac^X?u/6<9SEDwqHc%{Y3!ECbthO1<lmjTp;L:T=.f1j7on[LyrQqR(B#Yv4H:,/.0vak4BNV3U6LBoM;J]],7HFvGH&(jG&bxgbe.4`]eR

EnKyO+n|e&#gAwq}jXf![%;J]_M?tcJnh97d=|l|8zIYN7xL=NQVPM$;a;?*I#e!@#tNut_7UliTKILmx/$Lhsj~^N#-M3XxiQVEe?0<+pCnuuI7eCRi
-?g
a.msAWfjO2$"XwoN$K,?h!(tJU(j^$QIJPtl8oGfIgDN2Q;^5Ba^1dAb.#[/+a6j3`,SiBS]NObmY@oMNPSz&#F<(r(5rh>?G]U[Rc-x{uu%I7Es|<+qPV)aEAz9,"n90492P@<Be^:<`kksvl:=Yn5=>>LlMBm?Pa6wFe,Y^7x.%k#Sr8INmLPAUo/CG0++|T?;2?XEh%eMoY>FL1b<JcAW[>UY)&avS+,0OnC@iy;U4)
S^M`12NFv3s4.T98uyZt4g
v$IZ()dsW3/E9[_#Z82/v[5<AqbLId6yS2+nb?0$P4vog2*jtNSd!dB^vihx&LgO}JI@&rpHd#><&tEp7fne_Mn!2=3HdLT
E4AR<xJ&xH{W`+>!Yue"zfJ74d2F}2&;+%O^d,P)J"FMS9b08>zF,bXM~K-]b+Ltf^k<uOk4EUD,C[&IrZl?^,d$=KQ^=KiUDG[^Ej7D&P@)Vvk1)"m_z^h%6=M0`Z<;G4F4eSvB2B2J+=p:l,yV/e)x[i:2cr-U;)G?301d~8g(QIgYlGOP?JE)h_T.33E$DNL!Ne)YWA;T]KIk[>PBZ7v;5DEspHsx}#FpxdyB*
8E{iK)J=j;txWL*Bh+QB=peDHYq$Obc&}N=W`3z?c.$N-N,D7_Gr.1KN>ur.RNUpY(UCFT9[^^F;3j:]."+IU2MMs+MWXluX7V&deAx[&e+rCJJ
W-MKE0T:GN*/>Eg8QfWEn)xp[kWtjF{C:0,`v9AxNo%5`@,OxM6j:%
"+$z[{A}4kHEMp"~R1<EVclaFalN%7E%8W+[#7kk*p8$b*]dR>2]N6dnQUZ|i4Ehy!OMO2+mw/XZ0kYVdvF$RLLIcdZ>@i9_Z^os;~=X;|%0.Ktg;toG-EmkB$gY2O2Supr-yRokj31_7S0gOA+=%*p,q51aYEN4"~@`5=>Ln=kyNPh#)"8_!9u-+e9I=.N8+/S:2qJP@3C/%A5A(S3~:HDNX5ZOHt6DNu4:V3c)0Wea;7U`O$XG-X`M-!DDYsoU]F3Y6=-NpX7i->$7V-g<poC}A_34k+0??><o,hAj$_X}MBM4EPx!h|MR52]>:bg6N%lM.)v:I50_WRTK+CB}?ZhVJ!<m-QleCu^7ym;^+w&o2n)adR,F8SfoivA*^b0?#4mZ8,k+V3qWuz@pGjl/=PH?eTda>VH5%V_kT1+F-N*XyjTy",#
&JS:#X;CAq4AIZY(,o:B?<P0NPTTsw+].vsaQ,GZLK"Rf,7`daopujpBSZurLd
/"#?I)pwQGvCG%NacLA3k`"<C"e"n_T=};lH;pUs;>@c-nf>ifJ8(-?T(yiV*;se9rlaY.ZwJ?VSe*~K)A=dvRPOz]6$-qde
?~TMax(qCj6q3uY$0vBw"WApnD@;O3odW0=JLnxBy?C5N|67N]:2/hl4e"!o[>4qWO:^uC1/k$t2JFMkK+663epxS^2ZM#6be;_^NlV2]9AP^mM#qBKL8AxlY@gU7z"gSV=8&Bk`jeJ+DubV"&:5X^G4g9**c&wW!C;Akl`qlE3/T`s$:=j`bG$B/EhaxyXRd1_RC3Dzpfq9tCLpsW?LB
tRql$cIQMZd<F]Dr>Yg5&"Yc_))[Dqj$fbMF!>dVfi-)K._d[e24F4/p9L#5)`UD-Q*<P89}T=Nn#8(|mQUYy:jUiX:/%S*3OghvA,CeiTC(n^w4.Es>j[LW&[6O:ZPwx%n^=a.`Fc?uM1^R(_p(1RtMl~e(Z?bH7ppQ*Ii8oUIB0*)$]=)E;L+l!0u0tx-k@q"{K
;gP0#:"g(i@7VtC7YVw5qNhWN~>"K%%MK^CQ0xS^kWjQWZjxaI]TH`I~j-YMSM@HdNa-U%$Lvc.%S.k)mciue/bmj}l@Ob1d;[D-("[H;UCa95IGkrp>@;l3#Ss-Y/UlrX4HOJo#[7BiAXuUeP]k%Jht;O!6/B_S6vZbS~otk/e0d-3w=tLe]+8nBPy+-*ABq$#N<+TK04^j@6`NM`Z
y9U@k*,7Q?#*>|dVCFe&Z0-hd*sl$JiW/<k%_kFVXsoLsfc17*j!99K5(r>;aQ(]ae*J:lvSNRVVPYE]Dti*2:yHEj8I^09{HXUt:lYvTa_bq?QV&TAhXH:i2E)Y(;hbtmwZ%ko?<JKL[1KCSB:Ddb#ZvxuIS>Uw=L5(=S,BjS>:c1PM.KXI8H/JVo])pUmo(9;@9]j]DN.0oy"KCNJa^xc
Ltj8[Vs$jCqEA?%5lKm2A)v#1?d0mU^^12Tm(
Y*6i,aM2)#9S4,
^^}#BopY9#}IOCA<,fSF1<$W
L5m:!OQOZ>Z><X#>p/SRpyF/
ARNgpq=<[GrUr7uGS2P!h$|.!gQTGg?jjo.q$:NlfH9[w4+a>PJOid>10FhSnO&9RUh5JFbF?.E-!8hQ~^di|Q.]Q
}H/nUr1/1jF,@62RZHIP.9"kjw9sGIT&eH.tP&]20:o-IG`M(XSryi#b-3Cin-PRK7t!<I5&DkOu3ag=#)f<uZB),^f9G6soT>_j%KeGp9QgWcw6Xc+-0_i`6KH0kBk#|gXL{ZsE=]sErnh
J-+Ot/|R77CO(<@;sHAiX[sP(`JG>^v1(9~^M2/+`e&DYTO1_w^YlAB)=4Lr!l)I8?mj{?aNUyD,X02BRwr7v1Q2,)|hWlt]u=`AT8~/%R54mg!!aMhUH%0`>K6nU1CN~!CU#Y/A-NAUBM"M?W[gI$K)Zs$[K>[-G*%r*xcK:s*+77Y?Y.>
=qq?uuJZ1B}>u!-DJ=]:J[]>Ce/)YMid3Af+{Ui6kUhQ
>WB&eJ"nL>uQ#Y7VC-Q`H!;9L&aE.0J{/{jKO27s.#*t/~p]`3q3!voT-E:RczO}83P5E/^vgBb>y;Svl&gr#oSQ$f.6Z
"^B~TqU$)"JMLA8d??Qc>Qx[vQH"iI:j^5`uPq(ui4/+ngP""e!lZ#&kCC(E^`#O;5$|b/c
]:)$[-4()m$cG9$pCm-HZ~K[vp!hk=D"R/5Rk.Gkm~Mccw8h[/4Zc"ZVojM=RC,36A%,5
Ls8<Rdu1rF8OK}5,.mt~l35On`(dm/,Igk"Y(dy6z)`%.EejrD9xmBsoG`eHg$R|,k^VP{Lmg=i"X~gIgPOmGRg2^NPQ280.9)h;pn>$gYH1bJn+HG,gFTycr3cL12_&PMQ?5~1w8&?^MC_;sTB
e8`{=H^ut5A%E1?m;cO0k2<:y>I:e?^+qTj|eE&WKhq`"zHB#Ii2dES=vRY`K,o!+jB^413re>x"1c;/(ddrIbrayv@SLTBGvsq&/[]mXtUH=WKV@T]/?eZ{nf,iLJ)>R-i]C)u.@5Bm-~GQsN)TpKJJFyV=[_
*+rF[.{`K4u5OHNP7-Anl!,@t-T1e2=DSiiYpZ!1L0*jQ;60q.j9ay,a#oFs(ZpI]Q]XvvYMM"+fb`#([qPh_5[rFNrF:1~btFwfi[3I;S
w218^?[^U$QIgl1S*c_/^?))Z7[}k^mz!%M~kbD)4)w><Ro$0cUZ92wXC;:0nslp*Dm
)sV`92h|<4&s_.=L
4GibLmM&apO7o=j>W0YNt+i;@bP#asaZ]:y<Q&961vqkppbE;4
c`9]>@,=-@cKYu_ZU>f-B:03p->l-{u]?9%&;6WOXcp`G|6znr
fMUf;2E;EDWZe`j9=DC9]TNu3[&T.sp:4)f4!GmJBDI2$JqPgu3"_1
Ik#quX-y7[;2TtxGwz!P/?-v5oFDHeR-181ep}2Qlih`,)ob1F1efldF8<G*CT2$u7R;Kx*v@|DSQHNV47T/]y,/d!pS<<t"3t:.C50,Qj:F@ouEL?bCLcbHF-Qz-Mc8HnYLKR[Y?F<5YybujT0]NNDBAd>T7$e;#O&O5f3+S]HmF#U4b9SEx
M`*i<|[kX]@-U
jhId?vC|^[4sy&^K!1aHks+uM{ET^[<v7FRXysQavj?}TnFTwo,6r=,]
ltP`,b}3`o!O42*Z1B8gvRW)N4<qOA)q@=*b6A8u14`pW_^an4@RY#oVCG3[Enkp})iu<]#-f[7hSF8IR2W/4Jy>b"x0;YvV:qEAO"D:Ho>uKf7_wN5cP=],hfOs,EYvKRaU9@G8ow[=cCYh%;0PYGf-t@f_B2Zd#g[Qk6|S`u"86JG5&v|1Rk<0g)>W5^Pa
cOd7./18KcBaYt+;!J0k0n;8Tic42WIuN0)bo%pzRA_:%HY1^_X<N[V
-|E
]ZIoKG)^iJ(
0&fp:tlqcHOUgd-pU_AIh[crA+[-nD[0>|Ok6,%:l[vPOi6]w!e&mt]qcH0x`s?+WWmA7jE+?EG5yswy)8$XqOT9*AoFuJNJl1YLtv^Gv|n/p]vgfA1z1`nhf"5#%3,=/xUObiU2&`4.*H`z$@4{(W!U=A8hsKSIxuvf860jMcK(+AavT->Wl0_Z+>YK4]j?V/lT1GN*1Sh3!*@e>ShncJmS^kIzU4WkL!mG8ZirR?c*Ah.HRm8I>5(y3.D((+yhYay79ryIqvYE;mq6u6)p0IwOrrX8+{h>Fs&3fU(H)@l!x
c=iz5=<>N$16?nk$eq[U;LyOBHS$SmZ4YD#n)XegCE-6S^Y!$setPpDA)U[5j_n]YRY:;,qC(lVw7Px_jC5peB92a~?Pq]YFn8[k]HMgMl0&k,QX44Qe"V
TG&jagltP&vq}ApWG[A4|:9,Y2ZWHk8Pv^(=i-d5Mg(F^$VLbl@8Kw^.Ii:v+%Q(1$=FX:ei!:p3c6qQ,/WDcJ6n*/f5CO|N|XNN%QT-Zuu4=mXqcLGP7oOwKx`nNp+)>?n+Sj?`LB7/ErlbaJ5LVAA$TuW?pH*-1"M[[s^a.mXS#/#snLx.YrYs/il6<GshbAcmgnxX]k]ORCj)c*(3{XOY|
CqDG!]a!`EsgO,UH:rABm(C^QjJs`QgrTRoBRP#f]="mL[KWlh,jo]Rltc_[cx-(^QsJXaviPCn6E"hlN2W8V^36msBA?Bf^}<P2:pRz)GrBd+U^u)6GES0dz7`)RFCp%FLu7Qo7KW#?2$icrE<eIVNOFM2x[OQ>1>.q@L4s"c~$qUCj/ZDcD1jsx<dA`7SY0@ogdZVlUXzs#Aj53jySPrRsf(IcZ[Cf](}y6K1/p=yvG!-oXJf-_lC1P)hxNriObQ2V(h)ZKb@c5KD3LuwQyms8nyYU%qiNvG0e)kS`<ZOo%Ai%Az!Az"~+U:Tdj6h7K0/n)Q^w:5!;:4Mhu$?QdAQx"uc#"
3^]Eu<|kJwC!B4PQ7y6%{o>C-o:(kZ<iQ!|l0*!RaplOU2}xo^Kk(;r#~`%YuQ
rr,*N,0h=@iOi[wTu%!]s8$zQbpN+XBS[GnkLLk-XSoIM85@aVZc_&Fb+aRtNCF~?jkkZqquVpP!${4J-pt!0RTSJgxi][WR@>V/.GFd<4e%lH&iYa)^u=oHoCd(K;pn>5poll&}9>_l_@(Ksv^]_{H|qZlX#_x-!Mxtq766/kwAiVDEd?7j+GA3W7?=J`n*<n,DsfrHg;sL2ca1$Zn^9E/*-u`NhZ7IRHt*Fg3lpcCk
~/,(?Ur
ZMbxtE%F:b7wtaTkC)d<m0:QZ)Vt=&Y/0DzT?
9tyX$xw;P+jc[T+xHN<%*-mU!AIcdUX].T305?.JdIK%&p><haL>]U^iZC!*<U@#MP0:{6kOXVS.Zu(u&v.kZ1URS05:&RA/q/=.U,LRv57>Mcw]mA*HM;))Uv.?g!4N70>3UM<:@!e+*qOni.!Ec
%1s>~!K(Pt#!]I|,RW%<M/s=lPy-`A6Sif}8V3k%*3zA/(Gwmxq$cof:k-dBE9(Go=b_0p
[pop8m?8^"YIS!O13-76de8t0^mAu#(|b#z(pxy!+//<(kue?h2wK&#>/#EwG165g&rGp8^zd|cnE,@#7ir!Q}yzWVLwcr%[_Fp?;cOBf3IR%K_qgK[qfL"6(!ALHd<,2v,|2;
TGJ](JHph$K:_fl1%)@A2d46K%TknO3iV9VqO>4jE4%#RJt,lGs-dHi3%K?bjk(O)@(Jn#9Hy4}C[nSi&lM1FiR+&;."3TjZqJ`IX?Jc{"cnM64yooYQF_[g9_7ipn34#vunm:_K.(BN,Y"2pTvSG#pwA,]mq[1AGwl);6C;G^>@"hDN,ELoh:%
@/@uTFa[5R9:]ueDr751d2G?TfZB~G,?6_.Bo
}=qQJ@>*bOr>8M^63j@KQMA]Lm|tDV&wY>fTr@rXmx8
vj4<`4WY)w?UWUU>zXE:*n%x~e3Y2e5YlhZwyyBECNL)4pC:XPs/Khg(]mhSk5lC:j;+-:4`*`>p(_sZoyW9TRU6:ByJtchr2LR9(HyjOo>`}YgC_:3G<7v+L8MG;<KG|b{z"-J^)oBkgpL*~#H&(@Z$rNEm2=R
@!CY-;t@F3^WPHo$ob-I:R2rt8Mb31s!E;df-
yKZxxgb)PBZB6-uSY3x3DQ@<O7{r~>+qh&^CW+M%51Z.Z!AO8+~^#&]YIm*UdLaAM?d[AsC14j.2^qf;AD#4uUNJl)6UzyiN",JHg3xLBi%AVd]/&ncc#b1A

aL9NE@Z>s!Q+w8t_(`r(yqU@U=V?F(#r)$0P7k40~4/G""2/LiIk_te?Zk0D<y[M"r+3Lmr^"&QiEYQ_79CYeP]#EHQ4~,"k^PoskCs[^:Lfp*%&4Y-9bX%fbE=6e-.6+7JNfml38!ib~<no2_:&T8Vy;2ZKWt-.[N!1*k.Q_eqLqWv5[1:V;%=xM"mHKxH2Hr"EZ4#W@MguUUeqW-%$}yTUY!$VQt+v}BN8h1+raV"*!aTd}MFxtBxDr)M@S-:9NND<[Tt+^klN0w~mi*}&Oy
BU)l1`.42
O890IU%aX=4+N4I-,@0jS}t,KH?vT1,k$gdgg?2$d@*Eb8r?ouF4s!28SOA<(}rgNRlpnY"_u8wtD"+6cddMjd`2!(ui)W((8SuOy0V_i$#fyOHG0f&gE*P9v[&
FW-F6jLQ#am0%{[P!r(P.-uzhVGXT_.!Dwd9_iqnLu#z?Frgl7xOP&7anl=J?U@s/nbk3z-`#f/wTE/.rB4K8IuSp-C8f8k|Fis`[SdG7Pc1MR)}OoUY<^0~[OF?YG
Z;.7]/Miyv-Vl/xMv=v<8N7:~>oq4vK:Vx<KQcP/Ac^QT8n<(gU<.4*5oT5:w7}-`AE,.%{.6o=2FWR4plGw,i+jK=u5>#R2|
a9{^c=bn>#L
,Wn*mC3-AvW&oE8,{-)?SF,F]EXcN>u1s^1NW,KQgfJUYhIsn=f
AFur[;mnoN]UlPLMFuZOwfWPpb?5/1yanZZG:cH$LL0t.-*;/BA"7wnvwL]YRr~FP:95=[>La/a4YDTcMVz&9qIQ*q0i:M{W-6b+yo`-lu3<dOBT;#wB1HenAlF@PUr-,9Y[td()f<lEj2+wLtxWcKE$d1-tkmqE<^|t%xlVw7_3..94WKBRcyJn#x`s3s]Rz/4$n>/Ih2uy1HjiS
3=n$IU6;QoR3th!QZ#ibkgYce&K01uO2/]O2/h~
UapwTkkEPM*6u"1d$j~f5/hrN_Lq(/#go=Ov+8HE9jm[Jo]2Vy9Buf62w
`fY*YL:#$9B,X,-"8X;&,ssIG_8u7IiK]1bO
<;m9;W^L@"M"
=n2)yUR]"(Wa>qUFo;9
jE}B]"%gIw.)mbx2h0
b-Jl[hLp,k>hZbU4RRw%]9/itj:5<~$eZhhIgtc{
BjkiJvYJsp4b3G[)O2)Z*)3EN8(<Ybia,rSg:;!Mm*];l[yW*W[wJ^YL[`bYMm-iS7T!>eQT}7mSgp$83RClZ&HcR>#y#]C8PJK5Zb%eJc[`HK#Z@uQ<abNyd;Kg_du:7vVP2B$R-sP-<[(;&E+9ol*MeV@U#N2tIg6D~P,eh;drIPH,E:@+qDgSaSfP^+MGw.My~qgBW_k![s.M?Ryu>^6YO!bM",vrf,
wpF#2I%(TJ5N?F3E5%n{N*3v`!xjq`CMu582Cbm=kGe7`VDd@D&Ah,Zs60X@E<GP/,"e#lGv>H(3de)W[Q;<Pd`xBBV-KgKfv7^HO)@by96]ym5)4|=X19p".*O*bqFpMrGO4J+8$Jg~^hNj/+gs&")9[<Lr7#+$-hN:,|A&nfb1]b?1RGcR@"@4n*2I3hB%04kARETCo|#rB^*<tQmqth/wGQi-?KPoLRPhK;nSWB^/0=aI$3bHB=8j6kuXck0a?snEh;6z=<EZJtBTAsE-pg;4KO$SkUy?F9Y8@<_Bkb
Dhha^iiE|^2.gCPk~!mAw#)8%S|p>xNru;SdM[dBRj]"fE[pq)~0A0;G&%rW60n
<=s_R<,T6n$/~@Z!}T{lyg|Vfj&]OjLeyUM]RML/Mq"S_bWW5us(*&nXa"0EI7}STpR]]E:h&u*:b6a;Z]%fu!z]f;2i.
c*oA6@hYLouXYAteR<&y7uH%G`P:=VorB
#IAo1yb&jTb%@Qp1=<G3y+*Po.("z5OQLpaeW15ZbMWdHb8G2r>A"/T$b8uJA[poCR:orFgQLf`=p7sgm]EV&RVt[jDnMs>lM33$KBw.RpiN6uq&N%4
;%
fnZ?N>SO;nN7
QC{oYjumB.hrWk4g6fdx+Q3or1fce[=oy*t^#2:5Me4"{>cnncS=nD:`]Lvk&=SZ6XdW!YLKW7/1,wJ:6IS-N"6KFTSVFh,t>@IoNIPb,.HZJ;"Jo2{3SR&yRqr>Ity;IbJZ-u2&OJoltHG."6f+qGC,+0r9/$ueaOzA;OcX%
|iunC5MD7hm6(!snibJsq3Xt("mK!jM?jgnD20=;#MZG-;dD6O*lZKKw?B":.!_S4bxQ,RTlNCb5Oh=(zXD,ncFS0
S*"_"K$s9(mCg-g%.84kaVhjwa-58&#kdS2/gC^
MFnyQ;6OSYf`&X*eUWX/;vzNOU0`F?_6;]

d25!O%Cr;p~QnSVF$cWo<(.,6x-@%TMYsm@/L/.R,lFke%/),W9bT/qF1NePnJ6Os1:p)i%yL^wa00Vag3k-Y7WA9<_QMY$^LRi-Ny.&cb[x]3E="k%qRRXl.j_ejD]t<>A]UF|G+c^]E4`tREq20nb82ydU2c,j^"pDY2kCym=+>h-QPF;<u)bl+!DHuZaaK6a>Dm}B[,"1@uBB$*6@6Z}N$XnC
fwMEj&o<6^NDk`sJ&ZPSs?]3MB+6YyR4EC"]1$rA(fK*`h6`OIu)(%>Q&6@{wT?EJ"=FJ#Ze&:pBC][+"96k%(;ZBbe8-#*N%s%,"4*,nR)4=w4<b6ltIoCQA:$[cBwTvxEFajUB9hn/=5<e3G:{5@2$$9#!XftQa/Kn+Chb@ncp@gLRTy2.u<c}$})GV,ajSvZ[u^UOZX<#:Z>E/K8I9<]x"Rctk_qI;E:ts:`pL`$cEH(~J{3,vOiXGJnXb*CY7QXCncC%3ZnF`$I`qbqjdE?B#:SP1")L"sgVQpYX^y>;h(x6%)doib#eYr9V!M
nO#5{yZBR*};+jEt_e:=BoNQ.V!()FI$0;E=iv2r%buBk5@Vj9].ftlLy2#2O5)%B4CGKqwMPk)[8T>VB)%BGKM$aBS*hu}f7!sr*65&9<pdxWG(^,-Y*Q7WULo2nZaob$|SG?[fJT"yjg/mc^.5%O~m~
vHrEfBRMTBvL@ZIC8y$YEyvxQL9LJgs]~$p_.JoUy=.!>x*SS<bIEPaCF33#ptc:XRM0qF:n)QTcl/b@P;_4vS6y=^TqKP{nz
ZisE.J}.Mp[]wkBtBFfd#BO[1CvsdvYeuF52lrq3W[q$!uS^3[r<`7Gp$2&]3KaMh`@8Ll9*oiSZT2TNFhe-GkNrAFlA>h"iN7P%6<0J>!]!VE9ldkmqX
/*l!K-rUrPs<0+Wj&]hV0/hhNuTYbEFoe#2nG8zlkZ9qVMXKDtEgT?hmws|5TH_6.S+NH+S6h$|/iaR>)L;haIr_"7p]0c./DrzH4Q|J9i/Faqw8!LZhVJERiOL=(b0[HhU"z6;Jx
v$OlAo@ypx;d!ax3ckdh.ch?uH!rlNu36an0KP<"Cw=Z!oO^`G0:B9.*_sQ_wuyl2o&qVKL1Z_vxku+p6"BNG&5I%`YDFtN`(G"
H`SA$YUp:n8R]Jw1HfU^<q;-JqGJEC~*`2K5?jOvf<x))ofu-$&^l_+xnU}GS[j/Q7uSz<$A+n?`+_B1YH[x>jV)}r{%,5$m*xQG]&$cn?q=Kr5>9`6eul3/6H:3Q^pgVQ3$"QBZ?q)9sO=`~rl;xZrR,fv,A-y:mb+bGFm7Oid^9-O_c5y:msqj6ry)qlA^Knn;Hj_vxNej|Xq(aj43M0hb_b0c?s@mdlWR/DISj0Y/lxVe-k|Wa,"h*GQB%kgk:BX+C"MR;xLkwLsvv.
aXAl6[r~Kb@KTn=NmB3Id2+S7_Wkl/@-`FJF=^GqJX[xy)4W`tN>f8B8`"/!>L9|)oqJJJD&?
GPF$G55[*r@2>`7:q6*d,w[iP(<)AX/>4k6gc92>Yq%`D^5aiub]gfr%]=St5</W%#WY?$L0@vuRZUFS<$vso#Ta#yy0e9F:Rr%vs]4OFP5yE>/Ibws$3x7Bkdj1=+C7!AOlMd>1-#<vR
XZcBqfS_
flyG!c4K8IuGDI)gFa-4?v
ja`S%wt7te.nF
F}6bi.u<4R4:KkY`QLZ(7<>U3-neW$rdr|sWkl`?A~0:3Bn@6(Kln@]Ml8v;ueB.viT68q/?Xqagt/W2b:C`nS(i=`pdrw"HP?W?9|p5jv^<l]M+Chby<l=`@PrE&~v~nU9D9-.%a*.dnoDcM?2n."TReS#WrIrql*U]b[C@3o@1iot#
fs/`L5?7[gGrhW6JM&
My`E298Yj4e[Fhs"M|9>j&u{wOcC#1tE`yGNXD$RF&%-QBiankd-X;0Pw)=RiU4s8G%<<id`:_e>4Z+*njdGFLB8%;w8_}y3Y,HSkAktVR(^2jw<V`b4s)Fzy^ct-G".bQjee+DMMZm>gq;&x)S%XLhiec8Bmu;9>x<Jg9),P/$zP1TF
z3Jd)rxVDUJN~uQk$Z#J05?D$2.E_>0F4[(7NS4ijC@o()|M==Z?A,^4K?&Ir$*h>weqR5,O?idV]jUs-V==r62^J4nc_QCuOLEh:jHB?jG62scyYQtFu1.Wh]-?Nmz[C1b&-1xjbM|YBmGTyK8/@wU6HbWlm#((SBJ4[0v0[Tk,jq2D&(M%Ooj3<]O;8M+UjqDe@aTmkKGm
O`w9xVHN,PJLDMJ"/+9i[Eats=?EK,W!a}k(IUJ,G3qBfqK,tkY!y+4igbxxl#2f#<lRF)j{f#(H#tx^n#Guo`T@fL++mBJ]Fk[~&"]y_Ux#*Z[{EKW1[$XnPZrgr-)_x>1!R[^Pg^!@wp52Em4:C(d4#}<f03"_)#kGLBEKnI=(ypc26I*:c@"CHgIM^fF(27(1m,c`e*?CO3&PyET2jljG!&Q8--=HGN4-B6FhktUPS2A-*hmF_YQa38C|)jh,&7e
Y*;C_gniJpcD.Cxug3tF5!8W<Vgq"
yIUg^_x"k3AUq0fM?kUJVy=RN2*?lVk^9/lP/k4GsfrAX2h1@)sX={?r;]Ezd][25pVRMCx"#e-m8!4sy_V$"^HnQwa"I.0Q
<So=GNOqO"7M$p74lT"1dB24AFeGP3u<q
j*jVGxA8D6Eb?<YLuRR[^Z6!!;3tqf$][>wH"HFDb3)u$CmZN!5fA=<Y`ws;:7VjutyKm>/d-eg+C"aqeb0r+8+=#ai*-]?f,*q#}%v/@oN<v/ZZ#_Z<Nn,u"7cU@2Ch"XSVe")YB<=8,JM[G"[RfSD[>>c+C_UOZ=ub<Tybuh)<RxC29`v/QRgI2226fW0%sq|Dj;9T<lZ1Zn7a|Fv2jmZQCM#
o$hC1ePNA/T?
DZ8~`9sd"r">)=^yPEu=B*;qI49m,}tO5:XZF3[gb}d@-e3fAYlW`Hlr_!$CW&)&%GgFI&=8xDf"-m8ZQ-%,v/nkR:)Tv5-X_*gYj]*Vr|`G^/L{s^]<$%8+%zwWB1&9E2aDg44zL7``2H$eT`_+I%y0Qhb9[FZ(>g5|[Ak,/L3<0Cfs25PukKVqt
]tF<hB:o9nE>>RIlG~h7N,8`X]B$V-:KupsNlq:8K9NLC[Qk+Xx_s%]sLl49Xu$VBz2bTI9:pwNzRUAgg<4C*atb
At:4*?=C}GmV??"1Xdm+$(%<($Z=Pw8,]LfT#sed"7&!8fr?U/uhqB{I4h6^F<3[4"3*z<[Lc;GYm$)v%M5"a8gUs8h/r@mQNQ1`HHd!,QU=ZXZ!)WQ
O83+uv#!&,D?N-$0)60_D2&*VO)EMoVWNe~qooEWzY/@}lGr/8V%ZabfAx]cZ^FCA2I/-LntsRFPT4wW+ZY"O!MA4cWw8gK1CgYDm*]"ctqmei<#]K0VJwc[%=.[9db0P"<aV"G%#n>-~Nq,!=#SCM/S"8!tn/%)e"oom&#hzBzvzf2II?}a#tv``YG*qf4J,8
wm,jiSGqb4OEMHN;j
L|=O:/yZP)#&v=p!TdjHQc<S/PGgSh`_dgW`/Ia%d6c^=}Ob%5jw&SZZ0MaQ2[f|v"tQ!s%CeI3!&6kaO8)K"#P2jr39s{^vlrv&<"U3>6uY5IO?i6SJ"vPkqK4OHOu"8JZwHqtb[4_4bP5
hN5dO1T8:`QG70bI)Y17MiD:@o#pUMQsik<f8I
1T~D<E?7D;@,Z83diPq-j%4>*%,f~%-%]?6(*8v-wwcER":[qJ"fz#C8y[a7"aq"&Z9"}5NyfRol8rXL<mpBT&2<YKEuwy}8CKM)s.1;c4`1C6d&MnE3Do9NM+q9*qSS<<]"iCE7Iva1P2qvF=PKODoio*cpR-J:G)QYn*6#BZQLx?2YC!Lg,CcGEP6Mh%t%Y;9lQ8l$Z]4ydS{_yhT1Sm!Kwc-Rq:-[z5|dq$]lgkA[i[qS01G-d)dX.b3qCn.g9p2
y4=]_`{+pURH%"$%@x8W]wb>c[m2}/$Fa541L4"ur`Jj")[,P<4+{I7/)^QG:k+^]g91_a:?6Yr5%)f)0VPVqp{Bt.SRD<kx@EvaPl*L7;6W;I:y:in2_gEb8weA4IibSln50DZ"=GWI1`)KC_,t7[?SNt%9|6

HBsmhHtTCJ21@=pNnhe/?F;[)3Y"K
ABOWf%Ue])!^-@U<FyKIqav2*.Bm-8m.<6)*G)]w|8k0en1o5i$YB+?9"?#.%SuqB.8BJ(EGK2DMnkt%=<=&e`3OM>k]|o`"(
aqeW_5qkHtggg.<7OPOJER
_|F-4J(kO)JddEdC#*SkhpE4"l[O
d8C>LQ
;#
b^]a(X6PA6O0q_"%(HQ?c&j[09IG[x3FPs+U7tekGwR`
B=^lDa..L944wv]$`WE7C:Jpco
J)G)iQ;TlL
4D$rO8u$K3NA
[w}HLp"@r;lB
R{d[IoxrjXF"U1],]tE
mrN#1@5!OD?pc7RaE*=dq%D]]kA.FkvTA$?:jqDpx"1%`F;C4jRnUVE1##82VbV~`M7}5R]!hCvQ"OFzfuXTZ=YFf@:?=d/?H[<eV!mg:Q-+?3Zf/9#ySW*WZ>FDnd;:d5ksH/EX9y:tY$Y3/Sa1d13>+Q)%6NiL_=yaXA<Rst8=Dm3ZH`F_0V>bKjKHE"GN_N!gX$aU9Jf|DN4#3p;#>`#=0ZczS/$?;BC`sHkwxE3@3y@!9mk,-o6!9T]_bG%/!"[Nyn#).I[YOy2"y15G
`"-02hcAr]6/o9a7ij.k96:$eZb!>Mvao^TGjoHj5364>x}N~7y&Tw}q+Ck1zEaFplzGCEIfq4T]L6E7%O{vw2D)L`IuoEO+j]J7&+.QzNo5y-H+WlT;<>^5uL:-,07tz2etOQ.=->T?EIT"c1[.}pc`Tyn2HV^Img[:>=$Qu,j6M:iFwb=N-V}(Yi&XGY+Jrfw-L;MQ
#P7caA;FAb5pDn_bkkM+h2LgDD4@AdtjKjmEx}QLTAb%>|O3>k5O.1"n:G/T4H"3vp?mos)Z
m*NP)Bs,K`lTJ,9>My0$`jN-=qi5~@W^?D~mz>C-,y&
T&AMx8gk|GF"@i^aU#LUlyqF$HcDu=4,g`-6|muWir76_:gGx,+llq!7`+7w-JQA%<bld3>?Kbbb,BTQz?*xTC[G8w[TM9iFfxD5*5ZYM_iu4!`4>c1MGX+URPh$^is^>nNJN%Btlg{u]L;B6i4M[Bt<c&BxD7_<*S!cag=&>;s0n@s.uyB@Yv+6NNmtgL9s0i<G>O92Q]pifT?8V@eY`H<c
NeMmfoY^=:DY:)F93rDqa^h6t_M!VS8Ex3ShIci287M|(}S
e~tYC=;-
2@[#M;r,Q%~"<?-7z+
`5m09r$xL$0Y,+y!?]5Ii)#[l1n7a=HBd&5@[kN[8%>3!cKSH1+1Jn(~16W(ne9u>a!Ng}O=W:kA<jonb`XMSWpjcuQ";_x/j>%EpB=J""2z#&BBr!`/)JD!*OA7Z-;EIciV,~6rO
o{T@]s7u_kU{)Y^*h;"W.l"^6~^X=R#B7"KPLP;^<05Z6{q=,O81<:NbimYCY{@D#}Co,sQ.r5>}nOW6h?q;5k[qkdvvyDQUGI-(WoM-WZt@e+*D<Lj_+Xr7f?VG,J!HT{Nd[w%5IbySn(+RHv>8PO=d%SC;%;?9mW>u]}@OeY05Nurc]GP1u}3:0>>&"%S5trSkf}+y0@")Nmtp*
*Jq}f=
ARv7h"eo>dbY%8/(c+;P[ul]K2`C+*=NF[iW6+E.UL]J<r<E_w3Rx;eXJl@b>sHaBwNaH`{wAfjyDe)KxR]xKfiwCGmb~TuGR3/*&JQ1|q8Pwtn_[^e^}3|*#gx^<.Zq?xT=7S/)*vsF
$bwId?ez5p$;l;(iQ$C:VHI{,S/%tS[E[e26IVl6"l.a
+K?NgKiF[]QhBXl?rY-q"&+Y4Opx%jv$+m}O
$NK!8!Hdtq7Kh61UufXUVZr_I,r5XFj=-MYVtvx@rI%5(]/x55
As,H>tAb6O2%*@YB
nBTuJ82urU$aP>9q3,1>=n03]IpaW=)!lR&*u29=m2tn01yoDz-+K/a~*5x_2:+o6q9Xvc5TdLf7""i{%LF~E3f}&KmjL>H*/&sQ?("#
;o|dWxPo<rIuTq"orkhFMieL=vaEnj"e9.WnrnIoo@]3!>g.5?2n+!y.6RT!?`v"T#D^^J+e44/X$QIE@4wS*Z/[3F-ojRG_7WRuxAW[|s0vhZ}rq7hRe={E9A[<aSw"Ll!JPy
YV"i3f&r6$hqtAY@Duce1HR-M@8pj]2@ZJGhXF:WO)B<;w#Uf2s{7EdXiUIH2px&GiY;.4A%gzr/Mi<H(LYxp{vyi%-FQO1!N(<TF@3M--RZ<eIW!n/"
P:F%#im?~Z5/W`dK}HghBdqp6/e2<TNxaQ
4&C~;9*Ix~I}y%/j&7
rf3,USH"ZiD0$Y>u9JU1L4I)YGNv6[I#^fO_ptF/hd0y~Md,tt9ohf9[H`kIGIAu"1=."+)X:Ni@iY14LN4DPUU16Wb,/Hp]QaywN?vT9bR2Bn8PDON54hD2jPXo+O*f2wyr
A`kq-Si.5kPSFW+8<U8!
R;a
|?M#Q7]Z[B<?TU7%q4r#i9+PVfDEjCV13XJU_4j4P#}4d7`6F0ng`0qTZ,zgURgiHpxYB7QsE7V!+`rO+P{OVPJt,AV1Rke/dF-YM/w.>="j~aK3g!Ccft^AwbXrlb[hMSE)^lk_mvBW|0gh>Y)HW.";Y[;S$:hfSpm324U-&*~L~C=R>8;g)[F/
a&(m!I<:C`cO+iNv!n;1*BK76f5t[ax+jy(}(|FIN(.AV;34w
;SLmEjqpUi`4F<f6qYbdj(j<HUOAp$_SCsU0XE>>b*$P,^t
az5c/wdl.nOW?&o|ik-#Ik5cwZtr/Z<~9.X
6q-;$)#M$PHTX<r]F`K9ie??fJ7!L{3,m*X6rw_"!dypDkkK
IXR[7CDEOgIRkqZbDG.eKEC&Lo_,T]SrvA"faF}(FPt+9?q,6!R%ZE9+YKKAGB@MMmY*6QGmtG;mric@@Me@FV6=7c>tW9Vq/BV(8I(@#u]f^N,"MlMt@9kle3~R25iY/RPopyRs4)s;4cVQS!P#B8?b1)gIWV:Q=9.XJ4)7aT*,=>)v"^Yk"cWd
O]q.oL0Y7xG"H,:
)_9dG@3BxRs=maRzbN$&wM1,ceqeGOi`<9(Vp0C8es++319^q{5q>fRabIPGiH1Ue8N-
.iFhwV>JG60@}DTdUyKH8<HEHrM^6(LOCK[bnaT9VTXF.D8]/pDP"M
<JKiC`<?U.3o"K%+@~K:!v:@F!J2<`L+R.yQNCfziTL|`z$.c~!UI;S%m((out=>?=lxnoK3gW>?OO&*-E)ySLK
5vP%V@`{0%n/w
l%w#vFReCC<2=IKlizOgc:VKuP8Zu$6GYCKv?cDo7HP:Roe$yI[0x2u?fpu*Ls35GJ.$"]</(nh1fyuH?0#FWuYJh#]U6Dv3
<V
::4PR%nlL[Az=VU_1I]l[E;7kV*sSWQsl]0WVp#Lsr-bf%BSx}:CiQ3QiVD)@|*ze)Nfh|S/Rned"aGNDJ4fXHXE?&Spb@Q&8>tIJojz$l`W37Lxt]_6Sa4>-z`5.7&gBAGHE1.Dp]`:eG%@D%GU9&N00.oAubG4o*"OGLS!8h]9Hu:~VwCG17FpZ$*Wv92Rw`Q!ykY^[{jVCK_eom`bZvZ6UK.`2kiA^o`Yj
rtf`4o,dk-Wb3WarW`oDP8yhwPnS#P!*4c<tdWhWoYn/JF(^,R
iDOd~=jk_>(is[J]BEg!q_GCfHDa8#iX|o*56:s
7<daJcV16E1Z+WNG6j0,j/V&/Nc6Z2`&iU~hLGW0O(x![d@d,eNtgRIN2XFMyP7<|1&HU_LLJG0XxwX.CS0(F1E0T5nuJIsP`Ev;g)n_=:z`R#TdI+EgbR.[i9bY-S$)8sJx56)+?PR%crB73D*c0P|C>36L;Zj(-G8Ws5y
O3F:6"R_:BIF1E@W;y$*[>i2MMBIO1xw5WT+|hN/uy42b)j%Ts0jW4*0REsJz[;-EW^Ir9p8
LN467(W{=nY*"r9te!>sLK[s/k)[3W5hT>mk.xK)GZ6~udT(Rp[Km{`-X~51Vhg$WZO*,I
IYj@ska>
YaZrexZ(.@H6alAd;20TvSf|(fIjElB41+_lW|7#5
3JI#
u2DDz7vLlKyL8gew@?e-NQ7lyx|&{5(d8ZS1j/K@7xE8m5z$Jn_IkH!CnE1iklN;*MXn#/$UM;*gV"|tNv71^1YZKTz^vksQDc[eaGha+H[(<kD0E-o@1>jv{_on|_V6kC7A}`0EwalnutAOS#
^,jk?mSCbS<Cw1p+DJQ:!_8BJU=du6G3>.
Y_?VHl$lpY1B.L
!]k5sc=Z58``*"cCN<RONw^VJr%eI1Vm8`G7(4Q]`:RqvfeCf#=!&{c(XH3I>-Eq*m5{qLYnBds5x@DZ8#)vSO.Cy;fkJ?2
6B;0!z^c8Ml(msuuA`jN]9^MY2pcPXLK8tN~8G5a&60}3G7*3-E+L)2J,Xv!pc9MH<lEo0)GqC/KoP8]cIR_FG`:82Y
(ze="N-5
4
3n&w>0&*[!R2A+f/a
BY:BP/oNtXg/).-L~4!!YnVd-WJr7IXU/YiC(`8>1#+mQH3ZyF<Ja?69YHA<sk!(^T}uz3?84>xZQTmNhrxnr&fH>Cmq2iV4GTO<7n"j003?UtsA=Pjw!fY_3UrO_&[*BGae?RMRBc@`DeGPz!a?RgROh1LXq^C`>Z4mLQSOL@{q)l@lLcfP*w(?q4u4n!"@1TBea5Wso&fQ.y|,)&#^>h)$02^(hFqufSp:/Lfjpu*GaqAq5L(%jAHgA>LLG<_UgP-;!$Dga/-,R0/[
+A3sd_p0B?Lhl=B#.tp~r0KMJJKoVJEIw%ldWsk&(w&}S?Vod86DJ6Hk.:2p@ii&2-s13gblH
pX
#BRI*Pc^^mpmt2orqh)_sdWqJ[Di2V)RWb^P.5w`TYn.?uj+1Ai(rW~.~s_@&rFJRTv^25V(rRzOJB_WX:k>X=nw`$Q0P-RT>J3a(BRJ?2osIXBa7CprWl/5v]YMJwbRKHICrrl8wU-+cKpb">Vyho)-$l2a4;me*)SXa-6Q1]BGfTPh<<xh
x)_)TIJ%U&u;C!aUbN8ttQX{apY$#0h^)
h/4EmzBz-gum0UNy,;%;NH1q_rS4@nP9n<qxShfuuPE0(T,hEDMip}K!by]r1.GO5]e~@-_E
tz!7q-U/C[&)^8XQlJyGo>
hqE?[UZ`^!s;vAWY[an;&oCMS3r^<?$Jv]?+pQ<4jXwu1Q3yFw6s%H>)u(r4"uG)58A8sqW!8Kk7oV/k4r*vSdBX5F*vL9w15($u%6!FcEx.ppdJv}ODv)8:Q|EgZXyv>:]xkf[fMW*^:db2wj_7CWK5?@pT$I5K
~&7K=-kg/:C3(h8z%KrEob&Ia*Z5/+DpsTyrbw5wAA;.$,[`?q]<D/7%o2"i|v_>c/DaX2&w88|cWPwv@T&Oy.7q0t94%Esd(9"59#KV@]sQfV-9N.1anvkO^n+W2*9[I1Xh2)AcC;lyr&*QB@ab0J9KbQ]K+Fu8~JEyV:>0^Y1qx8w[s+z6OoB$$7@`[BO0#V>diPzU7ylx?$g?H8N5ZA]_9upe"`[t0d(q"J0Pa*C!OhCA5R[A)Xvy3e=f:
q=zy"4>()8.u@5T2-X+-Qd^$fLS&-;"fa0SM~aAI{hd[}y94!9-+#nlC%wTu
yU9)SCdmN^T$5O[{w$k{r,&:!D-#lX8ln[jUN!>Da%xptOVTFpw6(.
:cNoz11u~C=LAN!Yg%pBc$=kZ,(6O%*:AVKd]9+_*$?$!O0x_b@bula!3$xTw9^+0%{(Meo*,@H^jT90QF&d-`lxUUa1lV]rzV?4^*B?x
Z$e7-qI7Ts6cx0J%,oii&WXwUGoL)QbEm:A9az#Q{[[;")=j]CM%OA;>:u~qm3QeIu7
T(lCI4W@:#m6:k6K?2Oijw:e7,awgyKB!Y*n42Shs_f_^cCDX*Lp}He=O[l"c6jyy_$
.Jwx8JKbehRNGm4
/#~Ahx7PiW1GZ:!aHW<;n9ET2&<U1EI(4Gtr
,NM88NDrt
pkYX]/sw+B7v&e!<.#wGdCMl-*eC`!aj/&
%p>PI[D@h*Fyxkt,Y7
/e;~[=l(D1bN=~<TNFUz,/^:b08-r]jE"P]s>J;n"zc!q53$-a(zc6tDt)BT.XREsXn"PQ?%hcoFb6^hF^giw9-`E4j|)!lk(e#.6Fj.]!pLPx47+zk0gxF#_HXwsV96O"Prjd">=A"v!wZl(FCt)O
0&:&Y0f5yH&VdU&HVF
t.u?mu7zHm1{>P/]"TC(:_kGP@$yPZGe!J
<QBvjnb$"9.hHSy1C.:yV"z+q*k@/0,iZaCA@"yxX:Qcl&$2|K!m&
?>~N[dKo)Ao#kHy72SYTnC%-^(-.o%9J],&/s9),pQ(nx5@H_%<Yi=dtHS"!q,>Ar36.bA>)(Tf6s%V2.THb-8n829377xbU7`Z%L<$&|O%8fP-A7GxuPYOPlpLJ}89CGMj!:1)2S+z=G0RANDuG`0H#a1MBXg4Vp1c^VQ&H,>&q3qSSdNl*~B%+}YldnMox<u47i!4-%!Mo-YOyTdJ9Em7>g!.<3pgz$*`FP=lo|+O5yGuTZ*ov@,D[XoM.6hx#]PN+.hJ$(rSip&8UcwZ&W
?#cA#"!sJv;4%@HWa/@EhAPiaKBWwGU<dd4#dY.nI"M
bp*%uvS.&pjQKrS"~a7q)qWy@CfQ)yQ3=r2!3eX6qKzqZ`[%CQ4#
ai)maAe6[{I[!AV[#K([[IA"?-FS`4XO39:!Ccqjv<,O9bI$PY6_^e@S%Y8,:n-V*KW*b7(6o}g>chlm-W/aFC%3@$B:r`lF2pa5(vIwp[+Yt|*"fysSoK"fZIY>nB=b"m4H`eb"aZKMw%W2<$ou@/rHJtx+E5o,"~,fefe6!iRa&@Ya5uc1,iTnRuj&dZL][kqV!P?^9:7rYU6E4<i{7_8yRz;0"^<BN;=ZCcw7U
Y"BztjvL/j%Xx]NWCX=`j#&,<Y^:uz6YAl4wjLqaP8XftnP-oo=we6X0tS]9I]5BDPJvK|@_7Snu,^$HbbYzCMN.t>.h)P)uS8`t+cWSsQF4Iu:0Ahjdl,aK8LnUui@Qn7]fgXGxBj:P.v.~fKYx`[Qu>w4p>B)Sou`@!^&ysm/p,[3d6HVh+oV[&tr4!t:!,X:gLwoXf@F6LEanG>%[^<&4e%geClpR<rR#$WH
wO"P#38o4LtbDZ${r,axjin?*
B-%2<;cmVVac!aVU"E#LM1?EirCy*lXGl9^_,?gWGrM"#a<pwj/bWjt84d,.I(Jrmz:S!K9,-~/GFsDi6PNRR|8e_aT{>Que97u9w5K.3ix:FXW?4G.j^fX/m`a|Q^3HpZ*J^,:L`|Q#VhKZ
Af(KV+j//.t$0v(dfog_S/<wE8}ei:!vPBp;[NaLS1992G-jD+~j>!"#NDLjI"o=X+LW)#!=uRPO%^b3^@|%,#m*5mgrbsy8mN6l$#-(w7Ias2i`|^b`O.l;FP%N:%;fKnY6BQxTQ_tDT>{r!T@[x38o2qSWA]7Wkp3Nu9!N{.ZhvAX<W[45ON~mTd_xhJr.pSf0e0)5{.-eKo4=dD(qNw$)O$9*zFDR,kim&uH,-,A[h6lyR+/Zp.ZNtRBpFpYiv#^^OUk(T:SHw3`AqP)4tsKP/2*xli.-<vfi(nZ-c.gh&e^,!@{+8(q6eBcj?<SNxE&Mv(S#SJ/E_TB((mj/5VPOt$&oZ/5N_:s8v*!f7gYE^,TiV[57)pt/
CUD{j|9$?)3jwH&0j?F]*R;nXDIx_&[F.nk=JlS{(B=tvH8D4d+#>o0=
Ct`kX+J
#NU%8fJ#ml>GP9.4f3V+&lTO%+P*:rYg$`d"y08O6*oC5grX#ENvon7u>t)epfQ/=^x3&Qj1C0/GP[H`V38TwLPs_sbQ/d4XUV>ix-jNQ/u3GrQkU*K;;74RtXvNgOr5OkRj#d0gJjnP}=:U^K5f.M?:!8ATi3.yJ+oo2":fKUD>loRxeF-jZk**9e$QRwr2(3YO@(J=)g.8NU>I9_QIhELU^s.K/`@(
Rejk)1_T0Z[eU`L.N{(+ZdH,rfz!(/-e(GSRCf"oxwT5_G"hNMY7/|5ZX<A-@lc_QPG}g"BDSIC8#7+4#-4>dS,?XpBTC]9{<VR6dHI>8Eu[?JV/*f3PB&Ug+kCO0ySb(+ZL4cN>pKhS_4iy[U6-F?`@YM;qm;o{9?tP+FDs%mhmRZSaOs28NLH7ad!>k"NnSYqnZq>IjLK&!-c>bMm9`>2}
v;9Sn8p>:W-MAiK:;B0d~bo%y$?y)k>*("}=ev@yT2~og#^nb6d3vCUQ55
;EP`P7J,;?!Eml2OD)fwIcQ#$kW"i!1sm)9+aM6a_<Q^Oa>6lMQJ7gx$I2YT_[T<O51XSh4vj`ONN{0cQV!ouCC]3`cIKjG],!.ETHD$N+qv.de4RWorn*@7#%M3IZQ[X6y82_h&n|(hbG89dL`RRYe|c,/+
.pT_mBGNE#1,.T`OmJXbDM0&:nFgi6#W$lB#Xg0ts#[X58FHh:%^V>/?"
Uy8ejI_i@<IjxoTds5#-w;>;Tiz-G%t"Kjrd{A)#]puJF-w#yO%U<GJ>F*Js.3:v)Qp=:S#8^xr1iKxf!)PfrHHu*K1f}H(IYBL%%BJ
a?o>h.[Em/~w](3mxubR$pb//I
X!#q3Y9RK%YsfojG`:K(GghfT""4FOD>CK-Skx%1+)YDB=8T(~3T6Z
QsLQ#2:1J./@3r(LN#15T1ufHh!/pd;JAg&mSJfyL<7xDa2->%1Z1HDbp]#E!#t;HL
u}=k^qF2Mg66e=52+p"Sr#,g,8sleRAF=F(6C9L9>>yN$xZps:^W+<x1oj3=.YFl_D;UZ%T2`&k,aB0m2-._m"9Yih3yb.VeR.NhIkU)`FR:)MC6O9U+v{6_4g%Ig8/@wX"8bpuWZUcwR=CJ-r!b.D`^.Dc]aQ#D,>VV05X"#;]h((scU7dO"KUEZb5?R7k3Zv4{Oo<,j><W[N(
kyyn)o1w#J4VT94*I9$wte-2NfLFW+6VAgHEAMOBI=5
---05]"WP/-XVU/m!8d+vP!}ahbz"UEdq!$k4SGLBI"/0d9S`("V"hV]h83l2+W82C-hAGI$^)00:AA|0i;a0p-S=&hB<UNnC^MEgn0-["]gVt:;>>NY>,
,jdck-n(yFfCj3H8,p~f@GnDT

.w4!4kUS_|%eqv^FSAA+7,-Y(7y*AcM<la%Fi(qX(@QU6<0"I;)bKOCVVh,xia.E
Y6aau#
=omeKf,@:."wq%h%:xHw"Ejuf@OurZRb.93mk@*P3Q@oF`T~oD=WQwAj;?NRe[#E=69r1pZh/{j08)j1"569*`*S-U-VkX49ad:rS;1RMHw}w,&"pRdYTo-D`&e^BXK$#T*y3b"rn=C>OAJx<D)U8|QUC&q0@D*7.o[=m*P^c{xx.=kTrf4,3%liAZ6OfhC3UG3g7iNE${J:W&tH%,suVVoTr>-
1ZT*/;w%qg;J3(`gG34~MiN~_A;G[ICvJixNL~fpN5Q$)74pF2d8-sv]II8*h[A2rOF6`5,:G]W{
w6^t++TQJg=J|h9"PNa8D1)F+Z)XKQ}G1sKcUHh:V6T@L3wuC2--3^qMaO!=WR-2r$0`+D
1QN2NrO8Bv1oTI/e5@XmTv+MT5F%a)mHZr1=.GlQ%BqV%Cq|n
HD9SfY&PL`wT6wqXB4qq-qk]3:*I2}UWsON46f"ERKV|=V7U+{AIP.Q~w<`?bi,Bj{h;2NRLScWt_P("MK=oMgVz3-Jh8vIdjn7-WW^|R|_A)h.LhZx&jZn=!p-BJ8D_rAZ_R<[vQ"b~H
L,O188Cq
z
5m1WfAy>@qx6""#$BD
sZ!Mk:A5WT:sEiX#3,<>.TEZs/q(.u?=km,J;|q>z%p~4=FyMo=Oq1syS"+dI$e5Kmt*B)r!BhQYD"i!AU4ANbh}`S3A&joCd%(IZFpJ>vr"#aQAw|?D:~kGisYGZ89ZIgv6wZp`4:M%5%dq]L@ZwF%C_uu-%DBH7j;8(YQs;jetp1v,yUn097:#k0:+U5VD9[D%0$X4/3H`O5
y)d)xc$0"QurN>Dh:e}.X9Hm)I9ULYS1"il?H$l6O:|WieqdpA.IHOZKV!)Qt3G"d*5WZ4,qd0R?{_TZeYA=Ihi"+D_O`9;Mlo"Z_-:6f+H8YN2yPo"RPn^1hG=_U/6_bc,d19NaFwelS15O
O33gia>(o;4qX-_tgdqjX(=RL+,|mqM_fbrze|88mC1"y}GMWMh{g7;ve9]YJW0[d(i^(rq1ZAn=p^4$gXVQZiezRzYbI3T^Ku4yldJ0oumV4kgajOfE:&`-NtGz;IJ%SY`%8-a1l9KyIy[50bI-,,b&!e9,Nr4~IJ"hSoQ}sK62@(vxY^d*.X*Y,>9k5nHoc(FMai<QM7GrEXuCav/6L0q3QC#2.e?A`d;]E33~w?vSVFT?h^T+1)9x/)To+3>">@oRc:aV
/Aq#m_B5}uwJOtwZM%I)p$ci>#t!=-})GNp;-#lL/VYNHg~xw&ES_BTez_fUmc8l{f7$8R~X%K0R*jgZ;$Ab4`RQe@XO
D~>%**3Tr"i7NI#Wqa^yQu"68}J`9Ud:Zb3+I+T|VcHd9j#yt~DF0tGmV%W+&V@v6hh)EiA),G>WtXUX2w!iD4LSBl;T(f)UkPHIjhS
$iV3b5o@*~SZm7UnrrI(]Y^;ZJ*x0lP@D..br>c6rH*u0RuG9%+b[Tu.1{Y8M>*[$1UGmO-5%?0^(OJM050Yo/;&eWsGsW%hMcA#>IcY<ti]=kh`.DV5s337R,YHb#k`/D)UUKP%&H0C&}-"
HR1mvfT-3KzXmLrdU3C`W5@O.Z
8zE)qVlD&A>}00*IWzKNjbNrg37(W]7M]W!fS.afRO(dm)Y,ULG6]#yoCP,0vppn<I8Z57Wr3msaAAE34)Sf=6fo7rXVDdyFC^$]U.^HsJU|wK^H*?9q$.sE(,h~-FyA3#c<K5N1fJd~DH<EsI0{E,_.c<3q9RK&Y3iWZ6
"Mq.wy2;,GmJq2G;,Z:cT#>lCRl+j
(iH9.<%$)j/:^y}gNLG?iHH4bjEy}djx<ESAT@gw99%wa_8kvFW2DhI*Ib?([l"kHM2oP1Dq:)in1>wgqB4I#mzqdCpfvBVS9xKf"i{)XE2nKh!NxpcQ?7WKVlIIK+4rr>(c0,JrIaKIVhw,K$IP!@Ik;5O2$Yg,^ingcG#bmbNKN5jSM#XeK&&_"iM(oJBf7dU;UjE"!s9CXc@%J^>HwUS[E^}Xq?|kGLRS|1?>7uKI1C0V12rvfM=->LbyQeRd4Qes$uzQuWrU|GwQz$-&*N9/yGoC[aQ9tb-F+@#<spPDo&`#*yK-rh~$!sKg(?
Y?7}-S8^$47
Y3u<[b2v])2<gL!MZw)0FoQwi]%8gUmK?qP4rwp8WZDSfPwXGspU5;q8(ZU$?kD@"=]j.9#YQIv~NSU]eQ`{IBG2gqVT2k4#qqZ/m6DWxs],M<m#Sw2~3x5N)ePDJ^L8vj&!1bXtyOUy+V5[pDr((A>7OdD`>yo&(rg5TM>I#e&RMQZlSLsN3M5%_F8>R2Xs`f?[sVI@d)S!<JtHv"wW"v
Poi<O5Bb|Q2s:M%L4n(%NX$G?QC`GW#nwu@uj)J4i7kkb3b*Y1xt`(Nf]G
4q@Ih.ymBgewCO=QlFZv""y5OP_@3k2[)KA`V{"oX@LW9_AyQ3wnJ4FTPYIq%o["Y3TZ.a4C=>&Q;]&B+Z<@_5N~ddBeIa
{%Tc!eVP,=q/f=AR;d
:B1%+m
(s7=s"[0n/^Ib@PYg*gtYj$r?ODjO%u<<(7h*]UQT/s)DQsrwdd
uIooT.k_Hdl9eXnZ>k-@z->!TZLGNFCt}
FmHAWg00Fkfw2i2x
fY&ODPIzg=c*AZci._,3]=/$IL]@=)FfJ_8E[_Z!x&d6&AV;f^2,!-?"7">`J-NOVEe|(uS:=Lj;$m<(-A!@8iq=k:6H_j_~_{0+:>_Hon1H$84L1f
y?f/EeQubj?5x<]^##vx
ar+.Oa
)KT4OcI"33i31-_=/("=w8F?o<VYhuK36;S_/fR&mbzd%MI@AjYQah!R*6ffqT.w=^R]<xaGfTXFOFfgd%I;D;)+ekq3a$lj./5Z[^gQ34}YI#`CICn"<(v<x_z.kZ#vPg_Cf({
@Db:sD1.
J{*S*tVnCL;4NFdOe1:l73`}?+TI
yE-g2-#MJy[SU3
cLv%6ec_5-K_u[nrIa,%9,R7V4>T3x>*yKQM^{@x8r."nQB;OdbUZ<5z1yD-;#N0BESjg)+ybjZ^e$$;5Q)
/L0,WhdK295p$?-LAu$hpdn}F9Hr6)VDGR<?-A.t
!qBN<F8*&xon=BhWFxdW61+qr$6.p-J$^?
p[3)/KsY!cqw.k*DKvta5)BVOAvy3lUO,l8Qp$"l5`j`k.b9l/2]J}mFgT5(?ZZ)*KV(j[dr@d/R8}mB&>c
jDF>][O~f]KBh^`C"DLLy*0M&Ri_I
;+FbF,]pl3Y:8NuNZuE1R}oU[jf2%[ngA4ZnZHka2yK=iQuPKHfc#QHv,4OI]AtkJO&F%8W
E}WC*|(2;eraK/Qul]TI.yr+gnooG2)FEqCT-WYg=1O]^#;z@BO)i;y:[KF.*]t"921VIi5GA2aY#"T(B"x{OZiQCILZ:uP_o8&{$Iy9=?[._vT:kmJ~TW%aHlqsfj.Wh$n?pXp+k4&3e*U?tL*
64,s3qRZfR;]1DQ/mZYD&(06w*+:E!7nfW)TU:[w[VOz
Y?40&lxhlWjedb4VT0$ihrIEwpKj-)$n#x$cV?#_9x@dn:`L~fZtO4rD%(j8tw,6/,7i]R*,1y(B7w9xN]lt`<x
]3;5i"ic96R$;TvyGcT2bna3A#n/pWANE$I@p?
$59ZI>X2SPgob7)hmf#7D."}XUkHa(P6G:kohNAOjC924=(Q7x>G@Si]a)=Z>nWSdG>K,f,u7p=V-WG:-.uY`"82GX::2ebS/.27h7+FS>$(s3TTNZ)g&:gLbYiZZ-N{6MjM0f+EK+:<KO<!pz/%c|Cd(?4tW+F%M=c4HEl6yt5g3s4f&#dtOSN<BOXzAu*w.>Y2x&@;0>:3vO.Q,"CyN?FkKR3-)N/!8fxOiqfwE4mr=6qPe~-6jPg1D-hwrQe[vA#8M`#AGMsP[y!"]A6-j49!$U8N&"T/ZGN8Sic0/l3"j{JgkkxsA
=@oR:[o[[g#Ky(YuHG3{5Y)iA[Fc
s.L35tGPrfrf[mbN,;HB!0*XBETbT=Y4Fx
;;b[!_P+h!"TmXTO[XCETrt?jv8oj[D3KxaetvP~]@OM954]CE7K_CYWvvo$Np:ONsrO.Dqy8Zlh(+`$+*GK6)M
bFiZg1)t<>wEe!-[<+)jFGAxDOQo1yW4@1>>=2t<ywD!
^xEsS!.(@>v0ARgy+8A3(q*%_lA^q;?F3-<*M"PZNE_AQ*a/vc&6}rM=[J]k|1Vr5&bj4yFijblHbq%fQ6XTq5}Gq@O)b)+)k#x9kO]mPHu5Emb+66WLB(ch^!&nfW,.jwFrAa1sk,z%{SD!"6!S}_
r]kw64_FW)3`,vooSsiO%g9#]j:gQ{i=L4Npf}v0?[!:KadR$Kk3OQr@-oI?p8Ul`bf4riMDEq.K0^>.GOpQ@QJImV5|_950.EU%]]W*USB<$Z3_JK<Y<I2>*8@jDopV`pbdJ
:Q_jw;$T"vI;Fb<Lg33tJ]iO60Y]CMgM_+1X.@xL7Sr;TeM=ZD%v2D4E6Zj_#sv2lb5c`j%*ZAp*hF:?g>(Ce0y^[dLEe
r4gQ,}p=x@P"lALxN%VH:M/B9`Mf*_c#e9qaTd2R[8nd-d$[$*8(ierT4KfEgO4:5d;4+F4Vu|ETNl?G+Flh2i<x%}cq6/LY"x#=/AbdD"@I,(y<.ZCM>!r+KmcLfXaVoXDiLO)mf`KX<N8H?.%V;{^U/`3a[4N(6FgDGqXV02TaQ]J^lAU3Z{k5;O9aFNKJ+($6w0P;,6)ufZjb+)VD
1BCrepJ-kc6J:`-3sZLPYGsY[n1_l&W@C5(!aD.m
vHMo(s<-dui|.s&[Jamw+vW_Wbq>fMH_
55O?ybD-vmf48qabK-(?z%dV]@VEiC2MSx
H6d
@"+cpkKIQ%$9@+!H6a5&.F6!N=&]K?@=KY5c;[6C-0U~v=<?)<SK&*5tbVW?9;nW0u>1M%7>A;"!F)FG;L)-^Xl`WTq^$PiRV[D?*DQi`!)Ocq5=GbYm,;)hTsI]oDY@3JXg4/)Idz9ilAL3fsu39UHer[!?/o=I*QeHT8,WxK50,0:[/+"368/S#+d7$tc)3?3XB>(Bw|c@Wm0Y7mZ5BP?-9Ta*7qvDAu-QLb;fobKgZ+Y(;1;l!jSIvVYM+./h.@^K"d^CFNRMa,u}S[[#FTJ
51dol|lJ;EQN7Q.%;7
I8v?~Ng%O)Z`c`#p#QC$;xa./LE2zb_`k6BV
N8:#SngNP
,]fPm,.h6`_hVNw%n#2u::koimF~wnBHCz@q
LT2_cSkl3A4CTQJiJbJn`sH#en.@l@pa:>,sA,mOPsF`x`0q,t9WKQo,{e8CvUJE@;ht)4,H"P8UGZ:<HT-OVa:H5:4_Z31f2E.#cU2j~*:_wN*.)&1uc_.eD;_#:qE-Fk@Y89bW>.<@q+8%9a8OFbx%J![@4VOG<,mnz[D[:[Ta$568mbu^`Ao=kER8zk(h6"&J?&J0`+I`.ObsgxLI
wS0prrE.5]*ucjmB(ITZ2X^w<lIohhB(Lz$A#JJeD/%_,K`c<=il%{TAk65!aj]4evb;Fgd.x7"@.`l%
eb`;13Z@41{PV/4?V)8;1WJRM=6X@.>e!Nned64$bE2iLB0.oe]D-*+VCb%u.aQiRS28+(7(:t!W)B-3XW(8pu}.Z!ueXq`=<Dg&N4~S0IsBd%EO!-4$7lDQYAFY=naQOCy>eR):/,XxNAXwSlt_o;~QS&f5g*:sdk7kXgam9,NZUhsO+&0CGINI/>uL3_
FgQ#>xk;,hjzY{gSmA]Wpm+ihzwvaf5K@Elpa@y#:y>j9W4%%hE$u2Ss/30oe)k.?lj((FL)t0VTXeK@!5:M#~r[c[`Bz%B9(]q^=kEZ(0xY8X[AC#k_4v`N4v_c3+UL*bia>#c5-@s|fhwmfsTb3ar{JKt9tPUNY;E_0]KgCdB8+]GpA7#Cv3F+G
:+H~_rt*."E^m*epuFyM4mJL/?W`+Hs.t0S>$6pBhfS8d(XWX46/sk&^@aAc[/SgFNKMju18Tp<lgLD89vJ.B%/Jq*-
H?SA:M?IJyQ3k7$eHlh{*lKRj[k_:s7-xl,UFU4O359d^$Oq=mS1XUjDr&bKotw0jpj5o_rBc2+BS3#pvdXQe$*C[$(ddo
<VSUVNs9p)HQpA_M1tHF+Bf"7>B!v
voF=#-=J9!gD9+*P*@!%u*Res&/a<`YxBA3eA5FEJG`A~k_i^*0*4m<@gC@!Qt9dju6MU6sSTg)K]KVW=f!nuABmx2!24vNPAsZgPXu7[Y]$?paE+6?l;Zz"j]%pad%q!N%)Xj|
o]IXb:i9*iEuR&E4"UrTWr5WX_n.*,xPZPZY>3ll>WJua+.U(j1VAP
tw@(+`5#bCTY$p$Q(FQ)r#
q[E+q8Qpc8;Ij3EMzs~$_H/B,8y+]&=12e/=|>[[rS(oBe0JhNfn@wh-$e"1DamGootBHnRI]R#*P_/*}2)lr)&Q_^M%p=h<D0/ui%mZx+QHUd~(#Y>;:u1fxbxfSU?E0/tqCe>&ASO#L1cc{1!3HbJv+M
?<ATExM0G(>cFdo@$1p7?@9@Kfu5JRtSZ>m}yD]&m?H#Q8x~9t`J9Ckrc|FPiKrm`+XPKr`oBA
Ch)#nLU>V]4H;j|)TLo:.D1y/VjYB;EnGP_JYx#
&l.k8?>c}h40Fy6yJ]jJim)Yb4V[d1MJ9+ZjOftKpO.fi15boq^T@KuM6D-fmu%S_bgp]Xo%L@h6:U-D7nH(F82cdESiPP!v8+lM
L(&ms[q)KIxKorFh)fLr98eJT{1rqc
l6v%D#,_{9=Ez2d>6K4O>u>!828=]w1G`=u?Re$?L83mPaYRnM..]h?lJ7ViUYUA=]LA~0,8K=?X/uYfO:%F(_~H.FCZdl}5p2&qw+gw,crjm+FiW<s]dQBr>Zv5N`Qlu6&Z,[s0Z[<mB>bs`81"<n<WD
j5!_GsqUdK4ce.w:HU~@
V:er^vM"#oRz6aQ
Ni]SJD9im^$i]jC$[@3N>S]LkzFuC:;^`Dk>s#4?J`Qj0_6Esp9+3=90eLK+8T+FMX)ksPJ%<eA&SqKJYEkVr~t`+;yq&i>]`Mg6_GYeK&FK[{a#Jtm"5p4h8#Fdm<4BKws`B><{(yu9J_IUAj(rC+Etq{jm^4Bh;A8aXzs#@G,5EH76Bp`M9}i<kJX^O~#B:.XKoh`NJg@oVJfHvK?(x[sWU#Mx+5yj40L*"g
z58p~=yn3TsB!V+
W/Ku>&zSx<jGiQZt/]Z).kq<MTvvui,9dHPrhA;uSrHc],5ht#0JW>kb`Cik2hpsxUn]~e[H<L}@>%uF,;MZu+wRg
Y0IPgrJ?,Kx3:O&AyTeWelHt881
lF]y|lW,ZVr%2/C
(QRw*iRP.b//9#-[VYUYT75C0bbm&FB5J7cTnaOk84U#XG%s`!kad_<*q$E7Qm+.,(.X
h:=#;"Pr1GWkHb,N3COA133&.#DiQh:PZs]W!>/5u*Y/:DW8*SG2la#s$*AyH@jKdNb/Ac.r)g`_?{$3Y^3X1+6@p;+d3007#p0@4N#U9_J@LM=`9_/4b4fyV@Gu=$tkm;"c]eKN&Wh
N,mB+KYWm0p?>cY57546x(C:N2.t1$`a=T+!o?Q!
l>(XMD~3G$"fN
P8uQCTqs?
pO|YpXr;MP3<A&PuE6_py<Q;%Y=R
[33<#{)X9g8l_nVO)90;<b>xU`)1<XoyT@
C$}.Fl])DWm6e#$F"9KIk*L>^=sX4QZZ}Y,;>R]4QsBFeid=S#ZBv0M%WiRJpcD.CL`jOl1y6U&)p"3WdjW^>=8JFC@ilFvgHA9b.C4S}sSl07`*-kmr}I-6<ccLj:29KWoy<G$y_1b$`W/!r;n^hMp9DD&FY8B0R1L@=0(J+/er(e]+6/-kR)rP>Pw@4GB;:bYPO*69E]Fh-="o]1|D=wN^3q3cYAXp4%3u`"jG!dR8}DxrsJAD?PQ.`[uH|>MB(MTkLs$Uw%Lf"=DrY8a=1<go*G{$+a?
}%_!u]vi~V]cdVWy1qz.p@ot~3t9wdQLf,!@0`B*3rcSl<0L-<KK!6Vk0=!4yD[Oz0khdsG[aE9yxb(I&F@q$Whk*])6F5E_YO3JVo1FUq=Ln:edNUN2~xCJ{HmrU/k1cS."(E0K`jbIswQ9fh^(WxT3D9Vj}>f9iK]eCLI%T!Scz#@rHf$yH$^3u<1,KQ1lQew;B6a+4Jwg?._7vN9kvJk8igR?""La5,lX?k%B,QITQk%H[TD,V2tO*dDL9J>rZj3lY;E0!;
"]+cMNNC7*?"DLJ*IC6fA{<H*(AbIFMRj22NL/FQK:[Z,H_RL$N)hB>yF[Dd
^3U!IRdE*SSd_RUcy&kSm)WS]2ZYEW8,z2>Mk2Am_6ln,R&6"PiTtI-2&,n$;gEK!I~qh_i[ZU/y*k}$G+7s;1C#1Cg)1`uMONI$?YYLNpm9{ve*?9&WWuu%f=gN7ln2EWOd@_G$xJ<jX
;ElUG4_w1ms6%J[P3a(uSakPopUd8
$oK.67(9*+#KP[SNG;nd,5?PLh8Gwg=d_:U_Px-#UOEU9b,_bxM#6F~#uN6L5@Fo.]cERD+j?*d7u<2fSX+8>jOjK3)>9+
2V7Eu8fcg8@Ew34*QH#y#QM@Huo_+o-4FeDb#j</T
w<FZP;9|-!;bU=^Y!Gt8o7`O$e+>)?kvE&*c%{BTjuojPlox$}`.]o[vlRDKD(+RhtR#%$K3-6O|4<8S%^&#Ari0Tu.Y8{Pnd-VeTk"Ol#Wx$PK/<b3Y&b15-b`"?2-U"m5#Th5,/~AV`#i4o$3=#%@_b!flM$v$w}RrDH
H,mb`q8enFNni[[S;<P4al"P"?=Qn^./:jqh^Oa
2FZ]u?}4XsIV@^xEC!Dpg
8wY"e9nh!d{#6lt"^A|3_#[/vIwKIn!NFP(-`#+`q6,!HkpDKRqaRN9oK`|ngfk
0j&7XU_e$wbB|2{5BaGZ,1xm>Dl9_!sav_]aZu%KwE?1{]GNKJOU8"{UM143v>GK$*{3[T#$5dw6iVz8tHtj,1~uSU77F?U=/pY*xBzGL-E2vP}.ag&/aX[-{Jc$JWBf#DY5F8!R..Ww&WcP|dq)&hIGQ5.!p[~Su-^gRVAY8:vE
%b?wv}^z8OuC/&&.T,NUpp-T2S]{XE2t+"o`PQ0QT4]/g9@@AVYfL{TO];Ay2M.U(J6Q-``Md-eXNw*sscJAx=NAN]-zv%/a-6`,=|QT2XPOA7y+o5
z>U?mCarf[<s.c/ZX<]P@aa4qg0y7^mZ}t2g.5S1D$Kxa%xBAQ
]m4"c+,W)Zh13wlY&7nHH&"sCrRcydbj
zFE<%ww]0OXs-U>wwcmZAA;v>Kst
DC%@I(63.<xS?@aZ=XxwwPy.uY90`c!ZU=uBvUOM-jsrl/DBn4P7*@D2TCGeZE]kmwqn*~uXa:?nX_wCO5Uxk#cF=HC4rNG&nLmf?s"
wD?lR<xcZc^}4Sw~[x^-Rm0Md__J_6U2SA$UK:+E,7@xyUrwZ,)fyxcd8*n|67
^vJw51L3OR/b}2q2o](vnt4uuB|mj(14kexz"B=p/*):A+K.SA=Yz0@Fksw1v9sq<Flos<0HfTwx>H?mjA+y,fCON;x`Ek!/T(RU1f.a"L61V7N+.6v)_crx+3.KJy?Vs+`NK&z/-kx]fw<^k>NRQvJj&%`+WyG16A%(1R3Bz1gJUvARwofl8fW-BPzkKe;rKQC-Z*Pp6p;xO<jLASTkdMAiK,bJ=&;hirZ`bVCxpqpy~^yI{Rs6a5{Mc/*hGx.LdyHy-+
tT=K78X{SR(3[r9U#o=8>$,|&D-/vcbuPkdJbgonq`*9mo*xRq-G*B@*1eYg,#PT"h33,>m#CdRzp@HzrpaBg.2/;("2^VHp%&x67TbpCdfJuLJT/}ANDA"U8%GWMCUt+x@mMsdy$`C+W,)#n:b[;@j-v"w^+5TK*/2qp4@1<>`=8_wqbPH=hNHkq<%~Sim}o7%(w0feb_GSbU!"2u=~MtBTGfr$.[hqYg162}Og<>uO)r8Vm`w.<@!,J*MM!IhUfbJ>23ZW@T!!h@KBB,HngKX;!H$]u?9"_b%2s?H6*nLF1C.(e5*u,pQ*k?HxP,T!Kn(JYyxicG!gWM4I9orhC)5=.Jy1%L?/*9>1ay8peAd+wyi?hIe;Y9]&S?a
^</n-FH]M@J|xldjW-f&![9u9mD=FFD;rG=%eg
wDteSywU4Me+4-?-1FIBO,W/7-%b{oIsO"<XfN4.*W+JCaeY)e!+sZ,1Gf>nkE)4MXbgJh@Im`JJn^DD4*G#NlUvvC",BN$5#;h)9DAN0h3dZs&SSM{<XI85l]FqK>O)$,O9<`1)l+(dsaU9mBDwWJa2*Uz0Vi4+eHB$$KG^Ggr
:FOI-BU468+aKM%dFLPbK4Fq3W9lX5.RAXOX0)^V]sCi4Lo40,~nJ;YK-t+6;dlP:0#mq+pM]OG)Q0[^^1bMXNo79Ao5,Od_T!kC<RF_QpW;QD,v$/~++,$MQH%-%lRDi)es<Qug7gQ!5ebb$lLyd:fHTiwb![~y7fm5k[}c&edE{m)CS5ssmM>i2bSEaHc<t2P)o<b>>xoX+F@hYSxo+QcE
#h_T`zFSr4c[e!9#:)rGCjx44wOyb!QuqT]o,H-w!u:HR6;2Hp+
+f-dLe%-&#%a@X-EKlP0gfrVv*rnW#iL:.**!$CM?IvH:/KQa_fuK}[n*1IbLN<A[a"J$o#v]tu/9<"SCE,t#5e)x(=&!t4ku{E[V`.2%pSB]?;[+1VeveOuk=>cR,7EZ4&LldHqh.KuR0rP:I%#$z!w*6#AU?MS2,JZBc13Bsypf+BdLa?{#`"hv%vG*?,#,UQ~p]Y;]^"9SYPd`]J7
ROXE!Hv2iVOcmBB5^vDQL[O/2iR08nWAY(/K~968QGr7<4P*vF:w?=VK
4)3<]/2e6CfLFHXakm.dYl<_PU&HgI]3m,tJ@1[uFJI`,CnOY%a1ZlCU^TDR5Z!3YhKdXl%:7"N&6&K;!T;/9?-&O"Gr,`NQ<J!2!<B|VNve.?!#3;O_vxwVK2%-MF;v(4kz%8y-Id(Q(mRsCm@2f``8j4ccD(94NH;Kv8Z8NOXoRi.r*kK_&AH)OqnMI;!`s/dFe-ufCLZ;XT?ztlU+(2XpUcgU=7<D#>pn%5S~P0FC7Y.[yEDn
qQ>$jr]XpseS
8QOYrJ3!P}b]uj7m:g,H.Z+*,_9anIv$"!?8b
hJnVr9<eB-93Zyg.6~^f%]9:J^^AoY[$FdYe.Ie{%1!7VW[;rQ:[&|gFgG=df9G":q6w6fy?5S8hv>h}LZRwTfS]s!i3lb-2^)mQ/wZbkS5|H-bzia8js2$A6%2>J"TMT`wnq#k6muEay[L-jF
7%wM`PsCwk|,nCztv?2`vrI*//DrDJ6Pi[yK[E(9<Y5W+G4cJA
5-wL&qrsO*6z)fEm
uM[iN%]S/Xeff.H@b^>5-WCZLU4o}:;</%XPL7ZCW2?$u/T]C3.@nhH4`b>"9cIw4lq(5yMgp:`r
.m"I#IOYQ;H1QGhLJe"%f(*FquYxoM4<<rqS%DSf)})!q*x,nEN.HW,Bo6#RwY<2ABN)f4V8:O"L1Lme*k,K#XOJfqDV6N$}L)52K!%B7)pqj_>
.IvXIGII<EWSnl/E#&I?%^2mTJj|MK1x;]*:7}?qdWDWGF[k%8ymub
jJpl|:sCJiLHYe!lTObQknFCog_>[pS->]k;tjMZu<ho,/OLo;%FoY(7ua"1i$MbzhA>eP8Ip[yfRc0?tH"CkPUD[VoZRy*_A?_^`2MaX8
l0%ZQ(T
N(o&60(QPhy)c2;}*N"`naUAs
aD#)LefO>VC@,:sn`~<LglN%(dKoLDYr((VG8Af$CM2(i|$=!B;|NLuq1#qR"2(y-Z;Q8POQD[myV&SE&L:095@F0@:bUZM+-~AD/wq^&kQBu,lH+1E[V~(8BKlKizSahL*RtNk)xojEZLUcvv5#1=f4Usg}Qkh
V:#k#
)=-6hyDhw:Nhw>L$1Oti,o()mJ1kigE;q7HNK<8,27Zut;>|d%S39No[24[=!=;b6OVX5VnE*A]wvVGWoZL|$&T"Ic,r`e5af-*V9
Vr_5Su.pONdGFExP]HdrlYk3k+Q&C).~SwG@d~U/Vz
7!8-Ph9b#f(CEB2.;4W`.IMqrWHKcm!1M]wQ5^Zi2g)i3V8kfdp8W-P+zPEqS+M&8Oi1X.B/[TXqqKc1%=b!`b4m"#n+A5KC:o1%
.*%W9("S[~d
fiwSo957_@D(%_H;_CHt7ro%sqH{xR,lWK^$yT2&Mc_j5#;,J"tdBV4d=E2c5CBl`o0=d-Q=-T+|mZ/5TLVOSk+irLWA3WM/TT_v$+.V9G6EK$.Anajbl58`-982&Go3"d74Gv&2g:CC.JZ3({p<0}4#Jw$V^;Qf-))Cp~Bw5v(;aU&|[)tCB^f/tD
.rEeF
>4SV1bsK~F,o|7Z1avF
8W)!@KwesVn[OhS`n%JYP"/;YE7he"sI-U{bdHea2d_.BCQ$J/48<]OZe[C(zWs,HoYCGNG,w>.
YD3/#)IBdlys:Ex&0QZ6l]"H:);P`C?CMy(3nejkR.>oywsK56Lp29$SKQAa5^{<.o:?QXVBaFH)f@@)YS9(>bwZA6qq`>N9f#yR$k?qpiK!Jp1#iW*OmiS$8ty5oi:&kNY
|<C3,;??)$d(l:EsB&Axx1|LfQ2KmWbR0m._sD1U&3;8<Xb"cLk[9v/,:drGg/_D,h~8ql
QDo5wxHX]l;WZbEDfc)$5b/IGv8,?FB-oW%xQ9SHO6#v9pE<tFnxM>;-q-(]qmt/f86cTIca+.wEwDtnaIh+pyomi>k,5RE`Eg=ll-9.eHa!dmat8WL@AQ]xe^xsJ.5PyXF0&ir@IL%!LZ+<,VBH$hUTM-P%ecxv;4H>MF5J:ERRh(
yVBsHk1Idf$w$ooE=9*ua-Z%~Y{@
0v@55@CpJ?km,"BDI4`L,ZR*;6L97nomHI<wP>cFMxL$J;<tWc>Z?aE!E}w.ZhG
5]fR$DRrA?S
^Ztw!sPKnawttTx8l(SkwPrZ8;2m@$HSmQ0OW(s<hMn1X;B!nXuz82>6)h9UZ2+XXoqyFHS3`7-|e.8TIa/8NuZ[!a3T@e?iWcJCy=XDpHI8enn1xojYom=G!&ds;?vIz"h@=@[x%7X0uz;:K60iNP)kC;NON9)0]OoX3Bd>9skq%h!|
M#l1_og)Uqz.#NM,lGyf@cX^a?Zs=gftk&61
T:cM^ctr*|IBKPc#VY6[Cwq,X[Cl)9(8N0c>D7sKeNwo&W/=k$1:&:lOOr4sxU`/=0J:"8Zs!MV&
:`c4?W%nIrTY4#1e]r`w1xWJ|Rx1yyxl#TRiQJ<L;CQW@OH49xP^H*bP*a/&WR9n@iY6~$1dzWQmT5Mg.pEs75Y/16.=Nv>Q14^i^vv?=gv<Ed_#$SCK`$35O63bONLh2`2HZ2^"m-aG%#8Rzg[rek-/pc0<UqR7KopV*v)AM]xLscrA933rL_WkF6<_Z,-7B,;w3-)9KwO:#Ausvpu,&T#-g`]Q)<h$"fuC8)G&&*dUOT:[,WfX[.QSQwHbH`];7Gt"GskTtwpI?hsA-,.U?05a]pW9vjR#VPxR60@$|xl@3E=L
y6qCwQ&[%$`8B4;{8xk>
"U?.vLp8x@2g}gxgbPcjVN7<z8]xPD!e+d1FeL</r/pHeKqVs3<tgQ;42y+&X%X*1fRc>,>]^9#_[&<N5ZUCp6|d@4=p)DE!5i|y&f98|*}vn,;$
<*E-:
7ng!
F?j-=D[M36qk"#qX}i$c37i8Qb"y84L]6SOt}8!.iUeLGJ~xg#mG:,Q6kI8ES)LtW$6gr-[$8j#MGc~#{[kH:>dk`-5:WyE;ntaD@QI94IyrN[<7}U5ZcWF+K[e,PiD/ca0(9P2ZBP7YIyKL<
(u!"1g)hA6zS]uComrv94eKi9
P8E>r-;sXbErN:zrCHp9vW~t_MHr0u}q*5=LQdN_CyH2
C>aN,:a%91.6b=kgQ~*4#TSQ$]%3s/.2iM:!SkG`;:3C*]^O:4^Af5Yj:3v|$~-=.X3VR$0iH})9R%HFM$B2]b@8uHf(k^h?cB-FWtsYY9dRvQ>s.7jT0:LNLv0`L.K>QopuMbN8_,&vOFEzIE+!`,me@EH%Pa0qqPJ6R`9Xu"FEd#!y-g^:cdbaPR^bjVi
q~[8wC@YWhCDii%4fv#M5U<xQdwZ8r.DDDo+pGnY:{jy-3D0@%V3Jp2Hc%dAca=`(cd&97,/
wh|8>(xM&vyo|/TQqq8F5;3KgDt%&-)O.Ev
Hifi8"j6gsat1-,R
%KkCmGW2b~x57Lr7S0Y9NE^PndG[pk*}8^?UBe_,/EAi(f-thpvRj$5yxlW^K&V7$7MSaQ,]N8<Dw*0/viXnF&Q-?97wT$bQ_u_k(@tEW%`+bY@^CjZf!0i)j^$Qydms6Y6
&Hv>v:Ds`7UfFsG(9l3~>kIuf5&.sE7?Gq6At3R)Jm.4cA+5"0yN!m81g|/L^YIb3o+|M5I?Pif8<StLIZaB:q^xX.";tk,wK}r=b23q5w7@OY:.Wh]B4vVGY@eu08pr0xtu;]%MOa7SAoT%Z|1l]m4%rA
x[P3WDPA4U">u#V:y+~xa5}ocg4`^7|?YX.Gq&T/8-+f*)sP4:e49PKY<be+Y?hD%yb@ma>VjJrXPE9.heOA2NF57+ep3lK#m-yF-iv4vvD:&65T@,h,|g&@*)(/[Ir=:5"cEJX&1&3=|Mr0ajw
p:~9D?Q<24PEaD5)7&a#QgRa5jJI*$z^+rVG!Gf&JIyC:AhG..fq}&Fm<GQ@JIwho?QIle;DF9YT5
gh}v{Gr#f%~mfs=v0Hda>Nck#CJMkeRy8$@97nE_)Q4ZoENJ/[?2=dXv=dO[4"`V?bv4et#)]*@tYYl&#;UqsPY&}K9^"q7.69@.S"(_sl5N<ZIijCNOsYfmPaG%Hls]Sh*f{:3oU^eJc(T2,Q;)eOR6.5kgmOB1`@G9cn3)m7Q]xYST;Wb=
?iKp`KO6-W$%yS:4Uinj8*dLix:Xm{>AN@do>|3ae]jiT^f(i1TA*L])P`3pN9-?WfnzDb=uYP0zF#=e[C!aOB3UR$*m:_Ra[?&c9r1Nf,@"Jk+P[Y4,[`5VdHN}M7:`2<6dNxIsmNC(=f%2xMSe&,W3,|c+q-F?D_8pR3F[.FcR-vwJ_,lb=NWl5".h@@^PNe!9yf(
YZ%-?dW<=|.X&Svs:h@<pryyffU:PlM
;{g)x/3B@2VF_@(d.1_+?ISb`uZqPsHaF1p30`J0PI6r<r7ZRe17;RIjZ+]r(FSG<vEZrYm,.3TS_DE-HYEheE@pHO=Q.{GwCPE#sB"K"vt@!S9ir5-Q];h[Wx$4o]b5$$DB:|F+td+gr5Ty[2-FPq!k;:2hXwY`Q5SJ
hDG<=g2/MvE6*ZgEgSK?(V-VP9;.+,<j
nv$V9sGGXIDzo"hT7a@YtvLs$}m]v{Fy@<RK)a?}#/02dPmjby3.=:@_9[2S1`NxQI<N?CUwG8JPQ+(;9quR)q9gy>_]#SM!#z.h55J
]F)rFc5h@tSfe2!5#
uk.vhpqg-[N8+N]lp^r`1c)XV[=Oq:j<Q2gt!|s[s/3
r39lNX#>Pm!U/9`%-$5d8;Yd#o)@0:rn"qV34"qdNwXgKQBec_%=3X1o]?AHtO2[%pQTDCSysrM07G*$YxnlZMGX#jBBjunYnzEqw/,XKkI,Vf"<sWiSrJTo;meK@`7Mik@NK{kg.wFIrc/[?~gn`[BWIt#xBJxnb>R+@rkLEjGB"g0x6:l}E#2/q+v<=WFms&wcBfEK)kX#>DIK&y0@Hc:J1%vc=oY~bc3/6[EO?L5M.xr[SCVF`Er/u>O%%)r~+_,7CAO],]6(sv>d4i.p5<3E
a!OB(v!30voSSsjA0EC-}o<RA>n;8$%7]-p"H&UFLqAZ9yXlZE=$Knx37osYAdl`I!kvHLU=Tj]o>L(%SS?<tf+DH&q`]qN(o^8C/BP
3%T@etZjYKu$1Z0>ifb@_r<0Db<FRnV/s&b1O*cG|A,_!JMyYp?Xc_|p+<!SN!
tV$0Z8>SQM(Cdi,|a+Pn={V6Xb;q_@,;h8h)GhICOQgPZ^Y9tDBsOh&F#[J^TtSEIpXTTY^8"zHa=`N^yt^A0m)zw?mBuDc`>xKso5CLn=Ajw.G0iPycquBe,^/p,#B)[WbAKPpWw7d"@>Bq%V%`Uj%^$z;!%HtQ!v4Q%0Ci+&e#S@R3!P;-p9qfZ"H_=c2]Xk#f=l=MJW$z.hN9WXI<BG-PKh8P;6b4t*5t`aX&rz@Ql^=J>JBYq
?vGOb~w~5`EioCNd:].Y7[dt6H1AA3I!Dp5bE~l=:I`1
,U/;+>C5^jOxuAHyqovFap`ilt9sp:
dlvx^V7{bQLE`0.4Xc@P>}T|9Lf!Egm:0*4BYSZd
==|,}`+wlKh.jJnY;V8SX%rb%v49F1fkUkzy*hJGF3?9`X[U#>D8s?>w8tY^mO3@Q%$h>&MXP&}m
#tW/=+7`@,#p%{CD2Q!#L4s!AVV}kfq6LhL;xpcDFt_=[e?p=/G`OzxY-lnc)vAmBNu41QlT.$!K>tU!<_
]c?vCtGNEMm>/_hF_*x21;pN%xoqCxF@s"Q]@WD3+Ixx!U#)x/auC=FJNS37u%E!X:!$@JFv6wL-&f;7qYnr}TSvx+ZUHJ7_t^/GkTddxuS=LHu_mO,*l8m-y=@KUUdWg&Cs#;/v7k2q|1W.#!#mk`3;c"N_+XyXLJ42s"lG+Gs:IGiPDShJlQAb0trJzuQk:vD+z6.xNu2G[vn]vn1a&AVo7kb&/@5UiBv>pL|=QJpX2X?VT<5#/Egy^=0L@sH,o8R?L2&2<J!L7<>LU&v2L[+kDaO-]bx9<%w/B_RlT6/!;7GZMQ$OXq
fci5e.)~=~^58tbjhYizTm+=SS1Tj9yT.<FeI7$u8lr[?6:+1OWMy!G>(
d4C]@0>Te{+ZW[Ryw167w#J[
hd&J
<.Qlp[CT-ejk?zGtdTSc0`nyQPJUV!_j-qD=::[+Q@txmy,EJ">B^p0w"Q@jBzhB"ul#
TsPrK+K*Owc$]R6XP0&N525i=<
:7B%v(Bwdk:NBe+jis4[KTu)bej#F43>(bh{8%!j
Q%SY0Q*Nuk^>ds4->:9GFHF5SUP+@Xkp!wYo`)!LsFscR%;>`by=FmWEgR0)g-x-C,G*&&QRfTL4sX95^cHbBJHKg1cEl,O`2B!e`vML7ObR&eThr(Aq^Tp#e+}qsF5.K;Cl;&koxSgr,?kUoX+T?(pBmFm/f:5$>*
9?kqg+CR(:MfpNO.7{7[Gc7u.2tOgU:;FE?AouJw<Z&@F?b)5h3Maf4dIE3Rd^b[H)T3>klo.3hKTIUi%!*yUTCtDm&gV/2Di!`W<!@5:@ZB78rLjHRC68-ZyVPkgyaH&(Y^#_=RY`>O[NuOfux{@3f
I*#+K:=ZUfCLgt7euq##(5p3bs)zSwXB6+6[<fo*^th"eQISHjM=h&Gw0qBLDLZNc|1RK_I!V+XNq_!G`@V*aLY$@(d#>G#GWcYx5Z%ILj<UZh[Q.=[k[DRPO]RlPHA[K"1q6raXsBLhlo]`Ux1#crn4Fl!$F|>3?ctB.%s>E.CruE;bLK]]MFyAA5R0q2:S!)kYOIvdd<+=_cEL/4D#b5%|Cs%UU!u$QJe.3,bB`H?]bRE]^/c1,wk1QQ4VL4s%m
adW;im4jiw:)eG7hu0s.v*TI.)+a$l[Z.C+4p0Lp!j
v^YSO5WrW3FeN4AYL2
waJDBHU{w!0tw1Fu`tV4v8v4;aGE_Agk/i9vH*l(Q+_"@w@C
j1?(Cb$`8]WI%o`E!c`(E]<
"__F/3+ON;;>9J>3
sN`n-.4&+
!$8^HAa.YQB;9%L$t?=-__;?gWWTyX`tgt"F3D*Mt/<}TH;aR5S5>n#do+2Smd4kU>U>kAH~drF$knqNHR>r?npLr1*FSZ3G5oUSA|)QB*jt7dl+^T;tm%prv@#DGM[A*qu2Vq7hq)
UY%4|(@uZu^sQ!^8:DaoSQc2)4O&ic^(z5_S^`N401|Iqk`P+m0JKy{=jLSs|K*"*EwA}Q,2c*yr&xB$Thgf&n*A(:pqn/%6|OCNv6W?x*:B*sU["_,<.X&k-0f-$uOax*c1Z7c8Ux
Ceg+?
:84tA1H
SmpUHfgbtHn1I44@+F/~OsNj&2U^+&k#i;j=J7`QD)&,i9AZ0tZe:W9^GdgX5-v,,RP=
gHF4(/U=HGWqL^(@g`/j1Gq<6wRk,#w6<iFP,0HMay)e_uok2Gkj8Nf.>+o^51Lv
_2auK+ky!n,;*a-{/_a$AG;{tEhs[K,;^{av4^t9oDnq*}$No~j5_pK7[.<k_oIy[Y
~H/mx73]9c.E|WM;{1LXA]~;gbX;QlLAV8|t4l{%ca,^CvIh/bUCOr,nq+(445X?1W[*lsBho_C
cLN@*rd+r7+LNs)yJHA9|v|u{CjK(,rX
H1l[(wGEYS:?X/uxn9F?SaH"L/0{V,spB7?_t%5v`!LG;.^3UNcz[[x4(kJg@$_4G-gVFDjJkeI.8Zt(G[9.1_F#ON5~=,8578n[n<bYEnOkYZS=Jx[Bt;!K`sJHX1_6<MG[N7V*rN4G_OUYftyA+Qa4bo`J:,A|"(%krgn?BH4&b5$k
xe=a)`)Aos;Ae7<`Sa`]"V8Jf
=&T`sa4
=y@a3DUc0f
sRc;YU1L0#ldkn;$g,juL<+BY^%"kJ,3Z}J~nZll"[nLQ<7Xs;ja%Yn_n,c4K*r?+=iC>7-^;bhd5,r)):&3&8+$E><kx[JH+Sn7Zh@EjO%>GGiQ0NtY+!VrI,n653ZhGnZp0
w|c[v?Qd3dWC^cNsy.Uz4)bRlfs1)r$<G2`ib=$9-xq,j_P-l]GXLZr|)T_gUv?I_MmmN*,WHn*uv:LW`zdTrsd/PTrM0WhtSJB*iw2
TKhjFJ1uJ$Y@7QYceJ<TEF3BWDIdr)T`!qQeC]ie>CglL+Z+;elz`jiprmC-cmaOlFm=wqF{+,:d`d2A4:rtc=)q3a4]#+X}bY`j[XD#]/#OGvrHnN!Y^u3hy*-$,:
aRga|7BvE[;FKRg1YqoirD2q*AJH?0GP>>17Ux+R[
](`#kww;@A,y>c:(LM-B(v,S)R~hkJSQ$=]8=yQ+TMOrOh?JPTKGQ#V8B%yF]KWLwJ2f%G7ox),J4@XS?1h`uGEvgJRY6NF`CpDm]"mZC=5C|4y
sEZ*x5<d{P=Sz-*X6EIB8I/o#xrt13k]jZ,lT^<S%;n;.dB9
y.eLj[D)Z%H?Zjt>m}3r#>c;?F=nPUq.ye[E9j2z/}nEb/P5Y0m>
|n<>[e8l38Ic1i*Lub{J_>c*D/t74_F^@uON~dCLN]nfs2lNp.A$7MBZSLDj#m~JuSnt1f(LLH[>l&Bb8T)Cc-%4Vo-"}8xR7l-RhYBADjdNq%ZHo7bv$QqfZr(R%FyCA56eCX35SI545JKahFQ"7[IM9vTXAbY4N[5_e/`]`6>jSKtG;rn`9F[]1:V,`WQevc::U6EFyT36{Q2"trjjl?SA%!g@P-K[2.OjckX*)/;5%E"?gOLb=m;(>=TcyblYXK,W5P"_FsP=9Gl`^&4T-BZ2b^+r}gl

R^sH;en;igG~j3Cz53ABD16U%jLyqAP@yv*qNrK?u.D;nDQr2BB:DjmNm`F)4ohQAOi@+#*;G)e_mI[[M
Cw:S,MhU7
mxUMHA@~9t_E?^sjC]W"`5wuH,_.xz,.R3hedbAq&znl:s<K<kJu$19pT:bXy;vd_tcZ`}n;=t;Z
hFtqEp~Zp].f0TBqAZv^CuK4?;mnu^B`)j`YM#Vr*`bNh.P17gOO:%f)dvUZiG^@Gf.]w1dr3Opbe);Cx^=@]N|rkk5JOV;Y,yrPJhhCQsSf`G~ug_92I_og3SG[zbHZ8Gk085w3j=5>~n,HBvDl}hdC5caxLe=p//B]3!`]eYZjdG!a$8|Xg06d]p]
u5H]rY_C?C[@h%Pe*5fGFCro)_fV!bvjHe2t3+A"sZ3sgir;n#wT+T$r}^uKC!qT
$QH|h
l]N`lFV[ubtrnL^
vRA>H(4^vp(Ot3ESlphOq/A$
-`G)]-i2O/^#VQ4W<xH#NfE4:vC_%#{Lw0$"ftq_jAXWaQ5$N&Nw+>[Pe>KO5ZP/v@v3]=sV94J><r]EH<Nv{Z+ma*C`=yBGKbxTWqZs(IQYR*J0pc9S-jch!Fyy_B8lm/`Q8%8lf^drDOSVqW8Dbb59b)gE3rpf%sgT)UJVVa,RT%S`"miD]gvRkkXDeRB<n^cwVfGM@Z4+3lQ[ug}j[]Y"g94rE+kw}4")8/U*tkbS05d`)4q3@sPF[`gwadsj5LG(WmpP9!>nqPt?tP4GkEt+Ek#Qqn50yO#Q,!QvYW0p$3JQ`c(G)nK(P!`@P]8`|PI.PF)uF:BseT)?w_*(%Xe&a,:Gs5dCP7`7CV{7-c3
Yrt@q-G(^]ZMKi/PFmkOhB3MF"?yFY8yow/i;OJxjW;bMHR,7k:JsoGG_+3LWx4xPaFdiL?U1BKNJho
{XI[+1-g^[UF9KcX~`F5&1//ms}u!_UKHeAshGO:+IPV`pg<mc6$l*wmUdbCtqg#9Ls6wYRUFyHo"KUc7/ALm#I347qr&tGh#M$
jBF.,3BD
2
9j:)7EncP,Y?Qi,>[%SvZudh%@%(GGq`U>Sh8G
0H~s:v/F=qAGKjhBNCh7suKCBs`@B/t_PN6#l4E%T1b7o!;xC/3CA;W7J/:s_rD-Vb51jjg8p-IWCTy!RJ66d6Ph9NTCjH_p5q9F/U+V:Zl-5"XkwDa8&
Dy@]FXG^h!v)&F^4gR~8%K3ZC+V3UAnT@dJT+>&>%wD2=":b<K)WhF>`0-20g]fVGF!L,G;.Db`Y1i?jGu(AAVNo,4x=b$6w):=m"waHu@,dk^AB;QTyJc~^>R~.Aok!MGS3
^RiPTLx29fh9"A?CK<"6ns^+[!j.NU#$ra=q"T<D@ou%Ay@wV&uq=NMW1;]?X=jOt(tQy"x<eKwLOPu[Hwggp(rS<U.4P6*kaltLYYuqT|&@Dcnr/>Yn^eN-Vi;l*W,3TX$<bV-9.esB8iY
/>$l[~1I3;F{>WG|^2$`*Y$jq)F,KF!rlqFQ?z1E]LOY]E3KrTS6"{dh!~*~GGn+h>eyfzaJ,+RpO]2aFYb&omLNlrn|#9fp_{l(k",Ky`7.^M!W*zn2UTRkZ3]d4~w8Q9.VvKoS:DG`v(yRq@6JZcV
y?:ni"e0fm>&swq1.|i^9GYhFw8r5~=|W3RL!|wiC},~_$B=gfT(.=w,?,0q9@Pe:Wl>C>El6@rf<};tO,bWlos?O5<3k?.W=&roADEHbKC1by&ND6e>#b<Mi4^HpDk5U1dMIlmQ9+Wkj;y*<eKl7,!c/
:+8Q6G?fW14m^0BlaY;J421f#oWlQ-
gsWn%8%km`zDk2=,AO6yCZq9Fqc=-Pwx<1(FWUI8GTu?<D.sW&.I*I`[Y#COR)8Kj.=FaQK]7$>/KK)51NMEhH6Y]B[rP&pOf78jCZflgQ=e{dkmVqrUUow4=lvHm&MkCLHOXV!/Od>nq68!ZB>#9K>qhJQH);*n
dw"V4~@@Y2[Zc?gH=#33^mE.gk]XJu)6A$
bPndByBJQ1A@!D!//FS2!#EUEW)Sif%5wPLIAu]DJGl&<Rw1%7OZ64?urGA-~8<N8e;4,W!j{39b-Z~dYDQT/A9
^2#vQ;MYEYj9d5-$IhcuT/{WEOj9V-8/:&u*|9-%/Z
6h.K02--Et"d%wCqOO1SPr;nSy"uSak9yTVfHE=f/@Y/d,:Z1x/PJ?=tb$8g2K*`VRKv)0D:.(Ic[AqfjTSwi}VB:"0YTZ^VaOUNQ`4A0UZTY(029P9Zp`V[/b
9=t"Hj$3I?#B==uCg?@Q@#~tp[NT<970]6HdUUDQ?9|F2901`HE
u9m-d>8C6ii&[A+OgB#,RBJIm[pMApVCOI+Zb=Sg?rVXq[g#vp[Q$)gS_r!%Ai]5=)AY];nSfPC[:W}sL2b!#akPv&W;O%UQ;aWpPUFkk;C;/$LvpCtD?8%(|
SeU.S&pE~?18.QW<*gd#X]Y9INk5%O:E
-0/;?53(mm/>HJyL;O4fQ;<uQ]?;o<%l
1<5cM="Y8j~AspZ0Nct.6Z3ie0@G;-oPo(vS?P=+m>x:u0D<Tr]tOHi]+pC-ar60A&W/t^HQ}LXrh]P)t+ALo)Yd_Ys*WTw>kx0_i=(")U}#7[BlGbrQKtR
A6A/WUBn6mKRG:dNXRqkPA>mu8e?X7sxe`K&"M-i/xwm:]9Q#]+p)OV(gb](A(PkV2AErH|wnS>=TBjN&Q&Fc4B7-e/DUZ(2WgiR2u^:?W-voxF9V@xmhgBSXYCBEb
g`?YW(aq.}EAr{az?c"PAg9DBa
6x^is?D]lf)$Muhr}m}p3wdU=@u
v_nCMiP1,TO?xC|C6^.r+@"U2*__[;8._k)53(S(d8gZ?>+<}9&>y<"?Z]fk2#[?;!K[]oa+073a|^)x;Ma:t*V>7ZP-uSf:dAKf;LE7H/MvQyhXew?.q1A%26k$RKb9)Rn6QMMNxjUs1"U2MfM)n)g,)WSNA"w2WP-NEfy256U1h-PCihzE}"xcv?!?#alB2kC#7+N(6ama0q+cqiKU}`mZb-F
`F&wr@==`YjngsG(l;%tmm]ZBwM^f<T!fV6*NgzQh5V8^n>8N(`r&F%
w]"_r`IK(h;.+>^C/
T_~oz@E1%oK9R=~ZuTNry;ni*O|b6q`v1Y`_R9W=%i$KP$.rv5O"<r(j~V[)%OC1Bn(-~%YV`)CRyp{`oQ]SJDdXl,8T}K0S3K./O
X"_d!gcU}7&vCkatlSXU1T2_`=!I.XR`-<AVb3=,2Ft]2Nzbq9AO+U{#(8GF.mjB{Lp0.S!pf:W7TyW*->[(7m?t1!rP,a5mrQ&tA4~sGLN5[$U@&X@0EPi-X1H-!M|M2u(mWUb:gDW>a"?EwFI26=[KFxT(s,3V>7YI<YeZFITEc+idu.k
FPiEhZTiWchZ[v0/L0UV(S
C!
Ga&R9JZXV1<%faX:-r3DE!d)d9YEq]8.V"z8Y;T*uD>$x$:S>D*bp;GoS:Z0^<mAov"#r4HQM&8:2KEDpop5@bYG_r$L;iX&7S5KN!!3b9}8SV(%n6h27_2#"p+Rb@w
cT6X@:64f@@#Ri[T!s+lZ<FpzX~kF?,]~eXE;VHVY%S2gT3UG^qB]2op=
*^KA~B3;WHb2VOYm$T>A,p]q(UdVmacUvXm[a&r:B,6$$]DrF7b1V
^C0!F]7I6a[A>I;R|ul8u9Wf)0M
h1.y#6qo}RUlZyoD`<>Z-Pup`#zihSK:mFAyfkLP*".+%):GiC$)}2m6=V%XmjN2vR7l=3p@[?&tn)+&Kk4eSBGC!O1#U.8htr~[nN2*$T}%Sf7B$[7f]BaSs^Rbqi`ktYO00R?A&;PN53V&rkLb8^"kl)efXpEoaUyfQ;FcHY2w-Bl!Ifh-<JqH9FPNKD<dMUy"(VD?Z&_S1=4F/VV`g=.EA=N#-l!;ooy]!RwW=Jg*>jj^ev~P+n:<W?|M$[SdHisK@T7p#s{MG)nMJHWN-x:"F=n_<I;FcI0hlA3N`M=5dqWuEQphcwu`/BMFw&
b(vsx=AZ5$L>YI%jZ/L7*d@GB-@5Dtn)Q2uoJAJzpyK{jPVrNYWhF0$Y3mLKm,Y0;@uSFd!*IfGfRpsj_TBnh#?ou*1MaiG{xCBdQ#u{vi.:qaxK=7%oY:K$8z^$PXA|%Cc?WBLW1pg~LFYa!zg(A82gbT-FK$8zt&PYA|%Cay+NLU1pujL;5qstV,Bk@#vKZSlz+T4&i#hkK#ZAZ4qWE0i!]>n/inbTCTK#9%yQNbT3Via,s#q-Gmc^`1mYvUIVJx@)mMhoM^5jbO1%srEpM(M"lVP`bNq`lImZ+si&,0X;I0GMu6%FJ}Uti:^ia-5hX"6?6Lp>lypB*1Nt.iY"?rn;GMmYyR5jbQ.TtAnApCI?t6X?cLK#UrIdHK4~s(J|uQt"Ovn#%Fai+TIk2;p4n]X;WHGgt5KxKsJuH1l3a2EX`yn5"+[$HPbTx.a~`ITs?r1fHFbTwkan`ITs?r1fHFbTwkan`ITrJtu)XabyQ{^8rOm
KSgo2%n=V/GFp)7<Kl)pBGjua1u(UWB)bQ24lkf(LVuY1abm[dH9p!1!b1JxBG_PR&w.q+ADKT=;(-F}N(q8$du(wF_DtcP#d?;
=HB[rE]Y*<G][sF:EpCTFu8K/>arC<_+-4[aA3<p?c`sup`ZWn^Jq
y.0ys.AFBMlzjXi-l69}cwGfv0G`ZaHQ.qH?0g67B[kgvh`s_>`[9<d&Vm@6*Tt6!WmRKPGm8pm+kw1<sGqKg?sySBoqkw1<sGqKg?sySBoqkw1<sEX9VkXuX_x=]f]NlznEVkXuX_x=]f]NlznEVkXuX_x9]f^1ldnAU(X-B]k2]d=.ldnAU(X-B]k2]d=.ltnAWdc(`RvYLG@s_nba69Mn`RvYLG@s_nba69MmJPvELGAV_>ba/DMmJPvELGAV_>ba/DMmJPvELGAV_^ba4sMnJPvULGAV_^ba4sMnJPvULGAV_^ba4sMmvTvMLGAV_Nba2-MmvTvMLGAV_Nba2-MmvTvMI^@=l|lEU1>w/tm<!Ym<iajRiee)kfNtkkN
JU7:H;5hw&1Mq$X`a2WwxVklIRi%A
g
M<FualqV1mpqX``OmyunUzIQiEAXi"M<;|alEb1kqTcaZ}my].0gXP`OU1_+kk3OhzA8g
*SFu*gER1Qq$TT`OWwR$UlIQhbA(i":#;malEb1OqTZ&`NAu_lUlIIj(A8]~;FgyalFzs+I)kk3Oh~@Tg|5TFu5hD-q4SS3]>SS0%Vq$MQB1JR?0B/Yy_iI:V%uMflAxyRVwh!wnEj1{t=i3Z{x{_xV$Iiqm>NyRV}h!5tIv1[ylhQq(mw_8V4Muq=ISyQ@[h!7zI^5iMh]Rq(W}a2U1umkkIRi%AXg
5tFu5hqV1Xq$Vz`ObxZ<UouUhRA8i">o;oKjEN1OqTY)`N&D_DUf;_g~A8G|01gvZsE"]?V#U-p~jP_TUi<"gz>OG
@igv/"D|
<a4RDp~TT_S?oAQh}IPF8@i
{1hDlak`11EvOTT_KAuA)khIPg?@i0W1dFsakQ%o[Kq
h6OI;rmDi+Gpn;I7Nm:xC4LEDD3jTtWTBlD7I>P>g?8`=yrMx8Jh=I,^Qki,DWkPE:Dwo"DmPEDP
G<^1*,DaQJ7@2d_;iR3]&$2%ko"|TZ0[@}0[FT?3R.B7r^sRl3;2][*|4*_WF?G/wv,lq$[Ud~/I2{46
N1(OAp
wnCX@kB&XRKr*)ie!h^4,=c[s9kXiL<f*R]mA:0xbT5,[X4DN7N#_IdtpQt|SZXVk^JwN7e[bfRJPbu)`Ju@s(E!kE$mI0UUa;VQnw/WE!ZQhmp]
Fr[O%q|<LJgkMZ|*"49"XR_jbV*bK3.qf99lExa@eM=@oK*E5B
wjDHEXs`Zd^l%>&]b2v3c*l;UV%gEQRLte07I^5.KimiRZL)H=#qr{a/I&5ov0o@Q9d@^>->LzpR!Gkxz(eC1s*?gKXXRyI&PpZQoZCqk
.wC<J_=z,6cKv.dewW>9$Rap>F%U.#HVpp);x@j3,dG&x_CiL[^,HfDkntS&l3v`,V
1_mt02lLnAka5@X+1x^QDyESfy"n1B-YXc`%T(sI037l[Eo`Z-Hi5xNKR`KLQ26$%SO:M<PR9
.;(p)?cn<qe@):))sMkEa&o*ZuJDV.$SFqnqPts^c_MHN7Hh&P-L.cLL=,c*|Ma%%"_Xx9E3o"Mq1f?[byxE+/aoK$S:"(rRQ8_oa(roziIqrd.rMe.w|(XqpR2BjU=2{PBxodgv$VzNj[95|hqnhZS8_.x(1K%@@:!L*GQt^SuE.qS$Ie2rx,z8!5i6g?5$4)3&[n)Xc2giFB1TUPAC^(k<R,UZSe*%.ck%$I+?CV7W!lGa*5/!oRPd`];LmC9",
nveFF=Ci73:T5735z2q5VM_gea<khy^r-xn"3QIC1fw*7Pa:u7gWd%E4A:^KY4|hn62q6A!S:=}[CLiR
JGIxox9Vq4@8GNyX71fc^8sLSX&xPD*2[BNMg2!,85g+Ca9)D8y~n+!hf:O9_I355Gv$8"e,%!eqoXq_N-XR?$Z@oJy;[H8YWA7oT9?G,q"Gd#P"L"-1&7c(6PSO_3%j,;0Zo.(zSM8eB{6:P3d<&M_8I^Ln,n@}:iQ=bit`#rXr/+O(mr-[u>NirraJWG(
d&.@I/
#$jD,GgMI!xiS8U1kHU@z&e-GOmh:j:lb
2foP#*dM5(,%x$hNp2LI86e1<,pU~y9O)6_C?:!-heN72Ri.$yK<F#Ab1$Jm%(fr,?XGEfw
`xD:rMC-.8xT?i|fi#)bkaQK,g:6(B6OQ455(i{.$Y0[u+L$1wGV~Ze[3;_*c&EdNC)vD-c/Xy3(.);R"TK-iY]v2fQ(5tc"-
gd92f-Stbpk;B
8pPoKIHVd$@,<;EXK<P,rgW#<[[%Fr2)59Z5g#U+~M[Pi79x3NVS(yNm7YlyK/a8@L+j6bn%0vg1:@F;#Pd=C1Z!d)S^*9,,(SJPmbE
#u5J6ASu:+e29L"8"-xl`&UuFKE&Q[jd.q1>!vYu;PNdr7XLg=
o6b+v7x*g03X-t-dwf((<O7ALnLP.nbrlxYpT4mONzvV5?;5Gv(Gws2ly)!cXQCA+T]sK>:|!ev-VX!tI-LX/FWs%9$9)R]7t^R#RzSovZu{wS>.):8`_lt.)p&o5=NN2DwdZB%
JJq{=;3ETE$L=7Vj#A#.98.nCIp;VLig({76!6&>=96b9W*]$1CU:TC6+A@~>:C>_n"Y(.%p^FC);d)
UAE/((m/?g#/tg7^vBizM/0"E)"4#Fb]WoD
2sG%G0%Dce2hyFn+ZU12hExa#u4?lDx}NkC#5o6ZwYQ+=xVc-/<(h6y%m&!0p%9nf3`Jy&jmM_vG;tSU:^u7.2.IEyYYt8
#*ng[-g:.SUhY&@=Z2l;@(mNnicxcrP"r7D-5!C9J^
olQw2;+K/qX+`$#c.Wegh=2OQ(,|1/');}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo
base64_decode('iVBORw0KGgoAAAANSUhEUgAAADkAAAA5BAMAAAB+Np62AAAAMFBMVEUAAACDl60rTnZZdJNziaOerr60vszI0tr8jZH8c3X8SUr309T8Ly78Bgf8r7H6/PpDBKXXAAAAAXRSTlMAQObYZgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAbRJREFUOI3VlM1OwkAQx/sGG0Xh7GwTz7b1AaRwNhqIRy4kPRKjpcc+geEJDHc1chYPfYJ6N7I+gJFQE+UjJIyzS6FqqzeN/A/dtr/Mzsx/PzRtlYSI0fd0Ju5+wDMhHjCTMIqaXoS9QWYw3iLlvRHtLMrwKqDnNLyM4m+lReizCOjXWCgqWdPzvLgJNgnvUGNPV6IVyc7cim2SrHKDMMN+L6DhTKgBDVhqCyPWFW3KwfpqwEOAXUembeYAtn0W3ssErN+RdbxBOcBYowrU2Di8VrEdWcQrx0QjqGlx3m5LUThK4DFRNhGy5lkwp2CVHZ9Qs2ICUY1cGmiUfj7zOnBTyYAdo6a8otjzR0X1UT3uSc97kiqfFzPrMqM39woVZcoUTOhCin7QL1IoJLAOKcrniyCXwUhRboBplTYPSrYJPJ3XLS6Wd8fJqmrqVm2r6vxtvz9T3kigm3bDzPvxxqmn3QDg1l7VcasbtgEpqg+X2133ixlVuTky0Sw7/8eNF+4ncPi1oyFYy4Pk2tz/TPFELrt0w6aX/S93FMPT5OwXUvcbnQl3rWTT1nIy78akqjRbPb0DRTX3Uyvxl2MAAAAASUVORK5CYII=');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");define('Adminer\SESSION_NAME',session_name());if(isset($_GET["upload"])){$Pi=null;if(!defined("SID")&&$_COOKIE[SESSION_NAME]!=""){session_start();$Pi=$_SESSION[ini_get("session.upload_progress.prefix").$_GET["upload"]];}header("Content-Type: application/json; charset=utf-8");echo
json_encode(isset($Pi["bytes_processed"])?array($Pi["bytes_processed"],$Pi["content_length"]):array());exit;}if(function_exists('session_status')?session_status()==PHP_SESSION_NONE:!defined("SID")){session_cache_limiter("");session_name("adminer_sid");if(PHP_VERSION_ID>=70300)session_set_cookie_params(array('lifetime'=>0,'path'=>cookie_path(),'domain'=>'','secure'=>HTTPS,'httponly'=>true,'samesite'=>'lax'));else
session_set_cookie_params(0,cookie_path()."; SameSite=lax","",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Dd);$_POST=remove_slashes($_POST,$Dd);$_COOKIE=remove_slashes($_COOKIE,$Dd);}if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",'16');function
lang($u,$fh=null){$za=func_get_args();$za[0]=$u;return
call_user_func_array('Adminer\lang_format',$za);}function
lang_format($ml,$fh=null){if(is_array($ml)){$F=($fh==1?0:1);$ml=$ml[$F];}$ml=str_replace("'",'’',$ml);$za=func_get_args();array_shift($za);$Qd=str_replace("%d","%s",$ml);if($Qd!=$ml)$za[0]=format_number($fh);return
vsprintf($Qd,$za);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$E);abstract
function
quote($Q);abstract
function
select_db($dc);abstract
function
query($G,$_l=false);function
multi_query($G){return$this->multi=$this->query($G);}function
store_result(){return$this->multi;}function
next_result(){return
false;}function
inTransaction(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($Kc,$V,$E,array$Dh=array()){$Dh[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$Dh[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($Kc,$V,$E,$Dh);}catch(\Exception$fd){return$fd->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($G,$_l=false){$H=$this->pdo->query($G);$this->error="";if(!$H){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($H);return$H;}function
store_result($H=null){if(!$H){$H=$this->multi;if(!$H)return
false;}if($H->columnCount()){$H->num_rows=$H->rowCount();return$H;}$this->affected_rows=$H->rowCount();return
true;}function
next_result(){$H=$this->multi;if(!is_object($H))return
false;$H->_offset=0;return@$H->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($Fg){$I=$this->fetch($Fg);return($I?array_map(array($this,'unresource'),$I):$I);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$J=(object)$this->getColumnMeta($this->_offset++);$U=$J->pdo_type;$J->type=($U==\PDO::PARAM_INT?0:15);$J->charsetnr=($U==\PDO::PARAM_LOB||(isset($J->flags)&&in_array("blob",(array)$J->flags))?63:0);return$J;}function
seek($mh){for($s=0;$s<$mh;$s++)$this->fetch();}}}function
add_driver($t,$C){SqlDriver::$drivers[$t]=$C;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;static$passwords=true;protected$conn;protected$types=array();var$delimiter=";";var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();var$primary="";var$query="";static
function
jushModule(){return"";}static
function
jushAutocomplete(array$T,$ok){$Lk=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$n){foreach($n
as$m)$Lk[$R][]=$m["field"];}return"jush.autocompleteSql('".idf_escape("")."', ".json_encode($Lk).", ".json_encode($ok).")";}static
function
connect($N,$V,$E){list($Ce,$xi)=host_port($N);if(preg_match('~[^-\w.:/]~',$Ce.$xi))return'Invalid server.';if(preg_match('~^-?\d+~',$xi,$A)&&($A[0]<1024||$A[0]>65535))return'Connecting to privileged ports is not allowed.';$f=new
Db;return($f->attach($N,$V,$E)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($R,array$M,array$Z,array$ee,array$Fh=array(),$z=1,$D=0,$Ji=false){$of=(count($ee)<count($M));$G=adminer()->selectQueryBuild($M,$Z,$ee,$Fh,$z,$D);if(!$G)$G="SELECT".limit(($_GET["page"]!="last"&&$z&&$ee&&$of&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($ee&&$of?"\nGROUP BY ".implode(", ",$ee):"").($Fh?"\nORDER BY ".implode(", ",$Fh):""),$z,($D?$z*$D:0),"\n");$this->query=$G;$nk=microtime(true);$I=$this->conn->query($G,(!$z&&!$Ji?1:0));if($Ji)echo
adminer()->selectQuery($G,$nk,!$I);return$I;}function
delete($R,$Si,$z=0){$G="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$G,$Si):" $G$Si"));}function
update($R,array$O,$Si,$z=0,$Ij="\n"){$bm=array();foreach($O
as$x=>$X)$bm[]="$x = $X";$G=table($R)." SET$Ij".implode(",$Ij",$bm);return
queries("UPDATE".($z?limit1($R,$G,$Si,$Ij):" $G$Si"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$K,array$Hi){foreach($K
as$O){$Z=array();foreach($O
as$x=>$X){if(isset($Hi[idf_unescape($x)]))$Z[]="$x = $X";}if(!($Z&&$this->update($R,$O," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($R,$O))return
false;}return
true;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($G,$Zk){}function
convertSearch($u,array$X,array$m){return$u;}function
value($X,array$m){return(method_exists($this->conn,'value')?$this->conn->value($X,$m):$X);}function
quoteBinary($wj){return
q($wj);}function
typeName(\stdClass$m){return(isset($m->native_type)?$m->native_type:"");}function
warnings(){}function
tableHelp($C,$sf=false){}function
inheritsFrom($R){return
array();}function
inheritedTables($R){return
array();}function
partitionsInfo($R){return
array();}function
hasCStyleEscapes(){return
false;}function
lineComment(){return"--";}function
engines(){return
array();}function
supportsIndex(array$S){return!is_view($S);}function
supportsAlterIndex(array$S){return
true;}function
indexAlgorithms(array$_k){return
array();}function
indexOpclasses(){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($R):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$I=array();if(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$J){$J["null"]=($J["nullable"]=="YES");$I[$J["tab"]][]=$J;}}return$I;}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($Yc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$E){$j=adminer()->database();set_error_handler(array($this,'_error'));list($Ce,$xi)=host_port($N);$this->string="host='$Ce'".($xi?" port=$xi":"")." user='".addcslashes($V,"'\\")."' password='".addcslashes($E,"'\\")."'";$mk=adminer()->connectSsl();if(isset($mk["mode"]))$this->string
.=" sslmode=$mk[mode]";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($Q){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$Q):"'".pg_escape_string($this->link,$Q)."'");}function
value($X,array$m){return($m["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($dc){if($dc==adminer()->database())return$this->database;$I=@pg_connect("$this->string dbname='".addcslashes($dc,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($I)$this->link=$I;return$I;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($G,$_l=false){if(self::$untrusted)$H=(@pg_query($this->link,"BEGIN READ ONLY")?@pg_query_params($this->link,$G,array()):false);else$H=@pg_query($this->link,$G);$this->error="";if(!$H){$this->error=pg_last_error($this->link);$I=false;}elseif(!pg_num_fields($H)){$this->affected_rows=pg_affected_rows($H);$I=true;}else$I=new
Result($H);if(self::$untrusted)@pg_query($this->link,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$I;}function
warnings(){if(PHP_VERSION_ID>=70100){$I=implode("\n",pg_last_notice($this->link,PGSQL_NOTICE_ALL));pg_last_notice($this->link,PGSQL_NOTICE_CLEAR);}else$I=pg_last_notice($this->link);return
nl_br(h($I));}function
inTransaction(){$P=pg_transaction_status($this->link);return$P==PGSQL_TRANSACTION_INTRANS||$P==PGSQL_TRANSACTION_INERROR;}function
copyFrom($R,array$K){$this->error='';set_error_handler(function($Yc,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$I=pg_copy_from($this->link,$R,$K);restore_error_handler();return$I;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=pg_num_rows($H);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$I=new
\stdClass;$I->orgtable=pg_field_table($this->result,$d);$I->name=pg_field_name($this->result,$d);$U=pg_field_type($this->result,$d);$I->native_type=$U;$I->type=(preg_match(number_type(),$U)?0:15);$I->charsetnr=($U=="bytea"?63:0);return$I;}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach($N,$V,$E){$j=adminer()->database();list($Ce,$xi)=host_port($N);$Kc="pgsql:host='$Ce'".($xi?" port=$xi":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$mk=adminer()->connectSsl();if(isset($mk["mode"]))$Kc
.=" sslmode=$mk[mode]";return$this->dsn($Kc,$V,$E);}function
select_db($dc){return(adminer()->database()==$dc);}function
query($G,$_l=false){$I=(self::$untrusted?$this->readOnlyQuery($G):parent::query($G,$_l));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$I;}private
function
readOnlyQuery($G){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){list(,$this->errno,$this->error)=$this->pdo->errorInfo();return
false;}$H=$this->pdo->prepare($G);$I=false;if($H&&$H->execute()){$this->store_result($H);$I=$H;}else{list(,$this->errno,$this->error)=($H?$H->errorInfo():$this->pdo->errorInfo());if(!$this->error)$this->error='Unknown error.';}$this->pdo->query("COMMIT");return$I;}function
warnings(){}function
copyFrom($R,array$K){$I=$this->pdo->pgsqlCopyFromArray($R,$K);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$I;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($G){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$G),$A)){$K=explode("\n",$A[2]);$this->multi=false;$this->affected_rows=count($K);return$this->copyFrom($A[1],$K);}return
parent::multi_query($G);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";var$operators=array("=","<",">","<=",">=","!=","~","~*","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";private$userTypes=array();static
function
connect($N,$V,$E){$f=parent::connect($N,$V,$E);if(is_string($f))return$f;$em=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$em)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$em);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;$this->types['Ranges']=array("int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0);if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f)){$this->generated[]="STORED";if(min_version(18,0,$f))$this->generated[]="VIRTUAL";}$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$nh=$this->userTypes[$m["type"]];return($nh?type_values($nh):"");}function
setUserTypes(array$zl){$this->userTypes=array_flip($zl);$this->types['User types']=array_fill_keys(array_keys($this->userTypes),0);}function
insertReturning($R){$Ea=array_filter(fields($R),function($m){return$m['auto_increment'];});return(count($Ea)==1?" RETURNING ".idf_escape(key($Ea)):"");}function
insertUpdate($R,array$K,array$Hi){$e=array_keys(reset($K));$Db=array();$Il=array();foreach($e
as$x){if(isset($Hi[idf_unescape($x)]))$Db[]=$x;else$Il[]="$x = EXCLUDED.$x";}if(!$Db||!min_version(9.5)||count($Db)!=count($Hi))return
parent::insertUpdate($R,$K,$Hi);$Di="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$uk="\nON CONFLICT (".implode(", ",$Db).")".($Il?" DO UPDATE SET ".implode(", ",$Il):" DO NOTHING");$bm=array();$y=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($bm&&strlen($Di)+$y+strlen($Y)+strlen($uk)>1e6){if(!queries($Di.implode(",\n",$bm).$uk))return
false;$bm=array();$y=0;}$bm[]=$Y;$y+=strlen($Y)+2;}return
queries($Di.implode(",\n",$bm).$uk);}function
slowQuery($G,$Zk){$this->conn->query("SET statement_timeout = ".(1000*$Zk));$this->conn->timeout=1000*$Zk;return$G;}function
convertSearch($u,array$X,array$m){$pi=preg_match('(LIKE|^!?~)',$X["op"]);$Qg=preg_match('~^(character( varying)?|text|citext|bpchar|name)$~',$m["type"])||(!$pi&&preg_match('~'.number_type().'|^(date|time|timetz|timestamp|timestamptz|boolean)$~',$m["type"]));return($Qg&&!preg_match('~\[]$~',$m["full_type"])?$u:"CAST($u AS text)");}function
quoteBinary($wj){return"'\\x".bin2hex($wj)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($C,$sf=false){$Tf=array("information_schema"=>"infoschema","pg_catalog"=>($sf?"view":"catalog"),);$_=$Tf[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$C).".html";}function
inheritsFrom($R){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 2, 1");}function
inheritedTables($R){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 2, 1");}function
partitionsInfo($R){$J=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($R))->fetch_assoc():null);if($J){$c=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $J[partrelid] AND attnum IN (".str_replace(" ",", ",$J["partattrs"]).")");$Wa=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Wa[$J["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$c)),);}return
array();}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
indexAlgorithms(array$_k){static$I=array();if(!$I)$I=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$I;}function
indexOpclasses(){static$I=array();if(!$I&&$this->conn->flavor!='cockroach')$I=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$I;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$Ya;if($Ya===null)$Ya=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$Ya;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Ld){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($G,$Z,$z,$mh=0,$Ij=" "){return" $G$Z".($z?$Ij."LIMIT $z".($mh?" OFFSET $mh":""):"");}function
limit1($R,$G,$Z,$Ij="\n"){return(preg_match('~^INTO~',$G)?limit($G,$Z,1,0,$Ij):" $G".(is_view(table_status1($R))?$Z:$Ij."WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$Ij."LIMIT 1)"));}function
db_collation($j,array$tb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$G="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$G
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$G
.="
ORDER BY 1";return
get_key_vals($G);}function
count_tables(array$i){$I=array();foreach($i
as$j){if(connection()->select_db($j))$I[$j]=count(tables_list());}return$I;}function
table_status($C="",$td=false){static$re;if($re===null)$re=get_val("SELECT 'pg_table_size'::regproc");$Mj=(!$td&&min_version(10));$I=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($re?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".($Mj?"seq.last_value":"NULL")." AS \"Auto_increment\",
	".(min_version(10)?"relispartition::int AS partition,":"")."
	current_schema() AS nspname
FROM pg_class c
".($Mj?"LEFT JOIN (
	SELECT d.refobjid, max(s.last_value) AS last_value
	FROM pg_depend d
	JOIN pg_class sc ON sc.oid = d.objid AND sc.relkind = 'S' AND sc.relnamespace = ".driver()->nsOid."
	JOIN pg_sequences s ON s.schemaname = current_schema() AND s.sequencename = sc.relname
	WHERE d.classid = 'pg_class'::regclass AND d.refclassid = 'pg_class'::regclass AND d.deptype IN ('a', 'i')
	".($C!=""?"AND d.refobjid = ".driver()->tableOid($C):"")."
	GROUP BY d.refobjid
) seq ON seq.refobjid = c.oid
":"")."WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($C!=""?"AND relname = ".q($C):"ORDER BY relname"))as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support(array$S){return
true;}function
fields($R){$I=array();$ta=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz','time without time zone'=>'time','time with time zone'=>'timetz',);foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	t.typcategory,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
JOIN pg_type t ON t.oid = a.atttypid
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$J){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$J["full_type"],$A);list(,$U,$y,$J["length"],$ma,$_a)=$A;$J["length"].=$_a;$hb=$U.$ma;if(isset($ta[$hb])){$J["type"]=$ta[$hb];$J["full_type"]=$J["type"].$y.$_a;}else{$J["type"]=$U;$J["full_type"]=$J["type"].$y.$ma.$_a;}if(in_array($J['attidentity'],array('a','d')))$J['default']='GENERATED '.($J['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$J["generated"]=idx(array("s"=>"STORED","v"=>"VIRTUAL"),$J["attgenerated"],"");$J["composite"]=($J["typcategory"]=="C");$J["null"]=!$J["attnotnull"];$J["auto_increment"]=$J['attidentity']||preg_match('~^nextval\(~i',$J["default"])||preg_match('~^unique_rowid\(~',$J["default"]);$J["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(!$J['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$J["default"],$A))$J["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$I[$J["field"]]=$J;}return$I;}function
indexes($R,$g=null){$g=connection($g);$I=array();$Ek=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Ek AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($g->flavor=='cockroach'?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Ek
ORDER BY indisprimary DESC, indisunique DESC",$g)as$J){$gj=$J["relname"];$I[$gj]["type"]=($J["indisprimary"]?"PRIMARY":($J["indisunique"]?"UNIQUE":"INDEX"));$I[$gj]["columns"]=array();$I[$gj]["descs"]=array();$I[$gj]["algorithm"]=$J["amname"];$I[$gj]["partial"]=$J["partial"];$Ve=preg_split('~(?<=\)), (?=\()~',$J["indexpr"]);foreach(explode(" ",$J["indkey"])as$We)$I[$gj]["columns"][]=($We?$e[$We]:array_shift($Ve));foreach(explode(" ",$J["indoption"])as$Xe)$I[$gj]["descs"][]=(intval($Xe)&1?'1':null);$I[$gj]["opclasses"]=($J["opclasses"]!=""?explode(" ",$J["opclasses"]):array());$I[$gj]["lengths"]=array();}return$I;}function
foreign_keys($R){$I=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$J){$J['deferrable']=($J['deferrable']?'':'NOT ').'DEFERRABLE'.($J['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$J['definition'],$A)){$J['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$cg)){$J['ns']=idf_unescape($cg[2]);$J['table']=idf_unescape($cg[4]);}$J['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$J['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$cg)?$cg[1]:'NO ACTION');$J['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$cg)?$cg[1]:'NO ACTION');$I[$J['conname']]=$J;}}return$I;}function
view($C){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($C).")")));}function
collations(){return
array();}function
information_schema($j,$L=""){return
in_array($L!=""?$L:get_schema(),array("information_schema","pg_catalog","pg_toast"));}function
error(){$I=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$I,$A))$I=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($I);}function
create_database($j,$sb){return
queries("CREATE DATABASE ".idf_escape($j).($sb?" ENCODING ".idf_escape($sb):""));}function
drop_databases(array$i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($C,$sb){connection()->close();return!!queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($C));}function
auto_increment(){return"";}function
alter_table($R,$C,array$n,array$Nd,$yb,$Tc,$sb,$Ea,$hi){$b=array();$Ri=array();if($R!=""&&$R!=$C)$Ri[]="ALTER TABLE ".table($R)." RENAME TO ".table($C);$Jj="";foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b[]="DROP $d";else{$Yl=$X[5];unset($X[5]);if($m[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$Ri[]="ALTER TABLE ".table($C)." RENAME $d TO $X[0]";$b[]="ALTER $d TYPE$X[1]";$Kj=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($Kj).")":"DROP DEFAULT"));if(isset($X[6]))$Jj="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Kj)." OWNED BY ".idf_escape($R).".$X[0]";$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($m[0]!=""||$Yl!="")$Ri[]="COMMENT ON COLUMN ".table($C).".$X[0] IS ".($Yl!=""?substr($Yl,9):"''");}}$b=array_merge($b,$Nd);if($R==""){$P="";if($hi){$ob=(connection()->flavor=='cockroach');$P=" PARTITION BY $hi[partition_by]($hi[partition])";if($hi["partition_by"]=='HASH'){$ii=+$hi["partitions"];for($s=0;$s<$ii;$s++)$Ri[]="CREATE TABLE ".idf_escape($C."_$s")." PARTITION OF ".idf_escape($C)." FOR VALUES WITH (MODULUS $ii, REMAINDER $s)";}else{$Fi="MINVALUE";foreach($hi["partition_names"]as$s=>$X){$Y=$hi["partition_values"][$s];$di=" VALUES ".($hi["partition_by"]=='LIST'?"IN ($Y)":"FROM ($Fi) TO ($Y)");if($ob)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$di";else$Ri[]="CREATE TABLE ".idf_escape($C."_$X")." PARTITION OF ".idf_escape($C)." FOR$di";$Fi=$Y;}$P
.=($ob?"\n)":"");}}array_unshift($Ri,"CREATE TABLE ".table($C)." (\n".implode(",\n",$b)."\n)$P");}elseif($b)array_unshift($Ri,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($Jj)array_unshift($Ri,$Jj);if($yb!==null)$Ri[]="COMMENT ON TABLE ".table($C)." IS ".q($yb);foreach($Ri
as$G){if(!queries($G))return
false;}if($Ea!=""){foreach(fields($C)as$wd=>$m){if($m["auto_increment"])return!!queries("SELECT setval(pg_get_serial_sequence(".q(table($C)).", ".q($wd)."), $Ea)");}}return
true;}function
alter_indexes($R,$b){$h=array();$Fc=array();$Ri=array();foreach($b
as$X){if($X[0]!="INDEX")$h[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$Fc[]=idf_escape($X[1]);else$Ri[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($h)array_unshift($Ri,"ALTER TABLE ".table($R).implode(",",$h));if($Fc)array_unshift($Ri,"DROP INDEX ".implode(", ",$Fc));foreach($Ri
as$G){if(!queries($G))return
false;}return
true;}function
truncate_tables(array$T){return!!queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_kinds(array$T){$I=array("MATERIALIZED VIEW"=>array(),"VIEW"=>array(),"TABLE"=>array());foreach($T
as$C=>$S)$I[strtoupper($S["Engine"])][]=idf_escape($S["nspname"]).".".table($C);return
array_filter($I);}function
drop_views(array$gm){return
drop_tables($gm);}function
drop_tables(array$T){$pk=array();foreach($T
as$R)$pk[$R]=table_status1($R);foreach(drop_kinds($pk)as$Bf=>$Pg){if(!queries("DROP $Bf ".implode(", ",$Pg)))return
false;}return
true;}function
move_tables(array$T,array$gm,$Pk){foreach(array_merge($T,$gm)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Pk)))return
false;}return
true;}function
trigger($C,$R){if($C=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($C);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$J)$e[]=$J["event_object_column"];$I=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$J){if($e&&$J["Event"]=="UPDATE")$J["Event"].=" OF";$J["Of"]=implode(", ",$e);if($I)$J["Event"].=" OR $I[Event]";$I=$J;}return$I;}function
triggers($R){$I=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$J){$ql=trigger($J["trigger_name"],$R);$I[$ql["Trigger"]]=array($ql["Timing"],$ql["Event"]);}return$I;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($C,$U){$K=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($C));$I=idx($K,0,array());$I["returns"]=array("type"=>preg_replace('~^_(.*)~','\1[]',"$I[type_udt_name]"));$I["fields"]=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($C)."
ORDER BY ordinal_position");return$I;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(connection()->flavor=='cockroach'?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($C,array$J){$I=array();foreach($J["fields"]as$m){$y=$m["length"];$I[]=$m["type"].($y?"($y)":"");}return
idf_escape($C)."(".implode(", ",$I).")";}function
last_id($H){$J=(is_object($H)?$H->fetch_row():array());return($J?$J[0]:0);}function
explain(Db$f,$G){return$f->query("EXPLAIN $G");}function
found_rows(array$S,array$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$fj))return$fj[1];}function
types($pd=false){$ob=connection()->flavor=='cockroach';$Cf=($ob?"'e'":"'b','c','d','e'".(min_version(9.2)?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".driver()->nsOid."
AND t.typtype IN ($Cf)".($ob?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($pd?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($t){$Xc=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Xc?"'".implode("', '",array_map('addslashes',$Xc))."'":"");}function
collation_name($nh){return(min_version(9.1)?"(SELECT collname FROM pg_collation WHERE oid = $nh AND collname != 'default')":"NULL");}function
type_definition($t){$U=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $t"));$I=array("kind"=>($U?$U["typtype"]:""),"definition"=>"");if(!$U||!$U["defined"])return$I;switch($I["kind"]){case'e':$bm=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");$I["definition"]="AS ENUM (".implode(", ",array_map('Adminer\q',$bm)).")";break;case'c':$e=array();foreach(get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $U[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum")as$J)$e[]=idf_escape($J["attname"])." $J[full_type]".($J["collation"]?" COLLATE ".idf_escape($J["collation"]):"");$I["definition"]="AS (\n\t".implode(",\n\t",$e)."\n)";break;case'd':$Cc=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type WHERE oid = $t"));$I["definition"]="AS $Cc[base]".($Cc["collation"]?" COLLATE ".idf_escape($Cc["collation"]):"").($Cc["typdefault"]!=""?" DEFAULT $Cc[typdefault]":"").($Cc["notnull"]?" NOT NULL":"");foreach(get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $t AND contype != 'n' ORDER BY conname")as$J)$I["definition"].=" CONSTRAINT ".idf_escape($J["conname"])." $J[definition]";break;case'r':$Vi=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
".collation_name("rngcollation")." AS collation,
NULLIF(rngcanonical, 0)::regproc::text AS canonical,
NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(min_version(14)?",
(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range WHERE rngtypid = $t"));$Dh=array();foreach(array("subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1)as$x=>$bd){if($Vi[$x]!="")$Dh[]=strtoupper($x)." = ".($bd?idf_escape($Vi[$x]):$Vi[$x]);}$I["definition"]="AS RANGE (".implode(", ",$Dh).")";}return$I;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)get_val("SELECT current_schema()");}function
set_schema($L,$g=null){$I=connection($g)->query("SET search_path TO ".idf_escape($L));driver()->setUserTypes(types(true));return!!$I;}function
drop_sql(array$T){$I="";foreach(drop_kinds($T)as$Bf=>$Pg)$I
.="DROP $Bf IF EXISTS ".implode(", ",$Pg).";\n";return($I?"$I\n":"");}function
foreign_keys_sql($R){$I="";$P=table_status1($R);$ch=idf_escape($P['nspname']);$Jd=foreign_keys($R);ksort($Jd);foreach($Jd
as$Id=>$Hd)$I
.="ALTER TABLE ONLY $ch.".idf_escape($P['Name'])." ADD CONSTRAINT ".idf_escape($Id)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$ch.\\2",$Hd["definition"]).";\n";return($I?"$I\n":$I);}function
indexes_sql($R,$Hi=""){$I="";$G="SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($R).($Hi!=""?" AND indexname != ".q($Hi):"");foreach(get_rows($G,null,"-- ")as$J)$I
.="\n\n$J[indexdef];";return$I;}function
create_sql($R,$Ea,$sk){$mj=array();$Mj=array();$Nj=array();$Lj=array();$P=table_status1($R);$ch=idf_escape($P['nspname']);if(is_view($P)){$fm=view($R);$h="CREATE ".strtoupper($P["Engine"])." $ch.".idf_escape($R)." AS ".rtrim($fm["select"],";").";";return
rtrim($h.indexes_sql($R),';');}$n=fields($R);if(count($P)<2||empty($n))return"";$I="CREATE TABLE $ch.".idf_escape($P['Name'])." (\n    ";$Ck=q("$ch.".idf_escape($P['Name']));foreach($n
as$m){$Oj="";if($m['default']=="nextval('$P[Name]_$m[field]_seq')"){$Oj="$ch.".idf_escape("$P[Name]_$m[field]_seq");$m['default']=null;$m['full_type']=preg_replace('~int(eger)?~','serial',$m['full_type']);}$bi=idf_escape($m['field']).' '.$m['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$P['nspname']).'.\2',default_value($m)).($m['null']?"":" NOT NULL");$mj[]=$bi;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$dg)){$Kj=$dg[1];$fk=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Kj)):"SELECT * FROM $Kj"),null,"-- "));$Mj[]=($sk=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $ch.$Kj;\n":"")."CREATE SEQUENCE $ch.$Kj INCREMENT $fk[increment_by] MINVALUE $fk[min_value] MAXVALUE $fk[max_value]"." CACHE $fk[cache_value];";if(get_val("SELECT pg_get_serial_sequence($Ck, ".q($m['field']).")"))$Nj[]="\n\nALTER SEQUENCE $ch.$Kj OWNED BY $ch.".idf_escape($P['Name']).".".idf_escape($m['field']).";";if($Ea)$Lj[]="$ch.$Kj";}elseif($Ea&&$m['auto_increment'])$Lj[]=($Oj?:get_val("SELECT pg_get_serial_sequence($Ck, ".q($m['field']).")"));}if(!empty($Mj))$I=implode("\n\n",$Mj)."\n\n$I";$Hi="";foreach(indexes($R)as$Te=>$v){if($v['type']=='PRIMARY'){$Hi=$Te;$mj[]="CONSTRAINT ".idf_escape($Te)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$Fb=>$Hb)$mj[]="CONSTRAINT ".idf_escape($Fb)." CHECK ($Hb)";$I
.=implode(",\n    ",$mj)."\n)";$di=driver()->partitionsInfo($P['Name']);if($di)$I
.="\nPARTITION BY $di[partition_by]($di[partition])";$I
.="\nWITH (oids = ".($P['Oid']?'true':'false').");";$I
.=implode($Nj);if($P['Comment'])$I
.="\n\nCOMMENT ON TABLE $ch.".idf_escape($P['Name'])." IS ".q($P['Comment']).";";foreach($n
as$wd=>$m){if($m['comment'])$I
.="\n\nCOMMENT ON COLUMN $ch.".idf_escape($P['Name']).".".idf_escape($wd)." IS ".q($m['comment']).";";}$I
.=indexes_sql($R,$Hi);foreach(array_filter($Lj)as$Jj){$fk=first(get_rows("SELECT last_value, is_called::int FROM $Jj",null,"-- "));if($fk['is_called'])$I
.="\n\nDO \$\$ BEGIN PERFORM setval(".q($Jj).", $fk[last_value]); END \$\$;";}return
rtrim($I,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
truncate_all_sql(array$T){return($T?"TRUNCATE ".implode(", ",array_map('Adminer\table',$T)).";\n\n":"");}function
trigger_sql($R){$P=table_status1($R);$I="";foreach(triggers($R)as$pl=>$ol){$ql=trigger($pl,$P['Name']);$I
.="\nCREATE TRIGGER ".idf_escape($ql['Trigger'])." $ql[Timing] $ql[Event] ON ".idf_escape($P["nspname"]).".".idf_escape($P['Name'])." $ql[Type] $ql[Statement];;\n";}return$I;}function
use_sql($dc,$sk=""){$C=idf_escape($dc);$I="";if(preg_match('~CREATE~',$sk)){if($sk=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $C;\n";$I
.="CREATE DATABASE $C;\n";}return"$I\\connect $C";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return($m["composite"]?"$I::$m[type]":$I);}function
support($ud){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table'.'|transaction_ddl|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|deferrable').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$ud);}function
kill_process($t){return
queries("SELECT pg_terminate_backend(".number($t).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach($o,$V,$E){$this->link=new
\SQLite3($o);$em=\SQLite3::version();$this->server_info=$em["versionString"];return'';}function
query($G,$_l=false){$H=@$this->link->query($G);$this->error="";if(!$H){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($H->numColumns())return
new
Result($H);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".bin2hex($Q)."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($H){$this->result=$H;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$zl=array(1=>"integer","real","text","blob","null");$d=$this->offset++;$U=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($U==SQLITE3_TEXT?15:0),"native_type"=>$zl[$U],"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach($o,$V,$E){return$this->dsn(DRIVER.":$o","","");}function
quote($Q){return(is_utf8($Q)?parent::quote($Q):"x'".bin2hex($Q)."'");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach($o,$V,$E){parent::attach($o,$V,$E);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){$G="ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a";if(is_readable($o)&&$this->query($G))return!self::attach($o,'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";static$passwords=false;protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$E){return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");if(min_version(3.37,0,$f))$this->types[0]["any"]=0;}function
structuredTypes(){return
array_keys($this->types[0]);}function
quoteBinary($wj){return"x".q(bin2hex($wj));}function
engines(){$I=array("table");if(min_version("3.8.2")){if(min_version(3.37)){$I[]="STRICT";$I[]="STRICT, WITHOUT ROWID";}$I[]="WITHOUT ROWID";}return$I;}function
insertUpdate($R,array$K,array$Hi){$bm=array();foreach($K
as$O)$bm[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($K))).") VALUES\n".implode(",\n",$bm));}function
tableHelp($C,$sf=false){if(preg_match('~^sqlite_(seq|stat.)~',$C,$A))return"fileformat2.html#$A[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$C))return"schematab.html";}function
checkConstraints($R){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn),$dg);return
array_combine($dg[2],$dg[2]);}function
allFields(){$I=array();foreach(tables_list()as$R=>$U){foreach(fields($R)as$m)$I[$R][]=$m;}return$I;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Ld){return
array();}function
limit($G,$Z,$z,$mh=0,$Ij=" "){return" $G$Z".($z?$Ij."LIMIT $z".($mh?" OFFSET $mh":""):"");}function
limit1($R,$G,$Z,$Ij="\n"){return(preg_match('~^INTO~',$G)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($G,$Z,1,0,$Ij):" $G WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$Ij."LIMIT 1)");}function
db_collation($j,array$tb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$i){return
array();}function
db_status(){$Wh=get_val("PRAGMA page_size");$Td=get_val("PRAGMA freelist_count")*$Wh;return
array("Data_length"=>get_val("PRAGMA page_count")*$Wh-$Td,"Index_length"=>0,"Data_free"=>$Td,);}function
table_status($C="",$td=false){$I=array();$K=array();if(!$td&&$C==""){connection()->query("PRAGMA optimize = 0x10002");$K=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1 GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($C!=""?"AND name = ".q($C):"ORDER BY (name LIKE 'sqlite_%'), name"))as$J){if($J["Engine"]=="table"){$uk=preg_replace('~.*\)~s','',$J["sql"]);$J["Engine"]=implode(", ",array_filter(array((preg_match('~\bSTRICT\b~i',$uk)?"STRICT":0),(preg_match('~\bWITHOUT\s+ROWID\b~i',$uk)?"WITHOUT ROWID":0),)))?:"table";}unset($J["sql"]);$J["Rows"]=idx($K,$J["Name"],0);$I[$J["Name"]]=$J;}if(!$td){foreach(get_rows("SELECT * FROM sqlite_sequence".($C!=""?" WHERE name = ".q($C):""),null,"")as$J)$I[$J["name"]]["Auto_increment"]=$J["seq"];}return$I;}function
is_view(array$S){return$S["Engine"]=="view";}function
fk_support(array$S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){$I=array();$gk=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$Mi=array("select"=>1,"where"=>1,"order"=>1);if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$R))$Mi+=array("insert"=>1,"update"=>1);foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$J){$C=$J["name"];$U=strtolower($J["type"]);$k=$J["dflt_value"];$I[$C]=array("field"=>$C,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":(preg_match('~any~i',$U)?"any":"numeric"))))),"full_type"=>$U,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$J["notnull"],"privileges"=>$Mi,"primary"=>$J["pk"],);if($J["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$gk))$I[$C]["auto_increment"]=true;}$u='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$u.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$gk,$dg,PREG_SET_ORDER);foreach($dg
as$A){$C=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($I[$C])$I[$C]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$gk,$dg,PREG_SET_ORDER);foreach($dg
as$A){$C=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));$I[$C]["default"]=$A[3];$I[$C]["generated"]=strtoupper($A[4]);}return$I;}function
indexes($R,$g=null){$g=connection($g);$I=array();$gk=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$g);if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$gk,$A)){$I[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$dg,PREG_SET_ORDER);foreach($dg
as$A){$I[""]["columns"][]=idf_unescape($A[2]).$A[4];$I[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$I){foreach(fields($R)as$C=>$m){if($m["primary"])$I[""]=array("type"=>"PRIMARY","columns"=>array($C),"lengths"=>array(),"descs"=>array(null));}}$lk=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$g);foreach(get_rows("PRAGMA index_list(".table($R).")",$g)as$J){$C=$J["name"];$v=array("type"=>($J["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($C).")",$g)as$vj){$v["columns"][]=$vj["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($C).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$lk[$C],$fj)){preg_match_all('/("[^"]*+")+( DESC)?/',$fj[2],$dg);foreach($dg[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$I[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$I[""]["columns"]||$v["descs"]!=$I[""]["descs"]||!preg_match("~^sqlite_~",$C))$I[$C]=$v;}return$I;}function
foreign_keys($R){$I=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$J){$p=&$I[$J["id"]];if(!$p)$p=$J;$p["source"][]=$J["from"];$p["target"][]=$J["to"];}return$I;}function
view($C){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($C))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j,$L=""){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($C){$pd="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($pd)\$~",$C)){connection()->error=sprintf('Please use one of these file extensions: %s.',str_replace("|",", ",$pd));return
false;}return
true;}function
create_database($j,$sb){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach($j,'','');}catch(\Exception$fd){connection()->error=$fd->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases(array$i){connection()->attach(":memory:",'','');foreach($i
as$j){if(!check_sqlite_name($j))return
false;if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($C,$sb){if(!check_sqlite_name($C))return
false;connection()->attach(":memory:",'','');connection()->error='File exists.';return@rename(DB,$C);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$C,array$n,array$Nd,$yb,$Tc,$sb,$Ea,$hi){$Ol=($R==""||$Nd||$Tc);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$Ol=true;break;}}$b=array();$Qh=array();foreach($n
as$m){if($m[1]){$b[]=($Ol?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$Qh[$m[0]]=$m[1][0];}}if(!$Ol){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$C&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($C)))return
false;}elseif(!recreate_table($R,$C,$b,$Qh,$Nd,$Ea,array(),"","",$Tc))return
false;if($Ea){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Ea WHERE name = ".q($C));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($C).", $Ea)");queries("COMMIT");}return
true;}function
recreate_table($R,$C,array$n,array$Qh,array$Nd,$Ea="",$w=array(),$Gc="",$la="",$Tc=""){if($R!=""){if(!$n){foreach(fields($R)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$Qh[$x]=idf_escape($x);}}$Ii=false;foreach($n
as$m){if($m[6])$Ii=true;}$Ic=array();foreach($w
as$x=>$X){if($X[2]=="DROP"){$Ic[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$yf=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$Qh[$d])continue
2;$e[]=$Qh[$d].($v["descs"][$x]?" DESC":"");}if(!$Ic[$yf]){if($v["type"]!="PRIMARY"||!$Ii)$w[]=array($v["type"],$yf,$e);}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$Nd[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$yf=>$p){foreach($p["source"]as$x=>$d){if(!$Qh[$d])continue
2;$p["source"][$x]=idf_unescape($Qh[$d]);}if(!isset($Nd[" $yf"]))$Nd[]=" ".format_foreign_key($p);}queries("BEGIN");}$bb=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($Qh[array_search($m[0],$Qh)]);$bb[]="  ".implode($m);}$bb=array_merge($bb,array_filter($Nd));foreach(driver()->checkConstraints($R)as$fb){if($fb!=$Gc)$bb[]="  CHECK ($fb)";}if($la)$bb[]="  CHECK ($la)";$Tk=($R!=""&&$R==$C?"adminer_$C":$C);if(!$Tc&&$R!="")$Tc=idx(table_status1($R),"Engine");if(!queries("CREATE TABLE ".table($Tk)." (\n".implode(",\n",$bb)."\n)".($Tc!="table"&&in_array($Tc,driver()->engines())?" $Tc":"")))return
false;if($R!=""){if($Qh&&!queries("INSERT INTO ".table($Tk)." (".implode(", ",$Qh).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($Qh)))." FROM ".table($R)))return
false;$ul=array();foreach(triggers($R)as$sl=>$al){$ql=trigger($sl,$R);$ul[]="CREATE TRIGGER ".idf_escape($sl)." ".implode(" ",$al)." ON ".table($C)."\n$ql[Statement]";}$Ea=$Ea?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$C&&!queries("ALTER TABLE ".table($Tk)." RENAME TO ".table($C)))||!alter_indexes($C,$w))return
false;if($Ea)queries("UPDATE sqlite_sequence SET seq = $Ea WHERE name = ".q($C));foreach($ul
as$ql){if(!queries($ql))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$C,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($C!=""?$C:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$Hi){if($Hi[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables(array$T){return
apply_queries("DELETE FROM",$T);}function
drop_views(array$gm){return
apply_queries("DROP VIEW",$gm);}function
drop_tables(array$T){return
apply_queries("DROP TABLE",$T);}function
move_tables(array$T,array$gm,$Pk){return
false;}function
trigger($C,$R){if($C=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$tl=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$tl["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($C)),$A);$ih=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($ih?" OF":""),"Of"=>idf_unescape($ih),"Trigger"=>$C,"Statement"=>$A[4],);}function
triggers($R){$I=array();$tl=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$J){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$tl["Timing"]).')\s*(.*?)\s+ON\b~i',$J["sql"],$A);$I[$J["name"]]=array($A[1],$A[2]);}return$I;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
last_id($H){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain(Db$f,$G){return$f->query("EXPLAIN QUERY PLAN $G");}function
found_rows(array$S,array$Z){}function
types($pd=false){return
array();}function
create_sql($R,$Ea,$sk){$I=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$C=>$v){if($C=='')continue;$I
.=";\n\n".index_sql($R,$v['type'],$C,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$I;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($dc,$sk=""){return"";}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$I=array();foreach(get_rows("PRAGMA pragma_list")as$J){$C=$J["name"];if($C!="pragma_list"&&$C!="compile_options"){$I[$C]=array($C,'');foreach(get_rows("PRAGMA $C")as$J)$I[$C][1].=implode(", ",$J)."\n";}}return$I;}function
show_status(){$I=array();foreach(get_vals("PRAGMA compile_options")as$Ch)$I[]=explode("=",$Ch,2)+array('','');return$I;}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($ud){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$ud);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result,$warnings;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach($N,$V,$E){sqlsrv_configure("WarningsReturnAsErrors",0);$Gb=array("UID"=>$V,"PWD"=>$E,"CharacterSet"=>"UTF-8");$mk=adminer()->connectSsl();if(isset($mk["Encrypt"]))$Gb["Encrypt"]=$mk["Encrypt"];if(isset($mk["TrustServerCertificate"]))$Gb["TrustServerCertificate"]=$mk["TrustServerCertificate"];$j=adminer()->database();if($j!="")$Gb["Database"]=$j;list($Ce,$xi)=host_port($N);$this->link=@sqlsrv_connect($Ce.($xi?",$xi":""),$Gb);if($this->link){$Ye=sqlsrv_server_info($this->link);$this->server_info=$Ye['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){$Al=strlen($Q)!=strlen(utf8_decode($Q));return($Al?"N":"")."'".str_replace("'","''",$Q)."'";}function
select_db($dc){return$this->query(use_sql($dc));}function
query($G,$_l=false){$H=sqlsrv_query($this->link,$G);$this->error="";if(!$H){$this->get_error();return
false;}return$this->store_result($H);}function
multi_query($G){$this->result=sqlsrv_query($this->link,$G);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($H=null){if(!$H)$H=$this->result;if(!$H)return
false;$this->warnings=sqlsrv_errors(SQLSRV_ERR_WARNINGS);if(sqlsrv_field_metadata($H))return
new
Result($H);$this->affected_rows=sqlsrv_rows_affected($H);return
true;}function
next_result(){if(!$this->result)return
false;$I=sqlsrv_next_result($this->result);if($I===false){$this->get_error();$this->result=null;return
true;}return!!$I;}function
warnings(){$I=array();foreach((array)$this->warnings
as$jm)$I[]=$jm["message"];return$I;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($H){$this->result=$H;}private
function
convert($J){foreach((array)$J
as$x=>$X){if(is_a($X,'DateTime'))$J[$x]=$X->format("Y-m-d H:i:s");}return$J;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$I=new
\stdClass;$I->name=$m["Name"];$I->type=($m["Type"]==1?254:15);$I->charsetnr=(in_array($m["Type"],array(-2,-3,-4))?63:0);return$I;}function
seek($mh){for($s=0;$s<$mh;$s++)sqlsrv_fetch($this->result);}}function
last_id($H){return(string)get_val("SELECT SCOPE_IDENTITY()");}function
explain(Db$f,$G){$f->query("SET SHOWPLAN_ALL ON");$I=$f->query($G);$f->query("SET SHOWPLAN_ALL OFF");return$I;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($dc){return$this->query(use_sql($dc));}function
lastInsertId(){return$this->pdo->lastInsertId();}function
warnings(){$H=$this->multi;if(!is_object($H))return
array();$l=$H->errorInfo();return
array((string)$l[2]);}}function
last_id($H){return
connection()->lastInsertId();}function
explain(Db$f,$G){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach($N,$V,$E){list($Ce,$xi)=host_port($N);$Kc="sqlsrv:Server=$Ce".($xi?",$xi":"");$mk=adminer()->connectSsl();foreach(array("Encrypt","TrustServerCertificate")as$x){if(isset($mk[$x]))$Kc
.=";$x=".($mk[$x]?1:0);}return$this->dsn($Kc,$V,$E,array(\PDO::SQLSRV_ATTR_DIRECT_QUERY=>true));}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach($N,$V,$E){list($Ce,$xi)=host_port($N);return$this->dsn("dblib:charset=utf8;host=$Ce".($xi?(is_numeric($xi)?";port=":";unix_socket=").$xi:""),$V,$E);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";static
function
connect($N,$V,$E){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$E);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),);}function
insertUpdate($R,array$K,array$Hi){$n=fields($R);$Il=array();$Z=array();$O=reset($K);$e="c".implode(", c",range(1,count($O)));$Xa=0;$ef=array();foreach($O
as$x=>$X){$Xa++;$C=idf_unescape($x);if(!$n[$C]["auto_increment"])$ef[$x]="c$Xa";if(isset($Hi[$C]))$Z[]="$x = c$Xa";else$Il[]="$x = c$Xa";}$bm=array();foreach($K
as$O)$bm[]="(".implode(", ",$O).")";if($Z){$He=queries("SET IDENTITY_INSERT ".table($R)." ON");$I=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$bm)."\n) AS source ($e) ON ".implode(" AND ",$Z).($Il?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$Il):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($He?$O:$ef)).") VALUES (".($He?$e:implode(", ",$ef)).");");if($He)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$I=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$bm));return$I;}function
begin(){return
queries("BEGIN TRANSACTION");}function
convertSearch($u,array$X,array$m){return(preg_match('~^(bit|n?text|xml|uniqueidentifier|sql_variant|hierarchyid|geography|geometry)$~',$m["type"])?"CAST($u AS nvarchar(max))":$u);}function
quoteBinary($wj){return"0x".bin2hex($wj);}function
warnings(){$I=array();foreach($this->conn->warnings()as$B){$B=trim(preg_replace('~^(\[[^]]+])+~','',$B));if($B!="")$I[]=$B;}return
nl_br(h(implode("\n",$I)));}function
tableHelp($C,$sf=false){$Tf=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$Tf[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($C))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($Ld){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($G,$Z,$z,$mh=0,$Ij=" "){return($z?" TOP (".($z+$mh).")":"")." $G$Z";}function
limit1($R,$G,$Z,$Ij="\n"){return
limit($G,$Z,1,0,$Ij);}function
db_collation($j,array$tb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables(array$i){$I=array();foreach($i
as$j){connection()->select_db($j);$I[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$I;}function
table_status($C="",$td=false){$I=array();$Xj=array();foreach(get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats
GROUP BY object_id",null,"")as$J){$hh=$J["object_id"];unset($J["object_id"]);$Xj[$hh]=$J;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine,
	(SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($C!=""?"AND name = ".q($C):"ORDER BY name"))as$J){$hh=$J["object_id"];unset($J["object_id"]);$I[$J["Name"]]=$J+idx($Xj,$hh,array());}return$I;}function
is_view(array$S){return$S["Engine"]=="VIEW";}function
fk_support(array$S){return
true;}function
fields($R){$_b=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$I=array();$Ak=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name,
	t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($Ak))as$J){$U=$J["type"];$y=(preg_match("~char|binary~",$U)?intval($J["max_length"])/($U[0]=='n'?2:1):($U=="decimal"?"$J[precision],$J[scale]":""));$I[$J["name"]]=array("field"=>$J["name"],"full_type"=>$U.($y?"($y)":""),"type"=>$U,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$J["default"],$A)?str_replace("''","'",$A[1]):$J["default"]),"default_constraint"=>$J["default_constraint"],"null"=>$J["is_nullable"],"auto_increment"=>$J["is_identity"],"collation"=>$J["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$J["is_primary_key"],"comment"=>$_b[$J["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($Ak))as$J){$I[$J["name"]]["generated"]=($J["is_persisted"]?"PERSISTED":"VIRTUAL");$I[$J["name"]]["default"]=$J["definition"];}return$I;}function
indexes($R,$g=null){$I=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$g)as$J){$C=$J["name"];$I[$C]["type"]=($J["is_primary_key"]?"PRIMARY":($J["is_unique"]?"UNIQUE":"INDEX"));$I[$C]["lengths"]=array();$I[$C]["columns"][$J["key_ordinal"]]=$J["column_name"];$I[$C]["descs"][$J["key_ordinal"]]=($J["is_descending_key"]?'1':null);}return$I;}function
view($C){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($C))));}function
collations(){$I=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$sb)$I[preg_replace('~_.*~','',$sb)][]=$sb;return$I;}function
information_schema($j,$L=""){return
in_array($L!=""?$L:get_schema(),array("INFORMATION_SCHEMA","sys"));}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$sb){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$sb)?" COLLATE $sb":""));}function
drop_databases(array$i){return!!queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($C,$sb){if(preg_match('~^[a-z0-9_]+$~i',$sb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $sb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($C));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$C,array$n,array$Nd,$yb,$Tc,$sb,$Ea,$hi){$b=array();$_b=array();$Mh=fields($R);foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);$_b[$m[0]]=$X[5];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?substr($Nd[$X[0]],16+strlen($X[0])):"");else{$k=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$Lh=$Mh[$m[0]];if(default_value($Lh)!=$k){if($Lh["default"]!==null)$b["DROP"][]=" ".idf_escape($Lh["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($R==""){$ka=(array)$b["ADD"];foreach($Nd
as$x=>$X){if(!is_string($x))$ka[]="\n$X";}return
queries("CREATE TABLE ".table($C)." (".implode(",",$ka)."\n)");}if($R!=$C)queries("EXEC sp_rename ".q(table($R)).", ".q($C));if($Nd)$b[""]=$Nd;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($C)." $x".implode(",",$X)))return
false;}foreach($_b
as$x=>$X){$yb=substr($X,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($C).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $yb,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($C).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($R,$b){$v=array();$Fc=array();foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$Fc[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$Fc||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$Fc)));}function
found_rows(array$S,array$Z){}function
foreign_keys($R){$I=array();$wh=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");$L=get_schema();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q($L))as$J){$p=&$I[$J["FK_NAME"]];$p["db"]=($J["PKTABLE_QUALIFIER"]==DB?"":$J["PKTABLE_QUALIFIER"]);$p["ns"]=($J["PKTABLE_OWNER"]==$L?"":$J["PKTABLE_OWNER"]);$p["table"]=$J["PKTABLE_NAME"];$p["on_update"]=$wh[$J["UPDATE_RULE"]];$p["on_delete"]=$wh[$J["DELETE_RULE"]];$p["source"][]=$J["FKCOLUMN_NAME"];$p["target"][]=$J["PKCOLUMN_NAME"];}return$I;}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$gm){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$gm)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$gm,$Pk){return
apply_queries("ALTER SCHEMA ".idf_escape($Pk)." TRANSFER",array_merge($T,$gm));}function
trigger($C,$R){if($C=="")return
array();$K=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($C));$I=reset($K);if($I)$I["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$I["text"]);return$I;}function
triggers($R){$I=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$J)$I[$J["name"]]=array($J["Timing"],$J["Event"]);return$I;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($L,$g=null){$_GET["ns"]=$L;return
true;}function
create_sql($R,$Ea,$sk){if(is_view(table_status1($R))){$fm=view($R);return"CREATE VIEW ".table($R)." AS $fm[select]";}$n=array();$Hi=false;foreach(fields($R)as$C=>$m){$X=process_field($m,$m);if($X[6])$Hi=true;$n[]=implode("",$X);}foreach(indexes($R)as$C=>$v){if(!$Hi||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$C=idf_escape($C);$n[]=($v["type"]=="INDEX"?"INDEX $C":"CONSTRAINT $C ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$C=>$fb)$n[]="CONSTRAINT ".idf_escape($C)." CHECK ($fb)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($R){$n=array();foreach(foreign_keys($R)as$Nd)$n[]=ltrim(format_foreign_key($Nd));return($n?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($dc,$sk=""){return"USE ".idf_escape($dc);}function
trigger_sql($R){$I="";foreach(triggers($R)as$C=>$ql)$I
.=create_trigger(" ON ".table($R),trigger($C,$R)).";";return$I;}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($ud){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|scheme|sql|table|transaction_ddl|trigger|view|view_trigger)$~',$ud);}}add_driver("oracle","Oracle beta");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($Yc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$E){$this->link=@oci_new_connect($V,$E,$N,"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return($l?$l["message"]:'Unknown error.');}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($dc){$this->_current_db=$dc;return
true;}function
query($G,$_l=false){$H=oci_parse($this->link,$G);$this->error="";if(!$H){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$I=@oci_execute($H);restore_error_handler();if($I){if(oci_num_fields($H))return
new
Result($H);$this->affected_rows=oci_num_rows($H);oci_free_statement($H);}return$I;}function
timeout($Ig){return(function_exists('oci_set_call_timeout')?oci_set_call_timeout($this->link,$Ig):false);}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($H){$this->result=$H;}private
function
convert($J){foreach((array)$J
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$J[$x]=$X->load();}return$J;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$I=new
\stdClass;$I->name=oci_field_name($this->result,$d);$U=oci_field_type($this->result,$d);$I->native_type=$U;$I->type=$U;$I->charsetnr=(preg_match("~raw|blob|bfile~",$U)?63:0);return$I;}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach($N,$V,$E){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$V,$E);}function
select_db($dc){$this->_current_db=$dc;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
convertSearch($u,array$X,array$m){$U=$m["type"];$pi=strpos($X["op"],"LIKE")!==false;if($U=="xmltype")return"XMLSERIALIZE(CONTENT $u AS VARCHAR2(4000))";if($U=="json")return"JSON_SERIALIZE($u)";if(preg_match('~^(date|timestamp)~',$U))return"TO_CHAR($u, 'YYYY-MM-DD HH24:MI:SS')";if(preg_match('~char~',$U)||(preg_match('~clob~',$U)&&$pi))return$u;return(!$pi&&preg_match(number_type(),$U)?$u:"TO_CHAR($u)");}function
quoteBinary($wj){return"HEXTORAW(".q(bin2hex($wj)).")";}function
hasCStyleEscapes(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Ld){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($G,$Z,$z,$mh=0,$Ij=" "){return($mh?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $G$Z) t WHERE rownum <= ".($z+$mh).") WHERE rnum > $mh":($z?" * FROM (SELECT $G$Z) WHERE rownum <= ".($z+$mh):" $G$Z"));}function
limit1($R,$G,$Z,$Ij="\n"){return" $G$Z";}function
db_collation($j,array$tb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;connection()->_current_db=null;return$j;}function
where_owner($Di,$Uh="owner"){if(!$_GET["ns"])return'';return"$Di$Uh = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$Uh=where_owner('');return"(SELECT $e FROM all_views WHERE ".($Uh?:"rownum < 0").")";}function
tables_list(){$fm=views_table("view_name");$Uh=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$Uh
UNION SELECT view_name, 'view' FROM $fm
ORDER BY 1");}function
count_tables(array$i){$I=array();foreach($i
as$j)$I[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$I;}function
table_status($C="",$td=false){$I=array();$Aj=q($C);$j=get_current_db();$fm=views_table("view_name");$Uh=where_owner(" AND ","t.owner");foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i
	JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE t.tablespace_name = '.q($j).$Uh.($C!=""?" AND t.table_name = $Aj":"")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM $fm".($C!=""?" WHERE view_name = $Aj":"")."
ORDER BY 1")as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$S){return$S["Engine"]=="view";}function
fk_support(array$S){return
true;}function
fields($R){$I=array();$Uh=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)."$Uh ORDER BY column_id")as$J){$U=$J["DATA_TYPE"];$y="$J[DATA_PRECISION],$J[DATA_SCALE]";if($y==",")$y=$J["CHAR_COL_DECL_LENGTH"];$Mi=array("insert"=>1,"select"=>1,"update"=>1,"order"=>1);if($J["DATA_TYPE_OWNER"]==""||$U=="XMLTYPE")$Mi["where"]=1;$I[$J["COLUMN_NAME"]]=array("field"=>$J["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$J["DATA_DEFAULT"],"null"=>($J["NULLABLE"]=="Y"),"privileges"=>$Mi,);}return$I;}function
indexes($R,$g=null){$I=array();$Uh=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)."$Uh
ORDER BY ac.constraint_type, aic.column_position",$g)as$J){$Te=$J["INDEX_NAME"];$wb=$J["DATA_DEFAULT"];$wb=($wb?trim($wb,'"'):$J["COLUMN_NAME"]);$I[$Te]["type"]=($J["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($J["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$I[$Te]["columns"][]=$wb;$I[$Te]["lengths"][]=($J["CHAR_LENGTH"]&&$J["CHAR_LENGTH"]!=$J["COLUMN_LENGTH"]?$J["CHAR_LENGTH"]:null);$I[$Te]["descs"][]=($J["DESCEND"]&&$J["DESCEND"]=="DESC"?'1':null);}return$I;}function
view($C){$fm=views_table("view_name, text");$K=get_rows('SELECT text "select" FROM '.$fm.' WHERE view_name = '.q($C));return
reset($K);}function
collations(){return
array();}function
information_schema($j,$L=""){return($L!=""?$L:get_schema())=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain(Db$f,$G){$f->query("EXPLAIN PLAN FOR $G");return$f->query("SELECT * FROM plan_table");}function
found_rows(array$S,array$Z){}function
auto_increment(){return"";}function
alter_table($R,$C,array$n,array$Nd,$yb,$Tc,$sb,$Ea,$hi){$b=$Fc=array();$Mh=($R?fields($R):array());foreach($n
as$m){$X=$m[1];if($X&&$m[0]!=""&&idf_escape($m[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($m[0])." TO $X[0]");$Lh=$Mh[$m[0]];if($X&&$Lh){$oh=process_field($Lh,$Lh);if($X[2]==$oh[2])$X[2]="";}if($X)$b[]=($R!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$Fc[]=idf_escape($m[0]);}if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",array_merge($b,$Nd))."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$Fc||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$Fc).")"))&&($R==$C||queries("ALTER TABLE ".table($R)." RENAME TO ".table($C)));}function
alter_indexes($R,$b){$Fc=array();$Ri=array();foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$h=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($Ri,"ALTER TABLE ".table($R).$h);}elseif($X[2]=="DROP")$Fc[]=idf_escape($X[1]);else$Ri[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($Fc)array_unshift($Ri,"DROP INDEX ".implode(", ",$Fc));foreach($Ri
as$G){if(!queries($G))return
false;}return
true;}function
foreign_keys($R){$I=array();$G="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($G)as$J)$I[$J['NAME']]=array("db"=>$J['DEST_DB'],"table"=>$J['DEST_TABLE'],"source"=>array($J['SRC_COLUMN']),"target"=>array($J['DEST_COLUMN']),"on_delete"=>$J['ON_DELETE'],"on_update"=>null,);return$I;}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$gm){return
apply_queries("DROP VIEW",$gm);}function
drop_tables(array$T){return
apply_queries("DROP TABLE",$T);}function
last_id($H){return"0";}function
schemas(){$I=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($I?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($L,$g=null){return!!connection($g)->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($L));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$I=array();$K=get_rows('SELECT * FROM v$instance');foreach(reset($K)as$x=>$X)$I[]=array($x,$X);return$I;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($ud){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$ud);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=6.0.1")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($Ld=true){return
get_databases($Ld);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){$I=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$I))array_unshift($I,$_GET["ns"]);return$I;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Ub){return$Ub;}function
verifyVersion(){return
true;}function
head($Zb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$I=array();foreach(array("","-dark")as$Fg){$o="adminer$Fg.css";if(file_exists($o)){$_d=file_get_contents($o);$I["$o?v=".crc32($_d)]=($Fg?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$_d)?'':'light'));}}return$I;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,on('change','loginDriver'))),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".'hostname[:port] or :socket'."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("fire(qs('#username').form['auth[driver]'], 'change');")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($C,$we,$Y){return$we.$Y."\n";}function
login($Yf,$E){if($E=="")return'Adminer does not support accessing a database without a password.'.require_password_link(null);if(!Driver::$passwords)return'The database does not support passwords.'.require_password_link($E);if(!password_required())return'The server accepts any password, so filling it in protects nothing.'.require_password_link($E);return
true;}function
tableName(array$_k){return
h($_k["Name"]);}function
fieldName(array$m,$Fh=0){$U=$m["full_type"].($m["null"]?" NULL":"");$yb=$m["comment"];return'<span title="'.h($U.($yb!=""?($U?": ":"").$yb:'')).'">'.h($m["field"]).'</span>';}function
commentValue($U,$yb){if($yb==""||$U=='TABLE'||$U=='COLUMN')return
h($yb);$Ci=function($wj){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($wj))));};$R='(\+--[-+]+\+\n)';$J='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$R?$J$R?($J*)$R?~m",function($A)use($Ci){$Gd=$Ci($A[2]);return"<table>\n".($A[1]?"<thead>$Gd<tbody>\n":$Gd).$Ci($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($yb))))."</pre>\n";}function
commentInput($U,$c,$yb){$Y=h($yb);return(preg_match('~\n~',$Y)?"<textarea$c rows='2' cols='".($U=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$c value='$Y'>");}function
selectLinks(array$_k,$O=""){$C=$_k["Name"];echo'<p class="links">';$Tf=array();if($C!="")$Tf["select"]='Select data';if(support("table")||support("indexes"))$Tf["table"]='Show structure';$sf=false;if(support("table")){$sf=is_view($_k);if($sf){if(support("view"))$Tf["view"]='Alter view';}elseif(function_exists('Adminer\alter_table')&&$C!="")$Tf["create"]='Alter table';}if($O!==null)$Tf["edit"]='New item';foreach($Tf
as$x=>$X)echo" <a href='".h(ME)."$x=".url_escape($C).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($C,$sf)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$zk){return
array();}function
backwardKeysPrint(array$Ka,array$J){}function
selectQuery($G,$nk,$sd=false){$I="\n";if(!$sd&&($km=driver()->warnings())){$t="warnings";$I=", <a href='#$t' class='toggle'>".'Warnings'."</a>"."$I<div id='$t' class='hidden'>\n$km</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>(".format_time($nk).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($G)."' class='hover'>".'Edit'."</a>":"").$I;}function
sqlCommandQuery($G){return
shorten_utf8(trim($G),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$K,array$Od){return$K;}function
selectLink($X,array$m){}function
selectVal($X,$_,array$m,$Ph){$I=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$X</code>":(preg_match('~^jsonb?$~',$m["full_type"])?"<code class='jush-json'>$X</code>":$X)));if(is_blob($m)&&!is_utf8($X))$I="<i>".lang_format(array('%d byte','%d bytes'),strlen($Ph))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$I</a>":$I);}function
editVal($X,array$m){return$X;}function
config(){return
array();}function
tableStructurePrint(array$n,$_k=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."<tbody>\n";$rk=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$U=h($m["full_type"]);$sb=h($m["collation"]);echo"<td><span title='$sb'>".(in_array($U,(array)$rk['User types'])?"<a href='".h(ME.'type='.url_escape($U))."'>$U</a>":$U.($sb&&isset($_k["Collation"])&&$sb!=$_k["Collation"]?" $sb":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":""),(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($m["default"])),80,"</code>"):h($m["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$_k){$ci=false;foreach($w
as$C=>$v)$ci|=!!$v["partial"];echo"<table>\n";$ic=first(driver()->indexAlgorithms($_k));foreach($w
as$C=>$v){ksort($v["columns"]);$Ji=array();foreach($v["columns"]as$x=>$X)$Ji[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".h($v["lengths"][$x]).")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($C)."'>","<th>".h($v["type"]).($ic&&$v['algorithm']!=$ic?" (".h($v['algorithm']).")":""),"<td>".implode(", ",$Ji);if($ci)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",'Select',$M);$s=0;$M[""]=array();foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]' data-default=''".on('change',($x!==""?'selectFieldChange':'selectAddRow')),$e,$X["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]," data-default=''".on('change',($x!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"):''),"</div>\n";}$Ah=adminer()->operators();foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],$Ah)))echo"<div>".select_input(" name='where[$s][col]' data-default=''".on('change',($X?'selectFieldChange':'selectAddRow')),$e,$X["col"],"(".'anywhere'.")"),html_select("where[$s][op]",$Ah,$X["op"]," data-default='".h(first($Ah))."'".on('change','selectFirstChange')),"<input type='search' name='where[$s][val]' value='".h($X["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$Fh,array$e,array$w){print_fieldset("sort",'Sort',$Fh);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectFieldChange'),$e,$X),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectAddRow'),$e),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".h($z?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($Wk){echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Wk)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$Yb=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$Yb)$e[$Yb]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$Qc,array$e){}function
selectColumnsProcess(array$e,array$w){$M=array();$ee=array();foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$ee[]=$M[$x];}}return
array($M,$ee);}function
selectSearchProcess(array$n,array$w){$I=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$I[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($_GET["fulltext"][$s]).(isset($_GET["boolean"][$s])?" IN BOOLEAN MODE":"").")";}$Ah=adminer()->operators();foreach((array)$_GET["where"]as$x=>$X){$X+=array("col"=>"","op"=>first($Ah),"val"=>"");$_GET["where"][$x]=$X;$qb=$X["col"];if("$qb$X[val]"!=""&&in_array($X["op"],$Ah)){if($X["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$Cb=array();foreach(($qb!=""?array($qb=>$n[$qb]):$n)as$C=>$m){$Di="";$Bb=" $X[op]";if(preg_match('~IN$~',$X["op"]))$Bb
.=" ".($X["val"]!=""?process_in($X["val"]):"(NULL)");elseif($X["op"]=="SQL")$Bb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$A))$Bb=" $A[1] ".q("%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$Di="$X[op](".q($X["val"]).", ";$Bb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$Bb
.=" ".q($X["val"]);if($qb!=""||is_searchable($m,$X))$Cb[]=$Di.driver()->convertSearch(idf_escape($C),$X,$m).$Bb;}$I[]=(count($Cb)==1?$Cb[0]:($Cb?"(".implode(" OR ",$Cb).")":"1 = 0"));}}return$I;}function
selectOrderProcess(array$n,array$w){$I=array();foreach((array)$_GET["order"]as$x=>$X){if($X!="")$I[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC".(JUSH=='pgsql'&&idx($n[$X],"null")?" NULLS LAST":""):"");}return$I;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$Od){return
false;}function
selectQueryBuild(array$M,array$Z,array$ee,array$Fh,$z,$D){return"";}function
messageQuery($G,$Yk,$sd=false){restart_session();$_e=&get_session("queries");if(!idx($_e,$_GET["db"]))$_e[$_GET["db"]]=array();if(strlen($G)>1e6)$G=preg_replace('~[\x80-\xFF]+$~','',substr($G,0,1e6))."\n…";$_e[$_GET["db"]][]=array($G,time(),$Yk);$ik="sql-".count($_e[$_GET["db"]]);$I="<a href='#$ik' class='toggle'>".'SQL command'."</a> ".copy_icon()."\n";if(!$sd&&($km=driver()->warnings())){$t="warnings-".count($_e[$_GET["db"]]);$I="<a href='#$t' class='toggle'>".'Warnings'."</a>, $I<div id='$t' class='hidden'>\n$km</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $I<div id='$ik' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($G,1e4)."</code></pre>".($Yk?" <span class='time'>($Yk)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($_e[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
error(){return
error();}function
editRowPrint($R,array$n,$J,$Il,$G='',$Yk=''){echo($G!=""?"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>($Yk)</span>\n":"");}function
editFunctions(array$m){$I=($m["null"]?"NULL/":"");$se=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$Yd){if(!$x||(!isset($_GET["call"])&&$se)){foreach($Yd
as$pi=>$X){if(!$pi||preg_match("~$pi~",$m["type"]))$I
.="/$X";}}if($x&&$Yd&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$I
.="/SQL";}if($m["auto_increment"]&&!$se)$I='Auto Increment';return
explode("/",$I);}function
editInput($R,array$m,$c,$Y){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$c,$m,$Y,"NULL");return"";}function
editHint($R,array$m,$Y){return"";}function
processInput(array$m,$Y,$r=""){if($r=="SQL")return$Y;$C=$m["field"];$I=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$I="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$I=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$I=idf_escape($C)." $r $I";elseif(preg_match('~^[+-] interval$~',$r))$I=idf_escape($C)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$I);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$I="$r(".idf_escape($C).", $I)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$I="$r($I)";return
unconvert_field($m,$I);}function
dumpOutput(){$I=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$I['gz']='gzip';return$I;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpPrint(){}function
dumpDatabase($j){}function
dumpTable($R,$sk,$sf=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($sk)dump_csv(array_keys(fields($R)));}else{if($sf==2){$n=array();foreach(fields($R)as$C=>$m)$n[]=idf_escape($C)." $m[full_type]";$h="CREATE TABLE ".table($R)." (".implode(", ",$n).")";}else$h=create_sql($R,$_POST["auto_increment"],$sk);set_utf8mb4($h);if($sk&&$h){if(($sk=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$sf==1)echo"DROP ".($sf==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($sf==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($R,$sk,$G,array$M=array(),array$Z=array(),array$ee=array(),array$Fh=array()){if($sk){$jg=(JUSH=="sqlite"?0:1048576);$n=array();$Ie=false;if($_POST["format"]=="sql"){if($sk=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($R).";\n";$n=fields($R);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Ie=true;break;}}}}$H=($G!=""?connection()->query($G,1):driver()->select($R,($M?:array("*")),$Z,$ee,$Fh,0));if($H){$ef="";$Va="";$zf=array();$Zd=array();$uk="";$vd=($R!=''?'fetch_assoc':'fetch_row');$Qb=0;while($J=$H->$vd()){if(!$zf){$bm=array();foreach($J
as$X){$m=$H->fetch_field();if(idx($n[$m->name],'generated')){$Zd[$m->name]=true;continue;}$zf[]=$m->name;$x=idf_escape($m->name);$bm[]="$x = VALUES($x)";}$uk=($sk=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$bm):"").";\n";}if($_POST["format"]!="sql"){if($sk=="table"){dump_csv($zf);$sk="INSERT";}dump_csv($J);}else{if(!$ef)$ef="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$zf)).") VALUES";foreach($J
as$x=>$X){if($Zd[$x]){unset($J[$x]);continue;}$m=$n[$x];$J[$x]=($X===null?"NULL":($X===false?0:unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($X)?$X:(!is_blob($m)||is_utf8($X)?q($X):driver()->quoteBinary($X)))));}$wj=($jg?"\n":" ")."(".implode(",\t",$J).")";if(!$Va)$Va=$ef.$wj;elseif(JUSH=='mssql'?$Qb%1000!=0:strlen($Va)+4+strlen($wj)+strlen($uk)<$jg)$Va
.=",$wj";else{echo$Va.$uk;$Va=$ef.$wj;}}$Qb++;}if($Va)echo$Va.$uk;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Ie)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Ge){return
friendly_url($Ge!=""?$Ge:(SERVER?:"localhost"));}function
dumpHeaders($Ge,$Kg=false){$Th=$_POST["output"];$nd=(preg_match('~sql~',$_POST["format"])?"sql":($Kg?"tar":"csv"));header("Content-Type: ".($Th=="gz"?"application/x-gzip":($nd=="tar"?"application/x-tar":($nd=="sql"||$Th!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($Th=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$nd;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($Eg){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$Zg=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$Zg)<0?h($Zg):"").version_iframe()."</a>","</span></h1>\n";if($Eg=="auth"){$Th="";foreach((array)$_SESSION["pwds"]as$dm=>$Qj){foreach($Qj
as$N=>$Vl){$C=h(get_setting("vendor-$dm-$N")?:get_driver($dm));foreach($Vl
as$V=>$E){if($C&&$E!==null){$gc=$_SESSION["db"][$dm][$N][$V];foreach(($gc?array_keys($gc):array(""))as$j)$Th
.="<li><a href='".h(auth_url($dm,$N,$V,$j))."'>($C) ".h("$V@").($N!=""?adminer()->serverName($N):"").h($j!=""?" - $j":"")."</a>\n";}}}}if($Th)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$Th</ul>\n";}else{$T=array();if($_GET["ns"]!==""&&!$Eg&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($Eg);$ja=array();if(DB==""||!$Eg){if(support("sql")){$ja['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ja['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ja['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Ne=$_GET["ns"]!==""&&!$Eg&&DB!="";if($Ne&&function_exists('Adminer\alter_table'))$ja['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";$ja=adminer()->menuActions($ja,$Eg);echo($ja?"<p class='links'>\n".implode("\n",$ja)."\n":"");if($Ne){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.0.1",true);$Gg=preg_replace('~<(?=/script)~i','<\\',Driver::jushModule());echo($Gg?script("addEventListener('DOMContentLoaded', () => {\n$Gg\n});"):"");if(support("sql")){echo"<script".nonce().">\n";if($T){$Tf=array();foreach($T
as$R=>$U)$Tf[]=js_escape_re($R);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$Tf).')(?!\$)\b/g',false);$kk=array("sql","check","event","procedure","trigger","view","type","table","processlist");if(support("routine")&&array_intersect_key($_GET,array_flip($kk))){foreach(routines()as$J)json_row(js_escape(ME).'function='.url_escape($J["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($J["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$ok=(isset($_GET["trigger"])?array('INSERT INTO','UPDATE','DELETE FROM'):(isset($_GET["check"])?array():null));$Ga=Driver::jushAutocomplete($T,$ok);echo($Ga?"addEventListener('DOMContentLoaded', () => { autocompleter = $Ga; });\n":"");}}echo"</script>\n";}echo
script("syntaxHighlighting('".(preg_match('~^\d\.?\d~',connection()->server_info,$A)?$A[0]:"")."', '".connection()->flavor."');");}function
databasesPrint($Eg){if(support("single_db"))return;$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$ec=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB,$ec):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($Eg!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"],$ec)."</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
menuActions(array$ja,$Eg){return$ja;}function
tablesPrint(array$T){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($T
as$R=>$P){$R="$R";$C=adminer()->tableName($P);if($C!=""&&!$P["partition"])echo'<li><a href="'.h(ME).'select='.url_escape($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select hover")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$C</a>":"<span>$C</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$drivers=array();var$driverFiles=array();var$error='';private$hooks=array();function
__construct($wi){$Ec=SqlDriver::$drivers;$ye=" href='https://www.adminer.org/plugins/#use'".target_blank();if($wi===null){$wi=array();$Oa="adminer-plugins";if(is_dir($Oa)){foreach(glob("$Oa/*.php")as$o){$Ad=SqlDriver::$drivers;$this->includeOnce($o);foreach(array_diff_key(SqlDriver::$drivers,$Ad)as$t=>$C)$this->driverFiles[$t]=$o;}}if(file_exists("$Oa.php")){$Pe=$this->includeOnce("$Oa.php");if(is_array($Pe)){foreach($Pe
as$x=>$ti)$wi[is_object($ti)?get_class($ti):$x]=$ti;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Oa.php</b>",$ye)."<br>";}foreach(get_declared_classes()as$nb){if(!$wi[$nb]&&(preg_match('~^Adminer\w~i',$nb)||is_subclass_of($nb,'Adminer\Plugin'))){$cj=new
\ReflectionClass($nb);$Ib=$cj->getConstructor();if($Ib&&$Ib->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$ye,"<b>$nb</b>","<b>$Oa.php</b>")."<br>";else$wi[$nb]=new$nb;}}}$jf=array_filter($wi,function($ti){return!is_object($ti);});if($jf){$this->error
.=sprintf('Every plugin must <a%s>be an object</a>.',$ye)."<br>";$wi=array_diff_key($wi,$jf);}$this->drivers=array_diff_key(SqlDriver::$drivers,$Ec);$this->plugins=$wi;$na=new
Adminer;$wi[]=$na;$cj=new
\ReflectionObject($na);foreach($cj->getMethods()as$Bg){foreach($wi
as$ti){$C=$Bg->getName();if(method_exists($ti,$C))$this->hooks[$C][]=$ti;}}}function
includeOnce($o){return
include_once"./$o";}static
function
checksum($o){$_d=str_replace("\r","",file_get_contents($o));$_d=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$_d);return
dechex(crc32($_d));}function
checksums(){$Bd=array_values($this->driverFiles);foreach($this->plugins
as$ti){$cj=new
\ReflectionObject($ti);$Bd[]=$cj->getFileName();}$I=array();foreach($Bd
as$o)$I[basename($o,'.php')]=self::checksum($o);return$I;}static
function
officialChecksums(){return
array('adminer.js'=>'a0599090','backward-keys'=>'ed1ef78f','before-unload'=>'2a613523','config'=>'722eb4af','dark-switcher'=>'3d490dea','database-hide'=>'e304a899','designs'=>'d1515f34','dump-alter'=>'896b579e','dump-bz2'=>'f0d0e336','dump-date'=>'adc7f1c7','dump-json'=>'767dd321','dump-xml'=>'4fc3cd60','dump-zip'=>'93817d96','edit-foreign'=>'72ad1562','edit-textarea'=>'a24c3cc','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'96ee8718','file-upload'=>'10add0e8','foreign-system'=>'ebb4c654','frames'=>'b0e1d11a','highlight-codemirror'=>'f4baf411','highlight-monaco'=>'edd1b0af','highlight-prism'=>'267948e5','import-csv'=>'d429c77','login-ip'=>'4d174fea','login-otp'=>'5b5a68af','login-passkey'=>'f69f2f06','login-password-less'=>'e150daac','login-reverse-proxy'=>'24558ea2','login-servers'=>'19c42e45','login-ssl'=>'6ed147bc','login-table'=>'811f8cef','menu-links'=>'7f3d5020','remote-color'=>'86a39047','row-numbers'=>'eec8698c','select-email'=>'f84fbd2c','select-image'=>'f55c0231','slugify'=>'dec64713','sql-gemini'=>'c60ab309','sql-log'=>'8e435000','table-indexes-structure'=>'a90cc0c9','table-structure'=>'a8458e02','tables-filter'=>'ec2bcd6e','timeout'=>'97321caf','version-github'=>'627cadf9','version-noverify'=>'966937e9','clickhouse'=>'b0f6631c','elastic'=>'27503b8b','firebird'=>'5499d1a','igdb'=>'59055fd3','imap'=>'ac143217','mongo'=>'c3b8f5a4','redis'=>'ba56e72e','simpledb'=>'92f050ad',);}function
__call($C,array$Zh){$za=array();foreach($Zh
as$x=>$X)$za[]=&$Zh[$x];$I=null;foreach($this->hooks[$C]as$ti){$Y=call_user_func_array(array($ti,$C),$za);if($Y!==null){if(!self::$append[$C])return$Y;$I=$Y+(array)$I;}}return$I;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$fh=null){$za=func_get_args();$za[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$za);}}class
Password{private$password_hash;private$password_matches=null;function
__construct($li){$this->password_hash=$li;}function
description(){return'Require a password verified by Adminer';}function
credentials(){$E=get_password();return
array(SERVER,$_GET["username"],($this->passwordMatches($E)&&!password_required()?"":$E));}function
login($Yf,$E){if($this->passwordMatches($E))return
true;}protected
function
passwordMatches($E){if($this->password_matches===null)$this->password_matches=(function_exists('password_verify')&&password_verify(strval($E),$this->password_hash));return$this->password_matches;}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\mysqli{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$E){mysqli_report(MYSQLI_REPORT_OFF);list($Ce,$xi)=host_port($N);$mk=adminer()->connectSsl();$Rl=($mk&&($mk['key']||$mk['cert']||$mk['ca']||isset($mk['verify'])));if($Rl)$this->ssl_set($mk['key'],$mk['cert'],$mk['ca'],'','');$I=@$this->real_connect(($N!=""?$Ce:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$E!=""?$E:ini_get("mysqli.default_pw")),null,(is_numeric($xi)?intval($xi):ini_get("mysqli.default_port")),(is_numeric($xi)?null:$xi),($Rl?($mk['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($I?'':$this->error);}function
set_charset($db){if(parent::set_charset($db))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $db");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}function
inTransaction(){return
false;}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$E){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable the %s or %s extension.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),($N.$V!=""?$V:ini_get("mysql.default_user")),($N.$V.$E!=""?$E:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($db){return
mysql_set_charset($db,$this->link)||mysql_set_charset('utf8',$this->link);}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($dc){return
mysql_select_db($dc,$this->link);}function
query($G,$_l=false){$H=@($_l?mysql_unbuffered_query($G,$this->link):mysql_query($G,$this->link));$this->error="";if(!$H){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($H===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($H);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=mysql_num_rows($H);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$I=mysql_fetch_field($this->result,$this->offset++);$I->orgtable=$I->table;$I->charsetnr=($I->blob?63:0);return$I;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$E){$Dh=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);if(isset($_GET["select"]))$Dh[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$mk=adminer()->connectSsl();if($mk){if($mk['key'])$Dh[\PDO::MYSQL_ATTR_SSL_KEY]=$mk['key'];if($mk['cert'])$Dh[\PDO::MYSQL_ATTR_SSL_CERT]=$mk['cert'];if($mk['ca'])$Dh[\PDO::MYSQL_ATTR_SSL_CA]=$mk['ca'];if(isset($mk['verify']))$Dh[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$mk['verify'];}list($Ce,$xi)=host_port($N);return$this->dsn("mysql:charset=utf8".($Ce!=""?";host=$Ce":'').($xi?(is_numeric($xi)?";port=":";unix_socket=").$xi:""),$V,$E,$Dh);}function
set_charset($db){return$this->query("SET NAMES $db");}function
select_db($dc){return$this->query("USE ".idf_escape($dc));}function
query($G,$_l=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$_l);return
parent::query($G,$_l);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");var$partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");static
function
connect($N,$V,$E){$f=parent::connect($N,$V,$E);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($wj=iconv("windows-1252","utf-8//IGNORE",$f))>strlen($f))$f=$wj;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$f)){$this->types['Network']["inet6"]=39;if(min_version('','10.10',$f))$this->types['Network']["inet4"]=15;}if(min_version(9,11.7,$f))$this->types['Numbers']["vector"]=16383;if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):($m["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geom|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$K,array$Hi){$e=array_keys(reset($K));$Di="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$bm=array();foreach($e
as$x)$bm[$x]="$x = VALUES($x)";$uk="\nON DUPLICATE KEY UPDATE ".implode(", ",$bm);$bm=array();$y=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($bm&&(strlen($Di)+$y+strlen($Y)+strlen($uk)>1e6)){if(!queries($Di.implode(",\n",$bm).$uk))return
false;$bm=array();$y=0;}$bm[]=$Y;$y+=strlen($Y)+2;}return
queries($Di.implode(",\n",$bm).$uk);}function
slowQuery($G,$Zk){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$Zk FOR $G";elseif(preg_match('~^(SELECT\b)(.+)~is',$G,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($Zk*1000).") */ $A[2]";}}function
convertColumn($u,array$m){if(preg_match("~binary~",$m["type"]))return"HEX($u)";if($m["type"]=="bit")return"BIN($u + 0)";if($m["type"]=="vector")return($this->conn->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."($u)";if(preg_match("~geom|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT($u)";return"";}function
convertSearch($u,array$X,array$m){return($this->convertColumn($u,$m)?:(preg_match('~'.text_type().'~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u));}function
typeName(\stdClass$m){$zl=array("decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",);$I=idx($zl,$m->type,"");return
parent::typeName($m)?:($m->charsetnr==63?str_replace(array("text","varchar","char"),array("blob","varbinary","binary"),$I):$I);}function
quoteBinary($wj){return"X".q(bin2hex($wj));}function
warnings(){$H=$this->conn->query("SHOW WARNINGS");if($H&&$H->num_rows){ob_start();print_select_result($H);return
ob_get_clean();}}function
tableHelp($C,$sf=false){$ag=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($ag?"$C-table/":str_replace("_","-",$C)."-table.html"));if(DB=="sys")return($ag?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$C)).".html"));if(DB=="mysql")return($ag?"mysql$C-table/":"system-schema.html");}function
partitionsInfo($R){$Ud="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$H=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Ud ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=($H?$H->fetch_row():null);if(!$J)return
array();$I=array();list($I["partition_by"],$I["partition"],$I["partitions"])=$J;$ii=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Ud AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$I["partition_names"]=array_keys($ii);$I["partition_values"]=array_values($ii);return$I;}function
hasCStyleEscapes(){static$Ya;if($Ya===null){$jk=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Ya=(strpos($jk,'NO_BACKSLASH_ESCAPES')===false);}return$Ya;}function
lineComment(){return"#|-- ";}function
engines(){$I=array();foreach(get_rows("SHOW ENGINES")as$J){if(preg_match("~YES|DEFAULT~",$J["Support"]))$I[]=$J["Engine"];}return$I;}function
indexAlgorithms(array$_k){return(preg_match('~^(MEMORY|NDB)$~',$_k["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($Ld){$I=get_session("dbs");if($I===null){$G="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$nk=microtime(true);$I=($Ld?slow_query($G):get_vals($G));if(microtime(true)-$nk>0.1){restart_session();set_session("dbs",$I);stop_session();}}return$I;}function
limit($G,$Z,$z,$mh=0,$Ij=" "){return" $G$Z".($z?$Ij."LIMIT $z".($mh?" OFFSET $mh":""):"");}function
limit1($R,$G,$Z,$Ij="\n"){return
limit($G,$Z,1,0,$Ij);}function
db_collation($j,array$tb){$I=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$I=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$I=$tb[$A[1]][-1];return$I;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$I=array();foreach($i
as$j)$I[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$I;}function
table_status($C="",$td=false){$I=array();foreach(get_rows($td?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($C!=""?"AND TABLE_NAME = ".q($C):"ORDER BY Name"):"SHOW TABLE STATUS".($C!=""?" LIKE ".q(addcslashes($C,"%_\\")):""))as$J){if($J["Engine"]=="InnoDB")$J["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$J["Comment"]);if(!isset($J["Engine"]))$J["Comment"]="";if($C!="")$J["Name"]=$C;$I[$J["Name"]]=$J;}return$I;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
parse_type($Wd){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$Wd,$A);return
array($A[1],$A[2],ltrim($A[3].$A[4]));}function
fields($R){$ag=(connection()->flavor=='maria');$I=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$J){$m=$J["COLUMN_NAME"];$U=$J["COLUMN_TYPE"];$ae=$J["GENERATION_EXPRESSION"];$qd=$J["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$qd,$Zd);list($yl,$y,$Gl)=parse_type($U);$k=$J["COLUMN_DEFAULT"];if($k!=""){$rf=preg_match('~text|json~',$yl);if(!$ag&&$rf)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($ag||$rf){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$ag&&preg_match('~binary~',$yl)&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$I[$m]=array("field"=>$m,"full_type"=>$U,"type"=>$yl,"length"=>$y,"unsigned"=>$Gl,"default"=>($Zd?($ag?$ae:stripslashes($ae)):$k),"null"=>($J["IS_NULLABLE"]=="YES"),"auto_increment"=>($qd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$qd,$A)?$A[1]:""),"collation"=>$J["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$J[PRIVILEGES],where,order")),"comment"=>$J["COLUMN_COMMENT"],"primary"=>($J["COLUMN_KEY"]=="PRI"),"generated"=>($Zd[1]=="PERSISTENT"?"STORED":$Zd[1]),);}return$I;}function
indexes($R,$g=null){$I=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$g)as$J){$C=$J["Key_name"];$I[$C]["type"]=($C=="PRIMARY"?"PRIMARY":($J["Index_type"]=="FULLTEXT"?"FULLTEXT":($J["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$J["Index_type"])?$J["Index_type"]:"INDEX"):"UNIQUE")));$I[$C]["columns"][]=$J["Column_name"];$I[$C]["lengths"][]=($J["Index_type"]=="SPATIAL"?null:$J["Sub_part"]);$I[$C]["descs"][]=null;$I[$C]["algorithm"]=$J["Index_type"];}return$I;}function
foreign_keys($R){static$pi='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$I=array();$Rb=get_val("SHOW CREATE TABLE ".table($R),1);if($Rb){preg_match_all("~CONSTRAINT ($pi) FOREIGN KEY ?\\(((?:$pi,? ?)+)\\) REFERENCES ($pi)(?:\\.($pi))? \\(((?:$pi,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Rb,$dg,PREG_SET_ORDER);foreach($dg
as$A){preg_match_all("~$pi~",$A[2],$ck);preg_match_all("~$pi~",$A[5],$Pk);$I[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$ck[0]),"target"=>array_map('Adminer\idf_unescape',$Pk[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$I;}function
view($C){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($C),1)));}function
collations(){$I=array();foreach(get_rows("SHOW COLLATION")as$J){if($J["Default"])$I[$J["Charset"]][-1]=$J["Collation"];else$I[$J["Charset"]][]=$J["Collation"];}ksort($I);foreach($I
as$x=>$X)sort($I[$x]);return$I;}function
information_schema($j,$L=""){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$sb){return
queries("CREATE DATABASE ".idf_escape($j).($sb?" COLLATE ".q($sb):""));}function
drop_databases(array$i){$I=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$I;}function
rename_database($C,$sb){$I=false;if(create_database($C,$sb)){$T=array();$gm=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$gm[]=$R;else$T[]=$R;}$I=(!$T&&!$gm)||move_tables($T,$gm,$C);drop_databases($I?array(DB):array());}return$I;}function
auto_increment(){$Fa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Fa="";break;}if($v["type"]=="PRIMARY")$Fa=" UNIQUE";}}return" AUTO_INCREMENT$Fa";}function
alter_table($R,$C,array$n,array$Nd,$yb,$Tc,$sb,$Ea,$hi){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($R!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($R!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$Nd);$P=($yb!==null?" COMMENT=".q($yb):"").($Tc?" ENGINE=".q($Tc):"").($sb?" COLLATE ".q($sb):"").($Ea!=""?" AUTO_INCREMENT=$Ea":"");if($hi){$ii=array();if($hi["partition_by"]=='RANGE'||$hi["partition_by"]=='LIST'){foreach($hi["partition_names"]as$x=>$X){$Y=$hi["partition_values"][$x];$ii[]="\n  PARTITION ".idf_escape($X)." VALUES ".($hi["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $hi[partition_by]($hi[partition])";if($ii)$P
.=" (".implode(",",$ii)."\n)";elseif($hi["partitions"])$P
.=" PARTITIONS ".(+$hi["partitions"]);}elseif($hi===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",$b)."\n)$P");if($R!=$C)$b[]="RENAME TO ".table($C);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$bb=array();foreach($b
as$X)$bb[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$bb));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$gm){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$gm)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$gm,$Pk){$hj=array();foreach($T
as$R)$hj[]=table($R)." TO ".idf_escape($Pk).".".table($R);if(!$hj||queries("RENAME TABLE ".implode(", ",$hj))){$mc=array();foreach($gm
as$R)$mc[table($R)]=view($R);connection()->select_db($Pk);$j=idf_escape(DB);foreach($mc
as$C=>$fm){if(!queries("CREATE VIEW $C AS ".str_replace(" $j."," ",$fm["select"]))||!queries("DROP VIEW $j.$C"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$gm,$Pk){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$C=($Pk==DB?table("copy_$R"):idf_escape($Pk).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $C"))||!queries("CREATE TABLE $C LIKE ".table($R))||!queries("INSERT INTO $C SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){$ql=$J["Trigger"];list($cd,$ih)=trigger_event($J);if(!queries("CREATE TRIGGER ".($Pk==DB?idf_escape("copy_$ql"):idf_escape($Pk).".".idf_escape($ql))." $J[Timing] $cd".($ih!=""?" $ih":"")." ON $C FOR EACH ROW\n$J[Statement];"))return
false;}}foreach($gm
as$R){$C=($Pk==DB?table("copy_$R"):idf_escape($Pk).".".table($R));$fm=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $C"))||!queries("CREATE VIEW $C AS $fm[select]"))return
false;}return
true;}function
trigger_event(array$J){$ed=explode(",",$J["Event"]);$I=array();foreach(array("DELETE","INSERT","UPDATE")as$cd){if(in_array($cd,$ed))$I[]=$cd;}$I=implode(" OR ",$I);if(in_array("UPDATE",$ed)&&min_version('','12.0.1')&&preg_match('~\s(?:BEFORE|AFTER)\s+(.+?)\s+ON\s~is',get_val("SHOW CREATE TRIGGER ".idf_escape($J["Trigger"]),2),$A)&&preg_match('~\bOF\s+(.+)~is',$A[1],$ih))return
array("$I OF",$ih[1]);return
array($I,"");}function
trigger($C,$R){if($C=="")return
array();$K=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($C));$I=reset($K);if($I)list($I["Event"],$I["Of"])=trigger_event($I);return$I;}function
triggers($R){$I=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){list($cd)=trigger_event($J);$I[$J["Trigger"]]=array($J["Timing"],$cd);}return$I;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>(min_version('','12.0.1')?array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",):array("INSERT","UPDATE","DELETE")),"Type"=>array("FOR EACH ROW"),);}function
routine($C,$U){$K=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, COLLATION_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND SPECIFIC_NAME = ".q($C)."
ORDER BY ORDINAL_POSITION");$n=array();foreach($K
as$J){$Wd=$J["DTD_IDENTIFIER"];list($yl,$y,$Gl)=parse_type($Wd);$n[]=array("field"=>$J["PARAMETER_NAME"],"type"=>$yl,"length"=>$y,"unsigned"=>$Gl,"null"=>true,"full_type"=>$Wd,"inout"=>($U=="FUNCTION"?"":$J["PARAMETER_MODE"]),"collation"=>$J["COLLATION_NAME"],);}$I=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND ROUTINE_NAME = ".q($C))->fetch_assoc();if($n&&$n[0]['field']=='')$I['returns']=array_shift($n);$I['fields']=$n;return$I;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($C,array$J){return
idf_escape($C);}function
last_id($H){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$G){return$f->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$G);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$Ea,$sk){$I=get_val("SHOW CREATE TABLE ".table($R),1);if(!$Ea)$I=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$I);return$I;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($dc,$sk=""){$C=idf_escape($dc);$I="";if(preg_match('~CREATE~',$sk)&&($h=get_val("SHOW CREATE DATABASE $C",1))){set_utf8mb4($h);if($sk=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $C;\n";$I
.="$h;\n";}return$I."USE $C";}function
trigger_sql($R){$I="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$J){list($J["Event"],$J["Of"])=trigger_event($J);$I
.="\n".create_trigger(" ON ".table($J["Table"]),$J+array("Type"=>"FOR EACH ROW")).";\n";}return$I;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){return
driver()->convertColumn(idf_escape($m["field"]),$m);}function
unconvert_field(array$m,$I){if(preg_match("~binary~",$m["type"]))$I="UNHEX($I)";if($m["type"]=="bit")$I="CONVERT(b$I, UNSIGNED)";if($m["type"]=="vector")$I=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($I)";if(preg_match("~geom|point|linestring|polygon~",$m["type"])){$Di=(min_version(8)?"ST_":"");$I=$Di."GeomFromText($I, $Di"."SRID($m[field]))";}return$I;}function
support($ud){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$ud);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($pd=false){return
array();}function
type_values($t){return"";}function
type_definition($t){return
array("kind"=>"","definition"=>"");}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($L,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(isset($_GET["db"])?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));function
page_header($bl,$l="",$Ua=array(),$cl=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$dl=$bl.($cl!=""?": $cl":"");$el=strip_tags($dl.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'en\' dir=\'ltr\' class=\'ltr nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$el,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.0.1"),'">
';$Vb=adminer()->css();if(is_int(key($Vb)))$Vb=array_fill_keys($Vb,'light');$pe=in_array('light',$Vb)||in_array('',$Vb);$ne=in_array('dark',$Vb)||in_array('',$Vb);$Zb=($pe?($ne?null:false):($ne?:null));$sg=" media='(prefers-color-scheme: dark)'";if($Zb!==false)echo"<link rel='stylesheet'".($Zb?"":$sg)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.0.1")."'>\n";echo"<meta name='color-scheme' content='".($Zb===null?"light dark":($Zb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.0.1");if(adminer()->head($Zb))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=6.0.1")."'>\n";foreach($Vb
as$Ml=>$Fg){$c=($Fg=='dark'&&!$Zb?$sg:($Fg=='light'&&$ne?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$c href='".h($Ml)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".'Menu'."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($Ua!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ua===false)echo"$N\n";else{echo"<a href='".h($_.(DB!=""&&support("single_db")?"&db=":""))."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ua)))echo'<a href="'.h($_."&db=".url_escape(DB).(support("scheme")?"&ns=":"").(support("single_table")?"&select=":"")).'">'.h(DB).'</a> » ';if(is_array($Ua)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> » ';foreach($Ua
as$x=>$X){$oc=(is_array($X)?$X[1]:h($X));if($oc!="")echo"<a href='".h(ME."$x=").url_escape(is_array($X)?$X[0]:$X)."'>$oc</a> » ";}}echo"$bl\n";}}echo"<h2>$dl</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Ub){$ue=array();foreach($Ub
as$x=>$X)$ue[]="$x $X";header("Content-Security-Policy: ".implode("; ",$ue));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
design_checksums(){$Sl=array();foreach(array_keys(adminer()->css())as$Ml)$Sl[preg_replace('~\?.*~','',$Ml)]=true;$I=array();foreach(array("adminer.css","adminer-dark.css")as$o){if($Sl[$o]&&file_exists($o)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($o),$A);$I[$o]=array((string)$A[1],Plugins::checksum($o));}}return$I;}function
official_design_checksums(){return
array('adminer-border/adminer-dark.css'=>'b2527e3','adminer-border/adminer.css'=>'430977ad','adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'1f626deb','konya/adminer.css'=>'2b409696','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'e1ac813d','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'5ab360e7','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'ecb9bd1e','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'81be9a85','rmsoft/adminer.css'=>'6cd4a237','rmsoft_blue-dark/adminer.css'=>'32102a8','rmsoft_blue/adminer.css'=>'7d8d5b18','win98/adminer.css'=>'e82d63c3',);}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$bh;if(!$bh)$bh=base64_encode(rand_string());return$bh;}function
page_messages($l){$Ll=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$yg=idx($_SESSION["messages"],$Ll);if($yg){echo"<div class='message'>".implode("</div>\n<div class='message'>",$yg)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$Ll]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($Eg=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($Eg);echo"</div>\n";if($Eg!="auth")echo'<form action="" method="post">
<p class="logout">
<span title="Username">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'Logout\' id=\'logout\'>
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($Mg){while($Mg>=2147483648)$Mg-=4294967296;while($Mg<=-2147483649)$Mg+=4294967296;return(int)$Mg;}function
long2str(array$W,$im){$wj='';foreach($W
as$X)$wj
.=pack('V',$X);if($im)return
substr($wj,0,end($W));return$wj;}function
str2long($wj,$im){$W=array_values(unpack('V*',str_pad($wj,4*ceil(strlen($wj)/4),"\0")));if($im)$W[]=strlen($wj);return$W;}function
xxtea_mx($tm,$sm,$vk,$xf){return
int32((($tm>>5&0x7FFFFFF)^$sm<<2)+(($sm>>3&0x1FFFFFFF)^$tm<<4))^int32(($vk^$sm)+($xf^$tm));}function
encrypt_string($qk,$x){if($qk=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qk,true);$Mg=count($W)-1;$tm=$W[$Mg];$sm=$W[0];$Qi=floor(6+52/($Mg+1));$vk=0;while($Qi-->0){$vk=int32($vk+0x9E3779B9);$Lc=$vk>>2&3;for($Vh=0;$Vh<$Mg;$Vh++){$sm=$W[$Vh+1];$Lg=xxtea_mx($tm,$sm,$vk,$x[$Vh&3^$Lc]);$tm=int32($W[$Vh]+$Lg);$W[$Vh]=$tm;}$sm=$W[0];$Lg=xxtea_mx($tm,$sm,$vk,$x[$Vh&3^$Lc]);$tm=int32($W[$Mg]+$Lg);$W[$Mg]=$tm;}return
long2str($W,false);}function
decrypt_string($qk,$x){if($qk=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qk,false);$Mg=count($W)-1;$tm=$W[$Mg];$sm=$W[0];$Qi=floor(6+52/($Mg+1));$vk=int32($Qi*0x9E3779B9);while($vk){$Lc=$vk>>2&3;for($Vh=$Mg;$Vh>0;$Vh--){$tm=$W[$Vh-1];$Lg=xxtea_mx($tm,$sm,$vk,$x[$Vh&3^$Lc]);$sm=int32($W[$Vh]-$Lg);$W[$Vh]=$sm;}$tm=$W[$Mg];$Lg=xxtea_mx($tm,$sm,$vk,$x[$Vh&3^$Lc]);$sm=int32($W[0]-$Lg);$W[0]=$sm;$vk=int32($vk-0x9E3779B9);}return
long2str($W,true);}$ri=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$ri[$x]=$X;}}function
add_invalid_login(){$Ma=get_temp_dir()."/adminer-invalid";foreach(glob("$Ma*")?:array($Ma)as$o){$q=file_open_lock($o);if($q)break;}if(!$q)$q=file_open_lock("$Ma-".rand_string());if(!$q)return;$lf=json_decode(stream_get_contents($q),true);$Yk=time();if($lf){foreach($lf
as$mf=>$X){if($X[0]<$Yk)unset($lf[$mf]);}}$jf=&$lf[adminer()->bruteForceKey()];if(!$jf)$jf=array($Yk+30*60,0);$jf[1]++;file_write_unlock($q,json_encode($lf));}function
check_invalid_login(array&$ri){$lf=array();foreach(glob(get_temp_dir()."/adminer-invalid*")as$o){$q=file_open_lock($o);if($q){$lf=json_decode(stream_get_contents($q),true);file_unlock($q);break;}}$x=adminer()->bruteForceKey();$jf=idx($lf,$x,array());$ah=($jf[1]>29?$jf[0]-time():0);if($ah>0){$l=lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($ah/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$x==$_SERVER["REMOTE_ADDR"])$l
.='<br>'.sprintf('Use the %s <a%s>plugin</a> if Adminer runs behind a reverse proxy.','<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($l,$ri,false);}}function
password_required(){static$I;if($I===null){$I=(bool)get_session("password_required");if(!$I){$Tb=adminer()->credentials();$I=!is_object(Driver::connect($Tb[0],$Tb[1],""));if($I)set_session("password_required",true);}}return$I;}function
require_password_link($E){$Hg="<a href='https://www.adminer.org/password/'".target_blank().">".'More options'."</a>";if(!function_exists('password_hash'))return" $Hg";$ui=($E!==null?$E:base64_encode(substr(pack("H*",rand_string()),0,12)));$te=password_hash($ui,PASSWORD_DEFAULT);$o="adminer-plugins.php";$jd=file_exists("adminer-plugins.php");if($jd)$hf=($E!==null?sprintf('Add this line to %s to require the entered password:',"<b>$o</b>"):sprintf('Add this line to %s to require the password %s:',"<b>$o</b>","<b>$ui</b>"));else{$o="<button name='password_less' value='".h($te)."' class='link'>$o</button>";$hf=($E!==null?sprintf('Save %s next to Adminer to require the entered password:',$o):sprintf('Save %s next to Adminer to require the password %s:',$o,"<b>$ui</b>"));}$Rf="\t<a>new</a> Adminer\\Password(<span class='jush-apo'>'".h($te)."'</span>),";$I="<p>$hf
<pre><code class='jush'>".($jd?$Rf:"&lt;?php\n<a>return</a> <a>array</a>(\n$Rf\n);")."</code></pre>
<p>$Hg
";return" <a href='#password-less' class='toggle'>".'Require a password.'."</a>
<div id='password-less' class='hidden'>".($jd?$I:"<form action='' method='post'>\n".$I.input_token()."</form>")."</div>";}if(preg_match('~^[-\w$./]+$~',$_POST["password_less"])&&verify_token()){header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=adminer-plugins.php");echo"<?php\nreturn array(\n\tnew Adminer\\Password('$_POST[password_less]'),\n);\n";exit;}$Da=$_POST["auth"];if($Da&&verify_token()){session_regenerate_id();$dm=$Da["driver"];$N=$Da["server"];$V=$Da["username"];$E=(string)$Da["password"];$j=$Da["db"];set_password($dm,$N,$V,$E);$_SESSION["db"][$dm][$N][$V][$j]=true;if($Da["permanent"]){$x=implode("-",array_map('base64_encode',array($dm,$N,$V,$j)));$Ki=adminer()->permanentLogin(true);$ri[$x]="$x:".base64_encode($Ki?encrypt_string($E,$Ki):"");cookie("adminer_permanent",implode(" ",$ri));}if(!array_diff(array_keys($_POST),array("auth","token"))||$dm!=DRIVER||$N!=SERVER||$V!==$_GET["username"]||$j!=DB)redirect(auth_url($dm,$N,$V,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($ri);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer. Consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($ri&&!$_SESSION["pwds"]){session_regenerate_id();$Ki=adminer()->permanentLogin();foreach($ri
as$x=>$X){list(,$mb)=explode(":",$X);list($dm,$N,$V,$j)=array_map('base64_decode',explode("-",$x));set_password($dm,$N,$V,decrypt_string(base64_decode($mb),$Ki));$_SESSION["db"][$dm][$N][$V][$j]=true;}}function
unset_permanent(array&$ri){foreach($ri
as$x=>$X){list($dm,$N,$V,$j)=array_map('base64_decode',explode("-",$x));if($dm==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$j==DB)unset($ri[$x]);}cookie("adminer_permanent",implode(" ",$ri));}function
auth_error($l,array&$ri,$kf=true){$Rj=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Rj]||$_GET[$Rj])&&!$_SESSION["token"])$l='Session expired. Please log in again.';elseif($kf&&($E=get_password())!==null){restart_session();add_invalid_login();if($E===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> the %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);unset_permanent($ri);}}if(!$_COOKIE[$Rj]&&$_GET[$Rj]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$Zh=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$Zh["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo
input_token(),"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($ri);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){check_invalid_login($ri);$Tb=adminer()->credentials();$f=Driver::connect($Tb[0],$Tb[1],$Tb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Yf=null;if(!is_object($f)||($Yf=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($Yf)?$Yf:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the entered password, which might be the cause.':'');auth_error($l,$ri);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Submit the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Da&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){header("HTTP/1.1 403 Forbidden");$l='Invalid CSRF token. Submit the form again.'.' '.'If you did not send this request from Adminer, close this page.';}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){header("HTTP/1.1 413 Content Too Large");$l=sprintf('The POST data is too large. Reduce the data or increase the %s configuration directive.',"<b>post_max_size</b>");if(isset($_GET["sql"]))$l
.=' '.'You can upload a large SQL file via FTP and import it from the server.';}function
print_select_result($H,$g=null,array$Jh=array(),&$z=0){$Tf=array();$w=array();$e=array();$Sa=array();$zl=array();$I=array();for($s=0;(!$z||$s<$z)&&($J=$H->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($uf=0;$uf<count($J);$uf++){$m=$H->fetch_field();$C=$m->name;$Ih=(isset($m->orgtable)?$m->orgtable:"");$Hh=(isset($m->orgname)?$m->orgname:$C);if($Jh&&JUSH=="sql")$Tf[$uf]=($C=="table"?"table=":($C=="possible_keys"?"indexes=":null));elseif($Ih!=""){if(isset($m->table))$I[$m->table]=$Ih;if(!isset($w[$Ih])){$w[$Ih]=array();foreach(indexes($Ih,$g)as$v){if($v["type"]=="PRIMARY"){$w[$Ih]=array_flip($v["columns"]);break;}}$e[$Ih]=$w[$Ih];}if(isset($e[$Ih][$Hh])){unset($e[$Ih][$Hh]);$w[$Ih][$Hh]=$uf;$Tf[$uf]=$Ih;}}if($m->charsetnr==63)$Sa[$uf]=true;$zl[$uf]=$m->type;echo"<th title='".h(trim(($Ih!=""?"$Ih.$Hh":($m->name!=$Hh?$Hh:""))." ".driver()->typeName($m)))."'>".h($C).($Jh?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($C),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"<tbody>\n";}echo"<tr>";foreach($J
as$x=>$X){$_="";if(isset($Tf[$x])&&!$e[$Tf[$x]]){if($Jh&&JUSH=="sql"){$R=$J[array_search("table=",$Tf)];$_=ME.$Tf[$x].url_escape($Jh[$R]!=""?$Jh[$R]:$R);}else{$_=ME."edit=".url_escape($Tf[$x]);foreach($w[$Tf[$x]]as$qb=>$uf){if($J[$uf]===null){$_="";break;}$_
.="&where[".url_escape(bracket_escape($qb))."]=".url_escape($J[$uf]);}}}$m=array('type'=>($Sa[$x]?'blob':($zl[$x]==254?'char':'')),);$X=select_value($X,$_,$m,null);echo"<td".($zl[$x]<=9||$zl[$x]==246?" class='number'":"").">$X";}}$z=$s;echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$I;}function
textarea($C,$Y,$K=10,$ub=80){echo"<textarea name='".h($C)."' rows='$K' cols='$ub' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($c,array$Dh,$Y="",$si=""){if($Dh&&$Y!=""&&!isset($Dh[$Y]))$Dh=array($Y=>$Y)+$Dh;$Ok=($Dh?"select":"input");return"<$Ok$c".($Dh?"><option value=''>$si".optionlist($Dh,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$si'>");}function
json_row($x,$X=null,$bd=true){static$Fd=true;if($Fd)echo"{";if($x!=""){echo($Fd?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?($bd?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$Fd=false;}else{echo"\n}\n";$Fd=true;}}function
flat_collations(){$tb=collations();return(is_array(reset($tb))?call_user_func_array('array_merge',array_values($tb)):$tb);}function
edit_type($x,array$m,array$tb,array$Pd=array(),array$rd=array()){$U=(string)$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($U&&!array_key_exists($U,driver()->types())&&!isset($Pd[$U])&&!in_array($U,$rd))$rd[]=$U;$rk=driver()->structuredTypes();if($Pd)$rk['Foreign keys']=$Pd;echo
optionlist(array_merge($rd,$rk),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($tb?"<input list='collations' name='".h($x)."[collation]'".option_types($U,'('.text_type().')$')." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".option_types($U,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".option_types($U,'timestamp|datetime').'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($Pd?"<select name='".h($x)."[on_delete]'".option_types($U,'`')."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
option_types($U,$zl){return" data-types='".h($zl)."'".(preg_match("~$zl~",$U)?"":" class='hidden'");}function
process_length($y){$Wc=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$Wc(?:\\s*,\\s*$Wc)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$Wc~",$y,$dg)?"(".implode(",",$dg[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_in($X){$Wc=driver()->enumLength;if(preg_match("~^\\s*\\(?\\s*$Wc(?:\\s*,\\s*$Wc)*+\\s*\\)?\\s*\$~",$X)&&preg_match_all("~$Wc~",$X,$dg))return"(".implode(", ",$dg[0]).")";$I=array();foreach(explode(",",$X)as$tf)$I[]=q(trim($tf));return"(".implode(", ",$I).")";}function
process_type(array$m,$rb="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~'.text_type().'~',$m["type"])&&$m["collation"]?" $rb ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$xl){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($xl),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){if($m["default"]===null)return"";$k=str_replace("\r","",$m["default"]);$Zd=$m["generated"];return(in_array($Zd,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($Zd=="VIRTUAL"?"":" $Zd"):" GENERATED ALWAYS AS ($k) $Zd"):(preg_match('~^GENERATED ~i',$k)?" $k":" DEFAULT ".(preg_match('~char|binary|text|json|enum|set|String~',$m["type"])||preg_match('~^(?![a-z])~i',$k)?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
edit_fields(array$n,array$tb,$U="TABLE",array$Pd=array()){$n=array_values($n);$jc=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$zb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$jc>".'Default value',(support("comment")?"<td id='label-comment'$zb>".'Comment':"");$Hf=!support("move_col");echo"<td>".icon("plus","add[".($Hf?count($n):0)."]","+",'Add next',($Hf?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($n
as$s=>$m){$s++;$Kh=$m[($_POST?"orig":"field")];$vc=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$Kh=="");echo"<tr".($vc?"":" hidden").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",'Move')." ":"");if($vc)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$Kh);edit_type("fields[$s]",$m,$tb,$Pd);if($U=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$s][null]",1,$m["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$jc>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default"));$c=" name='fields[$s][default]' aria-labelledby='label-default'";$Y=h($m["default"]);echo(preg_match('~\n~',$m["default"])?"<textarea$c rows='2' cols='30' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$c value='$Y'>");if(support("comment")){$c=" name='fields[$s][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$zb>".adminer()->commentInput('COLUMN',$c,$m["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ":""),($Kh==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){if($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}return$_POST["add"]||$_POST["drop_col"];}function
drop_create($Fc,$h,$Hc,$Uk,$Jc,$Xf,$xg,$vg,$wg,$rh,$Vg){if($_POST["drop"])query_redirect($Fc,$Xf,$xg);elseif($rh=="")query_redirect($h,$Xf,$wg);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($Xf,$vg,queries($Fc)&&queries($h)&&driver()->commit());driver()->rollback();}elseif($rh!=$Vg){$Sb=queries($h);queries_redirect($Xf,$vg,$Sb&&queries($Fc));if($Sb)queries($Hc);}else
queries_redirect($Xf,$vg,queries($Uk)&&queries($Jc)&&queries($Fc)&&queries($h));}function
create_trigger($uh,array$J){$al=" $J[Timing] $J[Event]".(preg_match('~ OF~',$J["Event"])?" $J[Of]":"");return"CREATE TRIGGER ".idf_escape($J["Trigger"]).(JUSH=="mssql"?$uh.$al:$al.$uh).rtrim(" $J[Type]\n$J[Statement]",";").";";}function
q_dollar($Q){$nc='$$';while(strpos($Q.$nc,$nc)!=strlen($Q))$nc='$_'.substr($nc,1);return$nc.$Q.$nc;}function
routine_collate($sb){static$eb=array();if($sb&&!$eb){foreach(collations()as$db=>$am){foreach((array)$am
as$X)$eb[$X]=$db;}}return($eb[$sb]?"CHARACTER SET ".q($eb[$sb])." ":"")."COLLATE";}function
create_routine($rj,array$J){$O=array();$n=(array)$J["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,routine_collate($m["collation"]));}$lc=rtrim($J["definition"],";");return"CREATE $rj ".idf_escape(trim($J["name"]))." (".implode(", ",$O).")".($rj=="FUNCTION"?" RETURNS".process_type($J["returns"],routine_collate($J["returns"]["collation"])):"").($J["language"]?" LANGUAGE $J[language]":"").(JUSH=="pgsql"?" AS ".q_dollar("\n".trim($lc)."\n"):"\n$lc;");}function
remove_definer($G){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$G);}function
format_foreign_key(array$p){$j=$p["db"];$ch=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($ch!=""&&$ch!=$_GET["ns"]?idf_escape($ch).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"").($p["deferrable"]?" $p[deferrable]":"");}function
tar_file($o,$fl){$I=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($fl->size),decoct(time()));$kb=8*32;for($s=0;$s<strlen($I);$s++)$kb+=ord($I[$s]);$I
.=sprintf("%06o",$kb)."\0 ";echo$I,str_repeat("\0",512-strlen($I));$fl->send();echo
str_repeat("\0",511-($fl->size+511)%512);}function
doc_link(array$oi,$Vk="<sup>?</sup>"){$Pj=connection()->server_info;$em=preg_replace('~^(\d\.?\d).*~s','\1',$Pj);$Nl=array('sql'=>"https://dev.mysql.com/doc/refman/$em/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$em)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$Pj)."&id=",);if(connection()->flavor=='maria'){$Nl['sql']="https://mariadb.com/kb/en/";$oi['sql']=(isset($oi['mariadb'])?$oi['mariadb']:str_replace(".html","/",$oi['sql']));}return($oi[JUSH]?"<a href='".h($Nl[JUSH].$oi[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$em":""))."'".target_blank().">$Vk</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$I=0;foreach(table_status()as$S)$I+=$S["Data_length"]+$S["Index_length"];return
format_number($I);}function
set_utf8mb4($h){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$h)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if(!isset($_GET["db"])&&support("single_db")){$i=adminer()->databases();if($i)redirect(ME."db=".url_escape($i[0]));}if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged in as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$zj=support("scheme");$tb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".'Compute'."</a>"."<tbody>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$T){$qj=h(preg_replace('~&db=[^&]*~','',ME))."db=".url_escape($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$qj' id='$t'>".h($j)."</a>";$sb=h(db_collation($j,$tb));echo"<td>".(support("database")?"<a href='$qj".($zj?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$sb</a>":$sb),"<td align='right'><a href='$qj&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".'Drop'."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$na=adminer();$wi=($na
instanceof
Plugins?$na->plugins:array());$Ec=($na
instanceof
Plugins?$na->drivers:array());$sc=design_checksums();if($wi||$Ec||$sc){$lb=($na
instanceof
Plugins?$na->checksums():array());$jh=Plugins::officialChecksums();$Jl=function($Ml){return" (<a href='$Ml'".target_blank()." class='update'>".VERSION."</a>)";};$vi=function($_d)use($lb,$jh,$Jl){return($lb[$_d]&&$jh[$_d]&&$lb[$_d]!==$jh[$_d]?$Jl("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach($wi
as$ti){$cj=new
\ReflectionObject($ti);$pc=(method_exists($ti,'description')?$ti->description():"");if(!$pc){if(preg_match('~^/[\s*]+(.+)~',$cj->getDocComment(),$A))$pc=$A[1];}$_j=(method_exists($ti,'screenshot')?$ti->screenshot():"");echo"<li><b>".get_class($ti)."</b>".h($pc?": $pc":"").($_j?" (<a href='".h($_j)."'".target_blank().">".'screenshot'."</a>)":"").$vi(basename((string)$cj->getFileName(),'.php'))."\n";}foreach($Ec
as$t=>$C)echo"<li><b>".h($t)."</b>: ".h($C).$vi(basename((string)$na->driverFiles[$t],'.php'))."\n";if($sc){$lh=official_design_checksums();foreach($sc
as$o=>$rc){list($C,$kb)=$rc;$kh=$lh["$C/$o"];echo"<li><b>".h($o)."</b>".h($C?": $C":"").($kh&&$kh!==$kb?$Jl("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~&db=[^&]+~','\0&ns='.url_escape(get_schema()),relative_uri()));if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header('Schema'.h(": $_GET[ns]"),'Invalid schema.',true);page_footer("ns");exit;}}}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($Kb){$this->size+=strlen($Kb);fwrite($this->handler,$Kb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if($_GET["select"]!=""&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$H=driver()->select($a,$M,array(where($_GET,$n)),$M);$J=($H?$H->fetch_row():array());echo
driver()->value($J[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=adminer()->error()?:'No tables.';$S=table_status1($a);$C=adminer()->tableName($S);page_header(($n&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($C!=""?$C:h($a)),$l);$pj=array();foreach($n
as$x=>$m)$pj+=$m["privileges"];adminer()->selectLinks($S,(isset($pj["insert"])||!support("table")?"":null));$yb=$S["Comment"];if($yb!="")echo"<p class='nowrap'>".'Comment'.": ".adminer()->commentValue('TABLE',$yb)."\n";if($n)adminer()->tableStructurePrint($n,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$J){$_=preg_replace('~ns=[^&]*~',"ns=".url_escape($J["ns"]),ME);echo"<li><a href='".h($_."table=".url_escape($J["table"]))."'>".($J["ns"]!=$_GET["ns"]?"<b>".h($J["ns"])."</b>.":"").h($J["table"])."</a>";}echo"</ul>\n";}$af=driver()->inheritsFrom($a);if($af){echo"<h3>".'Inherits from'."</h3>\n";tables_links($af);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<div>\n","<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);if(driver()->supportsAlterIndex($S))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.'Alter indexes'."</a>\n";echo"</div>\n";}if(!is_view($S)){if(fk_support($S)){echo"<div>\n","<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$Pd=foreign_keys($a);if($Pd){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td class='hover'><tbody>\n";foreach($Pd
as$C=>$p){echo"<tr title='".h($C)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".url_escape($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".url_escape($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".url_escape($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($C)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.'Create foreign key'."</a>\n","</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".'Checks'."</h3>\n";$gb=driver()->checkConstraints($a);if($gb){echo"<table>\n";foreach($gb
as$x=>$X)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($X)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.'Create check'."</a>\n","</div>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<div>\n","<h3 id='triggers'>".'Triggers'."</h3>\n";$ul=triggers($a);if($ul){echo"<table>\n";foreach($ul
as$x=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.'Create trigger'."</a>\n","</div>\n";}$Ze=driver()->inheritedTables($a);if($Ze){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$di=driver()->partitionsInfo($a);if($di)echo"<p><code class='jush-".JUSH."'>BY ".h("$di[partition_by]($di[partition])")."</code>\n";tables_links($Ze);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));function
schema_column($R,array$bj,array&$e){if(!isset($e[$R])){$e[$R]=0;foreach((array)idx($bj,$R)as$C=>$dj){if($C!=$R)$e[$R]=max($e[$R],schema_column($C,$bj,$e)+1);}}return$e[$R];}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}$Fk=array();$Hk=array();$Gk=array();$xd=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$dg,PREG_SET_ORDER);foreach($dg
as$s=>$A){$Fk[$A[1]]=array((float)$A[2],(float)$A[3]);$Hk[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$L=array();$bj=array();$Pd=array();$ua=driver()->allFields();$ze=array();$Ik=array();foreach(table_status('',true)as$R=>$S){if(!is_view($S)){if(adminer()->tableName($S)!="")$Ik[$R]=$S;else$ze[$R]=true;}}foreach($Ik
as$R=>$S){$F=0;$L[$R]["fields"]=array();foreach($ua[$R]as$m){$F+=1.25;$xd[$R][$m["field"]]=$F;$L[$R]["fields"][$m["field"]]=$m;}foreach(adminer()->foreignKeys($R)as$X){if($X["db"]==""&&$X["ns"]==""&&!$ze[$X["table"]]){$Pd[$R][]=$X;$bj[$X["table"]][$R]=array();}}}$e=array();$de=array();$rm=array();$je=array();foreach(array_keys($L)as$C)schema_column($C,$bj,$e);arsort($e);foreach($e
as$C=>$d){$Cg=null;foreach((array)idx($Pd,$C)as$X){if($X["table"]!=$C&&$L[$X["table"]])$Cg=($Cg===null?$e[$X["table"]]:min($Cg,$e[$X["table"]]));}$e[$C]=max($d,(int)$Cg-1);}foreach($L
as$C=>$R){$d=$e[$C];$de[$d][]=$C;$Xk=.75*strlen($C);foreach($R["fields"]as$m)$Xk=max($Xk,.65*strlen($m["field"]));$rm[$d]=max(idx($rm,$d,0),ceil($Xk)+1);}foreach($Pd
as$C=>$am){foreach($am
as$X){$ie=$e[$C]+(idx($e,$X["table"],$e[$C])>$e[$C]?1:0);$je[$ie]=idx($je,$ie,0)+1;}}ksort($de);$xe=0;$qm=0;$vb=0;$Gi=null;$Bk=array();$Kk=array();foreach($de
as$d=>$T){if($Gi!==null){$vb=round($vb+$rm[$Gi]+1.7+idx($je,$d,0)*.1,1);$Fh=array();foreach($T
as$C){$vk=0;$Qb=0;$Rg=array_keys((array)idx($bj,$C));foreach((array)idx($Pd,$C)as$X)$Rg[]=$X["table"];foreach($Rg
as$Ng){if($L[$Ng]&&$e[$Ng]<$d){$vk+=$L[$Ng]["pos"][0];$Qb++;}}$Fh[$C]=($Qb?$vk/$Qb:$xe);}asort($Fh);$T=array_keys($Fh);}$il=0;foreach($T
as$C){$F=1.25*count($L[$C]["fields"]);$L[$C]["pos"]=($Fk[$C]?:array($il,$vb));$Bk[$C]=$L[$C]["pos"][1];$Kk[$C]=$rm[$d];$il+=2.5+$F;$xe=max($xe,$L[$C]["pos"][0]+2.5+$F);$qm=max($qm,round($L[$C]["pos"][1]+$rm[$d],1));if(!$Fk[$C])$Gk[]="\n\t'".js_escape($C)."': [ ".$L[$C]["pos"][0].", ".$L[$C]["pos"][1]." ]";}$Gi=$d;}$Lf=array();$Na=array();foreach($Pd
as$C=>$am){foreach($am
as$X){$Qk=idx($Bk,$X["table"],$Bk[$C]);$dk=$Bk[$C]+$Kk[$C];$oj=($Qk-1>$dk);$Jf=($oj?$dk+1:min($Bk[$C],$Qk)-1);$Ma=idx($Na,(string)$Jf,0);$Na[(string)$Jf]=$Ma+1;$Jf=round($oj?min($Jf+$Ma*.1,$Qk-1):$Jf-$Ma*.1,1);while($Lf[(string)$Jf])$Jf-=.0001;$L[$C]["references"][$X["table"]][(string)$Jf]=array($X["source"],$X["target"]);$bj[$X["table"]][$C][(string)$Jf]=$X["target"];$Lf[(string)$Jf]=true;}}echo'<div id="schema" style="height: ',$xe,'em; width: ',$qm,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$Hk)."\n",'};
const tablePosDefault = {',implode(",",$Gk)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$xe,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($L
as$C=>$R){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em; width: ".$Kk[$C]."em;'>",'<a href="'.h(ME).'table='.url_escape($C).'"><b>'.h($C)."</b></a>";foreach($R["fields"]as$m){$X='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$Rk=>$dj){foreach($dj
as$Jf=>$Yi){$Kf=$Jf-$R["pos"][1];$sk=($Kf>0?"left: 100%; width: calc($Kf"."em - 100%)":"left: $Kf"."em");$qm=($Kf>0?"100%":(-$Kf)."em");$s=0;foreach($Yi[0]as$ck)echo"\n<div class='references' title='".h($Rk)."' id='refs$Jf-".($s++)."' style='$sk"."; top: ".$xd[$C][$ck]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: $qm;'></div></div>";}}foreach((array)$bj[$C]as$Rk=>$dj){foreach($dj
as$Jf=>$Sk){$Kf=$Jf-$R["pos"][1];$s=0;foreach($Sk
as$Pk)echo"\n<div class='references arrow' title='".h($Rk)."' id='refd$Jf-".($s++)."' style='left: $Kf"."em; top: ".$xd[$C][$Pk]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Kf)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($L
as$C=>$R){foreach((array)$R["references"]as$Rk=>$dj){if($L[$Rk]){foreach($dj
as$Jf=>$Yi){$Dg=$xe;$lg=-10;foreach($Yi[0]as$x=>$ck){$yi=$R["pos"][0]+$xd[$C][$ck];$zi=$L[$Rk]["pos"][0]+$xd[$Rk][$Yi[1][$x]];$Dg=min($Dg,$yi,$zi);$lg=max($lg,$yi,$zi);}echo"<div class='references' id='refl$Jf' style='left: $Jf"."em; top: $Dg"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($lg-$Dg)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){$k=array("auto_increment"=>'');foreach(array("type","routine","event","trigger")as$xk){if(support($xk))$k[$xk."s"]='';}save_settings(array_intersect_key($_POST+$k,array_flip(array("output","format","db_style","table_style","data_style"))+$k),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$nd=dump_headers((count($T)==1?key($T):DB),(DB==""||$_GET["ns"]===""||count($T)>1));$qf=preg_match('~sql~',$_POST["format"]);if($qf){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$sk=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($qf&&$sk)echo
use_sql($j,$sk).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?array(""):adminer()->schemas()))as$L){if($L!=""){if(DB==""&&information_schema(DB,$L))continue;set_schema($L);}$pk=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):array());$md=array();$cc=array();foreach($pk
as$C=>$S){if(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["tables"]))$md[$C]=$S;if(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["data"]))$cc[$C]=$S;}if($qf){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($md);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$vl=array();foreach($cc
as$C=>$S){if(!is_view($S)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($md[$C])))$vl[]=$C;}echo
truncate_all_sql($vl);}$Sh="";if($_POST["types"]){foreach(types()as$t=>$U){$lc=type_definition($t);$gh=($lc["kind"]=='d'?"DOMAIN":"TYPE");if($lc["definition"])$Sh
.=($sk!='DROP+CREATE'?"DROP $gh IF EXISTS ".idf_escape($U).";;\n":"")."CREATE $gh ".idf_escape($U)." $lc[definition];\n\n";else$Sh
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$J){$C=$J["ROUTINE_NAME"];$rj=$J["ROUTINE_TYPE"];$h=create_routine($rj,array("name"=>$C)+routine($J["SPECIFIC_NAME"],$rj));set_utf8mb4($h);$Sh
.=($sk!='DROP+CREATE'?"DROP $rj IF EXISTS ".idf_escape($C).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$J){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($J["Name"]),3));set_utf8mb4($h);$Sh
.=($sk!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($J["Name"]).";;\n":"")."$h;;\n\n";}}echo($Sh&&JUSH=='sql'?"DELIMITER ;;\n\n$Sh"."DELIMITER ;\n\n":$Sh);}if($_POST["table_style"]||$_POST["data_style"]){$gm=array();foreach($pk
as$C=>$S){$R=array_key_exists($C,$md);$ac=array_key_exists($C,$cc);if($R||$ac){$fl=null;if($nd=="tar"){$fl=new
TmpFile;ob_start(array($fl,'write'),1e5);}adminer()->dumpTable($C,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$gm[]=$C;elseif($ac){$n=fields($C);$M=array("*");$Nb=convert_fields($n,$n);if($Nb)$M[]=substr($Nb,2);adminer()->dumpData($C,$_POST["data_style"],"",$M);}if($qf&&$_POST["triggers"]&&$R&&($ul=trigger_sql($C)))echo"\nDELIMITER ;;\n$ul\nDELIMITER ;\n";if($nd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$C.csv",$fl);}elseif($qf)echo"\n";}}if($qf&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($md
as$C=>$S){if(!is_view($S))echo
foreign_keys_sql($C);}}if($qf){foreach($gm
as$fm)adminer()->dumpTable($fm,$_POST["table_style"],1);}if($nd=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$fc=array('','USE','DROP+CREATE','CREATE');$Jk=array('','DROP+CREATE','CREATE');$bc=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$bc[]='INSERT+UPDATE';$J=get_settings("adminer_export");if(!$J)$J=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$J["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$J["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$fc,$J["db_style"]).(support("type")?checkbox("types",1,$J["types"],'User types'):"").(support("routine")?checkbox("routines",1,$J["routines"],'Routines'):"").(support("event")?checkbox("events",1,$J["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$Jk,$J["table_style"]).checkbox("auto_increment",1,$J["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$J["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$bc,$J["data_style"]),'</table>
';adminer()->dumpPrint();echo'<p><input type=\'submit\' value=\'Export\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$Ei=array();if($_GET["ns"]===""){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".'All'."'".on('click','formCheck','^schemas\[').">".'Schema'."</label>","<tbody>\n";foreach(adminer()->schemas()as$L){if(!information_schema(DB,$L))echo"<tr><td>".checkbox("schemas[]",$L,true,$L,"","block")."\n";}}elseif(DB!=""){$ib=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$ib class='jsonly' title='".'All'."'".on('click','formCheck','^tables\[').">".'Table'."</label>","<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$ib class='jsonly' title='".'All'."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$gm="";$Mk=tables_list();foreach($Mk
as$C=>$U){$Di=preg_replace('~_.*~','',$C);$ib=($a==""||$a==(substr($a,-1)=="%"?"$Di%":$C));$Ji="<tr><td>".checkbox("tables[]",$C,$ib,$C,"","block");if($U!==null&&!preg_match('~table~i',$U))$gm
.="$Ji\n";else
echo"$Ji<td align='right'><label class='block'><span id='Rows-".h($C)."'></span>".checkbox("data[]",$C,$ib)."</label>\n";$Ei[$Di]++;}echo$gm;if($Mk)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$i=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($i?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".'All'."'".on('click','formCheck','^databases\[').">":"").'Database'."</label>","<tbody>\n";if($i){foreach($i
as$j){if(!information_schema($j)){$Di=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$Di%",$j,"","block")."\n";$Ei[$Di]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$Fd=true;foreach($Ei
as$x=>$X){if($x!=""&&$X>1){echo($Fd?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$x%")."'>".h($x)."</a>";$Fd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$H=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$be=$H;if(!$H)$H=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($be?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<td class='hover'><tbody>\n";while($J=$H->fetch_assoc())echo'<tr><td>'.h($J["User"]),"<td>".h($J["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($J["User"]).'&host='.url_escape($J["Host"])).'">'.'Edit'."</a>\n";if(!$be||DB!="")echo"<tr><td><input name='user' autocapitalize='off'>","<td><input name='host' value='localhost' autocapitalize='off'>","<td class='hover'><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$Ae=&get_session("queries");$_e=&$Ae[DB];if(!$l&&$_POST["clear"]){$_e=array();redirect(remove_from_uri("history"));}stop_session();$oa=get_settings("adminer_import");if($_POST&&$oa)save_settings($oa,"adminer_import");page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$Sf=driver()->lineComment();if(!$l&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$nc=driver()->delimiter;$q=false;if(!isset($_GET["import"]))$G=$_POST["query"];elseif($_POST["webfile"]){$hk=adminer()->importServerPath();$q=@fopen((file_exists($hk)?$hk:"compress.zlib://$hk.gz"),"rb");$G=($q?fread($q,1e6):false);}else$G=get_file("sql_file",true,$nc);if(is_string($G)){if(($tg=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($tg,strval(2*strlen($G)+memory_get_usage()+8e6)));if($G!=""&&strlen($G)<1e6){$Qi=$G.(preg_match("~$nc\\s*\$~",$G)?"":$nc);if(!$_e||first(end($_e))!=$Qi){restart_session();$_e[]=array($Qi,time());set_session("queries",$Ae);stop_session();}}$ek="(?:\\s|/\\*[\s\S]*?\\*/|(?:$Sf)[^\n]*\n?|--\r?\n)";$mh=0;$Sc=true;$Pb=false;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$xb=0;$Zc=array();$ai='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Sf.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$jl=microtime(true);while($G!=""){if(!$mh&&preg_match("~^$ek*+DELIMITER\\s+(\\S+)~i",$G,$A)){$nc=preg_quote($A[1]);$G=substr($G,strlen($A[0]));}elseif(!$mh&&JUSH=='pgsql'&&preg_match("~^($ek*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$G,$A)){$nc="\n\\\\\\.\r?\n";$Pb=true;$mh=strlen($A[0]);}else{preg_match("($nc\\s*|$ai)",$G,$A,PREG_OFFSET_CAPTURE,$mh);list($Rd,$F)=$A[0];if(!$Rd&&$q&&!feof($q))$G
.=fread($q,1e5);else{if(!$Rd&&rtrim($G)=="")break;$mh=$F+strlen($Rd);if($Rd&&!preg_match("(^$nc)",$Rd)){$Za=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($F>0&&strtolower($G[$F-1])=="e"));$pi=($Rd=='/*'?'\*/':($Rd=='['?']':(preg_match("~^(?:$Sf)~",$Rd)?"\n":preg_quote($Rd).($Za?'|\\\\.':''))));while(preg_match("($pi|\$)s",$G,$A,PREG_OFFSET_CAPTURE,$mh)){$wj=$A[0][0];if(!$wj&&$q&&!feof($q))$G
.=fread($q,1e5);else{$mh=$A[0][1]+strlen($wj);if(!$wj||$wj[0]!="\\")break;}}}else{$Sc=false;$Qi=substr($G,0,$F+($Pb?3:0));$xb++;$Ji="<pre id='sql-$xb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($Qi)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$ek*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Qi,$A)!==0){echo$Ji,"<p class='error'>".sprintf('%s queries are not supported.',preg_match('~ATTACH~i',$A[1])?'ATTACH':'VACUUM INTO')."\n";$Zc[]=" <a href='#sql-$xb'>$xb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Ji;ob_flush();flush();}$nk=microtime(true);if(connection()->multi_query($Qi)&&$g&&preg_match("~^$ek*+USE\\b~i",$Qi))$g->query($Qi);do{$H=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Ji:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".adminer()->error()."\n";$Zc[]=" <a href='#sql-$xb'>$xb</a>";if($_POST["error_stops"])break
2;}else{$_=ME."sql=".url_escape(trim($Qi));$Yk=" <span class='time'>(".format_time($nk).")</span>".(strlen($_)<1900?" <a href='".h($_)."'>".'Edit'."</a>":"");$qa=connection()->affected_rows;$km=($_POST["only_errors"]?"":driver()->warnings());$lm="warnings-$xb";if($km)$Yk
.=", <a href='#$lm' class='toggle'>".'Warnings'."</a>";$kd=null;$Jh=null;$ld="explain-$xb";if(is_object($H)){$z=$_POST["limit"];$eh=$z;$Jh=print_select_result($H,$g,array(),$eh);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$eh=max($H->num_rows,$eh);echo"<p class='sql-footer'>".($eh?($z&&$eh>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$eh):""),$Yk;if($g&&preg_match("~^($ek|\\()*+SELECT\\b~i",$Qi)&&($kd=explain($g,$Qi)))echo", <a href='#$ld' class='toggle'>Explain</a>";$t="export-$xb";echo", <a href='#$t' class='toggle'>".'Export'."</a><span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$oa["output"])." ".html_select("format",adminer()->dumpFormat(),$oa["format"]).input_hidden("query",$Qi)."<input type='submit' name='export' value='".'Export'."'".($z?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$ek*+(CREATE|DROP|ALTER)$ek++(DATABASE|SCHEMA)\\b~i",$Qi)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$qa)."$Yk\n";}echo($km?"<div id='$lm' class='hidden'>\n$km</div>\n":"");if($kd){echo"<div id='$ld' class='hidden explain'>\n";print_select_result($kd,$g,$Jh);echo"</div>\n";}}$nk=microtime(true);}while(connection()->next_result());}$G=substr($G,$mh);$mh=0;if($Pb){$nc=driver()->delimiter;$Pb=false;}}}}}if($Sc)echo"<p class='message'>".'No commands to execute.'."\n";else{$Oe=connection()->inTransaction();driver()->rollback();if($Oe)echo"<pre><code class='jush-".JUSH."'>ROLLBACK -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$xb-count($Zc))," <span class='time'>(".format_time($jl).")</span>\n";elseif($Zc&&$xb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$Zc)."\n";}}else
echo"<p class='error'>".upload_error($G)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form"';$Kl="";if(!isset($_GET["import"]))echo
on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"));else
echo
on_upload_progress($Kl);echo'>
';$hd="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$Qi=$_GET["sql"];if($_POST)$Qi=$_POST["query"];elseif($_GET["history"]=="all")$Qi=$_e;elseif($_GET["history"]!="")$Qi=idx($_e[$_GET["history"]],0);echo"<p>";textarea("query",$Qi,20);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$hd\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$ke=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",($Kl?input_hidden(ini_get("session.upload_progress.name"),$Kl):""),"SQL$ke: ".file_input(" name='sql_file[]' multiple","\n$hd"),($Kl?" <progress class='jsonly hidden' max='1' value='0'></progress>":""),"</div></fieldset>\n";$Le=adminer()->importServerPath();if($Le)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Le)."$ke</code>")," <input type='submit' name='webfile' value='".'Run file'."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$_e){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($_e);$X;$X=prev($_e)){$x=key($_e);list($Qi,$Yk,$Oc)=$X;echo'<div><a href="'.h(ME."sql=&history=$x").'" class="hover">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$Yk)."'>".@date("H:i:s",$Yk)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$Sf).*~m",'',$Qi))),80,"</code>").($Oc?" <span class='time'>($Oc)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$Il=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$C=>$m){if((!$Il&&!isset($m["privileges"]["insert"]))||adminer()->fieldName($m)=="")unset($n[$C]);}if($_POST&&!$l&&!isset($_GET["select"])){$Xf=relative_uri((string)$_POST["referer"]);if($_POST["insert"])$Xf=($Il?null:relative_uri());elseif(!preg_match('~^.+&select=.+$~',$Xf))$Xf=ME."select=".url_escape($a);$w=indexes($a);$Cl=unique_array($_GET["where"],$w);$Ti="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Xf,'Item has been deleted.',driver()->delete($a,$Ti,$Cl?0:1));else{$O=array();foreach($n
as$C=>$m){$X=process_input($m);if($X!==false&&$X!==null)$O[idf_escape($C)]=$X;}if($Il){if(!$O)redirect($Xf);queries_redirect($Xf,'Item has been updated.',driver()->update($a,$O,$Ti,$Cl?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$H=driver()->insert($a,$O);$If=($H?last_id($H):0);queries_redirect($Xf,sprintf('Item%s has been inserted.',($If?" $If":"")),$H);}}}$J=null;$G="";$Yk="";if($Z){$M=array();$Ej=array("*");foreach($n
as$C=>$m){if(isset($m["privileges"]["select"])){$Aa=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$d=($Aa?"$Aa AS ":"").idf_escape($C);$M[]=$d;if($Aa)$Ej[]=$d;}}$J=array();if(!support("table")){$M=array("*");$Ej=$M;}if($M){$nk=microtime(true);$H=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));$G=str_replace("SELECT ".implode(", ",$M),"SELECT ".implode(", ",$Ej),driver()->query);$Yk=format_time($nk);if(!$H)$l=adminer()->error();else{$J=$H->fetch_assoc();if(!$J)$J=false;}if(isset($_GET["select"])&&(!$J||$H->fetch_assoc()))$J=null;}}if(!$n&&driver()->primary!=""){if(!$Z){$H=driver()->select($a,array("*"),array(),array("*"));$J=($H?$H->fetch_assoc():false);if(!$J)$J=array(driver()->primary=>"");}if($J){foreach($J
as$x=>$X){if(!$Z)$J[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}if($_POST["save"]){$_i=array();foreach((array)$_POST["fields"]as$x=>$X)$_i[bracket_escape($x,true)]=$X;$J=$_i+($J?$J:array());}edit_form($a,$n,$J,$Il,$l,$G,$Yk);}elseif(isset($_GET["create"])){function
referencable_primary($Gj){$I=array();foreach(table_status('',true)as$Dk=>$R){if($Dk!=$Gj&&fk_support($R)){foreach(fields($Dk)as$m){if($m["primary"]){if($I[$Dk]){unset($I[$Dk]);break;}$I[$Dk]=$m;}}}}return$I;}$a=$_GET["create"];$fi=driver()->partitionBy;$ji=($fi&&$a!=""?driver()->partitionsInfo($a):array());$aj=referencable_primary($a);$Pd=array();foreach($aj
as$Dk=>$m)$Pd[str_replace("`","``",$Dk)."`".str_replace("`","``",$m["field"])]=$Dk;$Mh=array();$S=array();if($a!=""){$Mh=fields($a);$S=table_status1($a);if(count($S)<2)$l='No tables.';}$J=$_POST;$J["fields"]=(array)$J["fields"];if($J["auto_increment_col"])$J["fields"][$J["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$l)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($J["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$ua=array();$Ol=false;$Nd=array();$Lh=reset($Mh);$sa=" FIRST";foreach($J["fields"]as$x=>$m){$p=$Pd[$m["type"]];$xl=($p!==null?$aj[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$Oi=process_field($m,$xl);$ua[]=array($m["orig"],$Oi,$sa);if(!$Lh||$Oi!==process_field($Lh,$Lh)){$n[]=array($m["orig"],$Oi,$sa);if($m["orig"]!=""||$sa)$Ol=true;}if($p!==null)$Nd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$Pd[$m["type"]],'source'=>array($m["field"]),'target'=>array($xl["field"]),'on_delete'=>$m["on_delete"],));$sa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$Ol=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$Lh=next($Mh);if(!$Lh)$sa="";}}$hi=array();if(in_array($J["partition_by"],$fi)){foreach($J
as$x=>$X){if(preg_match('~^partition~',$x))$hi[$x]=$X;}foreach($hi["partition_names"]as$x=>$C){if($C==""){unset($hi["partition_names"][$x]);unset($hi["partition_values"][$x]);}}$hi["partition_names"]=array_values($hi["partition_names"]);$hi["partition_values"]=array_values($hi["partition_values"]);if($hi==$ji)$hi=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$hi=null;$B='Table has been altered.';if($a==""){cookie("adminer_engine",$J["Engine"]);$B='Table has been created.';}$C=trim($J["name"]);$Xf=ME.(support("table")?"table=":"select=").url_escape($C);$H=alter_table($a,$C,(JUSH=="sqlite"&&($Ol||$Nd)?$ua:$n),$Nd,($J["Comment"]!=$S["Comment"]?$J["Comment"]:null),($J["Engine"]&&$J["Engine"]!=$S["Engine"]?$J["Engine"]:""),($J["Collation"]&&$J["Collation"]!=$S["Collation"]?$J["Collation"]:""),($J["Auto_increment"]!=""?number($J["Auto_increment"]):""),$hi);if($H&&!Queries::$queries&&$a!=""&&!$n&&!$Nd)redirect($Xf);queries_redirect($Xf,$B,$H);}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a));if(!$_POST){$zl=driver()->types();$J=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($zl["int"])?"int":(isset($zl["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$J=$S;$J["name"]=$a;$J["fields"]=array();if(!$_GET["auto_increment"])$J["Auto_increment"]="";foreach($Mh
as$m){if($m["generated"])$m["default"]=ltrim($m["default"]);$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$J["fields"][]=$m;}if($fi){$J+=$ji;$J["partition_names"][]="";$J["partition_values"][]="";}}}$tb=flat_collations();$Uc=driver()->engines();foreach($Uc
as$Tc){if(!strcasecmp($Tc,$J["Engine"])){$J["Engine"]=$Tc;break;}}$gg=max_input_vars(12,20);if($gg){$ze=(count($J["fields"])>$gg?"":" hidden");echo"<p".($ze?" id='max-fields' data-columns='$gg'":"")." class='error$ze'>".max_input_vars_error()."\n";}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($J["name"])."' autocapitalize='off'>\n",($Uc?html_select("Engine",array(""=>"(".'engine'.")")+$Uc,$J["Engine"],on('change','helpClose').on_help_value())."\n":"");if($tb)echo"<datalist id='collations'>".optionlist($tb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($J["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($J["fields"],$tb,"TABLE",$Pd);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($J["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',on('click','columnShowClick',5),"jsonly");$_b=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$_b,'Comment',on('click','editingCommentsClick',true),"jsonly").' ';$c=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($_b?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$c,$J["Comment"]);}echo'<p>
<input type=\'submit\' value=\'Save\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';if($fi&&(JUSH=='sql'||$a=="")){$gi=preg_match('~RANGE|LIST~',$J["partition_by"]);print_fieldset("partition",'Partition by',$J["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$fi),$J["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($J["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($gi||!$J["partition_by"]?" hidden":"")."' value='".h($J["partitions"])."'>\n","<table id='partition-table'".($gi?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."<tbody>\n";foreach($J["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off"'.($x==count($J["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($J["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$Ue=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$Re=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$Ue[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$Ue[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$S["Engine"]))$Ue[]="VECTOR";$w=indexes($a);$n=fields($a);$Hi=array();if(JUSH=="mongo"){$Hi=$w["_id_"];unset($Ue[0]);unset($w["_id_"]);}$J=$_POST;if($J)save_settings(array("index_options"=>$J["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($J["indexes"]as$v){$C=$v["name"];if(in_array($v["type"],$Ue)){$e=array();$Pf=array();$qc=array();$zh=array();$Se=(support("partial_indexes")?$v["partial"]:"");$Qe=(in_array($v["algorithm"],$Re)?$v["algorithm"]:"");$O=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$oc=idx($v["descs"],$x);$yh=idx($v["opclasses"],$x);$O[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($yh!=""?" ".idf_escape($yh):"").($oc?" DESC":"");$e[]=$d;$Pf[]=($y?:null);$qc[]=$oc;$zh[]="$yh";}}$id=$w[$C];if($id){ksort($id["columns"]);ksort($id["lengths"]);ksort($id["descs"]);if($v["type"]==$id["type"]&&array_values($id["columns"])===$e&&(!$id["lengths"]||array_values($id["lengths"])===$Pf)&&array_values($id["descs"])===$qc&&(!$id["opclasses"]||array_values($id["opclasses"])===$zh)&&$id["partial"]==$Se&&(!$Re||$id["algorithm"]==$Qe)){unset($w[$C]);continue;}}if($e)$b[]=array($v["type"],$C,$O,$Qe,$Se);}}foreach($w
as$C=>$id)$b[]=array($id["type"],$C,"DROP");if(!$b)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$zd=array_keys($n);if($_POST["add"]){foreach($J["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$J["indexes"][$x]["columns"][]="";}$v=end($J["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$J["indexes"][]=array("columns"=>array(1=>""));}if(!$J){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$J["indexes"]=$w;}$Pf=(JUSH=="sql"||JUSH=="mssql");$zh=driver()->indexOpclasses();$Uj=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">Index Type
';$Je=" class='idxopts".($Uj?"":" hidden")."'";if($Re)echo"<th id='label-algorithm'$Je>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" hidden>','Columns'.($Pf?"<span$Je> (".'length'.")</span>":"");if($Pf||support("descidx"))echo
checkbox("options",1,$Uj,'Options',on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Je>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
<tbody>
';if($Hi){echo"<tr><td>PRIMARY<td>";foreach($Hi["columns"]as$x=>$d)echo
select_input(" disabled",array_combine($zd,$zd),$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$uf=1;foreach($J["indexes"]as$v){if(!$_POST["drop_col"]||$uf!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$uf][type]",array(-1=>"")+$Ue,$v["type"],($uf==count($J["indexes"])?on('change','indexesAddRow'):""),"label-type");if($Re)echo"<td$Je>".html_select("indexes[$uf][algorithm]",array_merge(array(""),$Re),$v['algorithm'],"","label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$uf][columns][$s]' title='".'Column'."'".on('change','indexesChangeColumn',(JUSH=="sql"?"":$_GET["indexes"]."_")),($n&&($d==""||$n[$d])?array_combine($zd,$zd):array()),$d)," <span$Je>",($Pf?"<input type='number' name='indexes[$uf][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":"");if($zh){$yh=idx($v["opclasses"],$x);echo
html_select("indexes[$uf][opclasses][$s]",array(""=>"(".'operator class'.")")+array_combine($zh,$zh)+($yh!=""?array($yh=>$yh):array()),$yh),doc_link(array('pgsql'=>'indexes-opclass.html'));}echo(support("descidx")?checkbox("indexes[$uf][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"<br>","</span></span>";$s++;}echo"<td><input name='indexes[$uf][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Je><input name='indexes[$uf][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$uf]","x",'Remove',on('click','editingRemoveRow','indexes$1[type]'));}$uf++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'Save\'>
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$J=$_POST;if($_POST&&!$l&&!$_POST["add"]){$C=trim($J["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif($C!==DB){if(DB!=""){$_GET["db"]=$C;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".url_escape($C),'Database has been renamed.',rename_database($C,(string)$J["collation"]));}else{$i=explode("\n",str_replace("\r","",$C));$tk=true;$Gf="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,(string)$J["collation"]))$tk=false;$Gf=$j;}}restart_session();set_session("dbs",null);queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($Gf),'Database has been created.',$tk);}}else{if(!$J["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($C).(preg_match('~^[a-z0-9_]+$~i',$J["collation"])?" COLLATE $J[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$tb=collations();$C=DB;if($_POST)$C=$J["name"];elseif(DB!="")$J["collation"]=db_collation(DB,$tb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$be){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$be,$A)&&$A[1]){$C=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($C,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($C).'</textarea><br>':'<input name="name" autofocus value="'.h($C).'" data-maxlength="64" autocapitalize="off">')."\n",($tb?html_select("collation",array(""=>"(".'collation'.")")+$tb,$J["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):"")."\n",'<input type=\'submit\' value=\'Save\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$J=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$C=trim($J["name"]);$_
.=url_escape($C);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($C),$_,'Schema has been created.');elseif($_GET["ns"]!=$C)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($C),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$J)$J["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($J["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'Save\'>
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$_GET["ns"])).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$l);$tj=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$rj=routine($_GET["call"],$tj);$Me=array();$Sh=array();foreach($rj["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$Sh[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Me[]=$s;}if(!$l&&$_POST){$ab=array();foreach($rj["fields"]as$x=>$m){$X="";if(in_array($x,$Me)){$X=process_input($m);if($X===false)$X="''";if(isset($Sh[$x]))connection()->query("SET @".idf_escape($m["field"])." = $X");}if(isset($Sh[$x]))$ab[]="@".idf_escape($m["field"]);elseif(in_array($x,$Me))$ab[]=$X;}$G=(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($rj["returns"],"type")=="record"?"* FROM ":"").table($ba)."(".implode(", ",$ab).")";$nk=microtime(true);$H=connection()->multi_query($G);$qa=connection()->affected_rows;echo
adminer()->selectQuery($G,$nk,!$H);if(!$H)echo"<p class='error'>".adminer()->error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$H=connection()->store_result();if(is_object($H))print_select_result($H,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$qa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($Sh)print_select_result(connection()->query("SELECT ".implode(", ",$Sh)));}}echo'
<form action="" method="post">
';if($Me){echo"<table class='layout'>\n";foreach($Me
as$x){$m=$rj["fields"][$x];$C=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$Y=idx($_POST["fields"],$C);if($Y!=""){if($m["type"]=="set")$Y=implode(",",$Y);}input($m,$Y,idx($_POST["function"],$C,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'Call\'>
',input_token(),'</form>

',adminer()->commentValue($tj,$rj['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$C=$_GET["name"];$J=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$J["source"]=array_filter($J["source"],'strlen');ksort($J["source"]);$Pk=array();foreach($J["source"]as$x=>$X)$Pk[$x]=$J["target"][$x];$J["target"]=$Pk;}if(JUSH=="sqlite")$H=recreate_table($a,$a,array(),array(),array(" $C"=>($J["drop"]?"":" ".format_foreign_key($J))));else{$b="ALTER TABLE ".table($a);$H=($C==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($C)));if(!$J["drop"])$H=queries("$b ADD".format_foreign_key($J));}queries_redirect(ME."table=".url_escape($a),($J["drop"]?'Foreign key has been dropped.':($C!=""?'Foreign key has been altered.':'Foreign key has been created.')),$H);if(!$J["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and the referenced data must exist.';}page_header(($C!=""?'Alter foreign key':'Create foreign key'),$l,array("table"=>$a),h($C!=""?$C:$a));if($_POST){ksort($J["source"]);if($_POST["change"]||$_POST["change-js"])$J["target"]=array();else$J["source"][]="";}elseif($C!=""){$Pd=foreign_keys($a);$J=$Pd[$C];$J["source"][]="";}else{$J["table"]=$a;$J["source"]=array("");}echo'
<form action="" method="post">
';$ck=array_keys(fields($a));if($J["db"]!="")connection()->select_db($J["db"]);if($J["ns"]!=""){$Nh=get_schema();set_schema($J["ns"]);}$Zi=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$Pk=array_keys(fields(in_array($J["table"],$Zi)?$J["table"]:reset($Zi)));$c=on('change','foreignChange');echo"<p><label>".'Target table'.": ".html_select("table",$Zi,$J["table"],$c)."</label>\n";if(support("scheme")){$yj=array_filter(adminer()->schemas(),function($L){return!information_schema(DB,$L);});echo"<label>".'Schema'.": ".html_select("ns",$yj,$J["ns"]!=""?$J["ns"]:$_GET["ns"],$c)."</label>";if($J["ns"]!="")set_schema($Nh);}elseif(JUSH!="sqlite"){$gc=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$gc[]=$j;}echo"<label>".'DB'.": ".html_select("db",$gc,$J["db"]!=""?$J["db"]:$_GET["db"],$c)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'Change\'></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target<tbody>
';$uf=0;foreach($J["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$ck,$X,($uf==count($J["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Pk,idx($J["target"],$x),"","label-target");$uf++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$J["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$J["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",array('NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'),$J["deferrable"]).' ':''),doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",)),'<p>
<input type=\'submit\' value=\'Save\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'Add column\'></noscript>
';if($C!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$C)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$J=$_POST;$Oh="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$Oh=strtoupper($P["Engine"]);}if($_POST&&!$l){$C=trim($J["name"]);$Aa=" AS\n$J[select]";$Xf=ME."table=".url_escape($C);$B='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$C&&JUSH!="sqlite"&&$U=="VIEW"&&$Oh=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($C).$Aa,$Xf,$B);else{$Tk="adminer_".uniqid();drop_create("DROP $Oh ".table($a),"CREATE $U ".table($C).$Aa,"DROP $U ".table($C),"CREATE $U ".table($Tk).$Aa,"DROP $U ".table($Tk),($_POST["drop"]?substr(ME,0,-1):$Xf),'View has been dropped.',$B,'View has been created.',$a,$C);}}if(!$_POST&&$a!=""){$J=view($a);$J["name"]=$a;$J["materialized"]=($Oh!="VIEW");if(!$l)$l=adminer()->error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$J["materialized"],'Materialized view'):""),'<p>';textarea("select",$J["select"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$if=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$pk=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$J=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($J["INTERVAL_FIELD"],$if)&&isset($pk[$J["STATUS"]])){$xj="\nON SCHEDULE ".($J["INTERVAL_VALUE"]?"EVERY ".q($J["INTERVAL_VALUE"])." $J[INTERVAL_FIELD]".($J["STARTS"]?" STARTS ".q($J["STARTS"]):"").($J["ENDS"]?" ENDS ".q($J["ENDS"]):""):"AT ".q($J["STARTS"]))." ON COMPLETION".($J["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$xj.($aa!=$J["EVENT_NAME"]?"\nRENAME TO ".idf_escape($J["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($J["EVENT_NAME"]).$xj)."\n".$pk[$J["STATUS"]]." COMMENT ".q($J["EVENT_COMMENT"]).rtrim(" DO\n$J[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l);if(!$J&&$aa!=""){$K=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$J=reset($K);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($J["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$J[EXECUTE_AT]$J[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($J["ENDS"]),'">
<tr><th>Every
<td><input type="number" name="INTERVAL_VALUE" value="',h($J["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$if,$J["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$pk,$J["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($J["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$J["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$J["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$rj=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$J=$_POST;$J["fields"]=(array)$J["fields"];if($_POST&&!process_fields($J["fields"])&&!$l){foreach($J["fields"]as$x=>$m){if($m["field"]=="")unset($J["fields"][$x]);}$qh=routine_id($ba,routine($_GET["procedure"],$rj));$Ug=routine_id($J["name"],$J);$h=create_routine($rj,$J);$Xf=substr(ME,0,-1);$B='Routine has been altered.';if(!$_POST["drop"]&&$qh==$Ug&&connection()->flavor!="mysql")query_redirect(substr_replace($h,' OR REPLACE',6,0),$Xf,$B);else{$Tk="adminer_".uniqid();drop_create("DROP $rj $qh",$h,"DROP $rj $Ug",create_routine($rj,array("name"=>$Tk)+$J),"DROP $rj ".routine_id($Tk,$J),$Xf,'Routine has been dropped.',$B,'Routine has been created.',$ba,$J["name"]);}}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l);if(!$_POST){if($ba=="")$J["language"]="sql";else{$J=routine($_GET["procedure"],$rj);$J["name"]=$ba;}}$tb=(JUSH=="sql"?flat_collations():array());$sj=routine_languages();echo($tb?"<datalist id='collations'>".optionlist($tb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',($sj?"<label>".'Language'.": ".html_select("language",$sj,$J["language"])."</label>\n":""),'<input type=\'submit\' value=\'Save\'>
<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($J["fields"],$tb,$rj);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$J["returns"],$tb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$J["definition"],20);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($ba!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$ba)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$J=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$C=trim($J["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($C),$_,'Sequence has been created.');elseif($da!=$C)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($C),$_,'Sequence has been altered.');else
redirect($_);}page_header($da!=""?'Alter sequence'.": ".h($da):'Create sequence',$l);if(!$J)$J["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($J["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'Save\'>
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$da)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){function
enum_values($lc){$Y="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$Y.'(?:\s*,\s*'.$Y.')*)\s*\)$~i',$lc,$A))return
null;preg_match_all('~'.$Y.'~',$A[1],$dg);return$dg[0];}function
add_enum_values($U,$oh,$Sg){$th=enum_values($oh);$Yg=enum_values($Sg);if($th===null||$Yg===null)return
null;$I=array();$s=0;foreach($Yg
as$Y){if($Y===idx($th,$s))$s++;else$I[]="ALTER TYPE ".idf_escape($U)." ADD VALUE $Y".($s<count($th)?" BEFORE ".$th[$s]:"");}return($s==count($th)?$I:null);}$ea=$_GET["type"];$J=$_POST;$U=($ea!=""?type_definition(+array_search($ea,types(true))):array());$gh=($U["kind"]=='d'?"DOMAIN":"TYPE");if($_POST&&!$l){$_=substr(ME,0,-1);$C=trim($J["name"]);$Aa=trim(str_replace("\r","",$J["as"]));$Wg=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Aa)?"DOMAIN":"TYPE");$B='Type has been altered.';$b=(!$_POST["drop"]&&$ea!=""&&$Wg==$gh?($Aa==$U["definition"]?array():add_enum_values($ea,$U["definition"],$Aa)):null);if($b!==null){if($ea!=$C)$b[]="ALTER $gh ".idf_escape($ea)." RENAME TO ".idf_escape($C);if(!$b)redirect($_);$sd=false;foreach($b
as$G){if(!queries($G)){$sd=true;break;}}queries_redirect($_,$B,!$sd);}else
drop_create("DROP $gh ".idf_escape($ea),"CREATE $Wg ".idf_escape($C)." $Aa","","","",$_,'Type has been dropped.',$B,'Type has been created.',$ea,$C);}page_header($ea!=""?'Alter type'.": ".h($ea):'Create type',$l);if(!$J){$J["name"]=$ea;$J["as"]=($ea!=""?$U["definition"]:"AS ");}echo'
<form action="" method="post">
<p>
','Name'.": <input name='name' value='".h($J['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"sql-createtype.html",),"?");textarea("as",$J["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";if($ea!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$ea)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$C=$_GET["name"];$J=$_POST;if($J&&!$l){if(JUSH=="sqlite")$H=recreate_table($a,$a,array(),array(),array(),"",array(),"$C",($J["drop"]?"":$J["clause"]));else{$H=($C==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($C)));if(!$J["drop"])$H=queries("ALTER TABLE ".table($a)." ADD".($J["name"]!=""?" CONSTRAINT ".idf_escape($J["name"]):"")." CHECK ($J[clause])");}queries_redirect(ME."table=".url_escape($a),($J["drop"]?'Check has been dropped.':($C!=""?'Check has been altered.':'Check has been created.')),$H);}page_header(($C!=""?'Alter check':'Create check'),$l,array("table"=>$a),h($C!=""?$C:$a));if(!$J){$jb=driver()->checkConstraints($a);$J=array("name"=>$C,"clause"=>$jb[$C]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($J["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$J["clause"]);echo'<p><input type=\'submit\' value=\'Save\'>
';if($C!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$C)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$C="$_GET[name]";$tl=trigger_options();$J=(array)trigger($C,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$tl["Timing"])&&in_array($_POST["Event"],$tl["Event"])&&in_array($_POST["Type"],$tl["Type"])){$uh=" ON ".table($a);$Fc="DROP TRIGGER ".idf_escape($C).(JUSH=="pgsql"?$uh:"");$Xf=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($Fc,$Xf,'Trigger has been dropped.');else{if($C!="")queries($Fc);queries_redirect($Xf,($C!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($uh,$_POST)));if($C!="")queries(create_trigger($uh,$J+array("Type"=>reset($tl["Type"]))));}}$J=$_POST;}page_header(($C!=""?'Alter trigger':'Create trigger'),$l,array("table"=>$a),h($C!=""?$C:$a));$rl=on('change','triggerChange',"^".preg_quote($a,"/")."_[ba][iud]$",$a);echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time
<td>',html_select("Timing",$tl["Timing"],$J["Timing"],$rl),'<tr><th>Event<td>',html_select("Event",$tl["Event"],$J["Event"],$rl),(in_array("UPDATE OF",$tl["Event"])?" <input name='Of' value='".h($J["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$tl["Type"],$J["Type"]),'<tr><th>Name<td><input name="Trigger" value="',h($J["Trigger"]),'" data-maxlength="64" autocapitalize="off">
</table>
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$J["Statement"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($C!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$C)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){function
grant($be,array$Mi,$e,$uh){if(!$Mi)return
true;if($Mi==array("ALL PRIVILEGES","GRANT OPTION"))return($be=="GRANT"?queries("$be ALL PRIVILEGES$uh WITH GRANT OPTION"):queries("$be ALL PRIVILEGES$uh")&&queries("$be GRANT OPTION$uh"));return
queries("$be ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$Mi).$e).$uh);}$fa=$_GET["user"];$Mi=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$J){foreach(explode(",",($J["Privilege"]=="Grant option"?"":$J["Context"]))as$Lb)$Mi[$Lb=="File access on server"?"Server Admin":$Lb][$J["Privilege"]]=$J["Comment"];}unset($Mi["Server Admin"]["Usage"]);foreach($Mi["Tables"]as$x=>$X)unset($Mi["Databases"][$x]);$Tg=array();if($_POST){foreach($_POST["objects"]as$x=>$X)$Tg[$X]=(array)$Tg[$X]+idx($_POST["grants"],$x,array());}$ce=array();if(isset($_GET["host"])&&($H=connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])))){while($J=$H->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$J[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$dg,PREG_SET_ORDER)){foreach($dg
as$X){if($X[1]!="USAGE")$ce["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$J[0]))$ce["$A[2]$X[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$l){$sh=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $sh",ME."privileges=",'User has been dropped.');else{$Xg=q($_POST["user"])."@".q($_POST["host"]);$ki=$_POST["pass"];$Sb=false;$H=true;if($sh!=$Xg){$Sb=queries("CREATE USER $Xg IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($ki));$H=$Sb;}elseif($ki!="")$H=queries("SET PASSWORD FOR $Xg = ".(min_version(8,99)||$_POST["hashed"]?q($ki):"PASSWORD(".q($ki).")"));if($H){$nj=array();foreach($Tg
as$gh=>$be){if(isset($_GET["grant"]))$be=array_filter($be);$be=array_keys($be);if(isset($_GET["grant"]))$nj=array_diff(array_keys(array_filter($Tg[$gh],'strlen')),$be);elseif($sh==$Xg){$ph=array_keys((array)$ce[$gh]);$nj=array_diff($ph,$be);$be=array_diff($be,$ph);unset($ce[$gh]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$gh,$A)&&(!grant("REVOKE",$nj,$A[2]," ON $A[1] FROM $Xg")||!grant("GRANT",$be,$A[2]," ON $A[1] TO $Xg"))){$H=false;break;}}}if($H&&isset($_GET["host"])){if($sh!=$Xg)queries("DROP USER $sh");elseif(!isset($_GET["grant"])){foreach($ce
as$gh=>$nj){if(preg_match('~^(.+)(\(.*\))?$~U',$gh,$A))grant("REVOKE",array_keys($nj),$A[2]," ON $A[1] FROM $Xg");}}}if($H&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),$H);if($Sb)connection()->query("DROP USER $Xg");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')));$J=$_POST;if($J)$ce=$Tg;else{$J=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$ce[(DB==""||$ce?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($J["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($J["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($J["pass"]),'" autocomplete="new-password">
',($J["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$J["hashed"],'Hashed',on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($ce
as$gh=>$be){echo'<th>'.($gh!="*.*"?"<input name='objects[$s]' value='".h($gh)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"<tbody>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Procedures"=>'Routine',)as$Lb=>$oc){foreach((array)$Mi[$Lb]as$Li=>$yb){echo"<tr><td".($oc?">$oc<td":" colspan='2'").' lang="en" title="'.h($yb).'">'.h($Li);$s=0;foreach($ce
as$gh=>$be){$C="'grants[$s][".h(strtoupper($Li))."]'";$Y=$be[strtoupper($Li)];if($Lb=="Server Admin"&&$gh!=(isset($ce["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$C><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$C value='1'".($Y?" checked":"").($Li=="All privileges"?" id='grants-$s-all'":($Li=="Grant option"?"":on('click','grantsClick',"grants-$s-all"))).">","</label>";$s++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'Save\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',"$fa@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$Af=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$Af++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$Af),$Af||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$s=-1;foreach(adminer()->processList()as$s=>$J){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($J
as$x=>$X)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",));echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$J[JUSH=="sql"?"Id":"pid"],0):"");foreach($J
as$x=>$X)echo"<td>".($X!=""&&((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$J["Command"]))||(JUSH=="pgsql"&&$x=="query")||(JUSH=="oracle"&&$x=="sql_text"))?"<code class='jush-".JUSH."' data-full='".h($X)."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(($J["db"]!=""?preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($J["db"])."&":ME)."sql=".url_escape($X)).'">'.'Clone'.'</a>'.' '.copy_icon():h($X));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif($_GET["select"]!=""){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$n=fields($a);$Pd=column_foreign_keys($a);$nh=$S["Oid"];$pa=get_settings("adminer_import");$pj=array();$e=array();$Bj=array();$Gh=array();$Wk=null;foreach($n
as$x=>$m){$C=adminer()->fieldName($m);$Og=html_entity_decode(strip_tags($C),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$C!=""){$e[$x]=$Og;if(is_shortable($m))$Wk=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$C!="")$Bj[$x]=$Og;if(isset($m["privileges"]["order"])&&$C!="")$Gh[$x]=$Og;$pj+=$m["privileges"];}list($M,$ee)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$ee=array_unique($ee);$of=count($ee)<count($M);$Z=adminer()->selectSearchProcess($n,$w);$Fh=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Dl=>$J){$Aa=convert_field($n[key($J)]);$M=array($Aa?:idf_escape(key($J)));$Z[]=where_check(bracket_escape($Dl,true),$n);$I=driver()->select($a,$M,$Z,$M);if($I)echo
first($I->fetch_row());}exit;}$Hi=$Fl=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$Hi=array_flip($v["columns"]);$Fl=($M?$Hi:array());foreach($Fl
as$x=>$X){if(in_array(idf_escape($x),$M))unset($Fl[$x]);}break;}}if($nh&&!$Hi){$Hi=$Fl=array($nh=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($nh));}if($_POST&&!$l){$nm=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$jb=array();foreach($_POST["check"]as$fb)$jb[]=where_check($fb,$n);$nm[]="((".implode(") OR (",$jb)."))";}$pm=$nm;$nm=($nm?"\nWHERE ".implode(" AND ",$nm):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$Dj=($M?:array("*"));$Nb=convert_fields($e,$n,$M);if($Nb)$Dj[]=substr($Nb,2);$G="";if(is_array($_POST["check"])&&!$Hi){$Ud=implode(", ",$Dj)."\nFROM ".table($a);$ge=($ee&&$of?"\nGROUP BY ".implode(", ",$ee):"").($Fh?"\nORDER BY ".implode(", ",$Fh):"");$Bl=array();foreach($_POST["check"]as$X)$Bl[]="(SELECT".limit($Ud,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n).$ge,1).")";$G=implode(" UNION ALL ",$Bl);}adminer()->dumpData($a,"table",$G,$Dj,$pm,($of?$ee:array()),$Fh);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$Pd)){if($_POST["save"]||$_POST["delete"]){$H=true;$qa=0;$Pa=false;$O=array();if(!$_POST["delete"]){foreach($n
as$C=>$X){$u=bracket_escape($C);if(isset($_POST["fields"][$u])||$_FILES["fields-$u"]){$X=process_input($n[$C]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($C)]=($X!==false?$X:idf_escape($C));}}}if($_POST["delete"]||$O){$G=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($Hi&&is_array($_POST["check"]))||$of){$H=($_POST["delete"]?driver()->delete($a,$nm):($_POST["clone"]?queries("INSERT $G$nm".driver()->insertReturning($a)):driver()->update($a,$O,$nm)));$qa=connection()->affected_rows;if(is_object($H))$qa+=$H->num_rows;}else{$Pa=count((array)$_POST["check"])>1&&driver()->begin();foreach((array)$_POST["check"]as$X){$mm="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n);$H=($_POST["delete"]?driver()->delete($a,$mm,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$mm)):driver()->update($a,$O,$mm,1)));if(!$H)break;$qa+=connection()->affected_rows;}if($Pa&&$H&&!driver()->commit())$H=false;}}$B=lang_format(array('%d item has been affected.','%d items have been affected.'),$qa);if($_POST["clone"]&&$H&&$qa==1){$If=last_id($H);if($If)$B=sprintf('Item%s has been inserted.'," $If");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$B,$H);if($Pa)driver()->rollback();if(!$_POST["delete"]){$_i=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$_i),$_i,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){$H=true;$qa=0;$Pa=count((array)$_POST["val"])>1&&driver()->begin();foreach((array)$_POST["val"]as$Dl=>$J){$O=array();foreach($J
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$X!=""?adminer()->processInput($n[$x],$X):"NULL");}$H=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($Dl,true),$n),($of||$Hi?0:1)," ");if(!$H)break;$qa+=connection()->affected_rows;}if($Pa)$H=$H&&driver()->commit();queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$qa),$H);if($Pa)driver()->rollback();}elseif(!is_string($_d=get_file("csv_file",true)))$l=upload_error($_d);elseif(!preg_match('~~u',$_d))$l='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$pa["output"],"format"=>$_POST["separator"]),"adminer_import");$ub=array_keys($n);$Ij=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$Wb=parse_csv($_d,$Ij);$qa=count($Wb);driver()->begin();$K=array();foreach($Wb
as$x=>$bm){if(!$x&&!array_diff($bm,$ub)){$ub=$bm;$qa--;}else{$O=array();foreach($bm
as$s=>$qb)$O[idf_escape($ub[$s])]=($qb==""&&$n[$ub[$s]]["null"]?"NULL":q(csv_value($qb)));$K[]=$O;}}$H=(!$K||driver()->insertUpdate($a,$K,$Hi));if($H)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$qa),$H);driver()->rollback();}}}$Dk=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $Dk",$l);$O=null;if(isset($pj["insert"])||!support("table")){$O="";foreach((array)$_GET["where"]as$X){$Y=$X["val"];if(is_array($Y))$Y=(count($Y)==1&&preg_match('~^val-(.*)~s',reset($Y),$A)?$A[1]:"");if($X["col"]!=""&&$Y!=""&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$Y)))))$O
.="&set[".url_escape(bracket_escape($X["col"]))."]=".url_escape($Y);}}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($n?".":": ".adminer()->error())."\n";else{echo"<form action='' id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$Bj,$w);adminer()->selectOrderPrint($Fh,$Gh,$w);adminer()->selectLimitPrint($z);if($Wk!==null)adminer()->selectLengthPrint($Wk);adminer()->selectActionPrint($w);echo"</form>\n";foreach((array)$_GET["where"]as$X){if($X["op"]=="SQL"&&!in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"))){echo"<p class='error'>".'Invalid CSRF token. Submit the form again.'.' '.'If you did not send this request from Adminer, close this page.'."\n";page_footer();exit;}}$D=$_GET["page"];$Sd=null;if($D=="last"){$Sd=get_val(count_rows($a,$Z,$of,$ee));$D=floor(max(0,intval($Sd)-1)/$z);}$Cj=$M;$fe=$ee;if(!$Cj){$Cj[]="*";$Nb=convert_fields($e,$n,$M);if($Nb)$Cj[]=substr($Nb,2);}foreach($M
as$x=>$X){$m=$n[idf_unescape($X)];if($m&&($Aa=convert_field($m)))$Cj[$x]="$Aa AS $X";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$x=>$X){if(isset($Cj[$x])&&$X["fun"])$Cj[$x].=" AS ".idf_escape(apply_sql_function($X["fun"],($X["col"]!=""?$X["col"]:"*")));}}if(!$of&&$Fl){foreach($Fl
as$x=>$X){$Cj[]=idf_escape($x);if($fe)$fe[]=idf_escape($x);}}$H=driver()->select($a,$Cj,$Z,$fe,$Fh,$z,$D,true);if(!is_object($H))echo"<p class='error'>".(adminer()->error()?:'Unknown error.')."\n";else{if(JUSH=="mssql"&&$D)$H->seek($z*$D);$Rc=array();$K=array();while($J=$H->fetch_assoc()){if($D&&JUSH=="oracle")unset($J["RNUM"]);$K[]=$J;}$qe=($z&&(support("cursor")?$_GET["next"]!="":count($K)>=$z));if(is_ajax()&&$qe)header("X-Next-Page: ".pagination_href($D+1));if($_GET["modify"]&&$K){$mg=max_input_vars(count($K[0])+1,20);echo($mg&&count($K)>$mg?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form action='' method='post' enctype='multipart/form-data'".on_upload_progress($Kl).">\n";if($_GET["page"]!="last"&&$z&&$ee&&$of&&JUSH=="sql")$Sd=get_val(" SELECT FOUND_ROWS()");if(!$K)echo"<p class='message'>".'No rows.'."\n";else{$La=adminer()->backwardKeys($a,$Dk);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$ee&&$M?"":"<td class='hover check'><input type='checkbox' id='all-page' class='jsonly' title='".'All rows on this page'."'".on('click','formCheck','^check').">");$Pg=array();$Yd=array();reset($M);$Wi=1;foreach($K[0]as$x=>$X){if(!isset($Fl[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$C=($m?adminer()->fieldName($m,$Wi):($X["fun"]?"*":h($x)));if($C!=""){$Wi++;$Pg[$x]=$C;$d=idf_escape($x);$De=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($x);$oc="&desc[0]=1";$Zj=preg_replace('~ DESC( NULLS LAST)?$~','',$Fh[0]);$bk=($Zj==$d||$Zj==$x);echo"<th id='th[".h(bracket_escape($x))."]'".($bk?" aria-sort='".($Zj==$Fh[0]?"ascending":"descending")."'":"").">";$Xd=apply_sql_function($X["fun"],$C);$ak=isset($m["privileges"]["order"])||$Xd!=$C;echo($ak?"<a href='".h($De.($bk&&$Zj==$Fh[0]?$oc:''))."'>$Xd</a>":$Xd);$ug=($ak?"<a href='".h($De.$oc)."' title='".'descending'."' class='text'> ↓</a>":'');if(!$X["fun"]&&isset($m["privileges"]["where"]))$ug
.="<a href='#fieldset-search' title='".'Search'."' class='text jsonly'".on('click','selectSearch',$x)."> =</a>";echo($ug?"<span class='column'>$ug</span>":"");}$Yd[$x]=$X["fun"];next($M);}}$Pf=array();if($_GET["modify"]){foreach($K
as$J){foreach($J
as$x=>$X)$Pf[$x]=max($Pf[$x],min(40,strlen(utf8_decode($X))));}}echo($La?"<th>".'Relations':"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($K,$Pd)as$Mg=>$J){$Cl=unique_array($K[$Mg],$w);if(!$Cl){$Cl=array();reset($M);foreach($K[$Mg]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$Cl[$x]=$X;next($M);}}$Dl="";foreach($Cl
as$x=>$X){$m=(array)$n[$x];$nf=is_blob($m);if((JUSH=="sql"||JUSH=="pgsql")&&($nf||preg_match('~'.text_type().'~',$m["type"]))&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".($nf||JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($nf?(string)driver()->value($X,$m):$X);}$Dl
.="&".($X!==null?"where[".url_escape(bracket_escape($x))."]=".url_escape($X===false?"f":$X):"null[]=".url_escape($x));}echo"<tr>".(!$ee&&$M?"":"<td class='hover check'>".($of||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$Dl)."' class='edit'>".'edit'."</a> ").checkbox("check[]",substr($Dl,1),in_array(substr($Dl,1),(array)$_POST["check"])));reset($M);foreach($J
as$x=>$X){if(isset($Pg[$x])){$d=current($M);$m=(array)$n[$x];if($X!=""&&(!isset($Rc[$x])||$Rc[$x]!=""))$Rc[$x]=(is_mail($X)?$Pg[$x]:"");$_="";if(is_blob($m)&&$X!="")$_=ME.'download='.url_escape($a).'&field='.url_escape($x).$Dl;if(!$_&&$X!==null){foreach((array)$Pd[$x]as$p){if(count($Pd[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$ck)$_
.=where_link($s,$p["target"][$s],$K[$Mg][$ck]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.url_escape($p["db"]),ME):ME).'select='.url_escape($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.url_escape($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".url_escape($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Cl))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($Cl
as$xf=>$W)$_
.=where_link($s++,$xf,$W);}$Ee=select_value($X,$_,$m,$Wk);$u=bracket_escape($Dl);$t=h("val[$u][".bracket_escape($x)."]");$Bi=idx(idx($_POST["val"],$u),bracket_escape($x));$Il=idx($m["privileges"],"update");$Nc=!is_array($J[$x])&&!is_blob($m)&&is_utf8($X)&&$K[$Mg][$x]==$X&&!$Yd[$x]&&!$m["generated"]&&$Il;$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$Vk=preg_match('~text|json|lob~',$U);$pf=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($pf&&($X===null||is_numeric(strip_tags($Ee))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$Nc&&$X!==null)||$Bi!==null){$le=h($Bi!==null?$Bi:$X);echo">".($Vk?"<textarea name='$t' cols='30' rows='".(substr_count($X,"\n")+1)."'>$le</textarea>":"<input name='$t' value='$le' size='$Pf[$x]'>");}else{$Zf=strpos($Ee,"<i>…</i>");echo($Il?" data-text='".($Zf?2:($Vk?1:0))."'".($Nc?"":" data-warning='".'Use the edit link to modify this value.'."'"):"").">$Ee";}}next($M);}if($La)echo"<td>";adminer()->backwardKeysPrint($La,$K[$Mg]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($K||$D||$qe){$gd=true;if($_GET["page"]!="last"){if(!$z||(count($K)<$z&&($K||!$D)))$Sd=($D?$D*$z:0)+count($K);elseif(JUSH!="sql"||!$of){$Sd=($of?false:found_rows($S,$Z));if(intval($Sd)<max(1e4,2*($D+1)*$z))$Sd=first(slow_query(count_rows($a,$Z,$of,$ee)));elseif(JUSH=='sql'||JUSH=='pgsql')$gd=false;}}if(!support("cursor"))$qe=(($Sd===false?count($K)+1:$Sd-$D*$z)>$z);$Xh=($z&&($qe||$D));if($Xh)echo($qe?'<p><a href="'.h(pagination_href($D+1)).'" class="loadmore"'.on('click','selectLoadMore','Loading…').'>'.'Load more data'.'</a>':''),"\n";echo"<div class='footer'><div>\n";if($Xh){$kg=($Sd===false?$D+($K?(count($K)>=$z?2:1):0):floor(($Sd-1)/$z));echo"<fieldset><legend>".'Page'."</legend>";if(!support("cursor")){echo
pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($kg,$D+5);$s++)echo
pagination($s,$D);if($kg>0)echo($D+5<$kg?" …":""),($gd&&$Sd!==false?pagination($kg,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$kg'>".'last'."</a>");}else
echo
pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($qe?pagination($D+1,$D)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$wc=($gd?"":"~ ").$Sd;$Df=($Sd!==false?($gd?"":"~ ").lang_format(array('%d row','%d rows'),$Sd):"");echo
checkbox("all",1,0,$Df,on('click','countRows',$wc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".'Ctrl+click on a value to modify it.'."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>Modify</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'Save\'',($_GET["modify"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'Edit\'>
<input type=\'submit\' name=\'clone\' value=\'Clone\'>
<input type=\'submit\' name=\'delete\' value=\'Delete\'',confirm(),'>
</div></fieldset>
';$Qd=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($Qd['sql']);break;}}if($Qd){print_fieldset("export",'Export'." <span id='selected2'></span>");$Th=adminer()->dumpOutput();echo($Th?html_select("output",$Th,$pa["output"])." ":""),html_select("format",$Qd,$pa["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($Rc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".'Import'."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",($Kl?input_hidden(ini_get("session.upload_progress.name"),$Kl):""),file_input(" name='csv_file'"," ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$pa["format"])." <input type='submit' name='import' value='".'Import'."'>".($Kl?" <progress class='jsonly hidden' max='1' value='0'></progress>":"")),"</span>";echo
input_token(),"</form>\n",(!$ee&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$cm=($P?adminer()->showStatus():adminer()->showVariables());if(!$cm)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($cm
as$J){echo"<tr>";$x=array_shift($J);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($J
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$wk=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$C=>$S){json_row("Comment-$C",h($S["Comment"]));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$C",h($S[$x]));foreach(array_keys($wk+array("Auto_increment"=>0,"Rows"=>0))as$x){if(array_key_exists($x,$S))json_row("$x-$C",format_status($S,$x));if($S[$x]!=""&&isset($wk[$x]))$wk[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}}}if(function_exists('Adminer\db_status'))$wk=db_status();foreach($wk
as$x=>$X)json_row("sum-$x",format_number($X));json_row("");}elseif($_GET["script"]=="kill"){if(!$l)connection()->query("KILL ".number($_POST["kill"]));}else{foreach(count_tables(adminer()->databases(false))as$j=>$X){json_row("tables-$j",$X);json_row("size-$j",db_size($j));}json_row("");}exit;}else{if(!isset($_GET["select"])&&support("single_table")){$T=tables_list();if($T)redirect(ME.(support("table")?"table=":"select=").url_escape(key($T)));}$rg=ME.(isset($_GET["select"])?"select=&":"");$Nk=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Nk&&!$l&&!$_POST["search"]){$H=true;$B="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$H=truncate_tables($_POST["tables"]);$B='Tables have been truncated.';}elseif($_POST["move"]){$H=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$B='Tables have been moved.';}elseif($_POST["copy"]){$H=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$B='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$H=drop_views($_POST["views"]);if($H&&$_POST["tables"])$H=drop_tables($_POST["tables"]);$B='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$J)$B
.="<b>".h($R)."</b>: ".h($J["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$H=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$B='Tables have been optimized.';}elseif(!$_POST["tables"])$B='No tables.';elseif($H=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($J=$H->fetch_assoc())$B
.="<b>".h($J["Table"])."</b>: ".h($J["Msg_text"])."<br>";}queries_redirect(relative_uri(),$B,$H);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$Fh=$_GET["order"];$Vd=($Fh||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Mk=($Vd?table_status():tables_list());if(!$Mk)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if(!$l&&$_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr class="wrap">','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.'All'.'"'.on('click','formCheck','^(tables|views)\[').'>','<th'.(!$Fh&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr($rg,0,-1)).'">'.'Table'.'</a>';$e=array("Engine"=>array('Engine'.doc_link(array('sql'=>'storage-engines.html'))));if(collations())$e["Collation"]=array('Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')));if(function_exists('Adminer\alter_table'))$e["Data_length"]=array('Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286')),"create",'Alter table',);if(support("indexes"))$e["Index_length"]=array('Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),"indexes",'Alter indexes',);$e["Data_free"]=array('Data Free'.doc_link(array('sql'=>'show-table-status.html')),"edit",'New item');if(function_exists('Adminer\alter_table'))$e["Auto_increment"]=array('Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),"auto_increment=1&create",'Alter table',);$e["Rows"]=array('Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286')),"select",'Select data',);if(support("comment"))$e["Comment"]=array('Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')));$Ba=array('Engine','Collation','Comment');foreach($e
as$x=>$d)echo"<th".($Fh==$x?" aria-sort='".(in_array($x,$Ba)?"ascending":"descending")."'":"")."><a href='".h($rg)."order=$x'>$d[0]</a>";echo"<tbody>\n";if($Fh){uasort($Mk,function($ia,$Ia)use($Fh,$Ba){$I=($ia[$Fh]<$Ia[$Fh]?-1:($ia[$Fh]>$Ia[$Fh]?1:0));return(in_array($Fh,$Ba)?$I:-$I);});}$T=0;$wk=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach($Mk
as$C=>$P){$fm=($Vd?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$P=($Vd?$P:array('Engine'=>$P));$t=h("Table-".$C);echo'<tr><td class="hover">'.checkbox(($fm?"views[]":"tables[]"),$C,in_array("$C",$Nk,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($C)."' title='".'Show structure'."' id='$t'>".h($C).'</a>':h($C));if($fm&&!preg_match('~materialized~i',$P['Engine'])){$bl='View';echo'<td colspan="'.(count($e)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($C)."' title='".'Alter view'."'>$bl</a>":$bl),"<td align='right'><a href='".h(ME)."select=".url_escape($C)."' title='".'Select data'."'>?</a>";if(support("comment"))echo'<td>'.h($P['Comment']);}else{if($Vd){foreach(array_keys($wk)as$x)$wk[$x]+=($P["Engine"]!="InnoDB"||$x!="Data_free"?idx($P,$x):0);}foreach($e
as$x=>$d){$t=" id='$x-".h($C)."'";echo($d[1]?"<td align='right'><a href='".h(ME."$d[1]=").url_escape($C)."'$t title='$d[2]'>".format_status($P,$x)."</a>":"<td$t>".h(idx($P,$x,'?')));}$T++;}echo"\n";}echo"<tr><td class='hover'><th>".sprintf('%d in total',count($Mk)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),(collations()?"<td>".h(db_collation(DB,collations())):'');if($Vd&&function_exists('Adminer\db_status'))$wk=db_status();foreach($wk
as$x=>$vk)echo($e[$x]?"<td align='right' id='sum-$x'>".($Vd?format_number($vk):""):"");echo"\n","</table>\n",($Vd?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$Xl="<input type='submit' value='".'Vacuum'."'".on_help("VACUUM")."> ";$Bh="<input type='submit' name='optimize' value='".'Optimize'."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$Ji=(JUSH=="sqlite"?$Xl."<input type='submit' name='check' value='".'Check'."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$Xl.$Bh:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'".on_help("ANALYZE TABLE")."> ".$Bh."<input type='submit' name='check' value='".'Check'."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".'Repair'."'".on_help("REPAIR TABLE")."> ":""))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".'Truncate'."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".'Drop'."'".confirm().on_help("DROP TABLE").">":"");echo($Ji?"<div class='footer'><div>\n<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>$Ji\n</div></fieldset>\n":"");$i=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($i)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".'Move to another database'." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$T).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".'Create table'."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".'Routines'."</h3>\n";$uj=routines();if($uj){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td class='hover'><tbody>\n";foreach($uj
as$J){$C=($J["SPECIFIC_NAME"]==$J["ROUTINE_NAME"]?"":"&name=".url_escape($J["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($J["SPECIFIC_NAME"]).$C).'" title="'.'Call'.'">'.h($J["ROUTINE_NAME"]).'</a>','<td>'.h($J["ROUTINE_TYPE"]),'<td>'.h($J["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($J["SPECIFIC_NAME"]).$C).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n","</div>\n";}if(support("sequence")){echo"<div>\n","<h3 id='sequences'>".'Sequences'."</h3>\n";$Mj=get_vals("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." ORDER BY relname");if($Mj){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."<tbody>\n";foreach($Mj
as$X)echo"<tr><th><a href='".h(ME)."sequence=".url_escape($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n","</div>\n";}if(support("type")){echo"<div>\n","<h3 id='user-types'>".'User types'."</h3>\n";$Ul=types();if($Ul){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."<tbody>\n";foreach($Ul
as$X)echo"<tr><th><a href='".h(ME)."type=".url_escape($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."type='>".'Create type'."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".'Events'."</h3>\n";$K=get_rows("SHOW EVENTS");if($K){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td class='hover'><tbody>\n";foreach($K
as$J)echo"<tr>","<th>".h($J["Name"]),"<td>".($J["Execute at"]?'At given time'."<td>".h($J["Execute at"]):'Every'." ".h($J["Interval value"])." ".h($J["Interval field"])."<td>".h($J["Starts"])),"<td>".h($J["Ends"]),'<td class="hover"><a href="'.h(ME).'event='.url_escape($J["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$dd=get_val("SELECT @@event_scheduler");if($dd&&$dd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($dd)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.'Create event'."</a>\n","</div>\n";}}}}page_footer();