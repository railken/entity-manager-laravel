<?php

namespace Railken\Laravel\Manager\Tests\Core\Article\Attributes\DeletedAt;


use Railken\Laravel\Manager\Contracts\EntityContract;
use Railken\Laravel\Manager\ModelAttribute;
use Railken\Laravel\Manager\Traits\AttributeValidateTrait;
use Railken\Laravel\Manager\Tests\Core\Article\Attributes\DeletedAt\Exceptions as Exceptions;
use Respect\Validation\Validator as v;
use Railken\Laravel\Manager\Tokens;

class DeletedAtAttribute extends ModelAttribute
{

	/**
	 * Name attribute
	 *
	 * @var string
	 */
	protected $name = 'deleted_at';

    /**
     * Is the attribute required
     * This will throw not_defined exception for non defined value and non existent model
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * Is the attribute unique 
     *
     * @var boolean
     */
    protected $unique = false;

    /**
     * List of all exceptions used in validation
     *
     * @var array
     */
    protected $exceptions = [
    	Tokens::NOT_DEFINED => Exceptions\ArticleDeletedAtNotDefinedException::class,
    	Tokens::NOT_VALID => Exceptions\ArticleDeletedAtNotValidException::class,
        Tokens::NOT_AUTHORIZED => Exceptions\ArticleDeletedAtNotAuthorizedException::class
    ];

    /**
     * List of all permissions
     */
    protected $permissions = [
        Tokens::PERMISSION_FILL => 'article.attributes.deleted_at.fill',
        Tokens::PERMISSION_SHOW => 'article.attributes.deleted_at.show'
    ];

    /**
     * Is a value valid ?
     *
     * @param EntityContract $entity
     * @param mixed $value
     *
     * @return boolean
     */
	public function valid(EntityContract $entity, $value)
	{
		return v::length(1, 255)->validate($value);
	}


}