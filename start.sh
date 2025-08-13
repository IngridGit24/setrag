#!/usr/bin/env bash
set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"

log() { echo "[$(date +'%H:%M:%S')] $*"; }

kill_port() {
  local port="$1"
  if lsof -ti tcp:"$port" >/dev/null 2>&1; then
    lsof -ti tcp:"$port" | xargs kill -9 || true
  fi
}

wait_http() {
  local url="$1"; local name="$2"; local max=${3:-20}
  for i in $(seq 1 "$max"); do
    code=$(curl -s -o /dev/null -w "%{http_code}" "$url" || true)
    if [ "$code" = "200" ]; then log "ready: $name ($url)"; return 0; fi
    log "waiting $name ($url) attempt $i/$max => $code"; sleep 1
  done
  log "timeout: $name ($url)"
  return 1
}

start_py_service() {
  local name="$1"; local path="$2"; local module="$3"; local port="$4"; local extra_env="$5"; local logfile="$6"; local pidfile="$7"
  log "starting $name on :$port"
  kill_port "$port"
  cd "$path"
  python3 -m venv .venv
  source .venv/bin/activate
  pip install --upgrade pip >/dev/null 2>&1 || true
  pip install -r requirements.txt >/dev/null
  eval "$extra_env" nohup uvicorn "$module" --host 0.0.0.0 --port "$port" > "$logfile" 2>&1 &
  echo $! > "$pidfile"
}

start_php_placeholder() {
  local name="$1"; local path="$2"; local port="$3"; local logfile="$4"; local pidfile="$5"
  if command -v php >/dev/null 2>&1; then
    log "starting $name (PHP) on :$port"
    kill_port "$port"
    cd "$path"
    nohup php -S 0.0.0.0:"$port" -t public > "$logfile" 2>&1 &
    echo $! > "$pidfile"
  else
    log "php not found; skipping $name"
  fi
}

start_node_app() {
  local name="$1"; local path="$2"; local port="$3"; local logfile="$4"; local pidfile="$5"
  if ! command -v node >/dev/null 2>&1 || ! command -v npm >/dev/null 2>&1; then
    log "node/npm not found; skipping $name"
    return 0
  fi
  log "starting $name on :$port"
  kill_port "$port"
  cd "$path"
  npm install >/dev/null
  nohup npm run dev -- --host 0.0.0.0 --port "$port" > "$logfile" 2>&1 &
  echo $! > "$pidfile"
}

# Start Python services
start_py_service "tracking"        "$DIR/services/tracking"         "app.main:app" 8001 ""                               "/tmp/tracking.log" "/tmp/tracking.pid"
start_py_service "ai-agent"        "$DIR/services/ai-agent"         "app.main:app" 8002 ""                               "/tmp/ai.log"       "/tmp/ai.pid"
start_py_service "users"           "$DIR/services/users"            "app.main:app" 8104 ""                               "/tmp/users.log"    "/tmp/users.pid"
start_py_service "inventory-py"    "$DIR/services/inventory-py"     "app.main:app" 8105 ""                               "/tmp/inventory.log" "/tmp/inventory.pid"
start_py_service "pricing-booking" "$DIR/services/pricing-booking-py" "app.main:app" 8106 "export INVENTORY_BASE_URL=http://localhost:8105 USERS_JWT_SECRET=dev-secret-change-me;" "/tmp/pricing.log" "/tmp/pricing.pid"

# Start PHP placeholders (optional)
start_php_placeholder "auth"              "$DIR/services/auth"              8101 "/tmp/php-auth.log" "/tmp/php-auth.pid"
start_php_placeholder "inventory (php)"   "$DIR/services/inventory"         8102 "/tmp/php-inventory.log" "/tmp/php-inventory.pid"
start_php_placeholder "pricing-booking"   "$DIR/services/pricing-booking"   8103 "/tmp/php-pricing.log" "/tmp/php-pricing.pid"

# Start front-end
start_node_app "web-b2c" "$DIR/apps/web-b2c" 5173 "/tmp/web-b2c.log" "/tmp/web-b2c.pid"

# Health checks
wait_http "http://localhost:8001/health" "tracking"
wait_http "http://localhost:8002/health" "ai-agent"
wait_http "http://localhost:8104/health" "users"
wait_http "http://localhost:8105/health" "inventory-py"
wait_http "http://localhost:8106/health" "pricing-booking-py"

log "All services attempted. Open front: http://localhost:5173"
log "Users:            http://localhost:8104/health"
log "Inventory (py):   http://localhost:8105/health"
log "Pricing (py):     http://localhost:8106/health"
log "Tracking:         http://localhost:8001/health"
log "AI Agent:         http://localhost:8002/health"
log "Auth (php):       http://localhost:8101/health (if php present)"
log "Inventory (php):  http://localhost:8102/health (if php present)"
log "Pricing (php):    http://localhost:8103/health (if php present)"

exit 0


