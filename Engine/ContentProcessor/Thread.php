<?php

use SebastianBergmann\Diff\Differ;

/**
 * Class, responsible for processing threads
 */
class FH_LinkCleaner_Engine_ContentProcessor_Thread extends FH_LinkCleaner_Engine_ContentProcessor_Abstract
{
    /**
     * @param FH_LinkCleaner_Engine_Sorter_CleanItem[] $links
     *
     * @return void
     */
    public function clean(array $links)
    {
        $db = XenForo_Application::getDb();
        $db->beginTransaction();

        foreach ($links as $cleanItem) {
            $posts = $this->getPostModel()->getPostsInThread($cleanItem->getId());
            foreach ($posts as $post) {
                $this->processPost($post, $cleanItem);
            }
        }

        $db->commit();
    }

    /**
     * @param array                                  $post
     * @param FH_LinkCleaner_Engine_Sorter_CleanItem $cleanItem
     */
    private function processPost(array $post, FH_LinkCleaner_Engine_Sorter_CleanItem $cleanItem)
    {
        $cleaner = new FH_LinkCleaner_Engine_Cleaner_BBCodeTextCleaner(
            $this->logger,
            $post['message'],
            $cleanItem->getDeadLinks()
        );

        $message = $cleaner->clean();

        if (!$message) {
            $this->logger->addDebug(
                "No cleaning was required in thread {$post['thread_id']}, post {$post['post_id']}: \r\n"
            );

            return;
        }

        $diff = $this->getPostMessageDiff($post, $message);

        $this->logger->addInfo(
            "BBCode cleaned in thread {$post['thread_id']}, post {$post['post_id']}: \r\n $diff\r\n"
        );

        if (!$this->pretend) {
            $this->saveMessageIntoPost($post, $message);
        }

    }

    /**
     * @param $post
     * @param $message
     *
     * @throws Exception
     * @throws XenForo_Exception
     */
    private function saveMessageIntoPost($post, $message)
    {
        $dw = XenForo_DataWriter::create(
            'XenForo_DataWriter_DiscussionMessage_Post',
            XenForo_DataWriter::ERROR_EXCEPTION
        );
        $dw->setExistingData($post);
        $dw->set('message', $message);

        try {
            $dw->save();
        } catch (Exception $e) {
            $errors = implode("\r\n", $dw->getErrors());
            $this->logger->addError(
                "Error saving post {$post['post_id']}. Message is: '$message'. Errors are: {$errors}"
            );

            return;
        }

        $this->logger->debug("Saved post {$post['post_id']} into DB");
    }

    /**
     * @return XenForo_Model_Post
     * @throws XenForo_Exception
     */
    private function getPostModel()
    {
        return XenForo_Model::create('XenForo_Model_Post');
    }

    /**
     * @param array $post
     * @param       $message
     *
     * @return string
     */
    private function getPostMessageDiff(array $post, $message)
    {
        $differ = new Differ('');
        $diff = $differ->diff($post['message']."\r\n", $message);

        return $diff;
    }
}
