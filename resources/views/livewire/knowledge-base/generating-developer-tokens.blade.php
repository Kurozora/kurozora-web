<main>
    <x-slot:title>
        {{ __('Generating Developer Tokens') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Generate a developer token needed to make requests to Kurozora API.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="'Generating Developer Tokens — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Generate a developer token needed to make requests to Kurozora API.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="py-6 max-w-full prose prose-theme lg:prose-lg">
        <x-picture class="mb-8 ml-4 mr-4 not-prose">
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-60" src="{{ asset('images/static/banners/in-app_purchases.webp') }}"  alt="About Personalisation" />
        </x-picture>

        {{-- Header --}}
        <section class="pr-4 pl-4">
            <h1 class="text-2xl font-bold">Generating Developer Tokens</h1>

            <p>Generate a developer token needed to make requests to Kurozora API.</p>

            <x-hr />
        </section>

        {{-- Overview --}}
        <section id="overview" class="pr-4 pl-4">
            <h2 class="text-xl font-bold">
                <a href="#overview">Overview</a>
            </h2>

            <p>To make requests to the Kurozora API, you need to authorize yourself as a trusted developer and
                member of the Kurozora community. The header of every Kurozora API request requires a signed developer
                token.</p>

            <p>Follow the directions below to create and manage developer tokens for all platforms.</p>

            <aside class="pr-4 pl-4 bg-tinted border border-primary rounded-lg overflow-hidden">
                <p class="font-semibold">Tip</p>

                <p>If you’re developing an app for Apple platforms (iOS, tvOS, watchOS or macOS), the recommended way to integrate with Kurozora is to use <a href="{{ config('social.github.url')  }}/KurozoraKit" target="_blank">KurozoraKit</a> for Swift.</p>
            </aside>

            <x-hr />
        </section>

        {{-- Create a Developer Token --}}
        <section id="create-a-developer-token" class="pr-4 pl-4">
            <h2 class="text-xl font-bold">
                <a href="#create-a-developer-token">Create a Developer Token</a>
            </h2>

            <p>A developer token is a signed token used to authenticate a developer in Kurozora API requests. Creating a KurozoraKit identifier and private key allows you to use a developer token to authenticate yourself as a trusted developer and member of the Kurozora community.</p>

            <p>The Kurozora API supports the JSON Web Token (JWT) specification, so you can pass statements and metadata called claims. For more information, see the <a href="https://datatracker.ietf.org/doc/html/rfc7519" target="_blank">JWT specification</a> and the available libraries for generating signed JWTs.</p>

            <p>Construct a developer token as a JSON object whose header contains:</p>

            <ul class="list-disc">
                <li>
                    <p>The algorithm (alg) you use to sign the token, which should have a value of ES256</p>
                </li>
                <li>
                    <p>A UUID (kid) key, obtained when generating a new API client secret</p>
                </li>
            </ul>

            <p>In the <em>claims</em> payload of the token, include:</p>

            <ul class="list-disc">
                <li>
                    <p>The <em>issuer</em> (<code class="not-prose">iss</code>) registered claim key, whose value is your UUID, obtained from your settings</p>
                </li>
                <li>
                    <p>The <em>issued</em> at (<code class="not-prose">iat</code>) registered claim key, whose value indicates the time at which the token was generated, in terms of the number of seconds since epoch, in UTC</p>
                </li>
                <li>
                    <p>The <em>expiration</em> time (<code class="not-prose">exp</code>) registered claim key, whose value must not be greater than <code class="not-prose">15777000</code> (6 months in seconds) from the current Unix time on the server</p>
                </li>
                <li>
                    <p>Optional, but recommended for web clients, use the <em>origin claim</em> (<code class="not-prose">origin</code>). Only use this JWT if the origin header of the request matches one of the values in the array. This addition helps prevent unauthorized use of the tokens. For example: “<code class="not-prose">origin</code>”<code class="not-prose">:[</code>”<code class="not-prose">https://example<wbr />.com</code>”,”<code class="not-prose">https://kurozora<wbr />.example<wbr />.com</code>”<code class="not-prose">]</code>.</p>
                </li>
            </ul>

            <p>A decoded developer token has the following format.</p>

            <pre><code class="not-prose">{<br />     "alg": "ES256",<br />     "kid": "DDA8612A-4CC1-4AB1-AE4C-B296CAE1F1B4"<br />}<br />{<br />     "iss": "23251278-00B5-4ACB-945E-338622433C91",<br />     "iat": 1524009600,<br />     "exp": 1539786600<br />}</code></pre>

            <p>After you create the token, sign it with your KurozoraKit private key using the ES256 algorithm.</p>


            <aside class="pr-4 pl-4 bg-tinted border border-primary rounded-lg overflow-hidden">
                <p class="font-semibold">Note</p>

                <p>ES256 is the <a href="https://datatracker.ietf.org/doc/html/rfc7518" target="_blank"> JSON Web Algorithms (JWA)</a> name for the Elliptic Curve Digital Signature Algorithm (ECDSA) with the P-256 curve and the SHA-256 hash.</p>
            </aside>

            <x-hr />
        </section>

        {{-- Authorize Requests --}}
        <section id="authorize-requests" class="pr-4 pl-4">
            <h2 class="text-xl font-bold">
                <a href="#authorize-requests">Authorize Requests</a>
            </h2>

            <p>A developer token is used to authorize all Kurozora API requests. If you manage this directly, in all requests, pass the <code class="not-prose">X-API-Token</code> header set to the developer token.</p>

            <pre><code class="not-prose">curl -v -H 'X-API-Token: [developer token]' "https://api.kurozora.app/v1/test"</code></pre>

            <p>To sign in and authenticate requests for a Kurozora user, see User <a target="_blank">Authentication for KurozoraKit</a>. For more information about requests, responses, and error handling, see <a target="_blank">Handling Requests and Responses</a>.</p>

            <x-hr />
        </section>

        {{-- Request Rate Limiting --}}
        <section id="request-rate-limiting" class="pr-4 pl-4">
            <h2 class="text-xl font-bold">
                <a href="#request-rate-limiting">Request Rate Limiting</a>
            </h2>

            <p>Kurozora API limits the number of requests your app can make using a developer token within a specific period of time. If this limit is exceeded, you’ll temporarily receive <code>429 Too Many Requests</code> error responses for requests that use the token. This error resolves itself shortly after the request rate has reduced.</p>
        </section>
    </div>
</main>
