import './bootstrap';

// Bootstrap 5 JS (dropdowns, modals, the navbar toggler, etc.). Bundled here
// rather than loaded from a CDN — see resources/scss/app.scss.
import 'bootstrap';

import {
    renameChildField,
    canRemoveChild,
    registrationPrice,
    initRegistrationForm,
} from './registration';

// Expose the tested registration helpers (handy for debugging / inline use).
window.Registration = { renameChildField, canRemoveChild, registrationPrice, initRegistrationForm };

// app.js is loaded as a deferred ES module, so the DOM is already parsed here.
initRegistrationForm();
