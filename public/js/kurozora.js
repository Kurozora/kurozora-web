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
        }
    },

    // Full screen loader
    fullScreenLoader: function(display = true) {
        // Show the loader
        if(display) {
            $('body').append('<div id="kuroload">Loading&#8230;</div>\n');
        }
        // Hide the loader
        else $('#kuroload').remove();
    }
};