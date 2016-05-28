<?php
namespace Oauth2Tests\seeds;

use Carbon\CarbonInterval;
use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\AuthCodeModel;
use RTLer\Oauth2\Models\ClientModel;

class AuthCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AuthCodeModel::insert([
            'token' => 'testAuthCode',
            'client_id' => 'foo',
            'expire_time' => CarbonInterval::day(),
        ]);
        AuthCodeModel::insert([
            'token' => 'testAuthCodeExpired',
            'client_id' => 'foo',
            'expire_time' => CarbonInterval::sub(CarbonInterval::day()),
        ]);
    }
}
