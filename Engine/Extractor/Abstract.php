<?php

use Monolog\Logger;

/**
 * Abstract class for dead url extraction
 */
abstract class FH_LinkCleaner_Engine_Extractor_Abstract
{
    /**
     * @var Logger Monolog instance used for logging
     */
    protected $logger;

    /**
     * @var resource File handle to read data from
     */
    protected $fileHandle;

    /**
     * FH_LinkCleaner_Sorter_CSVReader constructor.
     *
     * @param Logger   $logger     Monolog instance used for logging
     * @param resource $fileHandle File handle to read data from
     */
    public function __construct(Logger $logger, $fileHandle)
    {
        $this->logger = $logger;
        $this->fileHandle = $fileHandle;
    }

    /**
     * @return FH_LinkCleaner_Engine_Extractor_FileEntry[]
     */
    abstract public function read();
}
