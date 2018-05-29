<?php

namespace Railken\Laravel\Manager\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Railken\Bag;
use Railken\Laravel\Manager\Generator;
use Railken\Laravel\Manager\Tests\Generated\Foo\FooManager;
use Railken\Laravel\Manager\Tests\Generated\Foo\FooServiceProvider;
use Railken\Laravel\Manager\Tests\User\User;

class GeneratedTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AppServiceProvider::class,
            \Railken\Laravel\Manager\ManagerServiceProvider::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/..', '.env');
        $dotenv->load();

        parent::setUp();

        Schema::dropIfExists('foo');

        Schema::create('foo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Return a new instance of user bag.
     *
     * @return Bag
     */
    public function getUserBag()
    {
        return new Bag(['email' => 'test@test.net', 'username' => 'test123', 'password' => microtime()]);
    }

    /**
     * Test generate Command.
     */
    public function testGenerate()
    {
        $generator = new Generator();

        File::deleteDirectory(__DIR__.'/Generated/Foo', true);

        $generator->generate(__DIR__.'/Generated/Foo', "Railken\Laravel\Manager\Tests\Generated\Foo");

        $generator->generateAttribute(__DIR__.'/Core/Article', "Railken\Laravel\Manager\Tests\Core\Article", 'deleted_at');

        $this->assertEquals(true, File::exists(__DIR__.'/Generated/Foo'));
        (new FooServiceProvider($this->app))->register();

        $user = new User();
        $m = new FooManager($user);

        $bag = new Bag(['name' => 'ban']);

        $this->assertEquals('FOO_NOT_AUTHORIZED', $m->create($bag)->getError()->getCode());

        $user->addPermission('foo.*');

        $foo = $m->create($bag->set('name', 'baar'))->getResource();
        $m->update($foo, $bag->set('name', 'fee'))->getResource();

        $foo_s = $m->getRepository()->findOneBy(['name' => 'fee']);

        $this->assertEquals($foo->id, $foo_s->id);
    }
}
