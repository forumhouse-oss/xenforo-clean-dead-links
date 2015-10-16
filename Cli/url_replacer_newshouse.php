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

$urlMap = json_decode(file_get_contents(__DIR__.'/url_newshouse.json'), true);

$articleIdToData = get_article_mapping();

$cleaners = array(
    new FH_LinkCleaner_Engine_Cleaner_UrlMapper(
        $urlMap,
        $logger
    ),
);

$db = XenForo_Application::getDb();
$postIds = $db->fetchCol("SELECT post_id FROM xf_post WHERE message LIKE '%newshouse.ru%'");
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

/**
 * @param string $url
 * @param string $regEx
 *
 * @return string
 * @throws Exception
 */
function map_newshouse_page($url, $regEx)
{
    global $articleIdToData;

    preg_match($regEx, $url, $matches);
    $articleId = $matches[1];

    if (!isset($articleIdToData[$articleId])) {
        return 'https://www.forumhouse.ru/articles'; //A link to a non-existing article
    }

    $articleData = $articleIdToData[$articleId];
    $categorySlug = $articleData['category_slug'];
    $articleSlug = $articleData['article_slug'];

    return "https://www.forumhouse.ru/articles/{$categorySlug}/{$articleId}-{$articleSlug}";
}

function map_newshouse_category($url, $regEx)
{
    $categories = array(
        'proekt' => 'house',
        'build' => 'house',
        'stroy' => 'house',
        'teplo' => 'garden',
        'condy' => 'garden',
        'electro' => 'garden',
        'homeipoint' => 'garden',
        'instrument' => 'house',
        'window' => 'house',
        'mebel' => 'house',
        'santtechnics' => 'garden',
        'metall' => 'house',
        'flat' => 'house',
        'sos' => 'garden',
        'pechi' => 'garden',
        'pravo' => 'house',
        'advice' => 'house',
        'technics' => 'engineering-systems',
        'lopata' => 'engineering-systems',
        'land' => 'engineering-systems',
        'sad' => 'engineering-systems',
        'hand' => 'other',
        'blackk' => 'other',
        'chapelen' => 'other',
        'dostik' => 'other',
        'eskor' => 'other',
        'cardiosoma' => 'other',
        'fedor' => 'other',
        'fizik' => 'other',
        'belkka' => 'other',
    );

    preg_match($regEx, $url, $matches);
    $oldCategoryId = $matches[1];

    if (!isset($categories[$oldCategoryId])) {
        return $url; //Strange link, that will be rewritten later
    }
    $newCategoryId = $categories[$oldCategoryId];

    return "https://www.forumhouse.ru/articles/{$newCategoryId}";
}

function get_article_mapping()
{
    $filename = __DIR__.'/map_newshouse.json';

    $jsonData = file_get_contents($filename);
    if (!$jsonData) {
        throw new Exception("Cannot read file '$filename' to get url map json data");
    }

    $pageMap = json_decode($jsonData, true);
    if (!$pageMap) {
        throw new Exception("Unable to decode url map data from '$jsonData'");
    }

    $articleIdToData = array();
    foreach ($pageMap as $item) {
        $articleIdToData[$item['id']] = $item;
    }

    return $articleIdToData;
}
