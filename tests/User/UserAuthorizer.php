<?php

namespace Railken\Laravel\Manager\Tests\User;

use Railken\Laravel\Manager\Contracts\EntityContract;
use Railken\Laravel\Manager\ParameterBag;
use Railken\Laravel\Manager\Tests\User\Exceptions as Exceptions;
use Railken\Laravel\Manager\Contracts\ModelAuthorizerContract;
use Illuminate\Support\Collection;

class UserAuthorizer implements ModelAuthorizerContract
{

    /**
     * @var ModelManager
     */
    protected $manager;

    /**
     * Construct
     */
    public function __construct(UserManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Authorize
     *
     * @param string $operation
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function can(string $operation, EntityContract $entity, ParameterBag $parameters)
    {
        $errors = new Collection();

        !$this->manager->agent->can($operation, $entity) && $errors->push(new Exceptions\UserNotAuthorizedException($entity));

        return $errors;
    }

    /**
     * Authorize create
     *
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function create(EntityContract $entity, ParameterBag $parameters)
    {
        return $this->can('create', $entity, $parameters);
    }

    /**
     * Authorize update
     *
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function update(EntityContract $entity, ParameterBag $parameters)
    {
        return $this->can('update', $entity, $parameters);
    }

    /**
     * Authorize retrieve
     *
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function retrieve(EntityContract $entity, ParameterBag $parameters)
    {
        return $this->can('retrieve', $entity, $parameters);
    }

    /**
     * Authorize remove
     *
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function remove(EntityContract $entity, ParameterBag $parameters)
    {
        return $this->can('remove', $entity, $parameters);
    }
}
