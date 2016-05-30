<?php

namespace Oauth2Tests\seeds;

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (\App::environment() == 'production') {
            die;
        }

        $this->call(ClientSeeder::class);
//        $this->call(GrantsTableSeeder::class);
        $this->call(ScopeSeeder::class);
//        $this->call(SessionsTableSeeder::class);
        $this->call(AuthCodeSeeder::class);
        $this->call(AccessTokenSeeder::class);
        $this->call(RefreshTokenSeeder::class);
    }
}
