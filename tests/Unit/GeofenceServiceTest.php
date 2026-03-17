<?php

namespace Tests\Unit;

use App\Services\GeofenceService;
use Tests\TestCase;

class GeofenceServiceTest extends TestCase
{
    protected GeofenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeofenceService();
    }

    public function test_haversine_distance_same_point_is_zero()
    {
        $distance = $this->service->haversineDistance(3.1390, 101.6869, 3.1390, 101.6869);
        $this->assertEquals(0, $distance);
    }

    public function test_haversine_distance_known_points()
    {
        // KL to Petaling Jaya ~15km
        $distance = $this->service->haversineDistance(3.1390, 101.6869, 3.1073, 101.6067);
        $this->assertGreaterThan(8000, $distance);
        $this->assertLessThan(20000, $distance);
    }

    public function test_geofence_disabled_always_allows()
    {
        config(['geofence.enabled' => false]);

        $result = $this->service->validateLocation(0, 0, null, false, false);

        $this->assertTrue($result['allowed']);
    }

    public function test_wfh_bypasses_geofence()
    {
        config(['geofence.enabled' => true]);

        $result = $this->service->validateLocation(0, 0, null, false, true);

        $this->assertTrue($result['allowed']);
    }

    public function test_mock_location_rejected()
    {
        config(['geofence.enabled' => true]);
        config(['geofence.anti_fake.reject_mock' => true]);

        $result = $this->service->validateLocation(3.1390, 101.6869, 10, true, false);

        $this->assertFalse($result['allowed']);
        $this->assertContains('mock_location', $result['flags']);
    }

    public function test_null_location_rejected_when_geofence_enabled()
    {
        config(['geofence.enabled' => true]);

        $result = $this->service->validateLocation(null, null, null, false, false);

        $this->assertFalse($result['allowed']);
    }

    public function test_low_accuracy_rejected()
    {
        config(['geofence.enabled' => true]);
        config(['geofence.max_accuracy' => 100]);

        $result = $this->service->validateLocation(3.1390, 101.6869, 200, false, false);

        $this->assertFalse($result['allowed']);
        $this->assertContains('low_accuracy', $result['flags']);
    }
}
