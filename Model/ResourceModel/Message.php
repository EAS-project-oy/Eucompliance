<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Eas\Eucompliance\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Message extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('eas_eucompliance_message', 'message_id');
    }
}

