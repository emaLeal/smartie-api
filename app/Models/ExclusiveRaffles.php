<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExclusiveRaffles extends Model
{
    //
    protected $fillable = [
        'participant_id',
        'raffle_id'
    ];
}
