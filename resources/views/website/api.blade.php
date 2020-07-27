<html>
    <head>
        <title>Kurozora API</title>

        <meta charset="UTF-8">
        <link rel="icon" href="{{ $api_logo }}">

        {{-- Don't index the API docs --}}
        <meta name="robots" content="noindex">

        {{-- Swagger CSS --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('css/swagger-ui.css') }}">
    </head>
    <body style="padding: 0; margin: 0;">
        <div id="swagger-ui"></div>

        {{-- Include Swagger JS files --}}
        <script type="text/javascript" src="{{ asset('js/swagger-ui-standalone-preset.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/swagger-ui-bundle.js') }}"></script>

        {{-- Initialize Swagger --}}
        <script>
            window.onload = function() {
                // Begin Swagger UI call region
                window.swaggerUi = SwaggerUIBundle({
                    url: '{{ $openapi_json_file  }}',
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset
                    ],
                    layout: 'StandaloneLayout'
                });
            };
        </script>

        {{-- Custom style --}}
        <style>
            .kuro-warn {
                padding: 10px 5px;
                background: crimson;
                color: #ffffff;
                display: inline-block;
                font-size: 20px;
                font-weight: bold;
                margin: 10px 0;
                border-radius: 5px;
            }

            .kuro-tip {
                padding: 10px 5px;
                background: lightskyblue;
                color: #ffffff;
                display: inline-block;
                font-size: 20px;
                font-weight: bold;
                margin: 10px 0;
                border-radius: 5px;
            }

            .topbar-wrapper img[alt="Swagger UI"], .topbar-wrapper span {
                visibility: collapse;
            }

            .topbar-wrapper .link:after {
                content: url('{{ $api_logo }}');
                position: absolute;
            }
        </style>
    </body>
</html>
