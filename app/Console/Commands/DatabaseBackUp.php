<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use File;

class DatabaseBackUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create copy of mysql dump for existing database and upload it to FTP.';

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
        $filename = "chemicalbackup". ".sql";

        // Create backup folder if it doesn't exist.
        $storageAt = storage_path() . "/app/Laravel/temp/db-dumps";
        if (!File::exists($storageAt)) {
            File::makeDirectory($storageAt, 0755, true, true);
        }

        $fullPath = $storageAt . '/' . $filename; // fixed missing separator

        $command = "" . env('DB_DUMP_PATH', 'C:\xampp\mysql\bin\mysqldump')
            . " --user=" . env('DB_USERNAME')
            . " --password=" . env('DB_PASSWORD')
            . " --host=" . env('DB_HOST')
            . " " . env('DB_DATABASE')
            . " > " . $fullPath; // dropped the gzip pipe — unsupported on plain Windows/XAMPP

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Backup failed: ' . implode("\n", $output));
            return 1;
        }

        // Sanity check: make sure the dump file actually exists and isn't empty
        if (!File::exists($fullPath) || File::size($fullPath) === 0) {
            $this->error("Backup file was not created or is empty: {$fullPath}");
            return 1;
        }

        $this->info("Backup created: {$filename}");

        // Only upload if the dump succeeded
        $this->call('my:custom-command');

        return 0;
    }
}