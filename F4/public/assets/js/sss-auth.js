const loginForm = document.querySelector('#login-form');
const registrationForm = document.querySelector('#registration-form');
const passwordToggles = document.querySelectorAll('.password-toggle');

passwordToggles.forEach((toggle) => {
    const passwordField = toggle.closest('.password-field').querySelector('input');

    toggle.addEventListener('click', () => {
        const isVisible = passwordField.type === 'text';
        const icon = toggle.querySelector('i');
        passwordField.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.setAttribute('aria-label', `${isVisible ? 'Show' : 'Hide'} ${passwordField.name === 'employee_code' ? 'employee invite code' : 'password'}`);
        icon.classList.toggle('fa-eye', isVisible);
        icon.classList.toggle('fa-eye-slash', !isVisible);
    });
});

function setMessage(form, message, type = '') {
    const messageElement = form.querySelector('.form-message');
    messageElement.textContent = message;
    messageElement.className = `form-message ${type}`.trim();
}

function setLoading(form, loading) {
    const button = form.querySelector('button[type="submit"]');
    button.disabled = loading;
    button.textContent = loading ? 'Please wait...' : button.dataset.label;
}

async function submitForm(form, endpoint) {
    const response = await fetch(endpoint, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' }
    });

    const result = await response.json();
    if (!response.ok) {
        throw new Error(result.message || 'Something went wrong.');
    }

    return result;
}

if (loginForm) {
    const button = loginForm.querySelector('button[type="submit"]');
    button.dataset.label = button.textContent;
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setLoading(loginForm, true);
        setMessage(loginForm, '');

        try {
            const endpoint = loginForm.dataset.endpoint || '/login';
            const result = await submitForm(loginForm, endpoint);
            setMessage(loginForm, 'Login successful. Redirecting...', 'success');
            window.location.href = result.redirect || '/home';
        } catch (error) {
            setMessage(loginForm, error.message, 'error');
        }

        setLoading(loginForm, false);
    });
}

if (registrationForm) {
    const button = registrationForm.querySelector('button[type="submit"]');
    button.dataset.label = button.textContent;
    registrationForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setLoading(registrationForm, true);
        setMessage(registrationForm, '');

        try {
            const endpoint = registrationForm.dataset.endpoint || '/register';
            await submitForm(registrationForm, endpoint);
            setMessage(registrationForm, 'Account created. Redirecting...', 'success');
            registrationForm.reset();
            window.setTimeout(() => {
                window.location.href = '/login';
            }, 900);
        } catch (error) {
            setMessage(registrationForm, error.message, 'error');
        }

        setLoading(registrationForm, false);
    });
}
