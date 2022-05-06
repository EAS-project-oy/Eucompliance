<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Api\Data;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
interface MessageInterface
{

    const ERROR_TYPE = 'error_type';
    const MESSAGE = 'message';
    const MESSAGE_ID = 'message_id';
    const RESPONSE = 'response';

    /**
     * Get message_id
     * @return string|null
     */
    public function getMessageId();

    /**
     * Set message_id
     * @param string $messageId
     * @return \Eas\Eucompliance\Message\Api\Data\MessageInterface
     */
    public function setMessageId($messageId);

    /**
     * Get error_type
     * @return string|null
     */
    public function getErrorType();

    /**
     * Set error_type
     * @param string $errorType
     * @return \Eas\Eucompliance\Message\Api\Data\MessageInterface
     */
    public function setErrorType($errorType);

    /**
     * Get response
     * @return string|null
     */
    public function getResponse();

    /**
     * Set response
     * @param string $response
     * @return \Eas\Eucompliance\Message\Api\Data\MessageInterface
     */
    public function setResponse($response);

    /**
     * Get message
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     * @param string $message
     * @return \Eas\Eucompliance\Message\Api\Data\MessageInterface
     */
    public function setMessage($message);
}
