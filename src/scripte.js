document.addEventListener('DOMContentLoaded', () => {
    'use strict'

    // ==========================================
    // EVENT LISTENERS: REGISTRIERUNG
    // ==========================================
    // Alle Felder finden, die validiert werden sollen
    const inputs = document.querySelectorAll('#nav-registrieren .form-control');

    inputs.forEach(input => {
        // Event-Listener: Reagiert bei jedem Tastendruck/Eingabe
        input.addEventListener('input', () => {

            // 1. Passwort-Match Spezialfall
            if (input.id === 'Password' || input.id === 'PasswordWd') {
                mainPasswordCheck();
                checkPasswordMatch();
            } else {
                // 2. Standard HTML5 Validierung (required, email, minlength)
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            }
            buttonState();
        });
    });

    // ==========================================
    // EVENT LISTENERS: PASSWORT ÄNDERN
    // ==========================================
    const inputChange = document.querySelectorAll('#PwChange .form-control');
    inputChange.forEach(input => {
        // Event-Listener: Reagiert bei jedem Tastendruck/Eingabe
        input.addEventListener('input', () => {

            // 1. Passwort-Match Spezialfall
            if (input.id === 'Password' || input.id === 'PasswordWd') {
                mainPasswordCheck();
                checkPasswordMatch();
            }
            buttonStateCh();
        });
    });

    // ==========================================
    // VALIDIERUNGSFUNKTIONEN
    // ==========================================
    
    // --- Main Password Check ---
    function mainPasswordCheck() {
        const pw = document.getElementById('Password');
        if (pw.value === "") {
            pw.classList.remove('is-valid', 'is-invalid');
            return;
        }
        if (pw.value.length >= 8) {
            pw.classList.add('is-valid');
            pw.classList.remove('is-invalid');
        } else {
            pw.classList.add('is-invalid');
            pw.classList.remove('is-valid');
        }

    }

    // --- Funktion für den Passwort-Vergleich ---
    function checkPasswordMatch() {
        const pw = document.getElementById('Password');
        const pwWd = document.getElementById('PasswordWd');
        if (!pw || !pwWd) return;

        // Wenn das Hauptpasswort leer ist, darf auch die Wiederholung leer sein
        if (pw.value === "" && pwWd.value === "") {
            pwWd.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (pw.value === pwWd.value && pwWd.value !== "") {
            pwWd.classList.add('is-valid');
            pwWd.classList.remove('is-invalid');
        } else {
            pwWd.classList.add('is-invalid');
            pwWd.classList.remove('is-valid');
        }
    }

    // --- Button Registrieren deaktivieren wenn nicht alle Felder richtig ausgefüllt sind ---
    function buttonState() {
        const formIsValid = Array.from(inputs).every(input => { return input.classList.contains('is-valid') });
        const regButton = document.getElementById('regButton');
        if (formIsValid) {
            regButton.disabled = false;
        } else {
            regButton.disabled = true;
        }

    }

    // --- Button Password ändern deaktivieren wenn nicht beide Password Felder richtig ausgefüllt sind ---
    function buttonStateCh() {
        const formIsValid = Array.from(inputChange).every(input => { return input.classList.contains('is-valid') });
        const regButton = document.getElementById('ChSpeichern');
        const pw = document.getElementById('Password');
        const pwWd = document.getElementById('PasswordWd');
        if (formIsValid || (pw.value === "" && pwWd.value === "")) {
            regButton.disabled = false;
        } else {
            regButton.disabled = true;
        }

    }
});
