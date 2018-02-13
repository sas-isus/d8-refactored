<?php

namespace Cheppers\GatherContent;

class GatherContentClientException extends \Exception
{
    const UNEXPECTED_CONTENT_TYPE = 1;
    const API_ERROR = 2;
    const UNEXPECTED_ANSWER = 3;
    const INVALID_RESPONSE_HEADER = 4;

    public function __construct($message, $code, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
