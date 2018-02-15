<?php

namespace Railken\Laravel\Manager\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Railken\Bag;
use Railken\Laravel\Manager\Agents\SystemAgent;
use Railken\Laravel\Manager\Tests\Core\Article\ArticleManager;
use Railken\Laravel\Manager\Tests\Core\Article\ArticleServiceProvider;
use Railken\Laravel\Manager\Tests\Core\Comment\CommentManager;
use Railken\Laravel\Manager\Tests\Core\Comment\CommentServiceProvider;
use Railken\Laravel\Manager\Tests\User\User;
use Railken\Laravel\Manager\Tests\User\UserManager;
use Railken\Laravel\Manager\Tests\User\UserServiceProvider;

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

    protected function getPackageProviders($app)
    {
        return [
            \Railken\Laravel\Manager\ManagerServiceProvider::class,
            UserServiceProvider::class,
            ArticleServiceProvider::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__."/..", '.env');
        $dotenv->load();

        parent::setUp();

        Schema::dropIfExists('comments');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('foo');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('notes')->nullable();
            $table->integer('author_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('author_id')->references('id')->on('users');
        });

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

    /**
     * Test basics
     */
    public function testBasics()
    {


        $um = new UserManager(new User());

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

        $um->findOneBy(['username' => 'test123']);
    }

    /** 
     * Test 
     */ 
    public function testArticles() 
    { 
        $um = new UserManager(new User()); 
        $user = $um->create(['email' => 'test1@test.net', 'username' => 'test1', 'password' => microtime()])->getResource(); 

        // $generator = new Generator();

        $user = new User(); 
        $am = new ArticleManager($user); 
 
        $ab = ['title' => 'foo', 'description' => 'bar', 'author_id' => $user->id]; 



        $this->assertEquals("ARTICLE_NOT_AUTHORIZED", $am->create($ab)->getError(0)->getCode());
        $this->assertEquals("ARTICLE_TITLE_NOT_AUTHTORIZED", $am->create($ab)->getError(1)->getCode());
        $this->assertEquals("ARTICLE_DESCRIPTION_NOT_AUTHTORIZED", $am->create($ab)->getError(2)->getCode());
        $user->addPermission('article.create');
        $user->addPermission('article.attributes.title.*');
        $user->addPermission('article.attributes.description.*');
        $user->addPermission('article.attributes.author_id.*');

        $this->assertEquals(1, $am->create($ab)->ok()); 
    } 
}
