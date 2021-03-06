<?php
/**
 * DZCP - deV!L`z ClanPortal 1.6 Final
 * http://www.dzcp.de
 */

ob_start();
define('basePath', dirname(__FILE__));
$sql_prefix = '';
$sql_host = '';
$sql_user = '';
$sql_pass = '';
$sql_db = '';

if (version_compare(phpversion(), '7.0', '<')) {
    die('Bitte verwende PHP-Version 7.0 oder h&ouml;her.<p>Please use PHP-Version 7.0 or higher.');
}

if (file_exists(basePath . "/inc/mysql.php"))
    require_once(basePath . "/inc/mysql.php");

include(basePath . '/vendor/autoload.php');
use GUMP\GUMP;
$gump = GUMP::get_instance();

if (empty($sql_user) && empty($sql_pass) && empty($sql_db)) {
    header('Location: _installer/index.php');
} else {
    $global_index = true;
    include(basePath . "/inc/debugger.php");
    include(basePath . "/inc/config.php");
    include(basePath . "/inc/bbcode.php");
    header('Location: ' . ($userid && $chkMe ? 'user/?action=userlobby' : 'news/'));
}
ob_end_flush();