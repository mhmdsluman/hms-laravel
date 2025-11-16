<?php

namespace Tests\Unit;

use App\Models\ReferenceRange;
use App\Models\Service;
use App\Services\LabResultFlaggingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabResultFlaggingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_flagging_service_returns_correct_flags()
    {
        $service = Service::factory()->create();
        ReferenceRange::factory()->create([
            'service_id' => $service->id,
            'range_low' => 10,
            'range_high' => 20,
        ]);

        $this->assertEquals('Low', LabResultFlaggingService::getFlag($service, 5, 10000, 'male'));
        $this->assertEquals('Normal', LabResultFlaggingService::getFlag($service, 15, 10000, 'male'));
        $this->assertEquals('High', LabResultFlaggingService::getFlag($service, 25, 10000, 'male'));
    }
}
