<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\Migration;

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

        $this->schema->create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on('countries');
        });

        $this->schema->create('city_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('city_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('locale')->index();

            $table->unique(['city_id', 'locale']);
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });

        $this->schema->create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        $this->schema->create('continents', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        $this->schema->create('foods', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        $this->schema->create('food_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('food_id')->unsigned();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['food_id', 'locale']);
            $table->foreign('food_id')->references('id')->on('foods')->onDelete('cascade');
        });

        $this->schema->create('continent_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('continent_id')->unsigned();
            $table->string('name');
            $table->string('locale')->index();
            $table->timestamps();
        });

        $this->schema->create('vegetables', function (Blueprint $table) {
            $table->increments('identity');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        $this->schema->create('vegetable_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vegetable_identity')->unsigned();
            $table->string('name');
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
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
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

        $this->schema->dropIfExists('companies');

        $this->schema->dropIfExists('continent_translations');
        $this->schema->dropIfExists('continents');
        $this->schema->dropIfExists('food_translations');
        $this->schema->dropIfExists('foods');
        $this->schema->dropIfExists('vegetable_translations');
        $this->schema->dropIfExists('vegetables');
        $this->schema->dropIfExists('person_translations');
        $this->schema->dropIfExists('people');
    }
}
