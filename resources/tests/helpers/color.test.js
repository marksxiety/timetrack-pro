import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { getTailwindColor } from '../../js/Pages/utils/helpers/color.js';

describe('getTailwindColor', () => {
    let computedStyleSpy;
    let appendSpy;
    let removeSpy;

    beforeEach(() => {
        computedStyleSpy = vi.spyOn(window, 'getComputedStyle').mockReturnValue({
            backgroundColor: 'rgb(255, 255, 255)',
        });
        appendSpy = vi.spyOn(document.body, 'appendChild');
        removeSpy = vi.spyOn(document.body, 'removeChild');
    });

    afterEach(() => {
        appendSpy.mockRestore();
        removeSpy.mockRestore();
        computedStyleSpy.mockRestore();
    });

    it('appends element to body and removes after reading', () => {
        const result = getTailwindColor('bg-base-100');

        expect(result).toBe('rgb(255, 255, 255)');
        expect(appendSpy).toHaveBeenCalledOnce();
        expect(removeSpy).toHaveBeenCalledOnce();
    });

    it('passes correct class to the temporary element', () => {
        let capturedElement;
        appendSpy.mockImplementation((el) => {
            capturedElement = el;
            return el;
        });
        removeSpy.mockImplementation(() => {});

        getTailwindColor('bg-base-200');

        expect(capturedElement.className).toBe('bg-base-200');
        expect(capturedElement.style.display).toBe('none');
    });

    it('returns computed backgroundColor', () => {
        computedStyleSpy.mockReturnValue({ backgroundColor: 'rgb(123, 45, 67)' });

        const result = getTailwindColor('bg-primary');
        expect(result).toBe('rgb(123, 45, 67)');
    });
});
