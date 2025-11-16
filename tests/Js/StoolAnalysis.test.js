/**
 * @vitest-environment jsdom
 */
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import StoolAnalysis from '../../resources/js/Pages/LabTests/Stool/StoolAnalysis.vue';

describe('StoolAnalysis', () => {
    it('highlights abnormal results', async () => {
        const wrapper = mount(StoolAnalysis, {
            props: {
                order: { id: 1 },
                parameters: [
                    {
                        id: 1,
                        name: 'pH',
                        category: 'chemical',
                        reference_range: { range_low: 6.5, range_high: 7.5 },
                    },
                    {
                        id: 2,
                        name: 'Occult blood',
                        category: 'chemical',
                        reference_range: { normal_text: 'Negative' },
                    },
                ],
            },
        });

        await wrapper.vm.$nextTick();

        const phInput = wrapper.find('input[id="param-1"]');
        await phInput.setValue('6.0');
        expect(wrapper.vm.flags[1]).toBe('Abnormal');
        expect(phInput.classes()).toContain('is-abnormal');

        await phInput.setValue('7.0');
        expect(wrapper.vm.flags[1]).toBe('Normal');
        expect(phInput.classes()).not.toContain('is-abnormal');

        const occultBloodInput = wrapper.find('input[id="param-2"]');
        await occultBloodInput.setValue('Positive');
        expect(wrapper.vm.flags[2]).toBe('Abnormal');
        expect(occultBloodInput.classes()).toContain('is-abnormal');
    });
});
