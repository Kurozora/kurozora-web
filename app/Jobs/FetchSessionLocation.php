<?php

namespace App\Jobs;

use App\Models\SessionAttribute;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchSessionLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of tries.
     *
     * @var int $tries
     */
    public int $tries = 1;

    /**
     * The session object.
     *
     * @var SessionAttribute $sessionAttribute
     */
    private SessionAttribute $sessionAttribute;

    /**
     * Create a new job instance.
     *
     * @param SessionAttribute $sessionAttribute
     */
    public function __construct(SessionAttribute $sessionAttribute)
    {
        $this->sessionAttribute = $sessionAttribute;
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        // Get the IP info
        $data = $this->getDataFromAPI();

        // Add IP info to the session
        $this->sessionAttribute->city = $data?->city ?? null;
        $this->sessionAttribute->region = $data?->regionName ?? $data?->region ?? $data?->state ?? $data?->subdivision ?? null;
        $this->sessionAttribute->country = $data?->country ?? $data?->country_name ?? null;
        $this->sessionAttribute->latitude = $data?->lat ?? $data?->latitude ?? null;
        $this->sessionAttribute->longitude = $data?->lon ?? $data?->longitude ?? null;

        // Save changes
        $this->sessionAttribute->save();
    }

    /**
     * Queries the API for information regarding an IP address.
     *
     * @return mixed
     */
    private function getDataFromAPI(): mixed
    {
        $ip = $this->sessionAttribute->ip_address;
        $url = 'http://ip-api.com/json/' . $ip;

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true, // Needed to read response headers on 429 etc.
            ]
        ]);

        try {
            $response = file_get_contents($url, false, $context);

            if ($response === false || !isset($http_response_header)) {
                return null;
            }

            // Parse headers
            $headers = [];
            foreach ($http_response_header as $headerLine) {
                if (stripos($headerLine, ':') !== false) {
                    [$key, $value] = explode(':', $headerLine, 2);
                    $headers[trim($key)] = trim($value);
                } else if (stripos($headerLine, 'HTTP/') === 0) {
                    $headers['Http-Status'] = $headerLine;
                }
            }

            $statusCode = null;
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers['Http-Status'], $matches)) {
                $statusCode = (int) $matches[1];
            }

            $remaining = (int) ($headers['X-Rl'] ?? 1);

            if ($remaining === 0) {
                return null;
            }

            if ($statusCode === 429) {
                return null;
            }

            $decoded = json_decode($response, true);
            if (!$decoded) {
                return null;
            }

            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
