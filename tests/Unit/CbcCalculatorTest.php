<?php

namespace Tests\Unit;

use App\Services\CbcCalculator;
use Tests\TestCase;

class CbcCalculatorTest extends TestCase
{
    public function test_calculator_computes_indices()
    {
        $values = ['wbc'=>7.5,'neutrophils_pct'=>60,'rbc'=>5.0,'hb'=>15.0,'hct'=>45,'plt'=>250,'mpv'=>10.0];
        $res = CbcCalculator::calculate($values);

        $this->assertEqualsWithDelta(4.5, $res['calculated']['neutrophils_abs'], 0.001);
        $this->assertEqualsWithDelta(90.0, $res['calculated']['mcv'], 0.01);
        $this->assertEqualsWithDelta(30.0, $res['calculated']['mch'], 0.01);
        $this->assertEqualsWithDelta(33.33, $res['calculated']['mchc'], 0.01);
        $this->assertEqualsWithDelta(0.25, $res['calculated']['pct'], 0.0001);
    }

    public function test_calculator_handles_missing_values()
    {
        $values = ['wbc'=>7.5,'rbc'=>5.0];
        $res = CbcCalculator::calculate($values);

        $this->assertArrayNotHasKey('neutrophils_abs', $res['calculated']);
        $this->assertArrayNotHasKey('mcv', $res['calculated']);
        $this->assertArrayNotHasKey('mch', $res['calculated']);
        $this->assertArrayNotHasKey('mchc', $res['calculated']);
        $this->assertArrayNotHasKey('pct', $res['calculated']);
    }

    public function test_calculator_handles_zero_rbc()
    {
        $values = ['rbc'=>0,'hb'=>15.0,'hct'=>45];
        $res = CbcCalculator::calculate($values);

        $this->assertArrayNotHasKey('mcv', $res['calculated']);
        $this->assertArrayNotHasKey('mch', $res['calculated']);
    }
}
