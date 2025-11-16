<?php

namespace Tests\Unit;

use App\Models\ReferenceRange;
use App\Models\Service;
use App\Services\StoolRangeInterpreter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoolRangeInterpreterTest extends TestCase
{
    use RefreshDatabase;

    public function test_interpreter_returns_correct_statuses()
    {
        $phService = Service::factory()->create(['name' => 'pH']);
        ReferenceRange::factory()->create([
            'service_id' => $phService->id,
            'range_low' => 6.5,
            'range_high' => 7.5,
        ]);

        $occultBloodService = Service::factory()->create(['name' => 'Occult blood']);
        ReferenceRange::factory()->create([
            'service_id' => $occultBloodService->id,
            'normal_text' => 'Negative',
        ]);

        $parasiteService = Service::factory()->create(['name' => 'Giardia lamblia cyst']);
        ReferenceRange::factory()->create([
            'service_id' => $parasiteService->id,
            'normal_text' => 'Absent',
        ]);

        $ageInDays = 10000;
        $gender = 'male';

        $this->assertEquals('Low', StoolRangeInterpreter::getStatus($phService->id, 6.0, $ageInDays, $gender));
        $this->assertEquals('Normal', StoolRangeInterpreter::getStatus($phService->id, 7.0, $ageInDays, $gender));
        $this->assertEquals('High', StoolRangeInterpreter::getStatus($phService->id, 8.0, $ageInDays, $gender));

        $this->assertEquals('Normal', StoolRangeInterpreter::getStatus($occultBloodService->id, 'Negative', $ageInDays, $gender));
        $this->assertEquals('Present', StoolRangeInterpreter::getStatus($occultBloodService->id, 'Positive', $ageInDays, $gender));

        $this->assertEquals('Absent', StoolRangeInterpreter::getStatus($parasiteService->id, 'Absent', $ageInDays, $gender));
        $this->assertEquals('Present', StoolRangeInterpreter::getStatus($parasiteService->id, 'Present', $ageInDays, $gender));
    }
}
