import { Html5Qrcode } from 'html5-qrcode';

/**
 * QR scanner is loaded only on the PWA login screen. Expose the constructor so
 * the Livewire view can start/stop the camera on demand instead of blocking
 * the initial page load.
 */
window.Html5Qrcode = Html5Qrcode;
