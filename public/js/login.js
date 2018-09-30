$(document).ready(function() {
    // Attempt login on press enter and press button
    $('#loginBtn').click(attemptLogin);

    $('.login-input').keypress(function(e) {
        if(e.which == 13)
            attemptLogin();
    });
});

// Makes login attempt
function attemptLogin() {
    hideLoginError();

    // Get the entered username and password
    var username = $('#username').val();
    var password = $('#password').val();

    if(!username.length)
        return showLoginError('Please enter a username.');

    if(!password.length)
        return showLoginError('Please enter a password.');

    // Disable the login form
    toggleLoginForm(false);

    // Make request
    kurozora.api.login({
        username: username,
        password: password,
        device: 'Kurozora Web'
    }, function(success, data) {
        if(!success) {
            toggleLoginForm(true);
            return showLoginError('Unable to request login with the server.');
        }
        else {
            // Failed login
            if(!data.success) {
                toggleLoginForm(true);
                return showLoginError(data.error_message);
            }
            // Successful login
            else {
                // Set cookies
                kurozora.createCookie('session_secret', data.session_secret);
                kurozora.createCookie('user_id', data.user_id);
                kurozora.createCookie('role', data.role);

                // Redirect to profile page
                window.location.href = $('#base-url').val() + '/user/' + data.user_id + '/profile';
            }
        }
    });
}

// Toggles the login form enabled/disabled
function toggleLoginForm(status) {
    $('#username').attr('disabled', !status);
    $('#password').attr('disabled', !status);
    $('#loginBtn').attr('disabled', !status);
    return true;
}

// Shows a login error
function showLoginError(text) {
    $('#loginError').text(text);
    $('#loginError').fadeIn();
    return true;
}

// Hides login error
function hideLoginError() {
    $('#loginError').hide();
    return true;
}