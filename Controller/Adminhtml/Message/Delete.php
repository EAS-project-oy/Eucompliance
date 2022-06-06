<?php

declare(strict_types=1);

namespace Easproject\Eucompliance\Controller\Adminhtml\Message;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Delete extends \Easproject\Eucompliance\Controller\Adminhtml\Message
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('message_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Easproject\Eucompliance\Model\Message::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Message.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['message_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Message to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
