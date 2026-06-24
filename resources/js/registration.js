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
