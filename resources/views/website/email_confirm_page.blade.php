@if ($success)
    <h1>You have successfully confirmed your email address.</h1>
@else
    Oops! We were unable to confirm your email address. <br><br>

    This could mean:
    <ul>
        <li>... this email address was already verified.</li>
        <li>... this link never existed in the first place.</li>
        <li>... something really weird is going on and you should <a href="https://twitter.com/kurozoraapp" target="_blank">Tweet us</a>.</li>
    </ul>
@endif