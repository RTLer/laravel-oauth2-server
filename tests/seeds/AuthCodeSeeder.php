<?php
namespace Oauth2Tests\seeds;

use Carbon\CarbonInterval;
use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\AuthCodeModel;
use RTLer\Oauth2\Models\ModelResolver;

class AuthCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modelResolver = new ModelResolver(config('oauth2.database_type'));
        $model = $modelResolver->getModel('AuthCodeModel');

        $model::insert([
            'token' => 'testAuthCode',
            'client_id' => 'foo',
            'expire_time' => CarbonInterval::day(),
        ]);
        $model::insert([
            'token' => 'testAuthCodeExpired',
            'client_id' => 'foo',
            'expire_time' => CarbonInterval::sub(CarbonInterval::day()),
        ]);
        $model::insert([
            'token' => 'testAuthCodeForBaz',
            'client_id' => 'baz',
            'expire_time' => CarbonInterval::sub(CarbonInterval::day()),
        ]);
    }
}
