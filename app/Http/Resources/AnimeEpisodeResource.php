<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AnimeEpisodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request)
    {
        $firstAiredUnix = Carbon::parse($this->first_aired);
        $formattedFirstAired = $firstAiredUnix->format('j M, Y');

        $resource = [
            'id'            => $this->id,
            'number'        => $this->number,
            'name'          => $this->name,
            'first_aired'   => $formattedFirstAired,
            'overview'      => $this->overview,
            'verified'      => (bool) $this->verified
        ];

        if(Auth::check())
            $resource = array_merge($resource, $this->getUserSpecificDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails() {
        $user = Auth::user();

        // Return the array
        return [
            'current_user' => [
                'watched'    => $user->watchedAnimeEpisodes()->where('episode_id', $this->id)->exists()
            ]
        ];
    }
}
