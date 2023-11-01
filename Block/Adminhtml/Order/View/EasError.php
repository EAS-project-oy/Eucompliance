<?php
namespace Easproject\Eucompliance\Block\Adminhtml\Order\View;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class EasError extends \Magento\Backend\Block\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected ?Registry $_coreRegistry = null;

    /**
     * @var Order|null
     */
    protected $_order = null;

    public function __construct(
        Registry $registry,
        Context $context,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct(
            $context,
            $data,
            $jsonHelper,
            $directoryHelper
        );
    }

    /**
     * Get order object
     *
     * @return Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            if ($this->hasData('order')) {
                $this->_order = $this->_getData('order');
            } elseif ($this->_coreRegistry->registry('current_order')) {
                $this->_order = $this->_coreRegistry->registry('current_order');
            } elseif ($this->getParentBlock()->getOrder()) {
                $this->_order = $this->getParentBlock()->getOrder();
            }
        }
        return $this->_order;
    }

    public function getOrderError()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new LocalizedException(__("No order"));
        }
        return $order->getData('eas_error');
    }
}
