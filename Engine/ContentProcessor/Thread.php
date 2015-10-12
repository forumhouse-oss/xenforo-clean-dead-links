<?php

/**
 * Class, responsible for processing threads
 */
class FH_LinkCleaner_Engine_ContentProcessor_Thread extends FH_LinkCleaner_Engine_ContentProcessor_Post
{
    /**
     * @param FH_LinkCleaner_Engine_Sorter_CleanItem[] $itemsToClean
     *
     * @return void
     */
    public function clean(array $itemsToClean)
    {
        $db = XenForo_Application::getDb();
        $db->beginTransaction();

        foreach ($itemsToClean as $cleanItem) {
            $posts = $this->getPostModel()->getPostsInThread($cleanItem->getId());
            foreach ($posts as $post) {
                $this->processPost($post, $cleanItem);
            }
        }

        $db->commit();
    }
}
