<?php

use Monolog\Logger;

/**
 * Abstract class to process various forum content
 */
abstract class FH_LinkCleaner_Engine_ContentProcessor_Abstract
{
    /**
     * @var string[]
     */
    protected $cleanerClasses;

    /**
     * @var Logger Monolog instance to use for logging
     */
    protected $logger;

    /**
     * @var bool If true - only logging is performed. No modification done to forum
     */
    protected $pretend;

    /**
     * @var bool
     */
    protected $silent;

    /**
     * FH_LinkCleaner_Engine_ContentProcessor_Abstract constructor.
     *
     * @param string[] $cleanerClasses Cleaner classes to apply to content
     * @param Logger   $logger         Monolog instance to use for logging
     * @param bool     $pretend        If true - only logging is performed. No modification done to forum
     * @param bool     $silent         Silent edit mode for posts
     */
    public function __construct(array $cleanerClasses, Logger $logger, $pretend, $silent)
    {
        $this->cleanerClasses = $cleanerClasses;
        $this->logger = $logger;
        $this->pretend = $pretend;
        $this->silent = $silent;
    }

    /**
     * @param FH_LinkCleaner_Engine_Extractor_FileEntry[] $itemsToClean Links, containing content to clean
     */
    abstract public function clean(array $itemsToClean);

    /**
     * @param string   $cleanerClass
     * @param string[] $deadLinks
     *
     * @return FH_LinkCleaner_Engine_Cleaner_Abstract
     * @throws Exception
     */
    protected function createCleaner($cleanerClass, array $deadLinks)
    {
        /** @var FH_LinkCleaner_Engine_Cleaner_Abstract $cleaner */
        $cleaner = new $cleanerClass($this->logger, $deadLinks);

        if (!$cleaner instanceof FH_LinkCleaner_Engine_Cleaner_Abstract) {
            $className = get_class($cleaner);
            throw new Exception("Cleaner is of class '$className', not FH_LinkCleaner_Engine_Cleaner_Abstract");
        }

        return $cleaner;
    }

    /**
     * @param string $messageOld
     * @param string $messageNew
     *
     * @return string
     */
    protected function getMessageDiff($messageOld, $messageNew)
    {
        $differ = new \SebastianBergmann\Diff\Differ('');
        $diff = $differ->diff($messageOld, $messageNew);

        return $diff;
    }

    /**
     * @param string   $message
     * @param string[] $deadLinks
     *
     * @return string
     * @throws Exception
     */
    protected function runCleaners($message, array $deadLinks)
    {
        foreach ($this->cleanerClasses as $cleanerClass) {
            $cleaner = $this->createCleaner($cleanerClass, $deadLinks);
            $message = $cleaner->clean($message);
        }

        return $message;
    }
}
