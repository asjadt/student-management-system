<?php

namespace App\Console\Commands;

use App\Http\Utils\SetupUtil;
use Illuminate\Console\Command;

class RoleRefresh extends Command
{
    use SetupUtil;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh roles and permissions from setup config';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        log_message('Role refresh started.', 'role_refresh.log');

        $this->roleRefreshFunc();

        log_message('Role refresh started.', 'role_refresh.log');
    }
}
