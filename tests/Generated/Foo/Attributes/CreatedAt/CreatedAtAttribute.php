<?php

namespace Railken\Laravel\Manager\Tests\Generated\Foo\Attributes\CreatedAt;

use Railken\Laravel\Manager\Attributes\BaseAttribute;
use Railken\Laravel\Manager\Contracts\EntityContract;
use Railken\Laravel\Manager\Tokens;
use Respect\Validation\Validator as v;

class CreatedAtAttribute extends BaseAttribute
{
    /**
     * Name attribute.
     *
     * @var string
     */
    protected $name = 'created_at';

    /**
     * Is the attribute required
     * This will throw not_defined exception for non defined value and non existent model.
     *
     * @var bool
     */
    protected $required = false;

    /**
     * Is the attribute unique.
     *
     * @var bool
     */
    protected $unique = false;

    /**
     * List of all exceptions used in validation.
     *
     * @var array
     */
    protected $exceptions = [
        Tokens::NOT_DEFINED    => Exceptions\FooCreatedAtNotDefinedException::class,
        Tokens::NOT_VALID      => Exceptions\FooCreatedAtNotValidException::class,
        Tokens::NOT_AUTHORIZED => Exceptions\FooCreatedAtNotAuthorizedException::class,
        Tokens::NOT_UNIQUE     => Exceptions\FooCreatedAtNotUniqueException::class,
    ];

    /**
     * List of all permissions.
     */
    protected $permissions = [
        Tokens::PERMISSION_FILL => 'foo.attributes.created_at.fill',
        Tokens::PERMISSION_SHOW => 'foo.attributes.created_at.show',
    ];

    /**
     * Is a value valid ?
     *
     * @param \Railken\Laravel\Manager\Contracts\EntityContract $entity
     * @param mixed          $value
     *
     * @return bool
     */
    public function valid(EntityContract $entity, $value)
    {
        return v::length(1, 255)->validate($value);
    }
}
