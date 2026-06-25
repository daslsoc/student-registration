import './bootstrap';
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
