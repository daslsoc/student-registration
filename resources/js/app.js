import './bootstrap';
import { renameChildField, canRemoveChild, registrationPrice } from './registration';

// Expose the tested registration helpers for the inline form script.
window.Registration = { renameChildField, canRemoveChild, registrationPrice };
