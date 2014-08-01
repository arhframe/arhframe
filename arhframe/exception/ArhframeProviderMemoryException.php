<?php

/**
 *
 */
final class ArhframeProviderMemoryException extends Exception
{
    public function __construct($message = "", $code = 0, $previous = NULL)
    {
        parent::__construct('Arhframe exception: ' . $message, $code, $previous);
    }
}
