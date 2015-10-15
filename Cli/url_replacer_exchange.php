<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

if (PHP_SAPI != 'cli') {
    die('This script may only be run at the command line.');
}

$xfRootDir = realpath(dirname(__FILE__).'/../../../..');

if (!class_exists('XenForo_Autoloader', false)) {
    chdir($xfRootDir);

    require_once($xfRootDir.'/library/XenForo/Autoloader.php');
    XenForo_Autoloader::getInstance()->setupAutoloader($xfRootDir.'/library');

    XenForo_Application::initialize($xfRootDir.'/library', $xfRootDir, true);
}

define('CLEANER_LOG_LEVEL', Logger::INFO);
define('CLEANER_PRETEND', false);
define('CLEANER_SILENT', true);

ini_set('auto_detect_line_endings', true); // MacOSX maybe fix
ini_set('memory_limit', '256M');

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PlainTextHandler($logger));
$whoops->register();

$logger = createLogger();

$urlMap = json_decode(file_get_contents(__DIR__.'/url_forumhouse.json'), true);

$cleaners = array(
    new FH_LinkCleaner_Engine_Cleaner_UrlMapper(
        $urlMap,
        $logger
    ),
);

$db = XenForo_Application::getDb();
$postIds = $db->fetchCol("SELECT post_id FROM xf_post WHERE message LIKE '%forumhouse.ru/stock%'");
$postCount = count($postIds);
$logger->addInfo("Found $postCount to edit");

$itemsToClean = array_map(
    function ($id) {
        return new FH_LinkCleaner_Engine_Sorter_CleanItem($id);
    },
    $postIds
);

$processor = new FH_LinkCleaner_Engine_ContentProcessor_Post($cleaners, $logger, CLEANER_PRETEND, CLEANER_SILENT);
$processor->clean($itemsToClean);

/**
 * @return Logger
 */
function createLogger()
{
    $logger = new Logger('logger');
    $logger->setTimezone(new DateTimeZone('Europe/Moscow'));

    $lineFormatter = new LineFormatter(null, null, true, true);

    $filename = basename(__FILE__, '.php');
    $handler = new RotatingFileHandler(__DIR__."/../{$filename}.log", 5, CLEANER_LOG_LEVEL);
    $handler->setFormatter($lineFormatter);
    $logger->pushHandler($handler);

    $handler = new \Monolog\Handler\StreamHandler("php://stdout", CLEANER_LOG_LEVEL);
    $handler->setFormatter($lineFormatter);
    $logger->pushHandler($handler);

    return $logger;
}
