<?php

namespace Railken\Laravel\Manager\Tests\User\Exceptions;

class UserEmailNotUniqueException extends UserAttributeException
{

    /**
     * The reason (attribute) for which this exception is thrown
     *
     * @var string
     */
    protected $attribute = 'email';

    /**
     * The code to identify the error
     *
     * @var string
     */
    protected $code = 'USER_EMAIL_NOT_UNIQUE';

    /**
     * The message
     *
     * @var string
     */
    protected $message = "The %s is already taken";
}
