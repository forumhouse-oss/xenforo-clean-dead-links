<?php

class FH_LinkCleaner_Engine_ContentProcessor_Post extends FH_LinkCleaner_Engine_ContentProcessor_Abstract
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
            $post = $this->getPostModel()->getPostById($cleanItem->getId());
            $this->processPost($post, $cleanItem);
        }

        $db->commit();
    }

    /**
     * @param array                                  $post
     * @param FH_LinkCleaner_Engine_Sorter_CleanItem $itemToClean
     */
    protected function processPost(array $post, FH_LinkCleaner_Engine_Sorter_CleanItem $itemToClean)
    {
        $oldMessage = $post['message'];
        $message = $oldMessage;

        $message = $this->runCleaners($message, $itemToClean->getDeadLinks());

        if ($oldMessage === $message) {
            $this->logger->addDebug(
                "No cleaning was required in thread {$post['thread_id']}, post {$post['post_id']}: \r\n"
            );

            return;
        }

        $diff = $this->getMessageDiff($oldMessage, $message);

        $this->logger->addInfo(
            "Cleaning done in thread {$post['thread_id']}, post {$post['post_id']}: \r\n $diff\r\n"
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
    protected function saveMessageIntoPost($post, $message)
    {
        $dw = XenForo_DataWriter::create(
            'XenForo_DataWriter_DiscussionMessage_Post',
            XenForo_DataWriter::ERROR_EXCEPTION
        );

        $dw->setExistingData($post);
        $dw->set('message', $message);
        $dw->setOption(XenForo_DataWriter_DiscussionMessage_Post::OPTION_UPDATE_EDIT_DATE, (int)(!$this->silent));

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
    protected function getPostModel()
    {
        return XenForo_Model::create('XenForo_Model_Post');
    }
}
