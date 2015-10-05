<?php

use Monolog\Logger;

/**
 * Class, that sorts links into collections to group items to clean by type and processor class
 */
class FH_LinkCleaner_Engine_Sorter_LinkSorter
{
    const LINK_TYPE_THREAD = 'thread';
    const LINK_TYPE_FORUM = 'forum';

    const LINK_REGEX_THREAD = '|forumhouse\\.ru/threads/(\\d+)/|ism';
    const LINK_REGEX_FORUM = '|forumhouse\\.ru/forums/(\\d+)/|ism';

    /**
     * @var Logger Monolog instance to use for logging
     */
    private $logger;

    /**
     * FH_LinkCleaner_Engine_LinkTypeManager constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param FH_LinkCleaner_Engine_Extractor_FileEntry[] $entries Entries from extractor to sort
     *
     * @return FH_LinkCleaner_Engine_Sorter_CleanCollection[] Collections ready to clean
     */
    public function sortLinks(array $entries)
    {
        /** @var FH_LinkCleaner_Engine_Sorter_CleanCollection[] $cleanCollections */
        $cleanCollections = array();

        foreach ($entries as $entry) {
            try {
                list($linkType, $contentId, $linkProcessorClass) = $this->parseLink($entry->getUrl());
            } catch (FH_LinkCleaner_Engine_Exception_UnknownLinkTypeException $e) {
                $this->logger->addError("Unknown link type for url '{$entry->getUrl()}'");
                continue;
            }

            if (!isset($cleanCollections[$linkType])) {
                $cleanCollections[$linkType] = new FH_LinkCleaner_Engine_Sorter_CleanCollection(
                    $linkType,
                    $linkProcessorClass
                );
            }

            $cleanCollection = $cleanCollections[$linkType];

            if (!$cleanCollection->hasItem($contentId)) {
                $cleanCollection->setItem($contentId, new FH_LinkCleaner_Engine_Sorter_CleanItem($contentId));
            }

            $cleanItem = $cleanCollection->getItem($contentId);

            $cleanItem->addDeadLink($entry->getDeadLink());
        }

        return $cleanCollections;
    }

    /**
     * Parses link and returns its data
     *
     * @param string $url
     *
     * @return array Returns [linkType, contentId, contentProcessorClassName] from link
     * @throws FH_LinkCleaner_Engine_Exception_UnknownLinkTypeException
     */
    private function parseLink($url)
    {
        if (preg_match(self::LINK_REGEX_THREAD, $url, $matches)) {
            return array(self::LINK_TYPE_THREAD, $matches[1], 'FH_LinkCleaner_Engine_ContentProcessor_Thread');
        }

        throw new FH_LinkCleaner_Engine_Exception_UnknownLinkTypeException($url);
    }
}
