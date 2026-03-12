<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\VideoLinkChecker\VideoLinkCheckerService;

class CheckVideoLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:check-links {import_file : The path to the input CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically tests a list of video URLs and identifies broken or inaccessible links';

    /**
     * @var VideoLinkCheckerService
     */
    protected $checkerService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VideoLinkCheckerService $checkerService)
    {
        parent::__construct();
        $this->checkerService = $checkerService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = $this->argument('import_file');

        if (!file_exists($file)) {
            $this->error("The import file '{$file}' does not exist.");
            return 1; // Error
        }

        $this->info("Starting process for file: {$file}");

        // Open input file
        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Could not read the import file.");
            return 1;
        }

        // Output configuration
        $outputFile = 'video_link_report.csv';
        // Make sure the storage/app directory exists
        if (!Storage::disk('local')->exists($outputFile)) {
             Storage::disk('local')->put($outputFile, '');
        }
        
        $outputPath = storage_path('app/' . $outputFile);
        $outputHandle = fopen($outputPath, 'w');
        
        if ($outputHandle === false) {
            $this->error("Could not write to output file at storage/app/{$outputFile}");
            fclose($handle);
            return 1;
        }

        // Headers
        $headers = fgetcsv($handle);
        if (!$headers) {
             $this->error("No headers found in the generic CSV.");
             fclose($handle);
             fclose($outputHandle);
             return 1;
        }
        
        // Define indices based on requirements
        // Input variants: firstName/lastName/email/media_url OR title/video_url/target_value
        
        $firstNameIndex = array_search('firstName', $headers);
        $lastNameIndex = array_search('lastName', $headers);
        $emailIndex = array_search('email', $headers);
        $mediaUrlIndex = array_search('media_url', $headers);
        
        // Fallbacks for the Hindi CSV
        if ($mediaUrlIndex === false) {
             $mediaUrlIndex = array_search('video_url', $headers);
        }
        if ($firstNameIndex === false) {
             $firstNameIndex = array_search('title', $headers);
        }
        if ($emailIndex === false) {
             $emailIndex = array_search('target_value', $headers);
        }

        if ($mediaUrlIndex === false) {
             $this->error("Missing required URL column (media_url or video_url) in CSV.");
             fclose($handle);
             fclose($outputHandle);
             return 1;
        }

        // Write output header
        fputcsv($outputHandle, ['firstName', 'lastName', 'email', 'media_url', 'status']);

        $batch = [];
        $batchSize = 100;
        $processedCount = 0;

        $this->info("Processing URLs in batches of {$batchSize}...");
        $bar = $this->output->createProgressBar();
        $bar->start();

        while (($row = fgetcsv($handle)) !== false) {
            $batch[] = [
                'firstName' => $row[$firstNameIndex] ?? '',
                'lastName'  => $row[$lastNameIndex] ?? '',
                'email'     => $row[$emailIndex] ?? '',
                'media_url' => $row[$mediaUrlIndex] ?? '',
            ];

            if (count($batch) >= $batchSize) {
                $this->processBatch($batch, $outputHandle);
                $processedCount += count($batch);
                $bar->advance(count($batch));
                $batch = [];
            }
        }

        // Process remaining rows in final batch
        if (!empty($batch)) {
            $this->processBatch($batch, $outputHandle);
            $processedCount += count($batch);
            $bar->advance(count($batch));
        }

        $bar->finish();
        
        fclose($handle);
        fclose($outputHandle);

        $this->newLine();
        $this->info("Processed {$processedCount} rows successfully.");
        $this->info("Report saved to: storage/app/{$outputFile}");

        return 0; // Success
    }

    /**
     * Process a batch and write to output handle.
     */
    protected function processBatch(array $batch, $outputHandle)
    {
        $results = $this->checkerService->checkBatch($batch);

        foreach ($results as $result) {
            fputcsv($outputHandle, [
                $result['firstName'],
                $result['lastName'],
                $result['email'],
                $result['media_url'],
                $result['status']
            ]);
        }
    }
}
