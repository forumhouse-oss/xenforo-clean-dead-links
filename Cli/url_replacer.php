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
define('CLEANER_PRETEND', true);
define('CLEANER_SILENT', true);

ini_set('auto_detect_line_endings', true); // MacOSX maybe fix
ini_set('memory_limit', '256M');

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PlainTextHandler($logger));
$whoops->register();

$logger = createLogger();

$urlMap = array(
    '#.+\.forumhouse\.ru$#ismu' => array(
        '#stock/contractors/categories/(\d+)(/.*)?#ismu' => 'exchange/contractors?categoriesSelected[]=$1',
        '#stock/portfolios/categories/(\d+)(/.*)?#ismu' => 'exchange/portfolios?categoriesSelected[]=$1',
        '#stock/contractors/(\d+)/portfolio(/.*)?#ismu' => 'exchange/contractors/$1',
        '#stock/contractors/(\d+)/services(/.*)?#ismu' => 'exchange/contractors/$1',
        '#stock/contractors/account-select(/.*)?#ismu' => 'exchange/profile',
        '#stock/contractors/(\d+)/reviews(/.*)?#ismu' => 'exchange/contractors/$1',
        '#stock/reviews/categories/(\d+)(/.*)?#ismu' => 'exchange/feedbacks?categoriesSelected[]=$1',
        '#stock/contractors/preferences(/.*)?#ismu' => 'exchange/profile',
        '#stock/orders/categories/(\d+)(/.*)?#ismu' => 'exchange?categoriesSelected[]=$1',
        '#stock/orders/(\d+)/add-offer(/.*)?#ismu' => 'exchange/orders/$1',
        '#stock/orders/categories(/.*)?#ismu' => 'exchange',
        '#stock/contractors/account(/.*)?#ismu' => 'exchange/profile',
        '#stock/contractors/map(/.*)?#ismu' => 'exchange/contractors',
        '#stock/orders/watched(/.*)?#ismu' => 'exchange/profile/my-orders/new-for-client',
        '#stock\?user_id=(\d+).*#ismu' => 'exchange/contractors/$1',
        '#stock/offers(/.*)#ismu' => 'exchange',
        '#stock/orders/(\d+)(/.*)#ismu' => 'exchange/orders/$1',
        '#stock/contractors(/.*)#ismu' => 'exchange/contractors',
        '#stock/rates/(\d+)(/.*)#ismu' => 'exchange',
        '#stock/portfolios(/.*)#ismu' => 'exchange/portfolios',
        '#stock/orders/add(/.*)#ismu' => 'exchange/orders/create',
        '#stock/orders/my(/.*)#ismu' => 'exchange/profile/my-orders/new-for-client',
        '#stock/reviews(/.*)#ismu' => 'exchange/feedbacks',
        '#stock/orders(/.*)#ismu' => 'exchange',
        '#stock(/.*)#ismu' => 'exchange',
    ),
);

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
