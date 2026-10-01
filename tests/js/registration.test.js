import { describe, it, expect, beforeEach } from 'vitest';
import {
    renameChildField,
    renameChildId,
    canRemoveChild,
    registrationPrice,
    initRegistrationForm,
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

describe('renameChildId', () => {
    it('rewrites the child index prefix and leaves other ids alone', () => {
        expect(renameChildId('child_0_first_name', 2)).toBe('child_2_first_name');
        expect(renameChildId('parent1_first_name', 2)).toBe('parent1_first_name');
        expect(renameChildId(undefined, 1)).toBe(undefined);
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

describe('initRegistrationForm', () => {
    function renderForm() {
        document.body.innerHTML = `
            <button id="addChildBtn"></button>
            <button id="removeChildBtn"></button>
            <div id="children-container">
                <div class="child-block">
                    <label for="child_0_first_name">Child First Name</label>
                    <input id="child_0_first_name" name="children[0][first_name]" value="Existing" />
                    <select name="children[0][gender]"><option>Male</option></select>
                    <input name="children[0][allergies]" value="" />
                    <input name="children[0][special_needs]" value="" />
                </div>
            </div>
        `;
    }

    beforeEach(() => renderForm());

    it('no-ops when the form is not present', () => {
        document.body.innerHTML = '<div></div>';
        expect(initRegistrationForm(document)).toBe(false);
    });

    it('adds a child block with re-indexed, cleared fields', () => {
        initRegistrationForm(document);
        document.getElementById('addChildBtn').click();

        const blocks = document.querySelectorAll('.child-block');
        expect(blocks).toHaveLength(2);

        const newInput = blocks[1].querySelector('input');
        expect(newInput.getAttribute('name')).toBe('children[1][first_name]');
        expect(newInput.value).toBe('');
    });

    it('re-indexes the ids and label targets so each label still names its own control', () => {
        initRegistrationForm(document);
        document.getElementById('addChildBtn').click();

        const blocks = document.querySelectorAll('.child-block');
        expect(blocks[1].querySelector('input').id).toBe('child_1_first_name');
        expect(blocks[1].querySelector('label').htmlFor).toBe('child_1_first_name');
        // The original block is untouched, so ids stay unique on the page.
        expect(document.querySelectorAll('#child_0_first_name')).toHaveLength(1);
    });

    it('defaults a new child\'s allergies and special needs to "None"', () => {
        initRegistrationForm(document);
        document.getElementById('addChildBtn').click();

        const newBlock = document.querySelectorAll('.child-block')[1];
        expect(newBlock.querySelector('[name="children[1][allergies]"]').value).toBe('None');
        expect(newBlock.querySelector('[name="children[1][special_needs]"]').value).toBe('None');
    });

    it('removes the last child but never the final one', () => {
        initRegistrationForm(document);
        const add = document.getElementById('addChildBtn');
        const remove = document.getElementById('removeChildBtn');

        add.click(); // now 2 blocks
        remove.click(); // back to 1
        expect(document.querySelectorAll('.child-block')).toHaveLength(1);

        remove.click(); // must not drop below 1
        expect(document.querySelectorAll('.child-block')).toHaveLength(1);
    });
});
