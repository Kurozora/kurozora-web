<head>
    <title>{{ $title ?? 'Home' }} | {{ config('app.name') }}</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <style>
        #outlook a {
            padding: 0;
        }
        .ExternalClass * {
            line-height: 100%;
        }
        html, body, * {
            -webkit-text-size-adjust: none;
            text-size-adjust: none;
        }
        a:hover {
            text-decoration: underline;
        }
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }
        p {
            display: block;
            margin: 13px 0;
        }
        @media only screen and (max-width:480px) {
            @-ms-viewport {
                width: 320px;
            }
            @viewport {
                width: 320px;
            }
        }
        @media only screen and (min-width:480px) {
            .mj-column-per-100,
            * [aria-labelledby="mj-column-per-100"] {
                width: 100%!important;
            }
        }
    </style>
</head>

<body style="background: #F9F9F9;">
<div style="background-color:#F9F9F9;">
    <div style="margin:0px auto;max-width:640px;background:transparent;">
        <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:transparent;" align="center" border="0">
            <tbody>
            <tr>
                <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:40px 0px;">
                    <div aria-labelledby="mj-column-per-100" class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                            <tbody>
                            <tr>
                                <td style="word-break:break-word;font-size:0px;padding:0px;" align="center">
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0px;" align="center" border="0">
                                        <tbody>
                                        <tr>
                                            <td style="width:172px;">
                                                <a href="{{ url('/') }}" target="_blank" style="color: #FF9300; font-weight: normal; text-decoration: none !important;">
                                                    <img src="{{ asset('images/static/email_header_logo.png') }}" alt="{{ config('app.name') }} logo" style="width: 100%; max-width: 180px;" />
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div style="max-width:640px;margin:0 auto;box-shadow:0px 1px 5px rgba(0,0,0,0.1);border-radius:4px;overflow:hidden">
        <table style="background-image: url('{{ asset('images/static/star_bg_md.jpg') }}'); width: 100%; background-position: bottom;" role="presentation" cellpadding="0" cellspacing="0" align="center" border="0">
            <tbody>
            <tr>
                <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:57px;">
                    <div style="cursor:auto;color:white;font-family:Whitney, Helvetica Neue, Helvetica, Arial, Lucida Grande, sans-serif;font-size:36px;font-weight:600;line-height:36px;text-align:center;">{{ $title ?? 'Home' }}</div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div style="margin:0px auto;max-width:640px;background:#ffffff;">
        <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:#ffffff;" align="center" border="0">
            <tbody>
            <tr>
                <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:40px 50px;">
                    <div aria-labelledby="mj-column-per-100" class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                            <tbody>
                            <tr>
                                <td style="word-break:break-word;font-size:0px;padding:0px 0px 20px;" align="left">
                                    <div style="cursor:auto;color:#737F8D;font-family:Whitney, Helvetica Neue, Helvetica, Arial, Lucida Grande, sans-serif;font-size:16px;line-height:24px;text-align:left;">
                                        @yield('content')
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div> </div>
<div style="margin:0px auto;max-width:640px;background:transparent;">
    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:transparent;" align="center" border="0">
        <tbody>
        <tr>
            <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:0px;">
                <div aria-labelledby="mj-column-per-100" class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                        <tbody>
                        <tr>
                            <td style="word-break:break-word;font-size:0px;">
                                <div style="font-size:1px;line-height:12px;">&nbsp;</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div style="margin:0px auto;max-width:640px;background:transparent;">
    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:transparent;" align="center" border="0">
        <tbody>
        <tr>
            <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;">
                <div aria-labelledby="mj-column-per-100" class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                        <tbody>
                        <tr>
                            <td style="word-break:break-word;font-size:0px;padding:0px;" align="center">
                                <div style="cursor:auto;color:#99AAB5;font-family:Whitney, Helvetica Neue, Helvetica, Arial, Lucida Grande, sans-serif;font-size:12px;line-height:24px;text-align:center;">
                                    {{ config('app.name') }} • <a href="{{ config('social.twitter.url') }}" style="color:#1EB0F4;text-decoration:none;" target="_blank">{{ '@' . config('social.twitter') }}</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="word-break:break-word;font-size:0px;padding:0px;" align="center">
                                <div style="cursor:auto;color:#99AAB5;font-family:Whitney, Helvetica Neue, Helvetica, Arial, Lucida Grande, sans-serif;font-size:12px;line-height:24px;text-align:center;">
                                    The Netherlands
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
