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
     * FH_LinkCleaner_Engine_Cleaner_BBCodeTextCleaner constructor.
     *
     * @param Logger $logger Monolog instance to use for logging
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string   $content
     * @param string[] $deadLinks
     *
     * @return string Cleaned content
     */
    abstract public function clean($content, array $deadLinks);

    /**
     * @param string $content
     * @param string $operationDescription


*
*@throws Exception
     */
    protected function assertIsNotRegExError($content, $operationDescription)
    {
        if (null === $content) {
            throw new Exception("Regular expression operation error at operation $operationDescription");
        }
    }
}
