document.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('audio-player');
    const playBtn = document.querySelector('.play-pause');
    const progress = document.querySelector('.progress');
    const progressBar = document.querySelector('.progress-bar');
    const currentTimeEl = document.querySelector('.current-time');
    const totalTimeEl = document.querySelector('.total-time');
    const volumeBar = document.querySelector('.volume-bar');
    const volumeFill = document.querySelector('.volume');

    let currentCard = null;

    function formatTime(s) {
        return `${Math.floor(s / 60)}:${Math.floor(s % 60).toString().padStart(2, '0')}`;
    }

    function setPlayIcon(playing) {
        playBtn.innerHTML = playing
            ? '<i class="fa-solid fa-circle-pause"></i>'
            : '<i class="fa-solid fa-circle-play"></i>';
    }

    function loadCard(card) {
        if (!card?.dataset.audio) return;
        currentCard = card;
        audio.src = card.dataset.audio;
        progress.style.width = '0%';
        currentTimeEl.textContent = '0:00';
        totalTimeEl.textContent = '0:00';
        document.querySelector('.song-title').textContent = card.dataset.title || 'Cancion Actual';
        document.querySelector('.artist-name').textContent = card.dataset.artist || 'Artista Desconocido';
        const cover = document.querySelector('.player-cover-image');
        if (cover && card.dataset.cover) cover.src = card.dataset.cover;
    }

    document.querySelectorAll('.playlist-card').forEach(card => {
        card.addEventListener('click', () => loadCard(card));
        card.addEventListener('dblclick', async () => {
            loadCard(card);
            await audio.play().catch(err => console.error(err));
        });
    });

    playBtn.addEventListener('click', async () => {
        if (!audio.src && currentCard) loadCard(currentCard);
        if (audio.paused) {
            await audio.play().catch(err => console.error(err));
        } else {
            audio.pause();
        }
    });

    audio.addEventListener('play', () => setPlayIcon(true));
    audio.addEventListener('pause', () => setPlayIcon(false));
    audio.addEventListener('ended', () => setPlayIcon(false));

    audio.addEventListener('timeupdate', () => {
        if (!audio.duration) return;
        progress.style.width = (audio.currentTime / audio.duration * 100) + '%';
        currentTimeEl.textContent = formatTime(audio.currentTime);
    });

    audio.addEventListener('loadedmetadata', () => {
        totalTimeEl.textContent = formatTime(audio.duration);
    });

    progressBar.addEventListener('click', e => {
        e.stopPropagation();
        if (!audio.duration) return;
        const rect = progressBar.getBoundingClientRect();
        audio.currentTime = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)) * audio.duration;
    });

    volumeBar.addEventListener('click', e => {
        const rect = volumeBar.getBoundingClientRect();
        audio.volume = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
        volumeFill.style.width = (audio.volume * 100) + '%';
    });

    audio.volume = 0.7;
    volumeFill.style.width = '70%';
    setPlayIcon(false);
});