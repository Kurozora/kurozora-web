var kurozora = {
    // API
    api: {
        baseURL: 'https://kurozora.app/api/v1/',

        // API methods
        makeAPICall: function(method, endpoint, data = {}, returnFunc) {
            $.ajax({
                url: kurozora.api.baseURL + endpoint,
                method: method,
                data: data,
                success: function(data) {
                    returnFunc(true, data);
                },
                error: function(err) {
                    returnFunc(false, err);
                }
            });
        },

        // Explore page
        explore: function(returnFunc) {
            return kurozora.api.makeAPICall('GET', 'anime/explore', {}, returnFunc);
        },

        // Privacy policy
        privacy: function(returnFunc) {
            return kurozora.api.makeAPICall('GET', 'misc/get_privacy_policy', {}, returnFunc);
        },

        // Login
        login: function(data, returnFunc) {
            return kurozora.api.makeAPICall('POST', 'user/login', data, returnFunc);
        }
    },

    // Check if the user is logged in
    isUserLoggedIn: function() {
        return (kurozora.getCookie('user_id') != null);
    },

    // Cookie functions
    createCookie: function (name, value, days = 365) {
        var expires = "";

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        }

        document.cookie = name + "=" + value + expires + "; path=/";
    },

    getCookie: function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(";");

        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == " ") c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }

        return null;
    },

    removeCookie: function (name) {
        kurozora.createCookie(name, "", -1);
    }
};

$(document).ready(function() {
    // Initialize sidenav
    $('.sidenav').sidenav();
});