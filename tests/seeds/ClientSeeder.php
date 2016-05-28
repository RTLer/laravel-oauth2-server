<?php
namespace Oauth2Tests\seeds;

use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\ClientModel;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClientModel::insert([
            '_id' => 'foo',
//            'grant_type' => str_random(10).'@gmail.com',
            'secret' => 'bar',
            'name' => 'foo_client',
            'redirect_uri' => 'http://foo/bar',
//            'scopes' => bcrypt('secret'),
        ]);
    }
}
