<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Geo;

use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\LocationRequest;
use App\Http\Resources\Geo\LocationResource;
use App\Models\Location;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function __construct(private readonly LocationServiceInterface $locationService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $locations = $this->locationService->getLocationsForUser($request->user());

        return LocationResource::collection($locations);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(LocationRequest $request): LocationResource
    {
        $this->authorize('create', Location::class);

        $location = $this->locationService->storeLocation($request->toDto());

        return new LocationResource($location);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Location $location): LocationResource
    {
        $this->authorize('view', $location);

        $location = $this->locationService->getLocationWithStats($location);

        return new LocationResource($location);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(LocationRequest $request, Location $location): LocationResource
    {
        $this->authorize('update', $location);

        $location = $this->locationService->storeLocation($request->toDto());

        return new LocationResource($location);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Location $location): JsonResponse
    {
        $this->authorize('delete', $location);

        $this->locationService->deleteLocation($location);

        return response()->json(null, 204);
    }
}
