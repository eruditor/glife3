<?

function MakePage() {
  global $page, $_self;
  
  ob_end_flush();
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  $page->topline="
    <a href='.' class=ttl>ALIFE.ERUDITOR.RU</a>
  ";
  
  $tm = [
    "gallery"     => "Gallery",
    "catalog"     => "Ñatalog",
    "stadium"     => "Stadium",
    "manufacture" => "Manufacture",
  ];
  $s = '';
  foreach($tm as $k=>$v) {
    $s .= "<td><a href='./?view=$k'".($_GET['view']==$k?" class=chsn":"").">$v</a></td>";
  }
  $page->TopMenu .= "
    <table cellspacing=0><tr>$s</tr></table>
  ";
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  $H1 = $title = $url = '';
  foreach($page->bread as $k=>$v) {
    $nm = $v[0];  $ah = $v[1];  $plus = $v[2] ?: '';
    if($ah) $url .= $ah;
    $crumb = $nm;
    if($ah && $k<count($page->bread)-1) $crumb = "<a href='$url'>$crumb</a>";
    if($plus) $crumb .= $plus;
    $H1 .= ($H1?" &rarr; ":"") . $crumb;
    $title = strip_tags($nm) . ($title?" &larr; ":"") . $title;
  }
  $title .= " &larr; Alife";
  $page->title = $title;
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
  if(!$page->viewport) $page->viewport = 450;
  if(!$_ENV->isMobile) $page->viewport = 0;
  if($_GET['hsh'] && substr($_GET['hsh'],0,8)=="viewport") $page->viewport = intval(substr($_GET['hsh'],8)) ?: $page->viewport;
  
  $page->mt .= sprintf("%.3lf", microtime(true)-$_ENV->startT);
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  $rax = "<script type=\"text/javascript\">document.write('<img src=\"//counter.yadro.ru/hit?t14.1;r' + escape(document.referrer) + ((typeof(screen)=='undefined')?'':';s'+screen.width+'*'+screen.height+'*'+(screen.colorDepth?screen.colorDepth:screen.pixelDepth)) + ';u' + escape(document.URL) + ';' + Math.random() + '\" border=0 width=88 height=31 alt=\"\">')</script>";
  $yam = '
  <script type="text/javascript">
    (function (d, w, c) {
      (w[c] = w[c] || []).push(function() {
        try {
          w.yaCounter11332753 = new Ya.Metrika({
            id:11332753,
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
          });
        } catch(e) { }
      });
      var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
      s.type = "text/javascript";
      s.async = true;
      s.src = "https://mc.yandex.ru/metrika/watch.js";
      if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
      } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
  </script>
  <noscript><div><img src="https://mc.yandex.ru/watch/11332753" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
  ';
  
  if(_local==="1" || $page->noindex) {
    $rax = "<img src='/i/rax31.gif' width=88 height=31>";
    $yam = "<!--".htmlSPC($yam)."-->";
  }
  
  if($page->noindex) {
    $page->meta .= "<meta name=\"robots\" content=\"noindex\">";
  }
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  if($page->mathjax) {
    $page->scripts .= '
    <script type="text/x-mathjax-config">
      MathJax.Hub.Config({
        config: ["MMLorHTML.js"],
        jax: ["input/TeX", "input/MathML", "output/HTML-CSS", "output/NativeMML"],
        extensions: ["tex2jax.js", "mml2jax.js"],
        TeX: {
          extensions: ["AMSmath.js","AMSsymbols.js","noErrors.js","noUndefined.js"]
        },
        showMathMenu: false, showMathMenuMSIE: false,
        showProcessingMessages: false,
        messageStyle: "none",
        displayAlign: "left",
        displayIndent: "0em",
        tex2jax: {
          inlineMath: [ ["[m]","[/m]"] ],
          displayMath: [ ["[M]","[/M]"], ["[mm]","[/mm]"] ],
          skipTags: ["script","noscript","style","textarea","input","pre","code","a"],
          ignoreClass: "nomathjax",
          processEnvironments: false,
          processRefs: false,
        },
        "HTML-CSS": {
          styles: {
            ".MathJax_Display": { margin: "1em 0 0 0" },
          },
        },
      });
    </script>
    <script type="text/javascript" src="//cdn.mathjax.org/mathjax/latest/MathJax.js" async></script>
    ';
  }
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  echo "<!DOCTYPE html>
<HTML>
<head>
  <META http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">".($page->viewport?"
  <META name=\"viewport\" content=\"width=$page->viewport\">":"")."
  <title>$page->title</title>
  <META name=\"Keywords\" content=\"$page->kws\">
  <META name=\"Description\" content=\"$page->descr\">
  $page->meta
  <LINK href=\"/style.css?v=40\" rel=stylesheet>
  <script src=\"/js.js?v=2\"></script>".($page->jquery?"
  <script src='//ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js'></script>":"").($page->scripts?"
  $page->scripts":"")."
  ".trim($yam)."
  
  <script>
    function XHRsave3(q) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'gl_save.php');
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if(xhr.status==200) { if(xhr.responseText) alert(xhr.responseText); }
      };
      xhr.send(q);
    }
    function DynamicScriptLoad(url) {
      var script = document.createElement('script');
      script.src = url;
      document.body.appendChild(script);
    }
  </script>
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

</head>
<BODY align=center>
<DIV id=Wrap align=left>
  <DIV id=TopLine>$page->topline</DIV>
  <DIV id=MenuLine>$page->TopMenu</DIV>
  <DIV id=Main align=left>
    " . ($H1 ? "<h1>$H1</h1>" : "") . "
    " . ($page->zabst ? "<div class=zabst>$page->zabst</div>" : "") . "
    <div class=zzt>$page->z</div>
    " . ($page->zpubd ? "<div class=zpubd>$page->zpubd</div>" : "") . "
  </DIV>
  <DIV id=BottomBuffer></DIV>
</DIV>
<DIV id=Bottom>
  <table id=BottomLine><tr>
  <td width=100% align=left>
    eruditor.ru <span title='$page->mt'>&copy;</span> 2019-".date("Y")."
  </td>
  <td style='padding-right:1px;'>$rax</td>
  </tr></table>
</DIV>
</BODY>
</HTML>";
  
}

?>