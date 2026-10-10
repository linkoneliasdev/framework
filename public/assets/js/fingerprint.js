// fingerprint.js

let deviceFingerprint = "";

document.addEventListener('DOMContentLoaded', () => {

    const generate = () => {
        Fingerprint2.get(function (components) {
            const values = components.map(c => c.value);
            deviceFingerprint = Fingerprint2.x64hash128(values.join(''), 31);
            console.log("Fingerprint:", deviceFingerprint);
        });
    };

    if (window.requestIdleCallback) {
        requestIdleCallback(generate);
    } else {
        setTimeout(generate, 500);
    }
});