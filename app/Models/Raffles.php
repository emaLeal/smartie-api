<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raffles extends Model
{
    protected $fillable = [
        'name',
        'is_played',
        'price',
        'price_photo',
        'has_questions',
        'winner_id',
        'winner_name',
        'events_id'
    ];
}
