$(document).ready(function() {
    kurozora.api.privacy(function(success, data) {
        if(success) {
            $('#privacyText').html(data.privacy_policy.text.replace(/\n/g, "<br />"));
            $('#privacyLastMod').text('Last updated at: ' + data.privacy_policy.last_update);
        }
        else {
            $('#privacyText').text('Unable to retrieve the privacy policy at this time.');
        }
    });
});