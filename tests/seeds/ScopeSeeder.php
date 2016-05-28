<?php
namespace Oauth2Tests\seeds;

use Illuminate\Database\Seeder;
use RTLer\Oauth2\Models\ScopeModel;

class ScopeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ScopeModel::insert([
            '_id' => 'foo',
            'description' => 'this is foo scope'
        ]);
    }
}
