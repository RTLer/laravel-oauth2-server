<?php
namespace Oauth2Tests\seeds;

use Carbon\CarbonInterval;
use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\AccessTokenModel;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccessTokenModel::insert([
            'token' => str_random(10),
            'session_id' => 'test',
            'expire_time' => CarbonInterval::day(),
        ]);

        AccessTokenModel::insert([
            'token' => 'AccessTokenFoo',
            'session_id' => 'SessionFoo',
            'expire_time' => CarbonInterval::day(),
        ]);
    }
}
