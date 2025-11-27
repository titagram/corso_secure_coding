#!/bin/bash

DUMP="$1"

if [ -z "$DUMP" ]; then
    echo "Uso: $0 <dump.sql>"
    exit 1
fi

echo "[*] Estrazione hash da $DUMP basata sui pattern..."

# MD5 (32 hex)
grep -oE '\b[a-fA-F0-9]{32}\b' "$DUMP" > md5_hashes.txt

# SHA1 (40 hex)
grep -oE '\b[a-fA-F0-9]{40}\b' "$DUMP" > sha1_hashes.txt

# SHA256 (64 hex)
grep -oE '\b[a-fA-F0-9]{64}\b' "$DUMP" > sha256_hashes.txt

# bcrypt $2y$...
grep -oE '\$2[aby]\$[0-9]{2}\$[A-Za-z0-9./]{53}' "$DUMP" > bcrypt_hashes.txt

echo "[+] Hash trovati:"
echo "  MD5:    $(wc -l < md5_hashes.txt)"
echo "  SHA1:   $(wc -l < sha1_hashes.txt)"
echo "  SHA256: $(wc -l < sha256_hashes.txt)"
echo "  bcrypt: $(wc -l < bcrypt_hashes.txt)"

echo "[*] File pronti per Hashcat."
