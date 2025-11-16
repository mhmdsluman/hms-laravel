<?php

namespace Tests\Unit;

use App\Services\UrineCalculator;
use Tests\TestCase;

class UrineCalculatorTest extends TestCase
{
    public function test_calculator_interprets_values()
    {
        $values = [
            'specific_gravity' => 1.040,
            'ph' => 3.0,
            'protein' => '1+',
            'rbcs' => 5,
        ];

        $res = UrineCalculator::calculate($values);

        $this->assertEquals('high', $res['flags']['specific_gravity']);
        $this->assertEquals('abnormal', $res['flags']['ph']);
        $this->assertEquals('positive', $res['flags']['protein']);
        $this->assertEquals('high', $res['flags']['rbcs']);
    }
}
