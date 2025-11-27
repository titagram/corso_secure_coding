document.addEventListener('DOMContentLoaded', () => {
    // Numero totale di slide
    const totalSlides = 25;
    
    // Recupera il numero della slide corrente dalla variabile globale definita nell'HTML
    const currentSlide = typeof slideNumero !== 'undefined' ? slideNumero : 1;
    
    const prevBtn = document.getElementById('nav-prev');
    const nextBtn = document.getElementById('nav-next');
    const menuBtn = document.getElementById('nav-menu');

    if (prevBtn) {
        if (currentSlide <= 1) {
            prevBtn.classList.add('disabled');
        } else {
            prevBtn.addEventListener('click', () => {
                window.location.href = `${currentSlide - 1}.html`;
            });
        }
    }

    if (nextBtn) {
        if (currentSlide >= totalSlides) {
            nextBtn.classList.add('disabled');
        } else {
            nextBtn.addEventListener('click', () => {
                window.location.href = `${currentSlide + 1}.html`;
            });
        }
    }

    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            // Crea menu overlay
            const overlay = document.createElement('div');
            overlay.id = 'menu-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(10, 14, 39, 0.95);
                z-index: 2000;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 40px;
                overflow-y: auto;
            `;
            
            const title = document.createElement('h2');
            title.textContent = '📚 Indice delle Slide';
            title.style.cssText = 'color: white; margin-bottom: 30px; font-size: 2em;';
            overlay.appendChild(title);

            const closeBtn = document.createElement('div');
            closeBtn.textContent = '✕';
            closeBtn.style.cssText = `
                position: absolute;
                top: 20px;
                right: 30px;
                font-size: 2em;
                color: white;
                cursor: pointer;
            `;
            closeBtn.addEventListener('click', () => overlay.remove());
            overlay.appendChild(closeBtn);

            const slideTitles = [
                '1. Introduzione – Business Email Compromise',
                '2. Anatomia di un\'Email',
                '3. Il Caso Reale – 20 Novembre 2025',
                '4. Domini Fraudolenti – Typosquatting',
                '5. Reply-Chain Hijacking',
                '6. Perché i Filtri Falliscono',
                '7. Lab Parte 1: Analisi DNS',
                '8. Lab Parte 1: SPF/DKIM/DMARC',
                '9. Lab Parte 1: Recon Aggiuntivo',
                '10. Lab Parte 2: Whois Domini Attaccante',
                '11. Lab Parte 2: IP e Geolocalizzazione',
                '12. Lab Parte 2: Threat Intelligence',
                '13. Lab Parte 3: Estrarre Header Email',
                '14. Lab Parte 3: Interpretare Header',
                '15. Lab Parte 3: Esercizio Pratico',
                '16. Lab Parte 4: Verifica Compromissione',
                '17. Lab Parte 4: Checklist Compromissione',
                '18. Piano di Difesa: Livelli 1-2',
                '19. Piano di Difesa: Livelli 3-4',
                '20. Piano di Difesa: Livello 5',
                '21. Template Risposta Cliente',
                '22. Checklist Finale',
                '23. Strumenti OSINT Riepilogo',
                '24. Zona Grigia – Confini Legali',
                '25. Conclusioni'
            ];

            const menuContainer = document.createElement('div');
            menuContainer.style.cssText = `
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 15px;
                width: 100%;
                max-width: 1200px;
            `;

            slideTitles.forEach((slideTitle, index) => {
                const item = document.createElement('a');
                item.href = `${index + 1}.html`;
                item.textContent = slideTitle;
                item.style.cssText = `
                    padding: 15px 20px;
                    background: ${index + 1 === currentSlide ? 'rgba(102, 126, 234, 0.3)' : 'rgba(255, 255, 255, 0.05)'};
                    border-radius: 8px;
                    color: ${index + 1 === currentSlide ? '#667eea' : '#d0d0e0'};
                    text-decoration: none;
                    transition: all 0.3s ease;
                    border-left: 4px solid ${index + 1 === currentSlide ? '#667eea' : 'transparent'};
                `;
                item.addEventListener('mouseenter', () => {
                    if (index + 1 !== currentSlide) {
                        item.style.background = 'rgba(255, 255, 255, 0.1)';
                        item.style.borderLeftColor = 'rgba(102, 126, 234, 0.5)';
                    }
                });
                item.addEventListener('mouseleave', () => {
                    if (index + 1 !== currentSlide) {
                        item.style.background = 'rgba(255, 255, 255, 0.05)';
                        item.style.borderLeftColor = 'transparent';
                    }
                });
                menuContainer.appendChild(item);
            });

            overlay.appendChild(menuContainer);
            document.body.appendChild(overlay);

            // Chiudi con ESC
            document.addEventListener('keydown', function closeOnEsc(e) {
                if (e.key === 'Escape') {
                    overlay.remove();
                    document.removeEventListener('keydown', closeOnEsc);
                }
            });
        });
    }

    // Navigazione da tastiera
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft' && currentSlide > 1) {
            window.location.href = `${currentSlide - 1}.html`;
        } else if (e.key === 'ArrowRight' && currentSlide < totalSlides) {
            window.location.href = `${currentSlide + 1}.html`;
        }
    });
});
