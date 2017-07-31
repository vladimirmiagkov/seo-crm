<?php

namespace AppBundle\Exception;

use Exception;

class SiteNotExistsException extends Exception
{
    protected $message = 'Site not exist.';
}