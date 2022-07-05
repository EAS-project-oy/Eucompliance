<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 7
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Api\Data;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 *
 * @author  EAS Project <magento@easproject.org>
 * @license https://github.com/EAS-project-oy/eascompliance/ General License
 * @link    https://github.com/EAS-project-oy/eascompliance
 */
interface MessageInterface
{

    const ERROR_TYPE = 'error_type';
    const MESSAGE = 'message';
    const MESSAGE_ID = 'message_id';
    const RESPONSE = 'response';

    /**
     * Get message_id
     *
     * @return string|null
     */
    public function getMessageId(): ?string;

    /**
     * Set message_id
     *
     * @param string $messageId message id
     *
     * @return $this
     */
    public function setMessageId(string $messageId): MessageInterface;

    /**
     * Get error_type
     *
     * @return string|null
     */
    public function getErrorType(): ?string;

    /**
     * Set error_type
     *
     * @param string $errorType error type
     *
     * @return $this
     */
    public function setErrorType(string $errorType): MessageInterface;

    /**
     * Get response
     *
     * @return string|null
     */
    public function getResponse(): ?string;

    /**
     * Set response
     *
     * @param string $response response
     *
     * @return $this
     */
    public function setResponse(string $response): MessageInterface;

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Set message
     *
     * @param string $message message
     *
     * @return $this
     */
    public function setMessage(string $message): MessageInterface;
}
