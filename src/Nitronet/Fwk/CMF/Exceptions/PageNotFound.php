<?php
namespace Nitronet\Fwk\CMF\Exceptions;

use Nitronet\Fwk\CMF\Exception;

class PageNotFound extends Exception
{
    public function __construct($page, $code = null, 
        \Exception $previous = null
    ) {
        parent::__construct(
            sprintf("Unknown page: %s", $page), 
            $code, 
            $previous
        );
    }
}