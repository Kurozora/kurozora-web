<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kurozora API</title>

    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/static/favicon.ico') }}">

    {{-- Don't index the API docs --}}
    <meta name="robots" content="noindex">

    {{-- Swagger CSS --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/swagger-ui.css') }}">

    {{-- Custom Style --}}
    <style>
        body {
            margin: 0;
            background: #FFF9F7;
        }

        .info a.link {
            color: #FF9300 !important;
        }

        .swagger-ui .scheme-container {
            background: #FFF9F7;
            box-shadow: 0 8px 12px 0 rgba(0, 0, 0, 0.05);
        }

        .topbar-wrapper img[alt="Swagger UI"], .topbar-wrapper span {
            visibility: collapse;
        }

        .topbar-wrapper .link {
            margin-block-end: 16px;
        }

        .topbar-wrapper .link:before {
            background-image: url('{{ asset('images/static/icon/app_icon.webp') }}');
            background-size: 48px 48px;
            content: '';
            margin-right: 0.5rem;
            height: 48px;
            width: 48px;
        }

        .topbar-wrapper .link:after {
            content: 'Kurozora';
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    {{-- Include Swagger JS files --}}
    <script type="text/javascript" src="{{ asset('js/swagger-ui-bundle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/swagger-ui-standalone-preset.js') }}"></script>

    {{-- Initialize Swagger --}}
    <script>
        window.onload = function() {
            // Begin Swagger UI call region
            const ui = SwaggerUIBundle({
                url: '{{ asset('openapi.json') }}',
                dom_id: '#swagger-ui',
                deepLinking: true,
                docExpansion: false,
                syntaxHighlight: {
                    theme: "arta"
                },
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: 'StandaloneLayout'
            });

            window.ui = ui;
        };
    </script>
</body>
</html>
