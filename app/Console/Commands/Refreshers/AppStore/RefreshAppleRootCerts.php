<?php

namespace App\Console\Commands\Refreshers\AppStore;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

class RefreshAppleRootCerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:apple_root_certs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the latest Apple root certs from apple.com and verify against pinned fingerprints and committed baseline certs.';

    /**
     * Apple PKI download base.
     *
     * @var string
     */
    private const string APPLE_PKI_BASE = 'https://www.apple.com/certificateauthority';

    /**
     * Directory holding the committed baseline certificates.
     *
     * @var string
     */
    private const string COMMITTED_DIRECTORY = 'certs/apple';

    /**
     * Path to the pinned SHA-256 fingerprints, relative to resource_path().
     *
     * @var string
     */
    private const string PINS_FILE = 'certs/apple/pins.json';

    /**
     * Cache key for storing the last check status and report.
     *
     * @var string
     */
    public const string STATUS_CACHE_KEY = 'apple_root_certs:status';

    /**
     * Backoff delays in seconds for retrying Apple fetches before giving up.
     *
     * @var list<int>
     */
    private const array BACKOFF_SECONDS = [0, 10, 30];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $pins = $this->loadPins();
        if ($pins === null) {
            return Command::FAILURE;
        }

        $hasFailure = false;
        $report = [];

        foreach ($pins as $filename => $expectedFingerprint) {
            $committedPath = resource_path(self::COMMITTED_DIRECTORY . '/' . $filename);
            $committedFingerprint = is_file($committedPath) ? hash_file('sha256', $committedPath) : null;

            $diskOk = $committedFingerprint === $expectedFingerprint;

            if (!$diskOk) {
                $this->error(sprintf(
                    'Committed cert %s does not match the pinned fingerprint. Expected %s, on-disk %s. The deploy artifact may be corrupt.',
                    $filename,
                    $expectedFingerprint,
                    $committedFingerprint ?? '(missing)',
                ));

                logger()->emergency('Apple root cert on-disk drift', [
                    'filename' => $filename,
                    'expected_sha256' => $expectedFingerprint,
                    'disk_sha256' => $committedFingerprint,
                    'path' => $committedPath,
                ]);

                $hasFailure = true;
            }

            $remoteBody = $this->downloadWithRetry($filename);
            $remoteReachable = $remoteBody !== null;
            $remoteFingerprint = $remoteReachable ? hash('sha256', $remoteBody) : null;
            $remoteOk = $remoteReachable && hash_equals($expectedFingerprint, $remoteFingerprint);

            if (!$remoteReachable) {
                $this->warn(sprintf('[%s] Could not reach Apple after retries; pin is intact on disk, deferring to next run.', $filename));

                $hasFailure = true;
            } else if (!$remoteOk) {
                $this->error(sprintf(
                    'Apple is serving a different %s than our pinned fingerprint. Expected %s, remote %s. MANUAL ACTION REQUIRED — verify via apple.com/certificateauthority/ over a second channel, then update %s and replace the committed cert.',
                    $filename,
                    $expectedFingerprint,
                    $remoteFingerprint,
                    self::PINS_FILE,
                ));

                logger()->emergency('Apple root cert remote drift — possible rotation or CDN compromise', [
                    'filename' => $filename,
                    'expected_sha256' => $expectedFingerprint,
                    'remote_sha256' => $remoteFingerprint,
                    'url' => self::APPLE_PKI_BASE . '/' . $filename,
                ]);

                $hasFailure = true;
            } else {
                $this->info(sprintf('[%s] OK (pin matches both disk and Apple).', $filename));
            }

            $report[$filename] = [
                'expected_sha256' => $expectedFingerprint,
                'disk_sha256' => $committedFingerprint,
                'disk_ok' => $diskOk,
                'remote_sha256' => $remoteFingerprint,
                'remote_reachable' => $remoteReachable,
                'remote_ok' => $remoteOk,
            ];
        }

        Cache::forever(self::STATUS_CACHE_KEY, [
            'checked_at' => now()->toIso8601String(),
            'ok' => !$hasFailure,
            'files' => $report,
        ]);

        return $hasFailure ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Load the pinned fingerprints, or null when the file is missing or malformed.
     *
     * @return array<string, string>|null
     */
    private function loadPins(): ?array
    {
        $path = resource_path(self::PINS_FILE);

        if (!is_file($path)) {
            $this->error(sprintf('Pins file missing at %s. The deploy artifact may be corrupt.', $path));

            logger()->emergency('Apple root cert pins file missing', [
                'path' => $path,
            ]);

            return null;
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error(sprintf('Pins file at %s is malformed: %s', $path, $e->getMessage()));

            logger()->emergency('Apple root cert pins file malformed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return $decoded;
    }

    /**
     * Fetch the cert with bounded retries, or null on exhaustion.
     */
    private function downloadWithRetry(string $filename): ?string
    {
        $url = self::APPLE_PKI_BASE . '/' . $filename;

        foreach (self::BACKOFF_SECONDS as $delay) {
            if ($delay > 0) {
                sleep($delay);
            }

            try {
                $response = Http::timeout(10)
                    ->withUserAgent('kurozora-web refresh:apple_root_certs')
                    ->get($url);
            } catch (Throwable $e) {
                $this->warn(sprintf('[%s] Attempt failed: %s', $filename, $e->getMessage()));
                continue;
            }

            if ($response->successful()) {
                return $response->body();
            }

            $this->warn(sprintf('[%s] HTTP %d from %s', $filename, $response->status(), $url));
        }

        return null;
    }
}
