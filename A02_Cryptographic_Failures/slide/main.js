document.addEventListener('DOMContentLoaded', () => {
    // Configurazione
    const TOTAL_SLIDES = 8;
    
    // Recupera il numero della slide corrente dalla variabile globale definita nell'HTML
    const currentSlide = typeof slideNumero !== 'undefined' ? slideNumero : 1;
    
    const prevBtn = document.getElementById('nav-prev');
    const nextBtn = document.getElementById('nav-next');
    const menuBtn = document.getElementById('nav-menu');

    // Pulsante Precedente
    if (prevBtn) {
        if (currentSlide <= 1) {
            prevBtn.classList.add('disabled');
        } else {
            prevBtn.addEventListener('click', () => {
                window.location.href = `${currentSlide - 1}.html`;
            });
        }
    }

    // Pulsante Successivo
    if (nextBtn) {
        if (currentSlide >= TOTAL_SLIDES) {
            nextBtn.classList.add('disabled');
        } else {
            nextBtn.addEventListener('click', () => {
                window.location.href = `${currentSlide + 1}.html`;
            });
        }
    }

    // Menu Indice
    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            showIndexMenu();
        });
    }

    // Navigazione da tastiera
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' && currentSlide < TOTAL_SLIDES) {
            window.location.href = `${currentSlide + 1}.html`;
        } else if (e.key === 'ArrowLeft' && currentSlide > 1) {
            window.location.href = `${currentSlide - 1}.html`;
        } else if (e.key === 'Escape') {
            closeIndexMenu();
        }
    });

    // Funzione per mostrare il menu indice
    function showIndexMenu() {
        // Rimuovi menu esistente se presente
        closeIndexMenu();

        const overlay = document.createElement('div');
        overlay.id = 'index-overlay';
        overlay.innerHTML = `
            <div class="index-menu">
                <h2>📑 Indice delle Slide</h2>
                <ul>
                    <li class="${currentSlide === 1 ? 'current' : ''}">
                        <a href="1.html">1. 🔓 Introduzione - OWASP A02:2021</a>
                    </li>
                    <li class="${currentSlide === 2 ? 'current' : ''}">
                        <a href="2.html">2. 🐳 Preparazione dell'Ambiente</a>
                    </li>
                    <li class="${currentSlide === 3 ? 'current' : ''}">
                        <a href="3.html">3. 🔍 Ricognizione con Nmap</a>
                    </li>
                    <li class="${currentSlide === 4 ? 'current' : ''}">
                        <a href="4.html">4. 🔨 Brute Force MySQL</a>
                    </li>
                    <li class="${currentSlide === 5 ? 'current' : ''}">
                        <a href="5.html">5. 💾 Dump del Database</a>
                    </li>
                    <li class="${currentSlide === 6 ? 'current' : ''}">
                        <a href="6.html">6. 🔬 Estrazione degli Hash</a>
                    </li>
                    <li class="${currentSlide === 7 ? 'current' : ''}">
                        <a href="7.html">7. ⚔️ Cracking degli Hash</a>
                    </li>
                    <li class="${currentSlide === 8 ? 'current' : ''}">
                        <a href="8.html">8. 🛡️ Conclusioni e Remediation</a>
                    </li>
                </ul>
                <div class="progress-info">
                    Slide ${currentSlide} di ${TOTAL_SLIDES}
                </div>
                <button class="close-btn" onclick="document.getElementById('index-overlay').remove()">✕ Chiudi</button>
            </div>
        `;

        // Stili inline per l'overlay
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        `;

        const style = document.createElement('style');
        style.textContent = `
            .index-menu {
                background: linear-gradient(135deg, #1a1f4d 0%, #0a0e27 100%);
                border-radius: 16px;
                padding: 40px;
                max-width: 500px;
                width: 90%;
                border: 2px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            }
            .index-menu h2 {
                color: #fff;
                margin-bottom: 25px;
                font-size: 1.5em;
            }
            .index-menu ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .index-menu li {
                margin: 8px 0;
            }
            .index-menu a {
                color: #d0d0e0;
                text-decoration: none;
                display: block;
                padding: 12px 15px;
                border-radius: 8px;
                transition: all 0.2s ease;
                font-size: 1em;
            }
            .index-menu a:hover {
                background: rgba(102, 126, 234, 0.3);
                color: #fff;
                transform: translateX(5px);
            }
            .index-menu li.current a {
                background: rgba(102, 126, 234, 0.5);
                color: #fff;
                font-weight: 600;
            }
            .progress-info {
                text-align: center;
                color: #667eea;
                margin-top: 20px;
                font-size: 0.9em;
            }
            .close-btn {
                display: block;
                width: 100%;
                margin-top: 20px;
                padding: 12px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: #fff;
                border-radius: 8px;
                cursor: pointer;
                font-size: 1em;
                transition: all 0.2s ease;
            }
            .close-btn:hover {
                background: rgba(255, 255, 255, 0.2);
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(overlay);

        // Chiudi cliccando fuori dal menu
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeIndexMenu();
            }
        });
    }

    function closeIndexMenu() {
        const existing = document.getElementById('index-overlay');
        if (existing) {
            existing.remove();
        }
    }
});
