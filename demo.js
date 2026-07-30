document.addEventListener('DOMContentLoaded', () => {
    // Login Form Handling
    const loginForm = document.getElementById('loginForm');
    const loginPasswordInput = document.getElementById('loginPassword');
    const toggleLoginPassword = document.getElementById('toggleLoginPassword');

    // Password toggle for login
    if (toggleLoginPassword) {
        toggleLoginPassword.addEventListener('click', () => {
            const isHidden = loginPasswordInput.type === 'password';
            loginPasswordInput.type = isHidden ? 'text' : 'password';
            toggleLoginPassword.textContent = isHidden ? '🙈' : '👁️';
        });
    }

    // Login form submission
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value.trim();
            const password = loginPasswordInput.value;

            const users = JSON.parse(localStorage.getItem('users')) || {};
            if (users[email] && users[email].password === password) {
                sessionStorage.setItem('loggedInUser', JSON.stringify({ email, role: users[email].role }));
                window.location.href = 'dashboard/index.html';
            } else {
                alert('Invalid email or password.');
            }
        });
    }

    // Register Form Handling
    const registerForm = document.getElementById('registerForm');
    const registerPasswordInput = document.getElementById('registerPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const toggleRegisterPassword = document.getElementById('toggleRegisterPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    // Password toggle for register
    if (toggleRegisterPassword) {
        toggleRegisterPassword.addEventListener('click', () => {
            const isHidden = registerPasswordInput.type === 'password';
            registerPasswordInput.type = isHidden ? 'text' : 'password';
            toggleRegisterPassword.textContent = isHidden ? '🙈' : '👁️';
        });
    }

    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', () => {
            const isHidden = confirmPasswordInput.type === 'password';
            confirmPasswordInput.type = isHidden ? 'text' : 'password';
            toggleConfirmPassword.textContent = isHidden ? '🙈' : '👁️';
        });
    }

    // Password validation
    if (registerPasswordInput) {
        registerPasswordInput.addEventListener('input', validatePassword);
    }

    function validatePassword() {
        const password = registerPasswordInput.value;
        const length = document.getElementById('length');
        const uppercase = document.getElementById('uppercase');
        const lowercase = document.getElementById('lowercase');
        const number = document.getElementById('number');
        const special = document.getElementById('special');

        length.className = password.length >= 8 ? 'valid' : 'invalid';
        uppercase.className = /[A-Z]/.test(password) ? 'valid' : 'invalid';
        lowercase.className = /[a-z]/.test(password) ? 'valid' : 'invalid';
        number.className = /\d/.test(password) ? 'valid' : 'invalid';
        special.className = /[!@#$%^&*]/.test(password) ? 'valid' : 'invalid';
    }

    // Register form submission
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('registerEmail').value.trim();
            const password = registerPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const role = document.getElementById('roleSelect').value;

            // Validate password requirements
            const isPasswordValid = password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /\d/.test(password) &&
                /[!@#$%^&*]/.test(password);

            if (!isPasswordValid) {
                alert('Password does not meet all requirements.');
                return;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return;
            }

            const users = JSON.parse(localStorage.getItem('users')) || {};
            if (users[email]) {
                alert('Email is already registered.');
                return;
            }

            users[email] = { password, role };
            localStorage.setItem('users', JSON.stringify(users));
            alert('Registration successful! Please log in.');
            window.location.href = 'index.html';
        });
    }

    // Google Sign-In
    window.handleCredentialResponse = (response) => {
        const base64Url = response.credential.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(
            atob(base64)
                .split('')
                .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
                .join('')
        );

        const userObject = JSON.parse(jsonPayload);
        sessionStorage.setItem('loggedInUser', JSON.stringify({
            email: userObject.email,
            name: userObject.name,
            googleUser: true
        }));
        window.location.href = 'dashboard/index.html';
    };

    if (window.google && google.accounts) {
        google.accounts.id.initialize({
            client_id: '395145046477-nidh67oud3t66a40hoq4u8fca0c6kuaq.apps.googleusercontent.com',
            callback: handleCredentialResponse,
            auto_select: false
        });

        const googleSignInButton = document.getElementById('googleSignInButton');
        if (googleSignInButton) {
            google.accounts.id.renderButton(
                googleSignInButton,
                { theme: 'outline', size: 'large', width: '300' }
            );
        }
    }
});