<?php

namespace Tests\Unit;

use App\Models\CbcParameterRange;
use App\Models\Patient;
use App\Services\CbcRangeMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbcRangeMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_range_matcher_returns_correct_flags()
    {
        CbcParameterRange::create([
            'parameter' => 'wbc',
            'unit' => '10^9/L',
            'min' => 4.0,
            'max' => 11.0,
            'min_age_days' => 6570, // 18 years
            'gender' => 'any',
            'label' => 'Adult',
        ]);

        $patient = Patient::factory()->create([
            'date_of_birth' => now()->subYears(25),
            'gender' => 'male',
        ]);

        $ageInDays = now()->diffInDays($patient->date_of_birth);

        $this->assertEquals('low', CbcRangeMatcher::getStatus($ageInDays, 'male', 'wbc', 3.0));
        $this->assertEquals('normal', CbcRangeMatcher::getStatus($ageInDays, 'male', 'wbc', 7.0));
        $this->assertEquals('high', CbcRangeMatcher::getStatus($ageInDays, 'male', 'wbc', 12.0));
        $this->assertEquals('no_range', CbcRangeMatcher::getStatus($ageInDays, 'male', 'rbc', 5.0));
    }
}
