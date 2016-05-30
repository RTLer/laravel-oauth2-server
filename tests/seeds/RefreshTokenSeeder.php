<?php

namespace Oauth2Tests\seeds;

use Carbon\CarbonInterval;
use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\ModelResolver;
use RTLer\Oauth2\Models\RefreshTokenModel;

class RefreshTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modelResolver = new ModelResolver(config('oauth2.database_type'));
        $model = $modelResolver->getModel('RefreshTokenModel');

        $model::insert([
            'token'           => 'RefreshTokenFoo',
            'access_token_id' => 'AccessTokenFoo',
            'expire_time'     => CarbonInterval::day(),
        ]);
    }
}
