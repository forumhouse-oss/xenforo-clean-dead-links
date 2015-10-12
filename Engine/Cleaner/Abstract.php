<?php

use Monolog\Logger;

/**
 * Abstract content cleaning class
 */
abstract class FH_LinkCleaner_Engine_Cleaner_Abstract
{
    /**
     * @var Logger Monolog instance to use for logging
     */
    protected $logger;

    /**
     * @var string[] Dead links to be cleaned out
     */
    protected $links;

    /**
     * FH_LinkCleaner_Engine_Cleaner_BBCodeTextCleaner constructor.
     *
     * @param Logger   $logger  Monolog instance to use for logging
     * @param string   $content Text content to clean
     * @param string[] $links   Dead links to be cleaned out
     */
    public function __construct(Logger $logger, array $links)
    {
        $this->logger = $logger;
        $this->links = $links;
    }

    /**
     * @param string $content
     *
     * @return string Cleaned content
     */
    abstract public function clean($content);
}
