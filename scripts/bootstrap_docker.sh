#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'EOF'
Usage: scripts/bootstrap_docker.sh [--project-root PATH] [--env-file PATH] [--dry-run] [--no-up]

Generates a portable .env for the Docker stack and optionally starts the stack.
Defaults are optimized for Linux hosts and any machine where Docker can run.
EOF
}

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$PROJECT_ROOT/.env"
DRY_RUN=0
DOCKER_UP=1

while [[ $# -gt 0 ]]; do
  case "$1" in
    --project-root)
      PROJECT_ROOT="$2"
      shift 2
      ;;
    --env-file)
      ENV_FILE="$2"
      shift 2
      ;;
    --dry-run)
      DRY_RUN=1
      shift
      ;;
    --no-up)
      DOCKER_UP=0
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

mkdir -p "$PROJECT_ROOT"

random_password() {
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -hex 24
  else
    python3 - <<'PY'
import secrets
print(secrets.token_hex(24))
PY
  fi
}

prompt_password() {
  local label="$1"
  local value=""

  if [[ -t 0 ]]; then
    read -r -s -p "$label: " value
    echo
    if [[ -n "$value" ]]; then
      printf '%s' "$value"
      return 0
    fi
  fi

  echo "$label" >&2
  echo "No password was provided; generating a secure random value instead." >&2
  random_password
}

APP_PORT="8080"
APP_BIND_ADDRESS="0.0.0.0"
LOCAL_GID="1000"
if command -v id >/dev/null 2>&1; then
  LOCAL_GID="$(id -g 2>/dev/null || echo 1000)"
fi

DB_HOST="mysql"
DB_PORT="3306"
DB_NAME="chatbot_v2"
DB_USER="chatbot_app"
DB_PASSWORD=""
DB_ROOT_PASSWORD=""
RASA_URL="http://rasa:5005"
OLLAMA_URL="http://ollama:11434/api/chat"
OLLAMA_MODEL="gemma3:1b"
DEBUG_2FA_CODE="false"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Ingresá las contraseñas para MariaDB."
  DB_PASSWORD="$(prompt_password "DB_PASSWORD")"
  DB_ROOT_PASSWORD="$(prompt_password "DB_ROOT_PASSWORD")"

  cat > "$ENV_FILE" <<EOF
APP_PORT=${APP_PORT}
APP_BIND_ADDRESS=${APP_BIND_ADDRESS}
LOCAL_GID=${LOCAL_GID}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}

RASA_URL=${RASA_URL}
OLLAMA_URL=${OLLAMA_URL}
OLLAMA_MODEL=${OLLAMA_MODEL}
DEBUG_2FA_CODE=${DEBUG_2FA_CODE}
EOF
  chmod 600 "$ENV_FILE"
  echo "Generated portable Docker environment at $ENV_FILE"
else
  echo "Existing .env detected at $ENV_FILE; leaving it in place."
fi

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "Dry run only; Docker was not started."
  exit 0
fi

if command -v docker >/dev/null 2>&1; then
  if docker compose version >/dev/null 2>&1; then
    echo "Starting Docker stack..."
    (cd "$PROJECT_ROOT" && docker compose up -d --build)
  elif command -v docker-compose >/dev/null 2>&1; then
    echo "Starting Docker stack with docker-compose..."
    (cd "$PROJECT_ROOT" && docker-compose up -d --build)
  else
    echo "Docker is installed but neither 'docker compose' nor 'docker-compose' is available." >&2
    exit 1
  fi
else
  echo "Docker was not found in PATH. Install Docker Engine and re-run this script." >&2
  exit 1
fi

if [[ "$DOCKER_UP" -eq 1 ]]; then
  echo "Docker stack bootstrapped successfully."
  echo "Open http://localhost:${APP_PORT:-8080}"
fi
