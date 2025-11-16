<?php

namespace Tests\Unit;

use App\Models\Patient;
use Carbon\Carbon;
use Tests\TestCase;

class PatientAgeDisplayTest extends TestCase
{
    public function tearDown(): void
    {
        // Clear any test now overrides
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_age_display_shows_days_for_under_31_days()
    {
        Carbon::setTestNow(Carbon::parse('2025-11-16 12:00:00'));
        $dob = Carbon::now()->subDays(10)->toDateString();

        $p = new Patient(['date_of_birth' => $dob]);

        $this->assertEquals('10 days', $p->age_display);
    }

    public function test_age_display_shows_months_for_under_12_months()
    {
        Carbon::setTestNow(Carbon::parse('2025-11-16 12:00:00'));
        // 3 months ago
        $dob = Carbon::now()->subMonths(3)->toDateString();

        $p = new Patient(['date_of_birth' => $dob]);

        $this->assertEquals('3 months', $p->age_display);
    }

    public function test_age_display_shows_years_for_12_months_and_over()
    {
        Carbon::setTestNow(Carbon::parse('2025-11-16 12:00:00'));
        // 4 years ago
        $dob = Carbon::now()->subYears(4)->toDateString();

        $p = new Patient(['date_of_birth' => $dob]);

        $this->assertEquals('4 yrs', $p->age_display);
    }

    public function test_age_display_returns_null_when_dob_missing()
    {
        $p = new Patient([]);
        $this->assertNull($p->age_display);
    }
}
