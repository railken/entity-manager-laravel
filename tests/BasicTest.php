<?php

namespace Railken\Laravel\Manager\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Schema\Blueprint;

use Railken\Laravel\Manager\Tests\User\User;
use Railken\Laravel\Manager\Tests\User\UserManager;
use Railken\Laravel\Manager\Tests\User\UserObserver;
use Railken\Laravel\Manager\Tests\User\UserPolicy;
use Railken\Bag;

class BasicTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {

        $dotenv = new \Dotenv\Dotenv(__DIR__."/..", '.env');
        $dotenv->load();

        parent::setUp();

        Schema::dropIfExists('users');
        
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->string('role')->default(User::ROLE_USER);
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        User::observe(UserObserver::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    /**
     * Return a new instance of user bag
     *
     * @return Bag
     */
    public function getUserBag()
    {
        return new Bag(['email' => 'test@test.net', 'username' => 'test123', 'password' => microtime()]);
    }

    public function testBasics()
    {

        $um = new UserManager();

        # Testing validation
        $this->assertEquals("USER_USERNAME_NOT_DEFINED", $um->create($this->getUserBag()->remove('username'))->getError()->getCode());
        $this->assertEquals("USER_USERNAME_NOT_VALID", $um->create($this->getUserBag()->set('username', 'wr'))->getError()->getCode());
        $this->assertEquals("USER_PASSWORD_NOT_DEFINED", $um->create($this->getUserBag()->remove('password'))->getError()->getCode());
        $this->assertEquals("USER_PASSWORD_NOT_VALID", $um->create($this->getUserBag()->set('password', 'wrong'))->getError()->getCode());
        $this->assertEquals("USER_EMAIL_NOT_DEFINED", $um->create($this->getUserBag()->remove('email'))->getError()->getCode());
        $this->assertEquals("USER_EMAIL_NOT_VALID", $um->create($this->getUserBag()->set('email', 'wrong'))->getError()->getCode());

        # Testing correct
        $resource = $um->create($this->getUserBag())->getResource();
        $this->assertEquals($this->getUserBag()->get('username'), $resource->username);

        # Testing uniqueness
        $this->assertEquals("USER_EMAIL_NOT_UNIQUE", $um->create($this->getUserBag())->getErrors()->first()->getCode());

        $um->update($resource, $this->getUserBag());
        $um->remove($resource);


        # An admin can change username/email/password of all users
        # An user can change only his own information

        $user_admin = $um->create($this->getUserBag()->set('role', 'admin')->set('email', 'admin@test.net'))->getResource();
        $user_admin_manager = new UserManager($user_admin);

        $user = $um->create($this->getUserBag()->set('role', 'user')->set('email', 'user@test.net'))->getResource();
        $user_manager = new UserManager($user);


        $this->assertEquals(false, $user_manager->update($user_admin, new Bag(['email' => 'new@test.net']))->ok());
        $this->assertEquals(true, $user_manager->update($user, new Bag(['email' => 'new@test.net']))->ok());

    }


}