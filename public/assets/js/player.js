document.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('audio-player');
    const playBtn = document.querySelector('.play-pause');
    const progress = document.querySelector('.progress');
    const progressBar = document.querySelector('.progress-bar');
    const currentTimeEl = document.querySelector('.current-time');
    const totalTimeEl = document.querySelector('.total-time');
    const volumeBar = document.querySelector('.volume-bar');
    const volumeFill = document.querySelector('.volume');

    // Click en tarjeta para reproducir
    document.querySelectorAll('.playlist-card').forEach(card => {
        card.addEventListener('click', () => {
            const audioSrc = card.dataset.audio;
            const title = card.dataset.title;
            const artist = card.dataset.artist;

            audio.src = audioSrc;
            audio.play();
            document.querySelector('.song-title').textContent = title;
            document.querySelector('.artist-name').textContent = artist;
            playBtn.innerHTML = '<i class="fa-solid fa-circle-pause"></i>';
        });
    });

    // Play/Pause
    playBtn.addEventListener('click', () => {
        if (audio.paused) {
            audio.play();
            playBtn.innerHTML = '<i class="fa-solid fa-circle-pause"></i>';
        } else {
            audio.pause();
            playBtn.innerHTML = '<i class="fa-solid fa-circle-play"></i>';
        }
    });

    // Progreso
    audio.addEventListener('timeupdate', () => {
        const percent = (audio.currentTime / audio.duration) * 100;
        progress.style.width = percent + '%';
        currentTimeEl.textContent = formatTime(audio.currentTime);
    });

    audio.addEventListener('loadedmetadata', () => {
        totalTimeEl.textContent = formatTime(audio.duration);
    });

    progressBar.addEventListener('click', (e) => {
        const rect = progressBar.getBoundingClientRect();
        audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
    });

    // Volumen
    volumeBar.addEventListener('click', (e) => {
        const rect = volumeBar.getBoundingClientRect();
        audio.volume = (e.clientX - rect.left) / rect.width;
        volumeFill.style.width = (audio.volume * 100) + '%';
    });

    function formatTime(seconds) {
        const min = Math.floor(seconds / 60);
        const sec = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${min}:${sec}`;
    }
});