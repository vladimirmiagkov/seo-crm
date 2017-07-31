<?php

namespace AppBundle\Exception;

use Exception;

class UserAlreadyExistsException extends Exception
{
    protected $message = 'User already exist.';
}