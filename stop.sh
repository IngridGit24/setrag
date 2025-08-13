#!/usr/bin/env bash
set -euo pipefail

log() { echo "[$(date +'%H:%M:%S')] $*"; }

kill_pidfile() {
  local name="$1"; local pidfile="$2"
  if [ -f "$pidfile" ]; then
    local pid
    pid=$(cat "$pidfile" || true)
    if [ -n "${pid:-}" ] && kill -0 "$pid" >/dev/null 2>&1; then
      log "stopping $name pid=$pid"
      kill "$pid" 2>/dev/null || true
      sleep 1
      if kill -0 "$pid" >/dev/null 2>&1; then
        log "$name still running, force kill"
        kill -9 "$pid" 2>/dev/null || true
      fi
    fi
    rm -f "$pidfile" || true
  else
    log "no pidfile for $name ($pidfile)"
  fi
}

kill_port() {
  local name="$1"; local port="$2"
  if lsof -ti tcp:"$port" >/dev/null 2>&1; then
    log "killing $name on port :$port"
    lsof -ti tcp:"$port" | xargs kill -9 || true
  fi
}

# Stop Python services via PID files first
kill_pidfile "tracking"        "/tmp/tracking.pid"
kill_pidfile "ai-agent"        "/tmp/ai.pid"
kill_pidfile "users"           "/tmp/users.pid"
kill_pidfile "inventory-py"    "/tmp/inventory.pid"
kill_pidfile "pricing-booking" "/tmp/pricing.pid"

# Stop PHP placeholders (no pidfile by default)
kill_port "auth (php)"            8101
kill_port "inventory (php)"       8102
kill_port "pricing-booking (php)" 8103

# Stop front-end
kill_pidfile "web-b2c" "/tmp/web-b2c.pid"
kill_port "web-b2c" 5173

# Fallback: ensure core ports are free
kill_port "tracking" 8001
kill_port "ai-agent" 8002
kill_port "users" 8104
kill_port "inventory-py" 8105
kill_port "pricing-booking" 8106

log "All stop signals sent."
exit 0


