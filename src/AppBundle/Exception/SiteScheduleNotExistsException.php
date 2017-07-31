<?php

namespace AppBundle\Exception;

use Exception;

class SiteScheduleNotExistsException extends Exception
{
    protected $message = 'SiteSchedule not exist.';
}