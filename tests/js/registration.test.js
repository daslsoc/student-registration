import { describe, it, expect } from 'vitest';
import {
    renameChildField,
    canRemoveChild,
    registrationPrice,
} from '../../resources/js/registration.js';

describe('renameChildField', () => {
    it('rewrites the child index while preserving the field key', () => {
        expect(renameChildField('children[0][first_name]', 2))
            .toBe('children[2][first_name]');
        expect(renameChildField('children[0][date_of_birth]', 1))
            .toBe('children[1][date_of_birth]');
    });

    it('rewrites from any starting index, not just [0]', () => {
        expect(renameChildField('children[3][gender]', 4))
            .toBe('children[4][gender]');
    });

    it('leaves unrelated names untouched', () => {
        expect(renameChildField('parent1_first_name', 2))
            .toBe('parent1_first_name');
    });

    it('is null-safe', () => {
        expect(renameChildField(null, 1)).toBe(null);
        expect(renameChildField(undefined, 1)).toBe(undefined);
    });
});

describe('canRemoveChild', () => {
    it('allows removal only when more than one child remains', () => {
        expect(canRemoveChild(2)).toBe(true);
        expect(canRemoveChild(1)).toBe(false);
        expect(canRemoveChild(0)).toBe(false);
    });
});

describe('registrationPrice', () => {
    const rates = { single: 100, multiple: 150 };

    it('charges the single rate for exactly one child', () => {
        expect(registrationPrice(1, rates)).toBe(100);
    });

    it('charges the multiple rate for two or more children', () => {
        expect(registrationPrice(2, rates)).toBe(150);
        expect(registrationPrice(5, rates)).toBe(150);
    });

    it('is free when there are no children', () => {
        expect(registrationPrice(0, rates)).toBe(0);
    });
});
