#!/bin/bash
# torecon – Lokaler Testserver
# Starte mit: bash start.sh
# Dann Browser öffnen: http://localhost:8080

cd "$(dirname "$0")"
echo ""
echo "  torecon – Lokaler Testserver"
echo "  ─────────────────────────────"
echo "  URL: http://localhost:8080"
echo "  Interner Bereich Passwort: torecon2024"
echo ""
echo "  Ctrl+C zum Beenden"
echo ""
python3 -m http.server 8080
