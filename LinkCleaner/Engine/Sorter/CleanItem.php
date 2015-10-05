<?php

/**
 * Information about contents that needs to be cleaned
 */
class FH_LinkCleaner_Engine_Sorter_CleanItem
{
    /**
     * @var int The id of the item (thread, blog, album etc) which we want to clean
     */
    protected $id;

    /**
     * @var string[] An array of dead links we need to clean out
     */
    protected $deadLinks;

    /**
     * FH_LinkCleaner_Engine_Cleaner_CleanData constructor.
     *
     * @param int $id The id of the item (thread, blog, album etc) which we want to clean
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getDeadLinks()
    {
        return $this->deadLinks;
    }

    /**
     * @param string[] $deadLinks
     */
    public function setDeadLinks($deadLinks)
    {
        $this->deadLinks = $deadLinks;
    }

    /**
     * @param string $link
     *
     * @return void
     */
    public function addDeadLink($link)
    {
        $this->deadLinks[] = $link;
    }
}
