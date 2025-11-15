<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Patient;

class PatientTest extends TestCase
{
    /**
     * Ensure Patient->toArray() does not throw when date_of_birth is missing.
     *
     * @return void
     */
    public function test_to_array_handles_missing_date_of_birth()
    {
        // Create a Patient instance without date_of_birth
        $patient = new Patient([
            'first_name' => 'Unit',
            'last_name' => 'Tester',
            // deliberately omit date_of_birth
        ]);

        // toArray should not throw and should return an array
        $arr = $patient->toArray();

        $this->assertIsArray($arr);

        // The appended 'age' attribute should be present and either null or an int
        $this->assertArrayHasKey('age', $arr);
        $this->assertTrue(is_null($arr['age']) || is_int($arr['age']));
    }
}
