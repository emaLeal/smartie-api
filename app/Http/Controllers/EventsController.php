<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Exception;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\CloudinaryController;
use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Traits\ApiExceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class EventsController
 * Handles the logic of the application to allow the frontend to get, create,
 * update or delete events in the application
 * @package App\Http\Controllers
 */
class EventsController extends Controller
{
    // Use the trait ApiExceptions to handle errors in any method
    use ApiExceptions;

    protected $cloudinary;

    /**
     * Constructor where dependency injection is used
     * to use the CloudinaryController class for handling
     * the files logic
     * @param CloudinaryController $cloudinary the CloudinaryController
     **/
    public function __construct(CloudinaryController $cloudinary) {
        $this->cloudinary = $cloudinary;
    }

    /**
     * List all events in the database, using a cache layer
     * with redis to store it the first time and display it faster
     * @return \Illuminate\Http\JsonResponse with the event data
     * @throws \Exception if an unexpected error occurs
     **/
    public function index(): JsonResponse {
        try {
            // If the cache event 'all_events' exist, will store it, if not, will make the db query
            // and store it in the event
            $events = Cache::remember('all_events', 3600, function () {
                return Events::all();
            });
            // Standarize the response using EventResource
            return EventResource::collection($events);
        } catch (Exception $e) {
            // Handles the error with the trait ApiExceptions
            return $this->handleException($e);
        }
    }

    /**
     * Returns one event using the id as query filter
     * @param int $id the event's id we're looking for
     * @return \Illuminate\Http\JsonResponse the event or the json of the error
     * @throws \Exception if an unexpected error occurs
     **/
    public function show(int $id): JsonResponse {
        try {
            // Try searching the cache event with the id and if not exists
            // makes the db query and store it in the cache layer
            $event = Cache::remember("event::$id", 3600, function () use($id) {
                return Events::findOrFail($id);
            });

            // Standarize the response using EventResource
            return new EventResource($event);
        } catch (Exception $e) {
            // Handles the error with the trait ApiExceptions
            return $this->handleException($e);
        }
    }

    /**
     * Creates an event after validating the user input
     * @param \App\Http\Requests\EventRequest $request the request with the body to validate
     * @return \Illuminate\Http\JsonResponse With the created event's data or the error
     * @throws \Illuminate\Validation\ValidationException If the user's request data is incorrect
     * @throws \Exception if an unexpected error occurs
     **/
    public function create(CreateEventRequest $request) {
        try {
            // Validate the user request and store it in the variable,
            // if the data is invalid, returns a ValidationException
            $data = $request->validated();

            // Process the images
            $data = $this->handleImageUploads($request, $data);

            // Create the event using the data
            $event = Events::create($data);

            // Reset the cache when the get methods are used
            Cache::forget('all_events');

            // Return the created event data
            return response()->json([
                'message' => 'Evento Creado',
                'evento' => new EventResource($event)
            ], 201);
        } catch (Exception $e) {
            // Handles unexpected errors
            return $this->handleException($e);
        }
    }

    /**
     * Function to update an event, and handles the logic regarding the images
     * @param int $id The event id to look for
     * @param \App\Http\Requests\UpdateEventRequest $request the request sent for the user
     * @return \Illuminate\Http\JsonResponse The Json to return the updated event or the error
     * @throws \Illuminate\Validation\ValidationException The data invalidated
     * @throws \Exception if an unexpected error occurs
     **/
    public function patch(int $id, UpdateEventRequest $request): JsonResponse {
        try {
            // Get the event instance using the id
            $event = Events::findOrFail($id);

            // Get the validated data from the user
            $data = $request->validated();

            // Handles the images
            $data = $this->handleImageUploads($request, $data, $event);

            // Update the event
            $event->update($data);

            // Reset the cache of both events
            Cache::forget('all_events');
            Cache::forget("event::$id");

            // Return the updated data
            return response()->json([
                'message' => 'Evento actualizado',
                'evento' => new EventResource($event)
            ], 200);
        } catch (Exception $e) {
            // Handle the unexpected errors
            return $this->handleException($e);
        }
    }

    /**
     *
     **/
    public function delete($id) {
        try {
            $event = Events::findOrFail($id);

            $filesToProcess = [
                'event_photo_public_id',
                'organization_photo_public_id'
            ];

            foreach($filesToProcess as $key) {
                $oldPublicId = $event->{$key};

                if ($oldPublicId)
                    $this->cloudinary->deleteImage($oldPublicId);
            }

            $event->delete();

            Cache::forget('all_events');
            Cache::forget("event::$id");

            return response()->noContent();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    /**
     * Private method to handle the logic to upload the
     * neccesary files
     * @param \Illuminate\Http\Request $request the request with the data uploaded for the user
     * @param array $data with the validated data to overwrite
     * @param \App\Models\Events $instance In case the instance has photos, delete them
     * @return the overwriten data
     * **/
    private function handleImageUploads(Request $request, array $data, Events $instance = null): array
    {
        // Set the files to process
        $filesToProcess = [
            'event_photo_url' => 'events',
            'organization_photo_url' => 'organization'
        ];

        // handles the upload of the images set
        foreach ($filesToProcess as $key => $folder) {
            // Checks if the file exist
            if ($request->hasFile($key)) {

                // If the instance exits and the user uploaded a new image
                // it will delete the existent images
                if ($instance) {
                    $publicIdKey = str_replace('_url', '_public_id', $key);
                    // extract the public id and uses it to delete the image
                    $oldPublicId = $instance->{$publicIdKey};

                    if ($oldPublicId)
                        $this->cloudinary->deleteImage($oldPublicId);
                }

                // Make the request
                $result = $this->
                    cloudinary->
                    uploadImage(
                        $request->file($key),
                        $folder
                    );
                // Save the secure url in the *_photo_url
                $data[$key] = $result['url'];

                // Makes the public_id replacing the original key
                $idKey = str_replace(
                    '_url',
                    '_public_id',
                    $key
                );
                // Save the public_id
                $data[$idKey] = $result['public_id'];
            }
        }
        // Return $data
        return $data;
    }
}
