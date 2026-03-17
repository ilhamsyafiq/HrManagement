<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleLocationService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.geocoding_api_key') ?: config('services.google.maps_api_key');
    }

    public function getLocationData($lat, $lng)
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        // Round to 4 decimal places (~11m precision) for cache deduplication
        $roundedLat = round($lat, 4);
        $roundedLng = round($lng, 4);
        $cacheKey = "geocode:{$roundedLat},{$roundedLng}";

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => $lat . ',' . $lng,
                    'key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['results'])) {
                        $result = $data['results'][0];
                        return [
                            'address' => $result['formatted_address'],
                            'city' => $this->extractCity($result),
                            'state' => $this->extractState($result),
                            'country' => $this->extractCountry($result),
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Geocoding API failed: ' . $e->getMessage());
                return null;
            }

            return null;
        });
    }

    private function extractCity($result)
    {
        foreach ($result['address_components'] as $component) {
            if (in_array('locality', $component['types'])) {
                return $component['long_name'];
            }
        }
        return null;
    }

    private function extractState($result)
    {
        foreach ($result['address_components'] as $component) {
            if (in_array('administrative_area_level_1', $component['types'])) {
                return $component['long_name'];
            }
        }
        return null;
    }

    private function extractCountry($result)
    {
        foreach ($result['address_components'] as $component) {
            if (in_array('country', $component['types'])) {
                return $component['long_name'];
            }
        }
        return null;
    }
}