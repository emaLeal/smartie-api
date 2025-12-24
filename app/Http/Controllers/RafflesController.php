<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaffleResource;
use App\Models\Raffles;
use App\Traits\ApiExceptions;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RafflesController extends Controller
{
    use ApiExceptions;

    protected $cloudinary;

    public function __construct(CloudinaryController $cloudinary) {
        $this->cloudinary = $cloudinary;
    }

    public function index() {
        try {
            $raffles = Cache::remember('all_raffles', 3600, function() {
                return Raffles::all();
            });

            return RaffleResource::collection($raffles);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id) {
        try {
            $raffle = Cache::remember("raffle::$id", 3600, function () use($id) {
                return Raffles::findOrFail($id);
            });

            return new RaffleResource($raffle);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function create(Request $request) {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'is_played' => 'sometimes|boolean',
                'price' => 'required|string',
                'price_photo_url' => 'nullable|image|max:2048',
                'has_questions' => 'nullable|bool',
                'winner_id' => 'nullable|integer|exists:participants,id',
                'winner_name' => 'nullable|string',
                'events_id' => 'required|integer|exists:events,id'
            ]);

            if ($request->hasFile('price_photo_url')) {
                $result = $this->cloudinary->uploadImage(
                    $request->file('price_photo_url'),
                    'raffles_prices'
                );
                $data['price_photo_url'] = $result['url'];
                $data['price_photo_public_id'] = $result['public_id'];
            }

            $raffle = Raffles::create($data);

            Cache::forget('all_raffles');

            return response()->json([
                'message' => 'Sorteo Creado',
                'sorteo' => new RaffleResource($raffle)
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function patch($id, Request $request) {
        try {
            $raffle = Raffles::findOrFail($id);

            $data = $request->validate([
                'name' => 'sometimes|string',
                'is_played' => 'sometimes|boolean',
                'price' => 'sometimes|string',
                'price_photo_url' => 'sometimes|image|max:2048',
                'has_questions' => 'sometimes|bool',
                'winner_id' => 'nullable|integer|exists:participants,id',
                'winner_name' => 'nullable|string',
                'events_id' => 'sometimes|integer|exists:events,id'
            ]);

            if ($request->hasFile('price_photo_url')) {
                if ($raffle->price_photo_public_id) {
                    $this->cloudinary->deleteImage($raffle->price_photo_public_id);
                }
                $result = $this->cloudinary->uploadImage(
                    $request->file('price_photo_url'),
                    'raffles_prices'
                );
                $data['price_photo_url'] = $result['url'];
                $data['price_photo_public_id'] = $result['public_id'];
            }

            $raffle->update($data);
            Cache::forget('all_raffles');
            Cache::forget("raffle::$id");

            return response()->json([
                'message' => 'Sorteo actualizado',
                'sorteo' => new RaffleResource($raffle)
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function delete($id) {
        try {
            $raffle = Raffles::findOrFail($id);

            if ($raffle->price_photo_public_id) {
                $this->cloudinary->deleteImage($raffle->price_photo_public_id);
            }

            $raffle->delete();

            Cache::forget('all_raffles');
            Cache::forget("raffle::$id");

            return response()->noContent();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

}
