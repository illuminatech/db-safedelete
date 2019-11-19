<?php

namespace Illuminatech\DbSafeDelete\Test;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Contracts\Container\Container test application instance.
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->createApplication();

        $db = new Manager;

        $db->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        Model::clearBootedModels();
        Model::setEventDispatcher($this->app->make('events'));

        $this->createSchema();
        $this->seedData();
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return Model::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema(): void
    {
        $this->getConnection()->statement('PRAGMA foreign_keys = ON;');

        $this->getSchemaBuilder()->create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->decimal('price');
            $table->softDeletes();
        });

        $this->getSchemaBuilder()->create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->string('invoice_number');
            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Seeds the database with test data.
     *
     * @return void
     */
    protected function seedData(): void
    {
        $this->getConnection()->table('items')->insert([
            [
                'name' => 'item-1',
                'price' => 10,
            ],
            [
                'name' => 'item-2',
                'price' => 20,
            ],
        ]);

        $this->getConnection()->table('purchases')->insert([
            [
                'item_id' => 1,
                'invoice_number' => '1111',
            ],
        ]);
    }

    /**
     * Creates dummy application instance, ensuring facades functioning.
     */
    protected function createApplication()
    {
        $this->app = Container::getInstance();

        Facade::setFacadeApplication($this->app);

        $this->app->singleton('events', function ($app) {
            return new Dispatcher($app);
        });
    }
}
