document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const toggleBtn = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');

    const requirements = {
        length: { el: document.getElementById('req-length'), regex: /.{8,}/ },
        capital: { el: document.getElementById('req-capital'), regex: /[A-Z]/ },
        number: { el: document.getElementById('req-number'), regex: /[0-9]/ },
        symbol: { el: document.getElementById('req-symbol'), regex: /[^A-Za-z0-9]/ }
    };

    // 1. Toggle Visibility Logic
    toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        confirmInput.setAttribute('type', type); // Sync both
        
        // Toggle Icon
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });

    // 2. Main Validation Function
    function validateForm() {
        const val = passwordInput.value;
        const confirmVal = confirmInput.value;
        let strengthValid = true;

        // Check Strength
        for (const key in requirements) {
            const isValid = requirements[key].regex.test(val);
            const element = requirements[key].el;
            const icon = element.querySelector('i');

            if (isValid) {
                element.classList.remove('text-muted');
                element.classList.add('text-success', 'fw-bold');
                icon.classList.replace('bi-circle', 'bi-check-circle-fill');
            } else {
                element.classList.remove('text-success', 'fw-bold');
                element.classList.add('text-muted');
                icon.classList.replace('bi-check-circle-fill', 'bi-circle');
                strengthValid = false;
            }
        }

        // Check Matching
        const matchError = document.getElementById('match-error');
        const matchSuccess = document.getElementById('match-success');
        let passwordsMatch = val === confirmVal && val !== '';

        if (confirmVal !== '') {
            if (passwordsMatch) {
                matchError.classList.add('d-none');
                matchSuccess.classList.remove('d-none');
                confirmInput.classList.add('is-valid');
                confirmInput.classList.remove('is-invalid');
            } else {
                matchError.classList.remove('d-none');
                matchSuccess.classList.add('d-none');
                confirmInput.classList.add('is-invalid');
                confirmInput.classList.remove('is-valid');
            }
        }

        // Final Button State
        submitBtn.disabled = !(strengthValid && passwordsMatch);
    }

    passwordInput.addEventListener('input', validateForm);
    confirmInput.addEventListener('input', validateForm);
});