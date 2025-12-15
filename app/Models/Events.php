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

    /**
     * Accesor para obtener la URL completa de la foto del evento
     */
    protected function eventPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->event_photo) {
                    return null;
                }
                return asset('storage/' . $this->event_photo);
            }
        );
    }

    /**
     * Accesor para obtener la URL completa de la foto de la organización
     */
    protected function organizationPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->organization_photo) {
                    return null;
                }
                return asset('storage/' . $this->organization_photo);
            }
        );
    }

/**
     * Accesor para obtener la ruta completa del sistema de archivos
     */
    protected function eventPhotoPath(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->event_photo) {
                    return null;
                }
                return storage_path('app/public/' . $this->event_photo);
            }
        );
    }

    protected function organizationPhotoPath(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->organization_photo) {
                    return null;
                }
                return storage_path('app/public/' . $this->organization_photo);
            }
        );
    }

}
