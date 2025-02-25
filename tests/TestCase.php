<?php

namespace Tests;

use Tests\Models\User;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Kirschbaum\Commentions\CommentionsServiceProvider;

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
            FilamentServiceProvider::class,
            LivewireServiceProvider::class,
            CommentionsServiceProvider::class,
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

        $migration = include __DIR__.'/../database/migrations/create_filament_comment_tables.php.stub';

        $migration->up();

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
