<?php

namespace Railken\Laravel\Manager\Tests\Core\Article\Attributes\Id\Exceptions;
use Railken\Laravel\Manager\Tests\Core\Article\Exceptions\ArticleAttributeException;

class ArticleIdNotAuthorizedException extends ArticleAttributeException
{

	/**
	 * The reason (attribute) for which this exception is thrown
	 *
	 * @var string
	 */
	protected $attribute = 'id';

	/**
	 * The code to identify the error
	 *
	 * @var string
	 */
	protected $code = 'ARTICLE_ID_NOT_AUTHTORIZED';
	
	/**
	 * The message
	 *
	 * @var string
	 */
	protected $message = "You're not authorized to interact with %s, missing %s permission";




}