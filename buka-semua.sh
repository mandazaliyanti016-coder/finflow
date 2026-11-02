#!/bin/bash
BASE="http://localhost:8000"
URLS=(
  "$BASE/accounts"
  "$BASE/reports?from=2026-10-01&to=$(date +%Y-%m-%d)"
  "$BASE/consolidation"
  "$BASE/invoices"
  "$BASE/reconciliation"
  "$BASE/sales/create"
  "$BASE/expenses/create"
)
for u in "${URLS[@]}"; do
  cmd.exe /c start "" "$u" 2>/dev/null
  sleep 0.5
done
