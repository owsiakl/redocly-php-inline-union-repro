#!/usr/bin/env bash
# Usage: ./run.sh [redocly-cli-version]   (default: latest)
set -euo pipefail
cd "$(dirname "$0")"

rm -rf out
npx --yes "@redocly/cli@${1:-latest}" generate-client repro --config ./redocly.yaml

echo
echo '--- Pet::$owner type ---'
grep -nE '\$owner = null' out/client.php

echo
echo '--- Pet::fromArray() owner argument ---'
grep -nE "owner: \\\$data\['owner'\]" out/client.php

echo
echo '--- Calling postPet() ---'
php -S 127.0.0.1:8099 server.php >/dev/null 2>&1 &
SERVER_PID=$!
trap 'kill $SERVER_PID' EXIT
sleep 0.5
php request.php
