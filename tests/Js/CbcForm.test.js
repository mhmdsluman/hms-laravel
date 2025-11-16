/**
 * @vitest-environment jsdom
 */
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CbcForm from '../../resources/js/Pages/Lab/CbcForm.vue';

describe('CbcForm', () => {
  it('correctly calculates derived values', async () => {
    const wrapper = mount(CbcForm);

    await wrapper.vm.values.wbc = 7.5;
    await wrapper.vm.values.neutrophils_pct = 60;
    await wrapper.vm.values.rbc = 5.0;
    await wrapper.vm.values.hb = 15.0;
    await wrapper.vm.values.hct = 45;
    await wrapper.vm.values.plt = 250;
    await wrapper.vm.values.mpv = 10.0;

    wrapper.vm.runClientCalc();

    expect(wrapper.vm.calculated.neutrophils_abs).toBeCloseTo(4.5, 3);
    expect(wrapper.vm.calculated.mcv).toBeCloseTo(90.0, 2);
    expect(wrapper.vm.calculated.mch).toBeCloseTo(30.0, 2);
    expect(wrapper.vm.calculated.mchc).toBeCloseTo(33.33, 2);
    expect(wrapper.vm.calculated.pct).toBeCloseTo(0.25, 4);
  });
});
