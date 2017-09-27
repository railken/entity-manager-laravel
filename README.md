# Laravel Model Manager

A precise way to structure your model in sub parts and improve readability and maintainability of your code.

## Requirements

PHP 7.0.0 and later.

## Composer

You can install it via [Composer](https://getcomposer.org/) by typing the
following command:

```bash
composer require railken/laravel-manager
```

## Installation
- Add the service provider to the `providers` array in `config/app.php`

```php
Railken\Laravel\Manager\ManagerServiceProvider::class,
```

## Usage

First you need to generate a new structure folder, use:

`php artisan railken:make:manager app App\Foo`.

Add `App\Foo\FooServiceProvider::class` in config/app.php.

Now you can used it.
```php
use App\Foo\FooManager;

$manager = new FooManager();
$result = $manager->create(['name' => 'foo']);

if ($result->ok()) {
    $foo = $result->getResource();
} else {
    $result->getErrors(); // All errors goes here.
}

```

How can you get an Error during an operation? An error occurs when a validation or authorization fails. The cool thing about it is that you have the total control during each process: using with [ModelValidator](#modelvalidator) and [ModelAuthorizer](#modelauthorizer). When you're retrieving errors you're receiving a Collection, it goes pretty well when you're developing an api. Here's an example
```php
$manager = new FooManager();
$result = $manager->create(['name' => 'f'));

print_r($result->getErrors()->toArray());
/*
Array
    (
        [0] => Array
            (
                [code] => FOO_TITLE_NOT_DEFINED
                [attribute] => title
                [message] => The title is required
                [value] =>
            )

        [1] => Array
            (
                [code] => FOO_NAME_NOT_DEFINED
                [attribute] => name
                [message] => The name isn't valid
                [value] => f
            )
    )
*/
```
So, what about the authorization part? You need first setup the agent. There are 3 types of agents:
- SytemAgent: The system (e.g. console)
- UserAgent: A user is authenticated
- GuestAgent: No one is authenticated

Note: if you don't set any agent, the SystemAgent will be used.

```php
use Railken\Laravel\Manager\Agents\SystemAgent;

$agent = new SystemAgent();
$manager = new FooManager();
$manager->setAgent($agent);

$result = $manager->create(['name' => 'f']);
if ($result->isAuthorized()) {
  ...
} else {
  ...
}
```


If you want to use the User Model as an agent add a Contract

```php
use Railken\Laravel\Manager\Contracts\UserAgentContract;

class User implements UserAgentContract
{
    ...
}

```

See [ModelAuthorizer](#modelauthorizer) and [ModelPolicy](#modelpolicy) for more explanations.
### Commands

- Generate a new set of files `php artisan railken:make:manager [path] [namespace]`.

### ModelManager
This is the main class, all the operations are performed using this: creating, updating, deleting, retrieving. This class is composed of components which are: validator, repository, authorizer, parameters, serializer

See [ModelManager](https://github.com/railken/laravel-manager/blob/master/src/ModelManager.php).
```php
namespace App\Foo;

use Railken\Laravel\Manager\ModelManager;
use Railken\Laravel\Manager\Contracts\AgentContract;

class FooManager extends ModelManager
{

    /**
     * Construct
     *
     * @param AgentContract|null $agent
     */
    public function __construct(AgentContract $agent = null)
    {
        parent::__construct($agent);
    }
}

```

### Model
This is the Eloquent Model, nothing changes, excepts for an interface

```php
namespace App\Foo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Railken\Laravel\Manager\Contracts\EntityContract;

class Foo extends Model implements EntityContract
{

    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'foo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
```

### ModelRepository
This is a Repository, the concept is very similar to the Repository of Symfony, code all your queries here.

See [ModelRepository](https://github.com/railken/laravel-manager/blob/master/src/ModelRepository.php) for more information.

```php
namespace App\Foo;

use Railken\Laravel\Manager\ModelRepository;

class FooRepository extends ModelRepository
{

    /**
     * Class name entity
     *
     * @var string
     */
    public $entity = Foo::class;

    /**
     * Custom method
     *
     * @param string $name
     *
     * @return Foo
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

}

```

### ModelParameterBag
This is a [Bag](https://github.com/railken/bag). This will contain all methods to filter attributes of a Model.
Use filterWrite to filter the bag before crearting/updating.
Use filterRead to filter the bag before retrieving.
See an [example](https://github.com/railken/laravel-manager/blob/master/tests/Core/Comment/CommentParameterBag.php)

```php
namespace App\Foo;

use Railken\Laravel\Manager\Contracts\AgentContract;
use Railken\Laravel\Manager\Contracts\ManagerContract;
use Railken\Laravel\Manager\Contracts\SystemAgentContract;
use Railken\Laravel\Manager\Contracts\GuestAgentContract;
use Railken\Laravel\Manager\Contracts\UserAgentContract;
use Railken\Laravel\Manager\ParameterBag;

class FooParameterBag extends ParameterBag
{

	/**
	 * Filter current bag using agent
	 *
	 * @param AgentContract $agent
	 *
	 * @return $this
	 */
	public function filterWrite(AgentContract $agent)
	{

		$this->filter(['name']);

		if ($agent instanceof UserAgentContract) {
			// ..
        }

        if ($agent instanceof GuestAgentContract) {
            // ..
        }

        if ($agent instanceof SystemAgentContract) {
            // ..
        }

		return $this;
	}

	/**
	 * Filter current bag using agent for a search
	 *
	 * @param AgentContract $agent
	 *
	 * @return $this
	 */
	public function filterRead(AgentContract $agent)
	{
		$this->filter(['id', 'name', 'created_at', 'updated_at']);

		if ($agent instanceof UserAgentContract) {
			// ..
        }

        if ($agent instanceof GuestAgentContract) {
            // ..
        }

        if ($agent instanceof SystemAgentContract) {
            // ..
        }

		return $this;
	}

}

```

### ModelValidator
Here comes the validator, and again it's very simple. validate() is called whenever a create/update operation is called.
Remember: always return the collection of errors. You can of course add a specific library for validation and use it here.


```php
namespace App\Foo;

use Railken\Laravel\Manager\Contracts\EntityContract;
use Railken\Laravel\Manager\Contracts\ModelValidatorContract;
use Railken\Laravel\Manager\ParameterBag;
use Illuminate\Support\Collection;
use App\Foo\Exceptions as Exceptions;


class FooValidator implements ModelValidatorContract
{

    /**
     * Validate
     *
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function validate(EntityContract $entity, ParameterBag $parameters)
    {

        $errors = new Collection();

        if (!$entity->exists)
            $errors = $errors->merge($this->validateRequired($parameters));

        $errors = $errors->merge($this->validateValue($entity, $parameters));

        return $errors;
    }

    /**
     * Validate "required" values
     *
     * @param EntityContract $entity
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function validateRequired(ParameterBag $parameters)
    {
        $errors = new Collection();

        !$parameters->exists('name') && $errors->push(new Exceptions\FooNameNotDefinedException($parameters->get('name')));

        return $errors;
    }

    /**
     * Validate "not valid" values
     *
     * @param ParameterBag $parameters
     *
     * @return Collection
     */
    public function validateValue(EntityContract $entity, ParameterBag $parameters)
    {
        $errors = new Collection();

        $parameters->exists('name') && !$this->validName($parameters->get('name')) &&
            $errors->push(new Exceptions\FooNameNotValidException($parameters->get('name')));


        return $errors;
    }

    /**
     * Validate name
     *
     * @param string $name
     *
     * @return boolean
     */
    public function validName($name)
    {
        return $name === null || (strlen($name) >= 3 && strlen($name) < 255);
    }

}

```
### ModelAuthorizer
As you can see this class has only one method, it does is a simple bridge between the [ModelManager](#modelmanager) and the [ModelPolicy](#modelpolicy). So all the "rules" for authorization are defined in the [ModelPolicy](#modelpolicy).

You can leave this as is it, or change and used another system for authorization.

```php
namespace App\Foo;

use Railken\Laravel\Manager\Contracts\EntityContract;
use Railken\Laravel\Manager\ParameterBag;
use Railken\Laravel\Manager\Contracts\ModelAuthorizerContract;
use Railken\Laravel\Manager\Contracts\SystemAgentContract;
use Railken\Laravel\Manager\Contracts\GuestAgentContract;
use Railken\Laravel\Manager\Contracts\UserAgentContract;
use Illuminate\Support\Collection;
use Railken\Laravel\Manager\Tests\Generated\Foo\Exceptions as Exceptions;

class FooAuthorizer implements ModelAuthorizerContract
{


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

		# SystemAgent can always do anything.
		if ($this->manager->agent instanceof SystemAgentContract) {
			return $errors;
		}

		# GuestAgent can always do anything.
		if ($this->manager->agent instanceof GuestAgentContract) {
			// ...
		}

		# GuestAgent can always do anything.
		if ($this->manager->agent instanceof UserAgentContract) {
			// ...
			!$this->manager->agent->can($operation, $entity) && $errors->push(new Exceptions\FooNotAuthorizedException($entity));

		}

		return $errors;
    }



}
```

### ModelPolicy
This is the the same as in [laravel](https://laravel.com/docs/5.5/authorization#writing-policies).
Remember to add the interface AgentContract to your User Model

```php
namespace App\Foo;

use Railken\Laravel\Manager\Contracts\AgentContract;
use Railken\Laravel\Manager\Contracts\ModelPolicyContract;
use Railken\Laravel\Manager\Contracts\EntityContract;

class FooPolicy implements ModelPolicyContract
{

    /**
     * Determine if the given entity can be manipulated by the agent.
     *
     * @param AgentContract $agent
     *
     * @return bool
     */
    public function interact(AgentContract $agent, EntityContract $entity = null)
    {   
        return true;
    }

    /**
     * Determine if the agent can create an entity
     *
     * @param AgentContract $agent
     *
     * @return bool
     */
    public function create(AgentContract $agent)
    {   
        return true;
    }

    /**
     * Determine if the given entity can be updated by the agent.
     *
     * @param AgentContract $agent
     * @param EntityContract $entity
     *
     * @return bool
     */
    public function update(AgentContract $agent, EntityContract $entity)
    {   
    	return $this->interact($agent, $entity);
    }

    /**
     * Determine if the given entity can be retrieved by the agent.
     *
     * @param AgentContract $agent
     * @param EntityContract $entity
     *
     * @return bool
     */
    public function retrieve(AgentContract $agent, EntityContract $entity)
    {   
        return $this->interact($agent, $entity);
    }

    /**
     * Determine if the given entity can be removed by the agent.
     *
     * @param AgentContract $agent
     * @param EntityContract $entity
     *
     * @return bool
     */
    public function remove(AgentContract $agent, EntityContract $entity)
    {   
        return $this->interact($agent, $entity);
    }
}
```

### ModelSerializer
This class will serialize your model

```php
namespace App\Foo;

use Railken\Laravel\Manager\Contracts\ModelSerializerContract;
use Railken\Laravel\Manager\Contracts\EntityContract;
use Railken\Bag;

class FooSerializer implements ModelSerializerContract
{

	/**
	 * Serialize entity
	 *
	 * @param EntityContract $entity
	 *
	 * @return Bag
	 */
	public function serialize(EntityContract $entity)
	{
		$bag = $this->serializeBrief($entity);

		return $bag;
	}

	/**
	 * Serialize entity
	 *
	 * @param EntityContract $entity
	 *
	 * @return Bag
	 */
	public function serializeBrief(EntityContract $entity)
	{
		$bag = new Bag();

		$bag->set('id', $entity->id);

		return $bag;
	}
}
```


### ModelServiceProvider
This class is very important, it will load all the components,
Load this provider with all others in your config/app.php

```php
namespace App\Foo;

use Gate;
use Illuminate\Support\ServiceProvider;

class FooServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        Foo::observe(FooObserver::class);
        Gate::policy(Foo::class, FooPolicy::class);

        FooManager::repository(FooRepository::class);
        FooManager::serializer(FooSerializer::class);
        FooManager::parameters(FooParameterBag::class);
        FooManager::validator(FooValidator::class);
        FooManager::authorizer(FooAuthorizer::class);
    }
}

```
