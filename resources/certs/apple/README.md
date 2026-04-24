# Apple Root Certificates

Trust anchors for StoreKit 2 signed-transaction verification, loaded by `appStoreVerifier()` in `app/Helpers/CustomHelpers.php`.

## Certificates

| File                 | Valid through | SHA-256 (DER)                                                      |
|----------------------|---------------|--------------------------------------------------------------------|
| `AppleRootCA-G2.cer` | 2039-04-30    | `c2b9b042dd57830e7d117dac55ac8ae19407d38e41d88f3215bc3a890444a050` |
| `AppleRootCA-G3.cer` | 2039-04-30    | `63343abfb89a6a03ebb57e9b3f5fa7be7c4f5c756f3017b3a8c488c3653e9179` |

Source: <https://www.apple.com/certificateauthority/>. The SHA-256 above is the pinned trust anchor; the bytes on disk are verified against it.

## Rotation

When Apple rotates a root, confirm the new cert's origin through a channel other than the CDN, then:

1. `curl -o resources/certs/apple/AppleRootCA-G.cer https://www.apple.com/certificateauthority/AppleRootCA-G.cer`
2. `shasum -a 256 resources/certs/apple/*.cer`
3. Update the fingerprint in `resources/certs/apple/pins.json`.
4. Update the table above to match.
5. Update the filename list in `appStoreVerifier()` in `app/Helpers/CustomHelpers.php` if the set changed.
6. Commit. CI green confirms disk and apple.com both match the new pin.

## Drift detection

Three independent surfaces will report drift:

- **Nova dashboard** — `Apple root certs` metric (reads the daily check status from cache).
- **`logger()->emergency()`** — emitted by `refresh:apple_root_certs` on any mismatch.
- **CI** — `.github/workflows/apple-cert-check.yml` fails PRs and cron runs.
