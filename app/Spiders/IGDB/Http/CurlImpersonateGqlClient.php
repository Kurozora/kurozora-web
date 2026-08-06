<?php

namespace App\Spiders\IGDB\Http;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Support\Facades\Process;
use RoachPHP\Http\ClientInterface;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use Throwable;

/**
 * A Roach HTTP client that dispatches requests through curl-impersonate.
 */
class CurlImpersonateGqlClient implements ClientInterface
{
    /**
     * The curl-impersonate binary.
     *
     * @var string
     */
    protected string $binary;

    /**
     * The browser profile curl-impersonate mimics.
     *
     * @var string
     */
    protected string $profile;

    /**
     * The per-request timeout in seconds.
     *
     * @var int
     */
    protected int $timeout;

    /**
     * The cookie jar shared across the run.
     *
     * @var string
     */
    protected string $cookieJar;

    /**
     * The session CSRF token.
     *
     * @var string|null
     */
    protected ?string $csrfToken = null;

    /**
     * The sentinel separating the response body from the status code on stdout.
     *
     * @var string
     */
    protected const STATUS_SENTINEL = "\n<<<CURL_IMPERSONATE_STATUS>>>";

    public function __construct()
    {
        $this->binary = config('scraper.curl_impersonate.binary');
        $this->profile = config('scraper.curl_impersonate.profile');
        $this->timeout = (int) config('scraper.curl_impersonate.timeout');
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'igdb_cookies_');
    }

    /**
     * Remove the cookie jar when the run finishes.
     */
    public function __destruct()
    {
        if (is_file($this->cookieJar)) {
            @unlink($this->cookieJar);
        }
    }

    /**
     * Dispatch every request sequentially, surfacing transport failures as
     * synthetic 5xx responses so the run continues.
     *
     * @param list<Request>             $requests
     * @param ?callable(Response): void $onFulfilled
     * @param ?callable(mixed): void    $onRejected
     *
     * @return void
     */
    public function pool(
        array $requests,
        ?callable $onFulfilled = null,
        ?callable $onRejected = null,
    ): void {
        foreach ($requests as $request) {
            try {
                $response = $this->send($request);
            } catch (Throwable $throwable) {
                logger()->error('[CurlImpersonateGqlClient] ' . $throwable->getMessage(), ['uri' => $request->getUri()]);
                $response = new Response(new PsrResponse(500, [], ''), $request);
            }

            if ($onFulfilled !== null) {
                $onFulfilled($response);
            }
        }
    }

    /**
     * Send a single request through curl-impersonate.
     *
     * @param Request $request
     *
     * @return Response
     */
    protected function send(Request $request): Response
    {
        $this->ensureSession();

        $method = $request->getPsrRequest()->getMethod();
        $options = $request->getOptions();
        $body = isset($options['json'])
            ? json_encode($options['json'], JSON_THROW_ON_ERROR)
            : ($options['body'] ?? '');

        $command = $this->command($method, $request->getUri(), $body);

        $result = Process::timeout($this->timeout)
            ->input($body)
            ->run($command);

        if ($result->failed()) {
            throw new \RuntimeException('curl-impersonate exited with an error: ' . $result->errorOutput());
        }

        [$responseBody, $status] = $this->splitOutput($result->output());

        return new Response(
            new PsrResponse($status, [], $responseBody),
            $request,
        );
    }

    /**
     * Warm the session before the first request.
     *
     * @return void
     */
    protected function ensureSession(): void
    {
        if ($this->csrfToken !== null) {
            return;
        }

        Process::timeout($this->timeout)->run([
            $this->binary,
            '--impersonate', $this->profile,
            '-s',
            '-o', '/dev/null',
            '-c', $this->cookieJar,
            '-b', $this->cookieJar,
            config('scraper.domains.igdb.base'),
        ]);

        $this->csrfToken = $this->readCsrfCookie();
    }

    /**
     * Build the curl-impersonate command for the given request.
     *
     * @param string $method
     * @param string $uri
     * @param string $body
     *
     * @return list<string>
     */
    protected function command(string $method, string $uri, string $body): array
    {
        $base = config('scraper.domains.igdb.base');

        $command = [
            $this->binary,
            '--impersonate', $this->profile,
            '-s',
            '--compressed',
            '-b', $this->cookieJar,
            '-c', $this->cookieJar,
            '-X', $method,
            '-H', 'accept: application/json',
            '-H', 'content-type: application/json',
            '-H', 'origin: ' . $base,
            '-H', 'referer: ' . $base . '/',
            '-H', 'sec-fetch-dest: empty',
            '-H', 'sec-fetch-mode: cors',
            '-H', 'sec-fetch-site: same-origin',
            '-H', 'x-requested-with: XMLHttpRequest',
            '-w', self::STATUS_SENTINEL . '%{http_code}',
        ];

        if ($this->csrfToken !== null) {
            $command[] = '-H';
            $command[] = 'x-csrf-token: ' . $this->csrfToken;
        }

        if ($body !== '') {
            $command[] = '--data-binary';
            $command[] = '@-';
        }

        $command[] = $uri;

        return $command;
    }

    /**
     * Split the combined stdout into the response body and status code.
     *
     * @param string $output
     *
     * @return array{0: string, 1: int}
     */
    protected function splitOutput(string $output): array
    {
        $position = strrpos($output, self::STATUS_SENTINEL);

        if ($position === false) {
            return [$output, 200];
        }

        $body = substr($output, 0, $position);
        $status = (int) substr($output, $position + strlen(self::STATUS_SENTINEL));

        return [$body, $status ?: 200];
    }

    /**
     * Read the token from the cookie jar.
     *
     * @return string|null
     */
    protected function readCsrfCookie(): ?string
    {
        if (!is_file($this->cookieJar)) {
            return null;
        }

        foreach (file($this->cookieJar, FILE_IGNORE_NEW_LINES) as $line) {
            $columns = explode("\t", $line);

            if (count($columns) >= 7 && $columns[5] === 'csrf') {
                return $columns[6];
            }
        }

        return null;
    }
}
