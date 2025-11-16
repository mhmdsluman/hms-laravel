/**
 * @vitest-environment jsdom
 */
import { describe, it, expect } from 'vitest';
import { CbcCalculationService } from '../../resources/js/Services/CbcCalculationService';

describe('CbcCalculationService', () => {
  it('correctly calculates derived values', () => {
    const results = {
        '1': '7.5', // WBC
        '2': '60',  // Neutrophils
        '4': '5.0', // RBC
        '5': '15.0',// HB
        '6': '45',  // HCT
    };
    const services = [
        { id: 1, name: 'White Blood Cell Count' },
        { id: 2, name: 'Neutrophils' },
        { id: 4, name: 'Red Blood Cell Count' },
        { id: 5, name: 'Hemoglobin' },
        { id: 6, name: 'Hematocrit' },
    ];

    const calculated = CbcCalculationService.calculate(results, services);

    expect(calculated['Neutrophils Abs']).toBe('4.500');
    expect(calculated['Mean Corpuscular Volume']).toBe('90.00');
    expect(calculated['Mean Corpuscular Hemoglobin']).toBe('30.00');
    expect(calculated['Mean Corpuscular Hemoglobin Concentration']).toBe('33.33');
  });
});
