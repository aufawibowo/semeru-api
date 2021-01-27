<?php

namespace Semeru\divisi-rtpo\Core\Domain\Exceptions;

class UnauthorizedException extends \Exception
{
    public function __construct($message = 'Unauthorized', $code = 403) {
        parent::__construct($message, $code);
    }
}