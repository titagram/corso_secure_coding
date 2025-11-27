#!/bin/bash

USER_MAP="user_hash_map.txt"
CRACKED="$1"
OUTPUT="user_password_map.txt"

if [ -z "$CRACKED" ]; then
    echo "Uso: $0 <file_cracked_da_hashcat>"
    echo "Esempio: $0 md5_cracked.txt"
    exit 1
fi

if [ ! -f "$USER_MAP" ]; then
    echo "Errore: manca $USER_MAP (usa SELECT username, password_hash FROM users)"
    exit 1
fi

echo "[*] Creazione mapping username → password..."
echo "" > "$OUTPUT"

# Per ogni riga tipo: hash:password
while IFS=':' read -r hash password; do
    # Trova l'username corrispondente
    username=$(grep "$hash" "$USER_MAP" | awk '{print $1}')
    if [ -n "$username" ]; then
        echo "$username:$password" >> "$OUTPUT"
    fi
done < "$CRACKED"

echo "[+] Mapping completato → $OUTPUT"
cat "$OUTPUT"
