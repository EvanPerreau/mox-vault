<?php

namespace Modules\CommonBusinessException\exceptions;

class BadParameterException extends BusinessException
{
    public function __construct(string $message)
    {
        super($message, 422);
    }
}
