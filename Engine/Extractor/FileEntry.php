<?php

/**
 * Information about dead link found
 */
class FH_LinkCleaner_Engine_Extractor_FileEntry
{
    /**
     * @var string Url of the site, where dead link is located
     */
    protected $url;

    /**
     * @var string Dead link text
     */
    protected $deadLink;

    /**
     * FH_LinkCleaner_Link constructor.
     *
     * @param string $url      Url of the site, where dead link is located
     * @param string $deadLink Dead link text
     */
    public function __construct($url, $deadLink)
    {
        $this->url = $url;
        $this->deadLink = $deadLink;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getDeadLink()
    {
        return $this->deadLink;
    }
}
