const x7y9z3 = "1313";
const passwordDialog = document.getElementById('passwordDialog');
const container = document.querySelector('.container');
const passwordInput = document.getElementById('passwordInput');
const submitPassword = document.getElementById('submitPassword');
const errorMessage = document.getElementById('errorMessage');

let attemptCount = 0;
const maxAttempts = 10;

function checkPassword() {
    if (passwordInput.value === x7y9z3) {
        setAuthenticated();
        showContent();
    } else {
        attemptCount++;
        if (attemptCount >= maxAttempts) {
            errorMessage.textContent = 'Too many failed attempts. Please try again later.';
            passwordInput.disabled = true;
            submitPassword.disabled = true;
        } else {
            errorMessage.textContent = `Incorrect password. ${maxAttempts - attemptCount} attempts remaining.`;
            passwordInput.value = '';
        }
    }
}

function setAuthenticated() {
    const now = new Date();
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
    localStorage.setItem('authExpiration', endOfDay.getTime());
}

function isAuthenticated() {
    const authExpiration = localStorage.getItem('authExpiration');
    if (authExpiration) {
        if (Date.now() < parseInt(authExpiration)) {
            return true;
        } else {
            localStorage.removeItem('authExpiration');
        }
    }
    return false;
}

function showContent() {
    passwordDialog.style.display = 'none';
    container.style.display = 'block';
}

function init() {
    if (isAuthenticated()) {
        showContent();
    } else {
        passwordDialog.style.display = 'flex';
        container.style.display = 'none';
    }
}

submitPassword.addEventListener('click', checkPassword);
passwordInput.addEventListener('keyup', function(event) {
    if (event.key === 'Enter') {
        checkPassword();
    }
});

document.querySelectorAll('.button').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        const buttonName = this.textContent.toLowerCase().replace(' ', '');
        const newUrl = '/' + buttonName;
        window.location.href = newUrl;
    });
});

init();
