# OSINT Lab – Email Spoofing, BEC e Domain Impersonation

## 1. Introduzione
Questo laboratorio guida passo‑passo nell’analisi OSINT di un caso realistico di:
- Email spoofing
- Domain impersonation (typosquatting)
- Possibile compromissione casella (BEC – Business Email Compromise)
- Investigazione su domini, header e attaccanti

Il file è utilizzabile come traccia di lezione, esercitazione tecnica o manuale operativo.

---

# 2. Anatomia generale di un’email

## 2.1 Componenti fondamentali
- **Envelope From (MAIL FROM):** utilizzato da SMTP per il trasporto.  
- **Header From:** ciò che l’utente vede come mittente.  
- **Return‑Path:** dove tornano i bounce.  
- **Received:** catena di server attraversati.  
- **SPF/DKIM/DMARC:** meccanismi di autenticazione del dominio.  
- **Message‑ID:** identificativo univoco della mail.

## 2.2 Perché è vulnerabile
Il protocollo SMTP non verifica di default l’identità del mittente:  
→ È possibile inviare mail con “From:” arbitrario.  
→ È possibile creare domini simili ai legittimi.  
→ È possibile inserirsi in conversazioni rubando o intercettando mail.

---

# 3. Caso reale (anonimizzato)
Abbiamo tre elementi:
- Dominio legittimo del cliente → `example.mc`
- Dominio legittimo del fornitore → `azienda.com`
- Dominio fasullo → `rnonaco.cam`, `nvovokolor.com` (typosquatting)

Pattern dell’attacco:
1. Arriva una mail legittima.  
2. L’attaccante invia un sollecito da un dominio quasi identico.  
3. La vittima risponde alla versione falsa.  
4. L’attaccante inoltra al vero cliente una risposta modificata, usando un altro dominio falso.  
5. Chiede modifiche finanziarie (IBAN estero).

Ciò richiede accesso o visibilità su almeno una casella.

---

# 4. Ipotesi tecniche sull’attacco

## 4.1 Dominio typosquatted
L’attaccante registra domini simili:
- `example.mc` → `rnoneco.cam`
- `azienda.com` → `aziencla.com`

## 4.2 Accesso a una casella
Possibile tramite:
- password trovata su leak  
- malware  
- phishing  
- regola di forwarding nascosta  
- accesso IMAP non monitorato

## 4.3 Reply-chain hijacking
L’attaccante vede i contenuti reali e costruisce mail perfettamente coerenti.

---

# 5. OSINT Lab – Parte 1: Analisi dei domini della vittima

## 5.1 Identificazione server e record DNS
Comandi:

```
nslookup azienda.com
dig azienda.com any
dig azienda.com MX
whois azienda.com
```

## 5.2 Verifica configurazioni SPF/DKIM/DMARC
Strumenti:
- https://mxtoolbox.com
- https://dmarcian.com
- https://www.kitterman.com/spf/validate.html

Comandi CLI:

```
dig txt azienda.com
dig txt _dmarc.azienda.com
```

Risultati attesi:
- SPF valido?  
- DKIM presente?  
- DMARC impostato su none/quarantine/reject?  

## 5.3 Ricerca esposizione pannelli della posta
```
nmap -p 80,443 azienda.com --script http-title
```

Verifica:
- pannelli di webmail esposti?
- versioni note vulnerabili?

## 5.4 Raccolta email pubbliche
Strumenti:
- https://hunter.io  
- https://emailrep.io  
- https://intelx.io  

Comandi:

```
emailrep user@azienda.com
```

---

# 6. OSINT Lab – Parte 2: Analisi dei domini dell’attaccante

## 6.1 Whois completo
```
whois rnonaco.cam
whois nvovokolor.com
```

Check:
- data di registrazione
- registrar
- privacy shield
- pattern sospetti (registrato da pochi giorni)

## 6.2 Risoluzione IP e geolocalizzazione
```
nslookup rnonaco.cam
whois <IP>
```

## 6.3 Reverse DNS / Passive DNS
Con strumenti come:
- https://securitytrails.com  
- https://dnsdumpster.com  

Obiettivo:
- vedere se l’IP contiene altri domini fraudolenti  
- correlazioni con campagne note  

## 6.4 Ricerca indicatori pubblici
```
curl https://urlscan.io/api/v1/search/?q=domain:rnonaco.cam
```

---

# 7. OSINT Lab – Parte 3: Analisi Header

## 7.1 Estrarre header
In Gmail:
- “Mostra originale”

In Outlook:
- “Mostra sorgente messaggio”

## 7.2 Campi da analizzare
- **From:** può essere falsificato  
- **Return‑Path:** indica il vero mittente SMTP  
- **Received:** ricostruisce il percorso  
- **Authentication‑Results:** mostra SPF, DKIM, DMARC  
- **Message‑ID:** spesso rivela server anomali  

## 7.3 Esercizio pratico
L’istruttore fornirà:
- una mail legittima  
- una mail spoofata  
- un header generato da dominio simile  

Il compito:
1. Identificare differenze nei Received  
2. Verificare SPF  
3. Verificare DKIM  
4. Trovare dominio reale che ha spedito  

---

# 8. OSINT Lab – Parte 4: Ipotesi di compromissione casella

## 8.1 Controlli su Microsoft 365 / Google Workspace
- Accessi recenti  
- Regole di inoltro  
- App OAuth collegate  
- Sessioni attive  
- Password reuse  

## 8.2 Strumenti utili
- Azure sign-in logs  
- Google “Attività dispositivi”  
- HIBP per leak password  

---

# 9. Zona Grigia – Pentesting Light (Disclaimer)

Le attività consentite SOLO con autorizzazione:
- controlli DNS  
- controlli SPF/DKIM/DMARC  
- OSINT pubblico  
- open-port recon  
- analisi header  

Le attività NON consentite:
- autenticazione non autorizzata  
- tentativi di login  
- scansioni intrusive  
- accesso a server o file non pubblici  

---

# 10. Contromisure operative

## 10.1 Tecniche
- SPF restrittivo  
- DKIM attivo  
- DMARC p=quarantine o reject  
- MFA obbligatoria  
- ban forwarding esterno salvo eccezioni  
- controlli mensili regole di posta  

## 10.2 Organizzative
- processo di verifica delle richieste di pagamento  
- callback telefonico prima di modificare l’IBAN  
- formazione del personale  

---

# 11. Checklist Finale

### Per valutare un caso sospetto:
[ ] Controllo dominio mittente  
[ ] Espansione header  
[ ] SPF/DKIM/DMARC  
[ ] Verifica server SMTP  
[ ] Whois dominio  
[ ] Data registrazione dominio  
[ ] Correlazioni OSINT  
[ ] Possibile compromissione casella  
[ ] Controllo regole di inoltro  
[ ] MFA attiva  

---

Fine del Lab OSINT.
