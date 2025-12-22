<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaffleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'price_photo' => $this->price_photo_url,
            'has_questions' => $this->has_questions,
            'winner_id' => $this->winner_id,
            'winner_name' => $this->winner_name,
            'event_id' => $this->events_id
        ];
    }
}
