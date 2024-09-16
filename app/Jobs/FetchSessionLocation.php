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
    public function handle()
    {
        // Get the IP info
        $data = $this->getDataFromAPI();

        // Add IP info to the session
        $this->sessionAttribute->city = $data->city ?? null;
        $this->sessionAttribute->region = $data->region ?? null;
        $this->sessionAttribute->country = $data->country ?? null;

        if ($coordinates = $this->getCoordinates($data)) {
            $this->sessionAttribute->latitude = $coordinates['lat'];
            $this->sessionAttribute->longitude = $coordinates['lon'];
        }

        // Save changes
        $this->sessionAttribute->save();
    }

    /**
     * Queries the API for information regarding an IP address.
     *
     * @return mixed
     * @throws Exception
     */
    private function getDataFromAPI(): mixed
    {
        // Get the IP in question and query the API
        $ip = $this->sessionAttribute->ip_address;
        $rawContent = file_get_contents('https://ipinfo.io/' . $ip . '/json');

        // Attempt to decode the content
        $decodedResponse = json_decode($rawContent);

        // If the content could not be decoded throw an exception
        if (!$decodedResponse) {
            throw new Exception("Could not get IP info for IP: " . $ip);
        }

        return $decodedResponse;
    }

    /**
     * Gets the latitude and longitude from a delimited string.
     *
     * @param $data
     * @return array|null
     */
    private function getCoordinates($data): ?array
    {
        if (!isset($data->loc) || !is_string($data->loc)) {
            return null;
        }

        // Explode the string
        $coords = explode(',', $data->loc);

        // Return null if the string wasn't properly split
        if (!count($coords)) {
            return null;
        }

        return [
            'lat' => $coords[0],
            'lon' => $coords[1],
        ];
    }
}
