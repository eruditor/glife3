<?

////////////////////////////////////////////////////////////////////////////////////////////////

spl_autoload_register(
  function ($class) {
    static $incs = [
      'CFG'           => "__common/config.php",
      'DB'            => "__common/db.php",
      'Logger'        => "__common/logger.php",
      'Timer'         => "__common/timer.php",
      'Validator'     => "__common/validator.php",
      'AQs'           => "__common/aqs.php",
      'Markup'        => "__common/markup.php",
      
      'Router'        => "class/router.php",
      
      'Page'          => "templates/page.php",
      'Pagination'    => "templates/pagination.php",
      'FamilyFilter'  => "templates/family_filter.php",
      'GLifeInfo'     => "templates/glife_info.php",
      'GLifeJS'       => "templates/glife_js.php",
      
      'glRecords' => 'class/glrecords.php',
      'glDicts'   => 'class/gldicts.php',
    ];

    $inc = $incs[$class];
    if($inc) include_once($_SERVER['DOCUMENT_ROOT'] . "/../$inc");
  }
);

////////////////////////////////////////////////////////////////////////////////////////////////

include_once($_SERVER['DOCUMENT_ROOT'] . "/../__common/request.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../__common/db.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../__common/common.php");

////////////////////////////////////////////////////////////////////////////////////////////////
