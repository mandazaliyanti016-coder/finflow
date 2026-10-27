#!/bin/bash
SECRET="dev-secret"
URL="localhost:8000/api/webhooks/payment"

send() {
  local sig=$(printf '%s' "$1" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')
  curl -s -X POST $URL -H "Content-Type: application/json" -H "X-Signature: $sig" -d "$1"
}

echo "== Uji 1: event yang sama dikirim 5x berurutan =="
B1='{"event_id":"evt-001","external_ref":"INV-DEMO-001","amount":150000}'
for i in 1 2 3 4 5; do send "$B1"; echo; done

echo "== Uji 2: event berbeda, faktur sama (harus already_paid) =="
send '{"event_id":"evt-001b","external_ref":"INV-DEMO-001","amount":150000}'; echo

echo "== Uji 3: 5 request bersamaan =="
B2='{"event_id":"evt-002","external_ref":"INV-DEMO-002","amount":200000}'
for i in 1 2 3 4 5; do send "$B2" & done
wait; echo

echo "== Uji 4: tanda tangan salah (harus 401) =="
curl -s -o /dev/null -w "%{http_code}\n" -X POST $URL -H "Content-Type: application/json" -H "X-Signature: salah" -d "$B1"
