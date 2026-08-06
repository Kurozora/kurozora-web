#!/usr/bin/env bash
# Regenerate google-special-crawlers.yaml from Google's published crawler IP ranges.
# Unions Googlebot, special crawlers, and user-triggered Google fetchers.
set -euo pipefail

cd "$(dirname "$0")"

python3 - <<'PY' > google-special-crawlers.yaml
import json, urllib.request

SOURCES = [
    "https://developers.google.com/static/search/apis/ipranges/googlebot.json",
    "https://developers.google.com/static/search/apis/ipranges/special-crawlers.json",
    "https://developers.google.com/static/search/apis/ipranges/user-triggered-fetchers-google.json",
]

seen = set()
prefixes = []
meta = []
for url in SOURCES:
    data = json.loads(urllib.request.urlopen(url).read())
    meta.append((url, data.get("creationTime")))
    for p in data["prefixes"]:
        cidr = p.get("ipv4Prefix") or p.get("ipv6Prefix")
        if cidr and cidr not in seen:
            seen.add(cidr)
            prefixes.append(cidr)

print("# Synced from Google's published crawler IP ranges:")
for url, created in meta:
    print(f"#   {url} (updated {created})")
print(f"# {len(prefixes)} unique prefixes")
print()
print("- name: google-crawlers")
print('  user_agent_regex: "Googlebot|Google-InspectionTool|Google-Site-Verification|AdsBot-Google|GoogleOther|Mediapartners-Google|Storebot-Google|Google-Read-Aloud|Google-CloudVertexBot"')
print("  action: ALLOW")
print("  remote_addresses:")
for cidr in prefixes:
    print(f'    - "{cidr}"')
PY

echo "Wrote $(wc -l < google-special-crawlers.yaml) lines to google-special-crawlers.yaml"
