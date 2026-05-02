document.addEventListener("DOMContentLoaded", () => {
  const audio = document.getElementById("audio-player");
  const playBtn = document.querySelector(".play-pause");
  const progress = document.querySelector(".progress");
  const progressBar = document.querySelector(".progress-bar");
  const currentTimeEl = document.querySelector(".current-time");
  const totalTimeEl = document.querySelector(".total-time");
  const volumeBar = document.querySelector(".volume-bar");
  const volumeFill = document.querySelector(".volume");
  const cards = Array.from(document.querySelectorAll(".playlist-card"));

  if (
    !audio ||
    !playBtn ||
    !progress ||
    !progressBar ||
    !currentTimeEl ||
    !totalTimeEl ||
    !volumeBar ||
    !volumeFill
  ) {
    return;
  }

  let selectedTrack = null;

  const setPlayIcon = (isPlaying) => {
    playBtn.innerHTML = isPlaying
      ? '<i class="fa-solid fa-circle-pause"></i>'
      : '<i class="fa-solid fa-circle-play"></i>';
  };

  const updateTrackInfo = (card) => {
    if (!card) {
      return;
    }

    selectedTrack = card;
    document.querySelector(".song-title").textContent =
      card.dataset.title || "Cancion Actual";
    document.querySelector(".artist-name").textContent =
      card.dataset.artist || "Artista Desconocido";
    const cover = document.querySelector(".player-cover-image");
    if (cover && card.dataset.cover) {
      cover.src = card.dataset.cover;
    }
  };

  const loadTrack = (card) => {
    if (!card) {
      return false;
    }

    const audioSrc = card.dataset.audio;
    if (!audioSrc) {
      return false;
    }

    updateTrackInfo(card);
    if (audio.src !== audioSrc) {
      audio.src = audioSrc;
      progress.style.width = "0%";
      currentTimeEl.textContent = "0:00";
      totalTimeEl.textContent = "0:00";
    }

    return true;
  };

  const playCurrentTrack = async () => {
    if (!audio.src) {
      const firstTrack = selectedTrack || cards[0];
      if (!loadTrack(firstTrack)) {
        return;
      }
    }

    try {
      await audio.play();
    } catch (err) {
      console.error("No se pudo reproducir el audio:", err);
    }
  };

  const getSeekDuration = () => {
    if (Number.isFinite(audio.duration) && audio.duration > 0) {
      return audio.duration;
    }

    if (audio.seekable && audio.seekable.length > 0) {
      return audio.seekable.end(audio.seekable.length - 1);
    }

    return 0;
  };

  cards.forEach((card) => {
    card.addEventListener("click", (event) => {
      if (event.target.closest(".admin-song-actions")) {
        return;
      }
      loadTrack(card);
    });

    card.addEventListener("dblclick", async (event) => {
      if (event.target.closest(".admin-song-actions")) {
        return;
      }
      if (!loadTrack(card)) {
        return;
      }
      await playCurrentTrack();
    });
  });

  // Play/Pause
  playBtn.addEventListener("click", async () => {
    if (audio.paused) {
      await playCurrentTrack();
    } else {
      audio.pause();
    }
  });

  audio.addEventListener("play", () => setPlayIcon(true));
  audio.addEventListener("pause", () => setPlayIcon(false));
  audio.addEventListener("ended", () => setPlayIcon(false));

  // Progreso
  audio.addEventListener("timeupdate", () => {
    const duration = getSeekDuration();
    if (!duration) {
      return;
    }
    const percent = (audio.currentTime / duration) * 100;
    progress.style.width = percent + "%";
    currentTimeEl.textContent = formatTime(audio.currentTime);
  });

  audio.addEventListener("loadedmetadata", () => {
    const duration = getSeekDuration();
    if (duration) {
      totalTimeEl.textContent = formatTime(duration);
    }
  });

  progressBar.addEventListener("click", (e) => {
    const duration = getSeekDuration();
    if (!duration) {
      return;
    }
    const rect = progressBar.getBoundingClientRect();
    const ratio = Math.min(
      1,
      Math.max(0, (e.clientX - rect.left) / rect.width),
    );
    audio.currentTime = ratio * duration;
  });

  volumeBar.addEventListener("click", (e) => {
    const rect = volumeBar.getBoundingClientRect();
    const rawVolume = (e.clientX - rect.left) / rect.width;
    audio.volume = Math.min(1, Math.max(0, rawVolume));
    volumeFill.style.width = audio.volume * 100 + "%";
  });

  audio.volume = 0.7;
  volumeFill.style.width = "70%";
  setPlayIcon(false);

  function formatTime(seconds) {
    const min = Math.floor(seconds / 60);
    const sec = Math.floor(seconds % 60)
      .toString()
      .padStart(2, "0");
    return `${min}:${sec}`;
  }
});
