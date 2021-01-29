<?php


namespace Semeru\Rtpo\Presentation\Controllers;


use Semeru\Rtpo\Core\Domain\Exceptions\InvalidOperationException;
use Semeru\Rtpo\Core\Domain\Exceptions\UnauthorizedException;
use Semeru\Rtpo\Core\Domain\Exceptions\ValidationException;

class BaseController extends \Semeru\Controllers\BaseController
{
    protected function handleException(\Exception $e)
    {
        if ($e instanceof InvalidOperationException) {
            $this->sendError($e->getMessage(), null, $e->getCode());
        } else if ($e instanceof ValidationException) {
            $this->sendError($e->getMessage(), $e->getErrors(), $e->getCode());
        } else if ($e instanceof UnauthorizedException) {
            $this->sendError($e->getMessage(), null, $e->getCode());
        } else {
            $this->sendException($e);
        }
    }
}