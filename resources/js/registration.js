/**
 * Pure helpers for the dynamic "add / remove child" behaviour on the
 * registration form. Kept framework-free and side-effect-free so they can be
 * unit tested with Vitest and reused by the inline form script.
 */

/**
 * Rewrite a child field's `name` attribute to a new child index.
 * e.g. renameChildField('children[0][first_name]', 2) === 'children[2][first_name]'
 *
 * Only the child index segment is touched; the field key is preserved.
 */
export function renameChildField(name, index) {
    if (typeof name !== 'string') {
        return name;
    }

    return name.replace(/children\[\d+\]/, `children[${index}]`);
}

/**
 * The form must always keep at least one child block. Returns true only when
 * removing one still leaves a child behind.
 */
export function canRemoveChild(currentCount) {
    return currentCount > 1;
}

/**
 * Mirror of the server-side pricing rule in config/custom.php:
 * a single child is charged the single rate, two or more the multiple rate.
 */
export function registrationPrice(childCount, { single, multiple }) {
    if (childCount <= 0) {
        return 0;
    }

    return childCount > 1 ? multiple : single;
}

/**
 * Wire the "+ Add Another Child" / "- Remove Last Child" buttons on the
 * registration form. No-ops (returns false) when the form isn't on the page,
 * so it's safe to call on every page that shares the layout.
 *
 * `doc` is injectable so the behaviour can be exercised under jsdom.
 */
export function initRegistrationForm(doc = document) {
    const addBtn = doc.getElementById('addChildBtn');
    const removeBtn = doc.getElementById('removeChildBtn');
    const container = doc.getElementById('children-container');

    if (!addBtn || !removeBtn || !container) {
        return false;
    }

    let childIndex = container.querySelectorAll('.child-block').length;

    addBtn.addEventListener('click', () => {
        const firstBlock = container.querySelector('.child-block');
        if (!firstBlock) {
            return;
        }

        const newBlock = firstBlock.cloneNode(true);
        newBlock.querySelectorAll('input, select').forEach((el) => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', renameChildField(name, childIndex));
            }
            el.classList.remove('is-invalid');
            el.value = '';
        });

        container.appendChild(newBlock);
        childIndex += 1;
    });

    removeBtn.addEventListener('click', () => {
        const blocks = container.querySelectorAll('.child-block');
        if (canRemoveChild(blocks.length)) {
            blocks[blocks.length - 1].remove();
            childIndex -= 1;
        } else {
            alert('You must have at least one child!');
        }
    });

    return true;
}
