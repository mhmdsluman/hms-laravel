/**
 * @vitest-environment jsdom
 */
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import UrineForm from '../../resources/js/Pages/Lab/UrineForm.vue';

// Mock axios
vi.mock('axios', () => ({
  default: {
    get: vi.fn(() => Promise.resolve({ data: {} })),
  },
}));

describe('UrineForm', () => {
  it('navigates between input fields with arrow and enter keys', async () => {
    const wrapper = mount(UrineForm, {
      props: {
        patient: { id: 1, date_of_birth: '1990-01-01', gender: 'male' },
      },
    });

    // Need to wait for the component to be mounted and refs to be populated
    await wrapper.vm.$nextTick();

    const colorInput = wrapper.find('select[id="color"]').element;
    const appearanceInput = wrapper.find('select[id="appearance"]').element;
    const sgInput = wrapper.find('input[id="specific_gravity"]').element;

    // Set focus on the first element
    colorInput.focus();
    expect(document.activeElement).toBe(colorInput);

    // Test ArrowDown
    await wrapper.find('select[id="color"]').trigger('keydown.down');
    expect(document.activeElement).toBe(appearanceInput);

    // Test Enter
    await wrapper.find('select[id="appearance"]').trigger('keydown.enter');
    expect(document.activeElement).toBe(sgInput);

    // Test ArrowUp
    await wrapper.find('input[id="specific_gravity"]').trigger('keydown.up');
    expect(document.activeElement).toBe(appearanceInput);
  });
});
