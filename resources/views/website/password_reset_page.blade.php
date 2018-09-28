<html>
    <head>
        <meta name="robots" content="noindex,nofollow" />
    </head>
    <body>
        @if ($success)
            <h1>Your password has been reset.</h1>
            <p>We have sent you an email with your new password.</p>
        @else
            Oops! We were unable to reset your password. <br><br>

            This could mean:
            <ul>
                <li>... this link has already been used.</li>
                <li>... this link never existed in the first place.</li>
                <li>... something really weird is going on and you should <a href="https://twitter.com/{{ env('APP_TWITTER_HANDLE') }}" target="_blank">Tweet us</a>.</li>
            </ul>
        @endif
    </body>
</html>