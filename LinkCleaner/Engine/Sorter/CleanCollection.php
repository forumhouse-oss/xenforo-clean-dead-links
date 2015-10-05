<?php

/**
 * A collection of items to be cleaned of the same type
 */
class FH_LinkCleaner_Engine_Sorter_CleanCollection
{
    /**
     * @var string Collection link type
     */
    private $linkType;

    /**
     * @var string Content processor class name
     */
    private $contentProcessorClass;

    /**
     * @var FH_LinkCleaner_Engine_Sorter_CleanItem[] Items to clean
     */
    private $items;

    /**
     * CleanCollection constructor.
     *
     * @param string $linkType              Collection link type
     * @param string $contentProcessorClass Content processor class name
     */
    public function __construct($linkType, $contentProcessorClass)
    {
        $this->linkType = $linkType;
        $this->contentProcessorClass = $contentProcessorClass;
        $this->items = array();
    }

    /**
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * @return string
     */
    public function getContentProcessorClass()
    {
        return $this->contentProcessorClass;
    }

    /**
     * @return FH_LinkCleaner_Engine_Sorter_CleanItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param int $index
     *
     * @return FH_LinkCleaner_Engine_Sorter_CleanItem
     */
    public function getItem($index)
    {
        return $this->items[$index];
    }

    /**
     * @param int                                    $index
     * @param FH_LinkCleaner_Engine_Sorter_CleanItem $item
     *
     * @return void
     */
    public function setItem($index, FH_LinkCleaner_Engine_Sorter_CleanItem $item)
    {
        $this->items[$index] = $item;
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    public function hasItem($index)
    {
        return isset($this->items[$index]);
    }
}
