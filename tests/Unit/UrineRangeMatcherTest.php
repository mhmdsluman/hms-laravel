<?php

namespace Tests\Unit;

use App\Models\Patient;
use App\Models\UrineParameterRange;
use App\Services\UrineRangeMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrineRangeMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_range_matcher_returns_correct_flags()
    {
        UrineParameterRange::create([
            'parameter' => 'ph',
            'min' => 4.5,
            'max' => 8.0,
        ]);

        UrineParameterRange::create([
            'parameter' => 'protein',
            'normal_text' => 'Negative',
        ]);

        $patient = Patient::factory()->create([
            'date_of_birth' => now()->subYears(30),
            'gender' => 'female',
        ]);

        $ageInDays = now()->diffInDays($patient->date_of_birth);

        $this->assertEquals('low', UrineRangeMatcher::getStatus($ageInDays, 'female', 'ph', 4.0));
        $this->assertEquals('normal', UrineRangeMatcher::getStatus($ageInDays, 'female', 'ph', 7.0));
        $this->assertEquals('high', UrineRangeMatcher::getStatus($ageInDays, 'female', 'ph', 9.0));

        $this->assertEquals('normal', UrineRangeMatcher::getStatus($ageInDays, 'female', 'protein', 'Negative'));
        $this->assertEquals('abnormal', UrineRangeMatcher::getStatus($ageInDays, 'female', 'protein', '1+'));

        $this->assertEquals('no_range', UrineRangeMatcher::getStatus($ageInDays, 'female', 'glucose', 'Negative'));
    }
}
