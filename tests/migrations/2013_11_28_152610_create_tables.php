<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class CreateTables extends Migration
{
    /** @var Builder */
    protected $schema;

    /** @var Migrator */
    protected $migrator;

    public function __construct()
    {
        $this->migrator = app('migrator');
        $this->schema = Schema::connection($this->migrator->getConnection());
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->schema->create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->schema->create('country_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['country_id', 'locale']);
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });

        $this->schema->create('vegetables', function (Blueprint $table) {
            $table->increments('identity');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        $this->schema->create('vegetable_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vegetable_identity')->unsigned();
            $table->string('name')->nullable();
            $table->string('locale')->index();

            $table->unique(['vegetable_identity', 'locale']);
            $table->foreign('vegetable_identity')->references('identity')->on('vegetables');
        });

        $this->schema->create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        $this->schema->create('person_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('person_id')->unsigned();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['person_id', 'locale']);
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->schema->dropIfExists('city_translations');
        $this->schema->dropIfExists('cities');

        $this->schema->dropIfExists('country_translations');
        $this->schema->dropIfExists('countries');

        $this->schema->dropIfExists('vegetable_translations');
        $this->schema->dropIfExists('vegetables');

        $this->schema->dropIfExists('person_translations');
        $this->schema->dropIfExists('people');
    }
}
