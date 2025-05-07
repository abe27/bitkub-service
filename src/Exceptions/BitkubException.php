<?php

namespace Abe27\Bitkub\Exceptions;

class BitkubException extends \Exception
{
    /**
     * The error code from Bitkub.
     *
     * @var int
     */
    protected $errorCode;

    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $errorCode
     * @return void
     */
    public function __construct($message, $errorCode = 0)
    {
        parent::__construct($message, 0);
        $this->errorCode = $errorCode;
    }

    /**
     * Get the Bitkub error code.
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
