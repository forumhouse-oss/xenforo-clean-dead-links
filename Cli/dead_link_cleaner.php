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

$logger = createLogger();

$fileName = __DIR__."/dead_links.csv";
$fileHandle = fopen($fileName, 'r');
if (!$fileHandle) {
    die("Cannot open file '$fileName");
}

$csvReader = new FH_LinkCleaner_Engine_Extractor_CSVReader($logger, $fileHandle);
$fileEntries = $csvReader->read();

$linkSorter = new FH_LinkCleaner_Engine_Sorter_LinkSorter($logger);
$linkCollections = $linkSorter->sortLinks($fileEntries);
$cleaners = array('FH_LinkCleaner_Engine_Cleaner_BBCodeTextCleaner');

foreach ($linkCollections as $linkCollection) {
    $processorClass = $linkCollection->getContentProcessorClass();
    $processor = createProcessor($processorClass, $cleaners, $logger);
    $processor->clean($linkCollection->getItems());
}

/**
 * @return Logger
 */
function createLogger()
{
    $logger = new Logger('logger');

    $lineFormatter = new LineFormatter(null, null, true, true);

    $handler = new RotatingFileHandler(__DIR__.'/../messages.log', 5, CLEANER_LOG_LEVEL);
    $handler->setFormatter($lineFormatter);
    $logger->pushHandler($handler);

    $handler = new \Monolog\Handler\StreamHandler("php://stdout", CLEANER_LOG_LEVEL);
    $handler->setFormatter($lineFormatter);
    $logger->pushHandler($handler);

    return $logger;
}

/**
 * @param string   $processorClass
 * @param string[] $cleaners
 * @param Logger   $logger
 *
 * @return FH_LinkCleaner_Engine_ContentProcessor_Abstract
 * @throws Exception
 */
function createProcessor($processorClass, $cleaners, $logger)
{
    $processor = new $processorClass($cleaners, $logger, CLEANER_PRETEND, CLEANER_SILENT);
    if (!($processor instanceof FH_LinkCleaner_Engine_ContentProcessor_Abstract)) {
        throw new Exception(
            "Class '$processorClass' is not a descendant of FH_LinkCleaner_Engine_ContentProcessor_Abstract"
        );
    }

    return $processor;
}
