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
     *
     * @throws Exception
     */
    public function __construct($id)
    {
        if (!is_numeric($id)) {
            throw new Exception("Passed id is not numeric: '$id'");
        }
        $this->id = $id;
        $this->deadLinks = array();
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
