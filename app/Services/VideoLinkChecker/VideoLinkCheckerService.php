<?php

namespace App\Services\VideoLinkChecker;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoLinkCheckerService
{
    /**
     * Check a batch of video URLs concurrently.
     *
     * @param array $items Array of data items containing 'media_url'.
     * @return array Array of original items with an added 'status' key.
     */
    public function checkBatch(array $items): array
    {
        $responses = Http::pool(function (Pool $pool) use ($items) {
            $requests = [];
            foreach ($items as $index => $item) {
                $url = $item['media_url'] ?? $item['video_url'] ?? null;
                if ($url) {
                    $requests[] = $pool->as((string) $index)->timeout(10)->head($url);
                }
            }
            return $requests;
        });

        $results = [];

        foreach ($items as $index => $item) {
            $url = $item['media_url'] ?? $item['video_url'] ?? null;
            
            if (empty($url)) {
                $item['status'] = 'ERROR';
                $results[] = $item;
                continue;
            }

            $response = $responses[(string) $index] ?? null;

            if ($response instanceof \Illuminate\Http\Client\Response) {
                $status = $response->status();
                
                if ($response->successful()) {
                    $item['status'] = 'OK';
                } elseif ($status === 403) {
                    $item['status'] = 'ACCESS_DENIED';
                    $this->logFailure($url, $status, 'Access Denied');
                } elseif ($status === 404) {
                    $item['status'] = 'NOT_FOUND';
                    $this->logFailure($url, $status, 'Not Found');
                } else {
                    $item['status'] = 'ERROR';
                    $this->logFailure($url, $status, 'HTTP Error');
                }
            } elseif ($response instanceof \Exception || $response === null) {
                $errorMsg = $response ? $response->getMessage() : 'Unknown Error';
                if (str_contains(strtolower($errorMsg), 'timeout') || str_contains(strtolower($errorMsg), 'resolving')) {
                    $item['status'] = 'TIMEOUT';
                } else {
                    $item['status'] = 'ERROR';
                }
                
                $this->logFailure($url, 'Exception', $errorMsg);
            }

            $results[] = $item;
        }

        return $results;
    }

    /**
     * Process a CSV file and return the results as an array.
     */
    public function processCsv($filePath): array
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        $firstNameIndex = array_search('firstName', $headers);
        if ($firstNameIndex === false) $firstNameIndex = array_search('title', $headers);
        
        $lastNameIndex = array_search('lastName', $headers);
        
        $emailIndex = array_search('email', $headers);
        
        $mediaUrlIndex = array_search('media_url', $headers);
        if ($mediaUrlIndex === false) $mediaUrlIndex = array_search('video_url', $headers);
        if ($mediaUrlIndex === false) $mediaUrlIndex = array_search('target_value', $headers);

        $results = [];
        $batch = [];
        $batchSize = 50; // Lower batch size for web to avoid timeouts

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;

            $batch[] = [
                'firstName' => $row[$firstNameIndex] ?? '',
                'lastName' => $lastNameIndex !== false ? ($row[$lastNameIndex] ?? '') : '',
                'email' => $emailIndex !== false ? ($row[$emailIndex] ?? '') : '',
                'media_url' => $mediaUrlIndex !== false ? ($row[$mediaUrlIndex] ?? '') : '',
            ];

            if (count($batch) >= $batchSize) {
                $results = array_merge($results, $this->checkBatch($batch));
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $results = array_merge($results, $this->checkBatch($batch));
        }

        fclose($handle);
        return $results;
    }

    /**
     * Log a failed URL check.
     *
     * @param string $url
     * @param mixed $code
     * @param string $message
     * @return void
     */
    protected function logFailure(string $url, $code, string $message): void
    {
        Log::error("Video URL check failed", [
            'url' => $url,
            'code' => $code,
            'message' => $message,
        ]);
    }
}
