<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'organization',
        'event_photo',
        'organization_photo',
    ];


}
