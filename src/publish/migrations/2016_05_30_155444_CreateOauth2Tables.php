<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauth2Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('token')->unique();
            $table->timestamp('expire_time')->nullable();
            $table->timestamps();
        });
        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('token')->unique();
            $table->string('client_id')->nullable();
            $table->timestamp('expire_time')->nullable();
            $table->timestamps();
        });
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->primary('id');
            $table->string('grant_type');
            $table->string('secret');
            $table->string('name');
            $table->string('redirect_uri');
            $table->string('scopes');
            $table->timestamps();
        });
        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('token')->unique();
            $table->string('access_token_id');
            $table->timestamp('expire_time')->nullable();
            $table->timestamps();
        });

        Schema::create('oauth_scopes', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->primary('id');
            $table->string('description');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_access_tokens');
        Schema::drop('oauth_auth_codes');
        Schema::drop('oauth_clients');
        Schema::drop('oauth_refresh_tokens');
        Schema::drop('oauth_scopes');
    }
}
