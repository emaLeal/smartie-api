<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    //
    protected $fillable = [
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'correct_option',
        'raffles_id'
    ];
}
