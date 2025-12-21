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
        'event_photo_url',
        'event_photo_public_id',
        'organization_photo',
        'organization_photo_url',
        'organization_photo_public_id'
    ];


}
