document.addEventListener('DOMContentLoaded', () => {
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
        // Logica semplice: prova ad andare avanti. 
        // Se è l'ultima slide, gestirai il link manualmente o lascerai l'errore 404 per ora.
        nextBtn.addEventListener('click', () => {
            window.location.href = `${currentSlide + 1}.html`;
        });
    }

    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            alert('Indice delle slide (To be implemented)');
        });
    }
});