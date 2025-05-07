<?php

namespace Tests;

use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kirschbaum\Commentions\CommentionsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Models\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        config()->set('app.key', '7xE3Nz29bGRceBATftriyTuiYF7DcOjb');
        config()->set('commentions.commenter.model', User::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            CommentionsServiceProvider::class,
            FilamentServiceProvider::class,
            SupportServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        Schema::dropAllTables();

        $migrations = [
            __DIR__ . '/../database/migrations/create_commentions_tables.php.stub',
            __DIR__ . '/../database/migrations/create_commentions_reactions_table.php.stub',
        ];

        foreach ($migrations as $migration) {
            $migration = include $migration;

            $migration->up();
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }
}
