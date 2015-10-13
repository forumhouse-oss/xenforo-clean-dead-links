<?php

use Monolog\Logger;

/**
 * Abstract class to process various forum content
 */
abstract class FH_LinkCleaner_Engine_ContentProcessor_Abstract
{
    /**
     * @var FH_LinkCleaner_Engine_Cleaner_Abstract[]
     */
    protected $cleaners;

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
     * @param FH_LinkCleaner_Engine_Cleaner_Abstract[] $cleaners Cleaner class instances to apply to content
     * @param Logger                                   $logger   Monolog instance to use for logging
     * @param bool                                     $pretend  If true - only logging is performed. No modification
     *                                                           done to forum
     * @param bool                                     $silent   Silent edit mode for posts
     */
    public function __construct(array $cleaners, Logger $logger, $pretend, $silent)
    {
        $this->cleaners = $cleaners;
        $this->logger = $logger;
        $this->pretend = $pretend;
        $this->silent = $silent;
    }

    /**
     * @param FH_LinkCleaner_Engine_Extractor_FileEntry[] $itemsToClean Links, containing content to clean
     */
    abstract public function clean(array $itemsToClean);

    /**
     * @throws Exception
     */
    protected function assertCleanerClassesOk()
    {
        foreach ($this->cleaners as $index => $cleaner) {
            if (!$cleaner instanceof FH_LinkCleaner_Engine_Cleaner_Abstract) {
                $className = get_class($cleaner);
                throw new Exception(
                    "Cleaner #$index class mismatch. It is '$className'.".
                    "Expected descendant of FH_LinkCleaner_Engine_Cleaner_Abstract"
                );
            }
        }
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
        foreach ($this->cleaners as $cleaner) {
            $message = $cleaner->clean($message, $deadLinks);
        }

        return $message;
    }
}
