<?php

namespace Modules\CommonBusinessException\exceptions;

use Exception;

abstract class BusinessException extends Exception
{
    public function __construct(string $message, int $code)
    {
        super($message, $code);
    }
}
