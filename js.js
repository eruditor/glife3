function wml(s) { var z="@"; var a=s+z+"eruditor.ru"; document.write("<a href='mailto:"+a+"'>"+a+"</a>"); }

function getCookieVal(offset, cookie) {
  var endstr=cookie.indexOf(";", offset);
  if(endstr==-1) {
    endstr=cookie.length;
  }
  return unescape(cookie.substring(offset, endstr));
}

function getCookie(name) {
  var arg=name + "=";
  var alen=arg.length;
  var c = document.cookie;
  var clen=c.length;
  var i=0;
  while(i<clen) {
    var j=i+alen;
    if(c.substring(i, j)==arg) {
      return getCookieVal(j, c);
    }
    i=c.indexOf(" ", i)+1;
    if(i==0) break;
  }
  return "";
}

function setCookieDays(name, value, days, path, domain, secure) {
  days = parseInt(days);  if(isNaN(days)) days=0;
  var expdt=new Date();  expdt.setDate(expdt.getDate()+days);
  setCookie(name, value,  expdt.toGMTString(), path, domain, secure);
}

function setCookie(name, value, expires, path, domain, secure) {
  if(!domain) {
    var s=location.hostname;
    var ar=s.split(".");
    var arn=ar.length;
    if(arn<2) { domain=""; }
    else if(arn>=3 && ar[arn-1]=="localhost") { domain="."+ar[arn-3]+"."+ar[arn-2]+"."+ar[arn-1]; }
    else { domain="."+ar[arn-2]+"."+ar[arn-1]; }
  }
  var t = name + "=" + escape (value) +
    ((expires) ? "; expires=" + expires : "") +
    ((path) ? "; path=" + path : "; path=/") +
    ((domain) ? "; domain=" + domain : "") +
    ((secure) ? "; secure" : "");
  document.cookie = t;
}
