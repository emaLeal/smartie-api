<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    //
    protected $fillable = [
        'name',
        'document_id',
        'position',
        'email',
        'photo',
        'is_active',
        'has_accepted',
        'events_id'
    ];
}
