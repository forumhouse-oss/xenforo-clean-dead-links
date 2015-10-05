<?php

/**
 * This is thrown when unsupported link type was encountered
 */
class FH_LinkCleaner_Engine_Exception_UnknownLinkTypeException extends Exception
{
    /**
     * @var string Link url
     */
    private $link;

    /**
     * FH_LinkCleaner_Engine_Exception_UnknownLinkTypeException constructor.
     *
     * @param string $link Link url
     */
    public function __construct($link)
    {
        $this->link = $link;
        parent::__construct("Unknown link type for link '$link'");
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
}
