#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEST_DIR="$(mktemp -d)"
trap 'rm -rf "$TEST_DIR"' EXIT

bash "$ROOT_DIR/scripts/bootstrap_docker.sh" --project-root "$TEST_DIR" --env-file "$TEST_DIR/.env" --dry-run </dev/null

grep -q '^DB_HOST=mysql$' "$TEST_DIR/.env"
grep -q '^APP_BIND_ADDRESS=0.0.0.0$' "$TEST_DIR/.env"
grep -q '^DB_NAME=chatbot_v2$' "$TEST_DIR/.env"
grep -q '^RASA_URL=http://rasa:5005$' "$TEST_DIR/.env"
grep -q '^LOCAL_GID=' "$TEST_DIR/.env"
grep -Eq '^DB_PASSWORD=[[:xdigit:]]{48}$' "$TEST_DIR/.env"
grep -Eq '^DB_ROOT_PASSWORD=[[:xdigit:]]{48}$' "$TEST_DIR/.env"

echo "bootstrap env test passed"
