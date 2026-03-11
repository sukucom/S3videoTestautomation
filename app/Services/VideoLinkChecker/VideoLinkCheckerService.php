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
                // Perform a HEAD request to save bandwidth, fallback to GET if needed by the server
                // We'll use GET here as some servers block head requests or return misleading statuses.
                // We set a timeout of 10 seconds.
                $url = $item['media_url'];
                if ($url) {
                    $requests[] = $pool->as((string) $index)->timeout(10)->get($url);
                }
            }
            return $requests;
        });

        $results = [];

        foreach ($items as $index => $item) {
            $url = $item['media_url'];
            
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
            } elseif ($response instanceof \Exception) {
                // If it's a specific connection/timeout exception
                $errorMsg = $response->getMessage();
                if (str_contains(strtolower($errorMsg), 'timeout') || str_contains(strtolower($errorMsg), 'resolving')) {
                    $item['status'] = 'TIMEOUT';
                } else {
                    $item['status'] = 'ERROR';
                }
                
                $this->logFailure($url, 'Exception', $errorMsg);
            } else {
                $item['status'] = 'ERROR';
                $this->logFailure($url, 'Unknown', 'No response received');
            }

            $results[] = $item;
        }

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
