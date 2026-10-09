#!/usr/bin/env bash
# Serve this static website locally at http://localhost:8080
cd "$(dirname "$0")" || exit 1
echo "Serving $(pwd) at http://localhost:8080 (Ctrl+C to stop)"
exec python3 -m http.server 8080
