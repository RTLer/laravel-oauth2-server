<?php
namespace Oauth2Tests\seeds;

use Carbon\CarbonInterval;
use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\AccessTokenModel;
use RTLer\Oauth2\Models\ModelResolver;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modelResolver = new ModelResolver(config('oauth2.database_type'));
        $model = $modelResolver->getModel('AccessTokenModel');
        $model::insert([
            'token' => str_random(10),
            'expire_time' => CarbonInterval::day(),
        ]);
        $model::getModel('AccessTokenModel')->insert([
            'token' => 'AccessTokenFoo',
            'expire_time' => CarbonInterval::day(),
        ]);
    }
}
