<?php

namespace App\Jobs;

use App\Models\Session;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchSessionLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    private $session;

    /**
     * Create a new job instance.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
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
        $this->session->city = (isset($data->city)) ? $data->city : null;
        $this->session->region = (isset($data->region)) ? $data->region : null;
        $this->session->country = (isset($data->country)) ? $data->country : null;

        if ($coordinates = $this->getCoordinates($data)) {
            $this->session->latitude = $coordinates['lat'];
            $this->session->longitude = $coordinates['lon'];
        }

        // Save the session
        $this->session->save();
    }

    /**
     * Queries the API for information regarding an IP address.
     *
     * @return mixed
     * @throws Exception
     */
    private function getDataFromAPI() {
        // Get the IP in question and query the API
        $ip = $this->session->ip;

        $rawContent = file_get_contents('https://ipinfo.io/' . $ip . '/json');

        // Attempt to decode the content
        $decodedResponse = json_decode($rawContent);

        // If the content could not be decoded throw an exception
        if (!$decodedResponse) throw new Exception("Could not get IP info for IP: " . $ip);

        return $decodedResponse;
    }

    /**
     * Gets the latitude and longitude from a delimited string.
     *
     * @param $data
     * @return array|null
     */
    private function getCoordinates($data) {
        if (!isset($data->loc) || !is_string($data->loc))
            return null;

        // Explode the string
        $coords = explode(',', $data->loc);

        // Return null if the string wasn't properly split
        if (!count($coords))
            return null;

        return [
            'lat' => $coords[0],
            'lon' => $coords[1],
        ];
    }
}
