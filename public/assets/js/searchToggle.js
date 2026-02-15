document.addEventListener('DOMContentLoaded', () => {
    const searchToggle = document.querySelector('.search-toggle');
    const searchBtn = searchToggle.querySelector('.search-btn');

    searchBtn.addEventListener('click', () => {
        searchToggle.classList.toggle('active');

        const input = searchToggle.querySelector('input');
        if (searchToggle.classList.contains('active') && input) {
            input.focus();
        }
    });
});