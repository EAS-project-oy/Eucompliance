<?php

declare(strict_types=1);

namespace Easproject\Eucompliance\Controller\Adminhtml\Message;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Edit extends \Easproject\Eucompliance\Controller\Adminhtml\Message
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('message_id');
        $model = $this->_objectManager->create(\Easproject\Eucompliance\Model\Message::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Message no longer exists.'));
                /**
                 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                 */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('eas_eucompliance_message', $model);

        // 3. Build edit form
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Message') : __('New Message'),
            $id ? __('Edit Message') : __('New Message')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Messages'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Message %1', $model->getId()) : __('New Message')
        );
        return $resultPage;
    }
}
