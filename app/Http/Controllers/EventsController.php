<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Exception;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\CloudinaryController;
use App\Http\Resources\EventResource;
use App\Http\Traits\ApiExceptions;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    use ApiExceptions;

    protected $cloudinary;

    public function __construct(CloudinaryController $cloudinary) {
        $this->cloudinary = $cloudinary;
    }

    public function index() {
        try {
            $events = Cache::remember('all_events', 3600, function () {
                return Events::all();
            });

            return EventResource::collection($events);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id) {
        try {
            $event = Cache::remember("event::$id", 3600, function () use($id) {
                return Events::findOrFail($id);
            });

            return new EventResource($event);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function create(Request $request) {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'organization' => 'required|string',
                'event_photo_url' => 'nullable|image|max:2048',
                'organization_photo_url' => 'nullable|image|max:2048'
            ]);

            if ($request->hasFile('event_photo_url')) {
                $result = $this->cloudinary->uploadImage(
                    $request->file('event_photo_url'),
                    'events'
                );
                $data['event_photo_url'] = $result['url'];
                $data['event_photo_public_id'] = $result['public_id'];
            }

            if ($request->hasFile('organization_photo_url')) {
                $result = $this->cloudinary->uploadImage(
                    $request->file('organization_photo_url'),
                    'organization'
                );
                $data['organization_photo_url'] = $result['url'];
                $data['organization_photo_public_id'] = $result['public_id'];
            }
            $event = Events::create($data);

            Cache::forget('all_events');

            return response()->json([
                'message' => 'Evento Creado',
                'evento' => new EventResource($event)
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function patch($id, Request $request) {
        try {
            $event = Events::findOrFail($id);

            $data = $request->validate([
                'name' => 'sometimes|string',
                'organization' => 'sometimes|string',
                'event_photo_url' => 'nullable|image|max:2048',
                'organization_photo_url' => 'nullable|image|max:2048'
            ]);

            if ($request->hasFile('event_photo_url')) {
                if ($event->event_photo_public_id) {
                    $this->cloudinary->deleteImage($event->event_photo_public_id);
                }
                $result = $this->cloudinary->uploadImage(
                    $request->file('event_photo_url'),
                    'events'
                );

                $data['event_photo_url'] = $result['url'];
                $data['event_photo_public_id'] = $result['public_id'];
            }

            if ($request->hasFile('organization_photo_url')) {
                if ($event->organization_photo_public_id) {
                    $this->cloudinary->deleteImage($event->organization_photo_public_id);
                }
                $result = $this->cloudinary->uploadImage(
                    $request->file('organization_photo_url'),
                    'organization'
                );

                $data['organization_photo_url'] = $result['url'];
                $data['organization_photo_public_id'] = $result['public_id'];
            }

            $event->update($data);
            Cache::forget('all_events');
            Cache::forget("event::$id");
            return response()->json([
                'message' => 'Evento actualizado',
                'evento' => new EventResource($event)
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function delete($id) {
        try {
            $event = Events::findOrFail($id);

            if ($event->event_photo_public_id) {
                $this->cloudinary->deleteImage($event->event_photo_public_id);
            }

            if ($event->organization_photo_public_id) {
                $this->cloudinary->deleteImage($event->organization_photo_public_id);
            }

            $event->delete();

            Cache::forget('all_events');
            Cache::forget("event::$id");

            return response()->noContent();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
