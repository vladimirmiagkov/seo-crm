<?php

namespace AppBundle\Exception;

use Exception;

class SearchEngineErrorException extends Exception
{
    protected $message = 'Search engine error.';
}