<?php

namespace AppBundle\Exception;

use Exception;

class SearchEngineNotAvailableException extends Exception
{
    protected $message = 'Search engine not available.';
}