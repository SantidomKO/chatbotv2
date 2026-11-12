#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'EOF'
Usage: scripts/bootstrap_docker.sh [--project-root PATH] [--env-file PATH] [--dry-run] [--no-up]

Generates a portable .env for the Docker stack and optionally starts the stack.
On Ubuntu and Linux Mint, missing Docker components are installed with sudo.
--dry-run and --no-up do not install or start Docker.
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

request_sudo() {
  if ! command -v sudo >/dev/null 2>&1; then
    echo "sudo is required to install Docker or start its service." >&2
    exit 1
  fi

  sudo -v
}

prepare_apt_docker_repository() {
  local codename=""
  local architecture=""

  if ! command -v apt-get >/dev/null 2>&1 || [[ ! -r /etc/os-release ]]; then
    echo "Automatic Docker installation requires Ubuntu or Linux Mint with apt." >&2
    exit 1
  fi

  . /etc/os-release
  if [[ "${ID:-}" != "ubuntu" && "${ID:-}" != "linuxmint" ]]; then
    echo "Automatic Docker installation is supported on Ubuntu and Linux Mint only." >&2
    echo "Install Docker Engine and Compose manually, then re-run this script." >&2
    exit 1
  fi

  codename="${UBUNTU_CODENAME:-${VERSION_CODENAME:-}}"
  if [[ -z "$codename" ]]; then
    echo "Could not determine the Ubuntu release codename from /etc/os-release." >&2
    exit 1
  fi

  request_sudo
  sudo apt-get update
  sudo apt-get install -y ca-certificates curl

  architecture="$(dpkg --print-architecture)"
  sudo install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg |
    sudo tee /etc/apt/keyrings/docker.asc >/dev/null
  sudo chmod a+r /etc/apt/keyrings/docker.asc
  printf 'deb [arch=%s signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu %s stable\n' \
    "$architecture" "$codename" |
    sudo tee /etc/apt/sources.list.d/docker.list >/dev/null
  sudo apt-get update
}

install_docker() {
  if command -v docker >/dev/null 2>&1; then
    echo "Docker Engine is installed; installing the missing Compose plugin..."
    request_sudo
    sudo apt-get update
    if apt-cache show docker-compose-v2 >/dev/null 2>&1; then
      sudo apt-get install -y docker-compose-v2
    else
      prepare_apt_docker_repository
      sudo apt-get install -y docker-compose-plugin
    fi
  else
    echo "Docker was not found. Installing Docker Engine and Compose for Ubuntu/Linux Mint..."
    prepare_apt_docker_repository
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  fi
}

ensure_docker() {
  DOCKER_COMPOSE_MODE=""
  DOCKER_PREFIX=()

  if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    DOCKER_COMPOSE_MODE="plugin"
  elif command -v docker >/dev/null 2>&1 && command -v docker-compose >/dev/null 2>&1; then
    DOCKER_COMPOSE_MODE="standalone"
  else
    install_docker
    if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
      DOCKER_COMPOSE_MODE="plugin"
    elif command -v docker >/dev/null 2>&1 && command -v docker-compose >/dev/null 2>&1; then
      DOCKER_COMPOSE_MODE="standalone"
    else
      echo "Docker was installed, but Docker Compose is still unavailable." >&2
      exit 1
    fi
  fi

  if docker info >/dev/null 2>&1; then
    return
  fi

  if [[ -d /run/systemd/system ]] && command -v systemctl >/dev/null 2>&1; then
    if ! systemctl is-active --quiet docker; then
      request_sudo
      sudo systemctl enable --now docker
    fi
  fi

  if docker info >/dev/null 2>&1; then
    return
  fi

  if command -v sudo >/dev/null 2>&1; then
    request_sudo
    if sudo docker info >/dev/null 2>&1; then
      DOCKER_PREFIX=(sudo)
      return
    fi
  fi

  echo "Docker is installed, but the daemon is not running or the current user cannot access it." >&2
  echo "Start the Docker service and ensure your account has permission to use Docker." >&2
  exit 1
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

if [[ "$DOCKER_UP" -eq 1 ]]; then
  ensure_docker
  if [[ "$DOCKER_COMPOSE_MODE" == "plugin" ]]; then
    echo "Starting Docker stack..."
    (cd "$PROJECT_ROOT" && "${DOCKER_PREFIX[@]}" docker compose up -d --build)
  else
    echo "Starting Docker stack with docker-compose..."
    (cd "$PROJECT_ROOT" && "${DOCKER_PREFIX[@]}" docker-compose up -d --build)
  fi

  echo "Docker stack bootstrapped successfully."
  echo "Open http://localhost:${APP_PORT:-8080}"
else
  echo "Configuration generated; Docker was not started."
fi
