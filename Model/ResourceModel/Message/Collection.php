<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Easproject\Eucompliance\Model\ResourceModel\Message;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'message_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Easproject\Eucompliance\Model\Message::class,
            \Easproject\Eucompliance\Model\ResourceModel\Message::class
        );
    }
}
