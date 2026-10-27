document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const username = document.getElementById('u').value.trim().toLowerCase();
    const password = document.getElementById('p').value;
    const message = document.getElementById('lerr');

    // Demo login
    const account = USERS[username];

    if (!username || !password) {
        message.className = 'err';
        message.textContent = 'Enter your username and password.';
        return;
    }

    if (account && account.p === password) {
        message.className = 'err msg-ok';
        message.textContent = 'Login successful. Redirecting...';
        try {
            sessionStorage.setItem('sms_user', JSON.stringify({ name: username, role: account.role }));
        } catch (e) { }
        setTimeout(function () {
            window.location.href = 'dashboard.html';
        }, 500);
    } else {
        message.className = 'err';
        message.textContent = 'Incorrect username or password.';
        document.getElementById('p').value = '';
    }
});
