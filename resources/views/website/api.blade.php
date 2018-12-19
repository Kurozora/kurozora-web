<html>
    <head>
        <title>Kurozora API</title>

        <meta charset="UTF-8">
        <link rel="icon" href="{{ $api_logo }}">

        {{-- Don't index the API docs --}}
        <meta name="robots" content="noindex">

        {{-- Swagger CSS --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('css/swagger-ui_3.20.3.css') }}">
    </head>
    <body style="padding: 0; margin: 0;">
        <div id="swagger-ui"></div>

        {{-- Include Swagger JS files --}}
        <script type="text/javascript" src="{{ asset('js/swagger-ui-standalone-preset_3.20.3.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/swagger-ui-bundle_3.20.3.js') }}"></script>

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