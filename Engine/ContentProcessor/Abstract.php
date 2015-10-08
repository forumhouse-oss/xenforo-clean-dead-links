<?php

use Monolog\Logger;

/**
 * Abstract class to process various forum content
 */
abstract class FH_LinkCleaner_Engine_ContentProcessor_Abstract
{
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
     * @param Logger $logger  Monolog instance to use for logging
     * @param bool   $pretend If true - only logging is performed. No modification done to forum
     * @param bool   $silent  Silent edit mode for posts
     */
    public function __construct(Logger $logger, $pretend, $silent)
    {
        $this->logger = $logger;
        $this->pretend = $pretend;
        $this->silent = $silent;
    }

    /**
     * @param FH_LinkCleaner_Engine_Extractor_FileEntry[] $links
     *
     * @return void
     */
    abstract public function clean(array $links);
}
