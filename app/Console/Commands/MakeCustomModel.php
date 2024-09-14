<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeCustomModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:custom-model {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model with migrations in both the migrations and business folders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Create the model with a migration
        Artisan::call("make:model $name --migration");

        // Convert the model name to snake_case and generate the migration name
        $snakeName = Str::snake(Str::pluralStudly($name));
        $migrationName = 'create_' . $snakeName . '_table';

        // Create a migration inside the default migrations folder
        Artisan::call("make:migration $migrationName");

        // Create a second migration in the business folder
        $this->createBusinessFolderMigration($migrationName);

        $this->info("Model and migrations created in both default and business folders.");
        return 0;
    }

    /**
     * Create a migration file inside the business folder.
     *
     * @param string $migrationName
     * @return void
     */
    protected function createBusinessFolderMigration($migrationName)
    {
        $filesystem = new Filesystem();

        // Get the path of the generated migration
        $defaultMigrationPath = database_path('migrations');
        $migrationFiles = $filesystem->files($defaultMigrationPath);
        $migrationFileName = null;

        // Find the recently created migration file for the model
        foreach ($migrationFiles as $file) {
            if (strpos($file->getFilename(), $migrationName) !== false) {
                $migrationFileName = $file->getFilename();
                break;
            }
        }

        if ($migrationFileName) {
            // Define the path to the business folder
            $businessFolderPath = database_path('business_migrations');

            // Create the business folder if it doesn't exist
            if (!$filesystem->exists($businessFolderPath)) {
                $filesystem->makeDirectory($businessFolderPath, 0755, true);
            }

            // Copy the migration file to the business folder
            $filesystem->copy(
                $defaultMigrationPath . '/' . $migrationFileName,
                $businessFolderPath . '/' . $migrationFileName
            );

            $this->info("Migration copied to business folder: $businessFolderPath");
        } else {
            $this->error("Could not find the migration file for $migrationName");
        }
    }
}
