<?php

namespace Oauth2Tests\seeds;

use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\ClientModel;
use RTLer\Oauth2\Models\ModelResolver;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modelResolver = new ModelResolver(config('oauth2.database_type'));
        $model = $modelResolver->getModel('ClientModel');

        $model::insert([
            '_id' => 'foo',
//            'grant_type' => str_random(10).'@gmail.com',
            'secret'       => 'bar',
            'name'         => 'foo_client',
            'redirect_uri' => 'http://foo/bar',
//            'scopes' => bcrypt('secret'),
        ]);
    }
}
