<?

include_once($_SERVER['DOCUMENT_ROOT'] . "/../class/autoloader.php");

////////////////////////////////////////////////////////////////////////////////////////////////

Router::ParseURL();

$_GET = $_ENV->route->get;

Page::$bread[] = ["GLife", "/", "<sup>".sprintf("%.2lf", $_ENV->ver/100)."</sup>"];

glUser::GetFromCookies();

$echo = Router::IncludeURL();
Page::$main .= $echo;

////////////////////////////////////////////////////////////////////////////////////////////////

Page::Make();
