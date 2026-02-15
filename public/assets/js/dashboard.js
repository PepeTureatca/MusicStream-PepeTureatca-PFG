// dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    // --- TOGGLE SIDEBAR EN MÓVIL ---
    const sidebar = document.querySelector('.sidebar');
    const hamburgerBtn = document.querySelector('.hamburger-btn');

    if (hamburgerBtn && sidebar) {
        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Cerrar sidebar si se hace click fuera en móvil
        document.addEventListener('click', (e) => {
            const isClickInside = sidebar.contains(e.target) || hamburgerBtn.contains(e.target);
            if (!isClickInside && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    }

    // --- TOGGLE BUSCADOR ---
    const searchToggle = document.querySelector('.search-toggle');
    if (searchToggle) {
        const searchBtn = searchToggle.querySelector('.search-btn');
        const searchForm = searchToggle.querySelector('.search-form');

        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            searchToggle.classList.toggle('active');

            // Focus automático en input cuando se abre
            if (searchToggle.classList.contains('active') && searchForm) {
                const input = searchForm.querySelector('input');
                if (input) input.focus();
            }
        });
    }

    // --- PLAYER JS (opcional si usas player.js separado) ---
    // Puedes inicializar aquí tus controles de audio si quieres
});