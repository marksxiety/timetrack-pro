import { describe, it, expect } from 'vitest';
import { truncateText, getInitials } from '../../js/Pages/utils/helpers/format.js';

describe('truncateText', () => {
    it('returns empty string for null', () => {
        expect(truncateText(null)).toBe('');
    });

    it('returns empty string for undefined', () => {
        expect(truncateText(undefined)).toBe('');
    });

    it('returns empty string for empty string', () => {
        expect(truncateText('')).toBe('');
    });

    it('returns text as-is when shorter than maxLength', () => {
        expect(truncateText('hello', 80)).toBe('hello');
    });

    it('returns text as-is when exactly maxLength', () => {
        expect(truncateText('hello', 5)).toBe('hello');
    });

    it('truncates and appends "..." when longer than maxLength', () => {
        expect(truncateText('hello world', 5)).toBe('hello...');
    });

    it('uses default maxLength of 80', () => {
        const text = 'a'.repeat(81);
        expect(truncateText(text)).toBe('a'.repeat(80) + '...');
    });

    it('does not truncate text at maxLength boundary', () => {
        const text = 'a'.repeat(80);
        expect(truncateText(text)).toBe(text);
    });

    it('truncates at custom maxLength', () => {
        expect(truncateText('abcdefghij', 3)).toBe('abc...');
    });
});

describe('getInitials', () => {
    it('returns "?" for null', () => {
        expect(getInitials(null)).toBe('?');
    });

    it('returns "?" for undefined', () => {
        expect(getInitials(undefined)).toBe('?');
    });

    it('returns "?" for empty string', () => {
        expect(getInitials('')).toBe('?');
    });

    it('returns first letter for single name', () => {
        expect(getInitials('John')).toBe('J');
    });

    it('returns first and last initials for two names', () => {
        expect(getInitials('John Doe')).toBe('JD');
    });

    it('returns first and last initials for three names', () => {
        expect(getInitials('John Middle Doe')).toBe('JD');
    });

    it('trims whitespace before splitting', () => {
        expect(getInitials('  John Doe  ')).toBe('JD');
    });

    it('converts to uppercase', () => {
        expect(getInitials('john doe')).toBe('JD');
    });

    it('handles multiple spaces between names', () => {
        expect(getInitials('John   Doe')).toBe('JD');
    });
});
