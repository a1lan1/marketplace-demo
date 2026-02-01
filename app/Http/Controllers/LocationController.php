<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Http\Resources\Geo\LocationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(private readonly LocationServiceInterface $locationService) {}

    public function index(Request $request): Response
    {
        $locations = $this->locationService->getLocationsForUser($request->user());

        return Inertia::render('Geo/Locations', [
            'locations' => LocationResource::collection($locations),
        ]);
    }
}
