<?php

namespace App\Services\Social\LinkedIn\Exceptions;

use Exception;

class LinkedInDuplicatePostException extends Exception
{
    private string $postId;

    /**
     * @param string $message
     * @param string $postId  The URN of the existing duplicate post
     */
    public function __construct(string $message = "", string $postId = "")
    {
        parent::__construct($message);
        $this->postId = $postId;
    }

    /**
     * Returns the URN of the duplicate share if available.
     */
    public function getPostId(): string
    {
        return $this->postId;
    }
}
