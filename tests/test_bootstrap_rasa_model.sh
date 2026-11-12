#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEST_DIR="$(mktemp -d)"
trap 'rm -rf "$TEST_DIR"' EXIT

PROJECT_ROOT="$TEST_DIR/project"
FAKE_BIN="$TEST_DIR/bin"
mkdir -p "$PROJECT_ROOT/docker" "$PROJECT_ROOT/models" "$FAKE_BIN"
cp "$ROOT_DIR/config.yml" "$ROOT_DIR/docker-compose.yml" "$PROJECT_ROOT/"
cp "$ROOT_DIR/docker/rasa.Dockerfile" "$PROJECT_ROOT/docker/"
cp -R "$ROOT_DIR/domain" "$ROOT_DIR/data" "$PROJECT_ROOT/"

cat > "$FAKE_BIN/docker" <<'EOF'
#!/usr/bin/env bash
set -euo pipefail

printf '%s\n' "$*" >> "$FAKE_DOCKER_LOG"
case "$*" in
  "compose version"|"info")
    exit 0
    ;;
  "compose run "*)
    mkdir -p models
    : > models/mock-model.tar.gz
    ;;
esac
EOF
chmod +x "$FAKE_BIN/docker"
export PATH="$FAKE_BIN:$PATH"
export FAKE_DOCKER_LOG="$TEST_DIR/docker.log"

run_bootstrap() {
  bash "$ROOT_DIR/scripts/bootstrap_docker.sh" \
    --project-root "$PROJECT_ROOT" \
    --env-file "$PROJECT_ROOT/.env" </dev/null
}

run_bootstrap
[[ "$(grep -c '^compose run ' "$FAKE_DOCKER_LOG")" -eq 1 ]]

run_bootstrap
[[ "$(grep -c '^compose run ' "$FAKE_DOCKER_LOG")" -eq 1 ]]

printf '\n# changed training data\n' >> "$PROJECT_ROOT/data/nlu.yml"
run_bootstrap
[[ "$(grep -c '^compose run ' "$FAKE_DOCKER_LOG")" -eq 2 ]]

echo "bootstrap Rasa model test passed"
