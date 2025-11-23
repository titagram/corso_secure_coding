# Lab 2 - OWASP Top 10 Vulnerabilities

Questo laboratorio contiene esercitazioni pratiche su vulnerabilità web comuni basate sulla lista OWASP Top 10.

## Risorse di Apprendimento

### XSS (Cross-Site Scripting)
- [TryHackMe - XSS Room](https://tryhackme.com/room/xss)
- [TryHackMe - Advanced XSS Room](https://tryhackme.com/room/axss)

## Challenge e Soluzioni

### 1. Neighbour Challenge
- **Challenge:** [TryHackMe - Neighbour](https://tryhackme.com/room/neighbour)
- **Solution:** [Neighbour Write-up](https://medium.com/@josephalan17201972/neighbour-tryhackme-write-up-7b48cb7f08b6)

### 2. SQHell Challenge
- **Challenge:** [TryHackMe - SQHell](https://tryhackme.com/room/sqhell)
- **Solution:** [SQHell Write-up](https://darrynbrownfield.co.uk/sqhell)

### 3. Mr. Robot CTF
- **Challenge:** [TryHackMe - Mr. Robot](https://tryhackme.com/room/mrrobot)
- **Solution:** [Mr. Robot CTF Walkthrough](https://medium.com/@cspanias/thms-mr-robot-ctf-walkthrough-2023-55ca5c19fbaf)

## Avvio dei Lab

Ogni cartella contiene un ambiente vulnerabile separato:

- **A01_Broken_Auth** - Vulnerabilità di autenticazione
- **A03_Injection** - SQL Injection e Command Injection
- **A07_Identification_and_Authentication_Failures** - Problemi di autenticazione

Per avviare un lab, entra nella cartella e esegui:

```bash
docker-compose up -d
```

Per fermare un lab:

```bash
docker-compose down
```

## Disclaimer

Questi ambienti sono **intenzionalmente vulnerabili** e devono essere utilizzati solo per scopi educativi in un ambiente controllato.
