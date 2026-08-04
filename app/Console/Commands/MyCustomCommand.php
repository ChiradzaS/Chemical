<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MyCustomCommand extends Command
{
    // Command signature, this defines what the user will type to run the command
    protected $signature = 'my:custom-command'; // <-- Check this line

    // Description of the command
    protected $description = 'This is my custom command';

    protected $commands = [
        \App\Console\Commands\MyCustomCommand::class,
    ];
    

    // Handle method to execute the command logic
    public function handle()
    {

        try {

                $disk = Storage::disk('ftp'); // FTP disk configuration

                // Specify the local directory where backups are stored
                $localDirectory = storage_path('/app/Laravel/temp/db-dumps');

                // Check if the directory exists
                if (!is_dir($localDirectory)) {
                    throw new \Exception("Local directory '{$localDirectory}' does not exist.");
                }

                // Use glob to find all files in the directory
                $files = glob($localDirectory . '/*');
                if (!$files) {
                    throw new \Exception("No files found in the directory '{$localDirectory}'.");
                }

                // Get the last modified file
                $lastFile = array_reduce(
                    $files,
                    function ($latestFile, $currentFile) {
                        return filemtime($currentFile) > filemtime($latestFile) ? $currentFile : $latestFile;
                    }
                );

                $fileName = basename($lastFile); // Extract the file name
                $remoteFilePath = 'public_html/localDatabaseBackup' . $fileName; // Target FTP path

                // Upload the last modified file to FTP
                if ($disk->put($remoteFilePath, file_get_contents($lastFile))) {
                    echo "File '{$fileName}' uploaded successfully to FTP server.";
                } else {
                    echo "File upload failed.";
                }
                } catch (\Exception $e) {
                echo "An error occurred: " . $e->getMessage();

            }
    }


}
