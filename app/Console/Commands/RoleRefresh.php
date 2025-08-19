<?php

namespace App\Console\Commands;

use App\Http\Controllers\SetUpController;
use Illuminate\Console\Command;

class RoleRefresh extends Command
{
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
        $this->info("Refreshing roles...");
        app(SetUpController::class)->roleRefreshFunc();
        $this->info("Role refresh Done.");
    }
}
