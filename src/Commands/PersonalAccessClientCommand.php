<?php

namespace RTLer\Oauth2\Commands;

use Illuminate\Console\Command;
use RTLer\Oauth2\Models\ModelResolver;
use RTLer\Oauth2\Oauth2Server;

class PersonalAccessClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth2:personal-access-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a client for personal access';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $type = app()->make(Oauth2Server::class)
            ->getOptions()['database_type'];
        $modelResolver = new ModelResolver($type);
        $clientModel = $modelResolver->getModel('ClientModel');
        $clientModel->create([
            'grant_type' => 'personal_access',
            'name'       => 'personal_access_client',
            'secret'     => 'secret',
        ]);

        $this->info('Personal access client created successfully.');
    }
}
