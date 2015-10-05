<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Class FH_LinkCleaner_ControllerAdmin_File
 */
class FH_LinkCleaner_ControllerAdmin_CleanFile extends XenForo_ControllerAdmin_Abstract
{
    /** PHP memory_limit instruction for processing large threads */
    const TIME_LIMIT = 300;

    /** If true, no content will be modified */
    const PRETEND_MODE = true;

    /** Logging level for Monolog */
    const LOG_LEVEL = Logger::DEBUG;

    /**
     * @var resource Resource to capture monolog messages for logging and displaying
     */
    private $messages;

    /**
     * @param string $action
     *
     * @throws XenForo_ControllerResponse_Exception
     */
    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('dev');
    }

    /**
     * @return XenForo_ControllerResponse_View
     */
    public function actionIndex()
    {
        $viewParams = array();

        return $this->responseView('FH_LinkCleaner_ViewAdmin_CleanFile_Upload', 'fh_lc_clean_file_upload', $viewParams);
    }

    /**
     * @throws Exception
     * @throws XenForo_Exception
     */
    public function actionClean()
    {
        ini_set('auto_detect_line_endings', true); // MacOSX maybe fix
        set_time_limit(self::TIME_LIMIT);
        ini_set('memory_limit', '256M');

        if (!$this->isConfirmedPost()) {
            throw $this->getErrorOrNoPermissionResponseException('Только POST доступ!');
        }

        $fileHandle = $this->getUploadedFileHandle();
        if (!$fileHandle) {
            throw $this->getErrorOrNoPermissionResponseException('Ошибка загрузки файла!');
        }

        $logger = $this->createLogger();

        $csvReader = new FH_LinkCleaner_Engine_Extractor_CSVReader($logger, $fileHandle);
        $fileEntries = $csvReader->read();

        $linkSorter = new FH_LinkCleaner_Engine_Sorter_LinkSorter($logger);
        $linkCollections = $linkSorter->sortLinks($fileEntries);

        foreach ($linkCollections as $linkCollection) {
            $processorClass = $linkCollection->getContentProcessorClass();
            $processor = $this->createContentProcessor($processorClass, $logger, self::PRETEND_MODE);
            $processor->clean($linkCollection->getItems());
        }

        rewind($this->messages);
        $viewParams = array(
            'output' => preg_split('|\r?\n|', stream_get_contents($this->messages)),
        );

        return $this->responseView(
            'FH_LinkCleaner_ViewAdmin_CleanFile_Result',
            'fh_lc_clean_file_result',
            $viewParams
        );
    }

    /**
     * @return resource
     * @throws Exception
     * @throws XenForo_Exception
     */
    private function getUploadedFileHandle()
    {
        $file = XenForo_Upload::getUploadedFile('dead_links_file');
        if (!$file->isValid()) {
            throw new XenForo_Exception($file->getErrors(), true);
        }

        $fileName = $file->getTempFile();
        $handle = fopen($fileName, 'r');

        if (!is_resource($handle)) {
            throw new Exception("Cannot open uploaded file '$fileName' for reading");
        }

        return $handle;
    }

    /**
     * Configures Monolog for logging all actions
     *
     * @return Logger
     */
    private function createLogger()
    {
        $logger = new Logger('logger');

        $lineFormatter = new LineFormatter(null, null, true, true);

        $handler = new RotatingFileHandler(__DIR__.'/../messages.log', 5, self::LOG_LEVEL);
        $handler->setFormatter($lineFormatter);
        $logger->pushHandler($handler);

        $fiveMBs = 5 * 1024 * 1024;
        $this->messages = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        $handler = new \Monolog\Handler\StreamHandler($this->messages, self::LOG_LEVEL);
        $handler->setFormatter($lineFormatter);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @param string $processorClass
     * @param Logger $logger
     * @param bool   $pretend
     *
     * @return FH_LinkCleaner_Engine_ContentProcessor_Abstract
     * @throws Exception
     */
    private function createContentProcessor($processorClass, $logger, $pretend)
    {
        /** @var FH_LinkCleaner_Engine_ContentProcessor_Abstract $processor */
        $processor = new $processorClass($logger, $pretend);
        if (!($processor instanceof FH_LinkCleaner_Engine_ContentProcessor_Abstract)) {
            throw new Exception(
                "Class '$processorClass' is not a descendant of FH_LinkCleaner_Engine_ContentProcessor_Abstract"
            );
        }

        return $processor;
    }
}
