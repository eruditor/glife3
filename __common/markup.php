<?

class Markup 
{
    
    public static function parse($text, $options = array())
    {
        if (!$text) return '';

        //$locale = setlocale(LC_ALL, 'ru_RU.utf8');

        $s = str_replace(array("\r\n", "&amp;#"), array("\n", "&#"), htmlspecialchars($text, ENT_HTML401 | ENT_NOQUOTES));
        
        // Заменить преформатированные тексты плейсхолдерами
        $source = $s;
        $chunks = self::parsePre($s);
        $schunks = array();
        
        foreach ($chunks as $key => $chunk) {
            if ($chunk[0] == 'plain')       $schunks[$key] = substr($s, $chunk[1], $chunk[2]); 
            if ($chunk[0] == 'inline_code') $schunks[$key] = "`$key`";      // <code>
            if ($chunk[0] == 'pre')         $schunks[$key] = "``$key``";    // <pre>
            if ($chunk[0] == 'code')        $schunks[$key] = "```$key```";  // <pre><code>
            if ($chunk[0] == 'cite')        $schunks[$key] = "````$key````";// <pre><code><small>
        }
        $s = implode('', $schunks);

        // Заменили, теперь распарсить
        $s = self::parseBlock(self::parseInline($s, $options), $options);
        // Подставить вместо плейсхолдеров преформатированный текст обратно
        $s = self::substituteEscaped($s, $source, $chunks, $options);
        $s = str_replace("<br>\n</p>", "<br><br>\n</p>", $s);  // Иначе браузеры не отображают этот <br>
        
        //setlocale(LC_ALL, $locale);
        return $s;
    }

    /**
     * Части текста по типам в формате array(type, start, len)
     * Типы: plain, inline_code, pre, code
     * 
     * @param $s
     * @return array
     */
    public static function parsePre($s)
    {
        $chunks = array();

        $pos = 0;
        $l = strlen($s);
        while ($pos < $l) {
            // Найти `
            $bp = strpos($s, '`', $pos);  // backtick position
            if ($bp === false) {
                break;
            }
            // Если нашли ````
	          if (substr($s, $bp, 4) == '````' && (($epos = strpos($s, '````', $bp+4)) !== false)) {  // $epos - end position
              if ($bp != $pos) {
                  $n = substr($s,$bp-1,1)=="\n" ? 1 : 0;
	                $chunks[] = array('plain', $pos,  $bp-$pos-$n);
	            }
	            $n = substr($s,$bp+4,1)=="\n" ? 1 : 0;
	            $chunks[] = array('cite',  $bp+4+$n, $epos-$bp-4-$n);
	            $pos = $epos+4;
	            if (substr($s, $pos, 1)=="\n") $pos+=1;
	          }
	          // Если нашли ```
	          elseif (substr($s, $bp, 3) == '```' && (($epos = strpos($s, '```', $bp+3)) !== false)) {  // $epos - end position
                if ($bp != $pos) {
                    $n = substr($s,$bp-1,1)=="\n" ? 1 : 0;
                    $chunks[] = array('plain', $pos,  $bp-$pos-$n);
                }
                $n = substr($s,$bp+3,1)=="\n" ? 1 : 0;
                $chunks[] = array('code',  $bp+3+$n, $epos-$bp-3-$n);
                $pos = $epos+3;
                if (substr($s, $pos, 1)=="\n") $pos+=1;
            }
            // Если ``
            elseif (substr($s, $bp, 2) == '``' && ($epos = strpos($s, '``', $bp+2)) !== false) {
                if ($bp != $pos) {
                    $n = substr($s,$bp-1,1)=="\n" ? 1 : 0;
                    $chunks[] = array('plain', $pos,  $bp-$pos-$n);
                }
                $n = substr($s,$bp+2,1)=="\n" ? 1 : 0;
                $chunks[] = array('pre',   $bp+2+$n, $epos-$bp-2-$n);
                $pos = $epos+2;
                if (substr($s, $pos, 1)=="\n") $pos+=1;
            }
            // Иначе это `
            else {
                // Символ перед открывающим ` - не разделительный
                if ($bp!=0 && !self::isSep(substr($s, $bp-1, 1))) {
                    $chunks[] = array('plain', $pos, $bp+1-$pos);
                    $pos = $bp+1;
                    continue;
                }
                $cur = $bp+1;
                $pos_set = false;
                while (($epos = strpos($s, '`', $cur)) !== false) {
                    // Есть перенос строки до закрытия `
                    if (strpos($s, "\n", $cur) !== false && $epos >= strpos($s, "\n", $cur)) {
                        $chunks[] = array('plain', $pos, $epos-$pos);
                        $pos = $epos;
                        $pos_set = true;
                        break;
                    }
                    // После закрывающего ` разделитель
                    if ($epos==$l-1 || self::isSep(substr($s, $epos+1, 1))) {
                        if ($bp != $pos) $chunks[] = array('plain', $pos, $bp-$pos);
                        $chunks[] = array('inline_code', $bp+1, $epos-$bp-1);
                        $pos = $epos+1;
                        $pos_set = true;
                        break;
                    }
                    $cur = $epos+1;
                }
                if (!$pos_set) {
                    if (!$epos) {
                        break;
                    }
                    $chunks[] = array('plain', $pos,  $epos-$pos+1);
                    $pos = $epos+1;
                }
            }
        }
        
        if ($pos < $l) {
            $chunks[] = array('plain', $pos, $l-$pos);
        }
        
        // Склеить идущие подряд куски plain-текста
        $ret = array();
        $cur = array();
        foreach ($chunks as $chunk) {
            if ($cur && $cur[0] == 'plain' && $cur[0] == $chunk[0]) {
                $cur[2] += $chunk[2];
            } else {
                if ($cur) $ret[] = $cur;
                $cur = $chunk;
            }
        }
        $ret[] = $cur;
        
        return $ret;
    }
    
    private static function isSep($char)
    {
        return preg_match('`\W`', $char);
    }

    private static function substituteEscaped($s, $source, $chunks, $options)
    {
        $spaceChars = array(" ",  "\t", "\n", "\r", "\0", "\x0B");
        $search = array();
        $replace = array();
        $allowed_langs = array('html'=>'markup','css'=>'css','php'=>'php','sql'=>'sql','js'=>'javascript','uml'=>'plantuml');
        $div_langs = array('uml');
        foreach ($chunks as $key => $chunk) {
            if ($chunk[0] == 'inline_code') {
                $search[] = "`$key`";
                $replace[] = '<code>' . substr($source, $chunk[1], $chunk[2]) . '</code>';
            }
            if ($chunk[0] == 'pre') {
                $search[] = "``$key``";
                $replace[] = "</p>\n<pre>" . self::parseInline(substr($source, $chunk[1], $chunk[2]), $options) . "</pre>\n<p>";
            }
            if ($chunk[0] == 'code') {
                $search[] = "```$key```";
                $lang = !in_array($source[$chunk[1]-1], $spaceChars) 
                  ? substr($source, $chunk[1], min(array_map(function($spChar) use ($source, $chunk) { return intval(strpos($source, $spChar, $chunk[1]))?: $chunk[1]+$chunk[2]; }, $spaceChars))-$chunk[1]) 
                  : '';
                if (!in_array($lang, array_keys($allowed_langs))) $lang = '';
                if (in_array($lang, $div_langs)) {
                  $replace[] = "</p>\n<div class={$allowed_langs[$lang]}>" . ltrim(substr($source, $chunk[1]+strlen($lang), $chunk[2]-strlen($lang)), "\n\r") . "</div>\n<p>";
                }
                else {
                  $replace[] = "</p>\n<pre><code".($lang ?" class='language-{$allowed_langs[$lang]}'":'').">" . ltrim(substr($source, $chunk[1]+strlen($lang), $chunk[2]-strlen($lang)), "\n\r") . "</code></pre>\n<p>";
                }
            }
            if ($chunk[0] == 'cite') {
                $search[]  = "````$key````";
                $sss = preg_replace("` #([0-9])`", " # $1", substr($source, $chunk[1], $chunk[2]));
                $replace[] = "</p>\n<pre><code><small>> " . self::parseInline($sss, $options) . "</small></code></pre>\n<p>";
            }
        }
        
        $s = str_replace($search, $replace, $s);
        if (substr($s, 0, 13) == "<p></p>\n<pre>") $s = substr($s, 8); 
        if (substr($s, -14) == "</pre>\n<p></p>") $s = substr($s, 0, strlen($s)-8); 
        
        return $s;
    }
    
    public static function parseSymbols($s, $options=array())
    {
        $dash = $options['use_n_dash'] ? '–' : '—';
        $nbsp = "\xc2\xa0";  //chr(160)

        $s = str_replace(
            [" &lt;- ",  " -&gt; ",  " &lt;= ",  " =&gt; ",  " - ",         " -- ",        ":)",              ":-)",              ";-)"             ],
            [" &larr; ", " &rarr; ", " &lArr; ", " &rArr; ", "$nbsp$dash ", "$nbsp$dash ", "<nobr>:)</nobr>", "<nobr>:-)</nobr>", "<nobr>;-)</nobr>"],
            $s
        );
        $s = self::inlineReplace($s, '"', '«', '»');
        $s = preg_replace("` +$nbsp`", "$nbsp", $s);

        return $s;
    }
    
    private static function parseInline($s, $options)
    {
        $video_w = $options['video_w'] ? : 476;
        $video_h = $options['video_h'] ? : 300;
        
        $s = self::parseSymbols($s, $options);

        $s = self::inlineReplace($s, '_', '<i>', '</i>');
        $s = self::inlineReplace($s, '\*\*', '<b>', '</b>');
        $s = self::inlineReplace($s, '\*', '<b>', '</b>');
        $s = self::inlineReplace($s, '~~', '<s>', '</s>');
        $s = self::inlineReplace($s, '~', '<s>', '</s>');
        $s = self::inlineReplace($s, '__', '<u>', '</u>');

        $s = self::tagReplace($s, 'i', '<i>', '</i>');
        $s = self::tagReplace($s, 'b', '<b>', '</b>');
        $s = self::tagReplace($s, 's', '<s>', '</s>');
        $s = self::tagReplace($s, 'u', '<u>', '</u>');
        $s = self::tagReplace($s, 'c', '<span class=ccc>', '</span>');
        $s = self::tagReplace($s, 'cg', '<span class=cccg>', '</span>');
        $s = self::tagReplace($s, 'cy', '<span class=cccy>', '</span>');
        $s = self::tagReplace($s, 'cr', '<span class=cccr>', '</span>');
        $s = self::tagReplace($s, 'cm', '<span class=cccm>', '</span>');
        $s = self::tagReplace($s, 'cb', '<span class=cccb>', '</span>');
        $s = self::tagReplace($s, 'cgr', '<span class=cccgr>', '</span>');
        $s = self::tagReplace($s, 'gr', '<span class=grgr>', '</span>');
        $s = self::tagReplace($s, 'sp', '<span class=sp_controls><input type="checkbox" class=sp_input tabindex="-1" ><span class=close>Скрыть</span><span class=open>Показать</span><span class=sp>', '</span></span>');

        $s = preg_replace("`\[([^\]]*)\]\(((?:https?://|/admin/)$options[current_host].*?)\)`", "<a href='\\2'>\\1</a>", $s);
        $s = preg_replace("`\[([^\]]*)\]\(((?:https?://|/admin/).*?)\)`", "<a href='\\2' rel=nofollow>\\1</a>", $s);

        $s = preg_replace("`\[y ([a-zA-Z0-9_\-]+)\]`i", "<iframe src=\"//www.youtube.com/embed/$1?rel=0\" width=$video_w height=$video_h frameborder=0 allowfullscreen></iframe>", $s);
        $s = preg_replace("`\[y ([a-zA-Z0-9_\-]+) ([0-9]+)(x|х)([0-9]+)\]`i", "<iframe src=\"//www.youtube.com/embed/$1?rel=0\" width=$2 height=$4 frameborder=0 allowfullscreen></iframe>", $s);

        $s = preg_replace("`\[tableau ([a-zA-Z0-9_/\-]+)((\?:([a-zA-Z0-9_=\-]+))?)(\])`i", "<iframe src=\"//tableau.x340.org/views/$1?iframeSizedToWindow=true&:embed=y&:showAppBanner=false&:display_count=no&:showVizHome=no\" align='center' width=640 height=480 frameborder=0 allowfullscreen></iframe>", $s);  // Объект без заданного размера
        $s = preg_replace("`\[tableau ([a-zA-Z0-9_/\-]+)((\?:([a-zA-Z0-9_=\-]+))?) ([0-9]+)(x|х)([0-9]+)(\])`i", "<iframe src=\"//tableau.x340.org/views/$1?iframeSizedToWindow=true&:embed=y&:showAppBanner=false&:display_count=no&:showVizHome=no\" align='center' width=$5 height=$6 frameborder=0 allowfullscreen></iframe>", $s);  // Объект с заданным размером

        if (is_array($options['inline_processors'])) {
            foreach ($options['inline_processors'] as $f) {
                $s = call_user_func($f, $s);
            }
        }

        // Увеличиваем размер смайликов
        //$s = preg_replace("`(&#\d+;)`i", "<span class='emojif'>$1</span>", $s);

        return $s;
    }

    private static function parseBlock($s, $options)
    {
        $video_w = $options['video_w'] ? : 476;
        $video_h = $options['video_h'] ? : 300;
  
        $s = preg_replace("`\[y ([a-zA-Z0-9_\-]+)\]`i", "<iframe src=\"//www.youtube.com/embed/$1?rel=0\" width=$video_w height=$video_h frameborder=0 allowfullscreen></iframe>", $s);
        $s = preg_replace("`\[y ([a-zA-Z0-9_\-]+) ([0-9]+)(x|х)([0-9]+)\]`i", "<iframe src=\"//www.youtube.com/embed/$1?rel=0\" width=$2 height=$4 frameborder=0 allowfullscreen></iframe>", $s);

        $s = preg_replace("`\[tableau ([a-zA-Z0-9_/\-]+)((\?:([a-zA-Z0-9_=\-]+))?)(\])`i", "<iframe src=\"//tableau.x340.org/views/$1?iframeSizedToWindow=true&:embed=y&:showAppBanner=false&:display_count=no&:showVizHome=no\" align='center' width=640 height=480 frameborder=0 allowfullscreen></iframe>", $s);  // Объект без заданного размера
        $s = preg_replace("`\[tableau ([a-zA-Z0-9_/\-]+)((\?:([a-zA-Z0-9_=\-]+))?) ([0-9]+)(x|х)([0-9]+)(\])`i", "<iframe src=\"//tableau.x340.org/views/$1?iframeSizedToWindow=true&:embed=y&:showAppBanner=false&:display_count=no&:showVizHome=no\" align='center' width=$5 height=$6 frameborder=0 allowfullscreen></iframe>", $s);  // Объект с заданным размером

        $s = preg_replace('`^##### +(.*?)[^\S\n]*(#[^\S\n]*)?$`m', "</p>\n<h6>\\1</h6>\n<p>", $s);
        $s = preg_replace( '`^#### +(.*?)[^\S\n]*(#[^\S\n]*)?$`m', "</p>\n<h5>\\1</h5>\n<p>", $s);
        $s = preg_replace(  '`^### +(.*?)[^\S\n]*(#[^\S\n]*)?$`m', "</p>\n<h4>\\1</h4>\n<p>", $s);
        $s = preg_replace(   '`^## +(.*?)[^\S\n]*(#[^\S\n]*)?$`m', "</p>\n<h3>\\1</h3>\n<p>", $s);
        $s = preg_replace(    '`^# +(.*?)[^\S\n]*(#[^\S\n]*)?$`m', "</p>\n<h2>\\1</h2>\n<p>", $s);
        $s = str_replace("\n<p>\n</p>\n", "\n", $s);     // Склеить идущие подряд
        $s = str_replace(">\n<p>\n", ">\n<p>", $s);      // Убрать перенос после
        $s = str_replace("\n</p>\n<h", "</p>\n<h", $s);  // Убрать перенос перед

        $s = preg_replace('`^(?:(?:<p>)?&gt;)+ ?(.*)$`m', "</p>\n<blockquote>\\1</blockquote>\n<p>", $s);
        $s = str_replace("</blockquote>\n<p>\n</p>\n<blockquote>", "</blockquote>\n<blockquote>", $s);  // Склеить идущие подряд
        $s = str_replace("</blockquote>\n<p>\n", "</blockquote>\n<p>", $s);  // Убрать перенос после
        $s = str_replace("\n</p>\n<blockquote>", "</p>\n<blockquote>", $s);  // Убрать перенос перед

        $s = preg_replace('`^(?:<p>)?[-\*•] (.*)$`m', "</p>\n<ul><li>\\1</li></ul>\n<p>", $s);
        $s = str_replace("</li></ul>\n<p>\n</p>\n<ul><li>", "</li>\n<li>", $s);  // Склеить идущие подряд
        $s = str_replace("</li></ul>\n<p>\n", "</li></ul>\n<p>", $s);  // Убрать перенос после
        $s = str_replace("\n</p>\n<ul><li>", "</p>\n<ul><li>", $s);  // Убрать перенос перед

        $s = self::tagTableReplace($s);
        $s = self::parseTables($s);
        $s = str_replace("</table>\n<p>\n", "</table>\n<p>", $s);  // Убрать перенос после
        $s = str_replace("\n</p>\n<table>", "</p>\n<table>", $s);  // Убрать перенос перед

        if (substr($s, 0, 5) == "</p>\n") $s = substr($s, 5);   // Убрать </p> в начале
        if (substr($s, -4) == "\n<p>") $s = substr($s, 0, strlen($s)-4);  // Убрать <p> в конце

        // Убрать затесавшиеся </p> внутрь блочных элементов и <p> перед блочными элементами
        $s = str_replace("</p></", "</", $s);  // При замене заголовков </p> попал в конце строки, которая потом попала в блочный тег 
        $s = preg_replace('`((?:h\d|ul|blockquote)>)</p>`m', "\\1", $s);  // </p> встал сразу после блочного тега
        $s = preg_replace('`<p>(<(?:h|ul|blockquote))`m', "\\1", $s);  // <p> встал перед блочным тегом

        $b = ''; $e = '';
        if (substr($s, 0, 12) != "<blockquote>" && substr($s, 0, 2) != "<h" && substr($s, 0, 4) != "<ul>" && substr($s, 0, 7) != "<table>") $b = "<p>";
        if (substr($s, -13) != "</blockquote>" && substr($s, -5, -2) != "</h" && substr($s, -5) != "</ul>" && substr($s, -8) != "</table>") $e = "</p>";
        
        $s = $b . preg_replace('`[^\S\n]*\n[^\S\n]*\n[^\S\n]*`', "<br>\n", $s) . $e;
        $s = str_replace("<p></p>\n", "<br>", $s);
        $s = preg_replace('`(?<!</h\d>|</li>|</ul>|</table>|</p>|</p>\n|</blockquote>)\n(?!<p>|\n<p>)`', "<br>\n", $s);

        return $s;
    }
    
    private static function parseTables($s)
    {
        preg_match_all(
            '`(?<=^|\n|^<p>|\n<p>)'  // Таблица идет с новой строки или в начале параграфа
          . '\|.*\|[^\S\n]*'         // Шапка
          . '\n\|[- |:0-9]*\|[^\S\n]*'  // Разделитель тела и шапки
          . '\n(?:\|.*\|[^\S\n]*(?:\n|$))*'  // Строки с данными кончаются \n
          . '(?:\|.*\|[^\S\n]*)(?=\n|$|</p>\n|</p>$)`',  // Последняя строка заканчивается по-другому
            $s, $matches, PREG_OFFSET_CAPTURE);
        $matches = $matches[0];
        if (!count($matches)) return $s;

        // Из $s составить строку где таблицы заменены их html-представлением
        $chunks = array();
        $pos = 0;
        foreach ($matches as $match) {
            if ($match[1] != $pos) $chunks[] = substr($s, $pos, $match[1]-$pos);
            $chunks[] = self::parseTable($match[0]);
            $pos = $match[1] + strlen($match[0]);
        }
        $chunks[] = substr($s, $pos, strlen($s)-$pos);
        return implode('', $chunks);
    }
    
    private static function parseTable($table)
    {
        // Разделитель шапки и тела
        $sep_reg = '`\n(\|([- :]+[0-9]*[ ]*[|])*)[^\S\n]*\n`';
        
        preg_match($sep_reg, $table, $matches);
        // Получить выравнивание столбцов - оно закодировано в разделителе
        $aligns = explode('|', trim($matches[1], '|'));
        $widths = [];
        foreach ($aligns as $key => $a) {
            $a = trim($a);
            preg_match("`([- |:]*)([0-9]*)$`", $a, $b);
            $a = trim($b[1]); // align тип :-, -: или :-:
            $widths[$key] = $b[2] > 0 ? "max-width: $b[2]px; min-width: $b[2]px;" : ""; // ширина столбца
            if     (substr($a, 0, 2) == ':-' && substr($a, -2) == '-:') $aligns[$key] = 'center';
            elseif (substr($a, 0, 2) == ':-') $aligns[$key] = 'left';
            elseif (substr($a, -2) == '-:') $aligns[$key] = 'right';
            else $aligns[$key] = '';
        }

        // Получить шапку и тело 
        $parts = preg_split($sep_reg, $table);
        $head = explode('|', trim($parts[0], "| \t\n"));
        // Массив строк
        $body = array_map(function ($s) {return explode('|', trim($s, "| \t"));}, explode("\n", trim($parts[1])));

        $t = "<table><thead><tr>";
        foreach ($head as $key => $header) {
            $ws = $widths[$key];
            if ($aligns[$key]) {
                $t .= "<th style='text-align: $aligns[$key]; $ws' title='$header'>$header</th>";
            } else {
                $t .= "<th title='$header' style='$ws'>$header</th>";
            }
        }
        $t .= '</tr></thead><tbody>';
        foreach ($body as $row) {
            $t .= "<tr>";
            foreach ($row as $key => $val) {
                $ws = $widths[$key];
                if ($aligns[$key]) {
                    $t .= "<td style='text-align: $aligns[$key]; $ws'>$val</td>";
                } else {
                    $t .= "<td style='$ws'>$val</td>";
                }
            }
            $t .= "</tr>";
        }
        $t .= "</tbody></table>";
        
        return $t;
    }
    
    private static function inlineReplace($s, $char, $open, $close) 
    {
        $outer_symbol = substr_count($char, '_') ? "[^\w$char]" : "[^\w$char]|_";
        return preg_replace("`(?<=^|$outer_symbol)$char([^\s$char]|[^\s$char].*?[^\s$char])$char(?=$|$outer_symbol)`", "$open\\1$close", $s);
    }

    private static function tagReplace($s, $tag, $open, $close)
    {
        return preg_replace("`\[$tag\](.*?)\[/$tag\]`si", "$open\\1$close", $s);
    }

    private static function tagTableReplace($s)
    {
        return preg_replace_callback(
          "`\[table\]\n(.*?)\n\[/table\]`si",
            function ($matches) {
              $p = trim($matches[1]);
              $p = str_replace("\t","|",$p);
              $p = preg_replace("'\\n'","\n:-\n", $p, 1);
              $p = str_replace("\n","|\n|",$p);
              $p = "|$p|";
              return self::parseTable($p);
            },
            $s);
      }
  
}
