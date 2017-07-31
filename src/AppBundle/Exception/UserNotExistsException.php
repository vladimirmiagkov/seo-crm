<?php

namespace AppBundle\Exception;

use Exception;

class UserNotExistsException extends Exception
{
    protected $message = 'User not exist.';
}