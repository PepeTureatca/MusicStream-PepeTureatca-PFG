document.addEventListener('DOMContentLoaded', () => {
    let currentAudio = null;
    let currentTitle = null;
    let currentArtist = null;
    let anuncio = new Audio('assets/audio/anuncio.mp3');

    const cards = document.querySelectorAll('.playlist-card');
    const songTitleEl = document.querySelector('.song-title');
    const artistNameEl = document.querySelector('.artist-name');
    const playPauseBtn = document.querySelector('.play-pause i');

    cards.forEach(card => {
        card.addEventListener('click', () => {
            // Reproducir anuncio primero
            anuncio.play().then(() => {
                if(currentAudio) currentAudio.pause();
                currentAudio = new Audio(card.dataset.audio);
                currentTitle = card.dataset.title;
                currentArtist = card.dataset.artist;
                songTitleEl.textContent = currentTitle;
                artistNameEl.textContent = currentArtist;
                currentAudio.play();
                playPauseBtn.classList.remove('fa-circle-play');
                playPauseBtn.classList.add('fa-circle-pause');
            });
        });
    });

    // Control play/pause
    document.querySelector('.play-pause').addEventListener('click', () => {
        if(!currentAudio) return;
        if(currentAudio.paused){
            currentAudio.play();
            playPauseBtn.classList.remove('fa-circle-play');
            playPauseBtn.classList.add('fa-circle-pause');
        } else {
            currentAudio.pause();
            playPauseBtn.classList.remove('fa-circle-pause');
            playPauseBtn.classList.add('fa-circle-play');
        }
    });
});