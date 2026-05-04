document.addEventListener("DOMContentLoaded", () => {
  const pageBody = document.body;
  const audio = document.getElementById("audio-player");
  const playBtn = document.querySelector(".play-pause");
  const progress = document.querySelector(".progress");
  const progressBar = document.querySelector(".progress-bar");
  const currentTimeEl = document.querySelector(".current-time");
  const totalTimeEl = document.querySelector(".total-time");
  const volumeBar = document.querySelector(".volume-bar");
  const volumeFill = document.querySelector(".volume");
  const cards = Array.from(document.querySelectorAll(".playlist-card"));
  const likeBtn = document.querySelector(".like-btn");
  const likesCountEl = document.querySelector(".likes-count");
  const titleEl = document.querySelector(".song-title");
  const artistEl = document.querySelector(".artist-name");
  const coverEl = document.querySelector(".player-cover-image");

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

  let likedSongIds;
  try {
    likedSongIds = new Set(
      JSON.parse(pageBody?.dataset.likedSongIds || "[]").map(Number),
    );
  } catch {
    likedSongIds = new Set();
  }

  const currentView = pageBody?.dataset.currentView || "all";
  const isPremium = pageBody?.dataset.isPremium === "1";
  let selectedTrack = null;
  let currentSongId = null;
  let currentIndex = -1;
  let repeatMode = false;
  let songsSinceLastAd = 0;
  let nextAdAfter = isPremium ? Number.POSITIVE_INFINITY : randomBetween(3, 5);
  let isAdPlaying = false;
  let adTimeoutId = null;
  let adProgressId = null;
  const defaultCoverSrc =
    coverEl?.getAttribute("src") || "image.php?file=placeholder.png";

  // Botones de control extra
  const prevBtn = document
    .querySelector(".control-buttons .fa-backward-step")
    ?.closest("button");
  const nextBtn = document
    .querySelector(".control-buttons .fa-forward-step")
    ?.closest("button");
  const repeatBtn = document
    .querySelector(".control-buttons .fa-repeat")
    ?.closest("button");
  const shuffleBtn = document
    .querySelector(".control-buttons .fa-shuffle")
    ?.closest("button");

  const setPlayIcon = (isPlaying) => {
    playBtn.innerHTML = isPlaying
      ? '<i class="fa-solid fa-circle-pause"></i>'
      : '<i class="fa-solid fa-circle-play"></i>';
  };

  function randomBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  function formatTime(seconds) {
    const min = Math.floor(seconds / 60);
    const sec = Math.floor(seconds % 60)
      .toString()
      .padStart(2, "0");
    return `${min}:${sec}`;
  }

  function setControlState(disabled) {
    // Durante el anuncio solo bloqueamos prev/next/shuffle/like, NO el play/pause
    [prevBtn, nextBtn, shuffleBtn].forEach((btn) => {
      if (btn) btn.disabled = disabled;
    });
    progressBar.style.pointerEvents = disabled ? "none" : "auto";
    if (likeBtn) {
      likeBtn.disabled = disabled || !currentSongId;
    }
  }

  const syncLike = (isLiked) => {
    if (likesCountEl) likesCountEl.textContent = likedSongIds.size;
    if (!likeBtn) return;
    likeBtn.innerHTML = `<i class="${isLiked ? "fa-solid" : "fa-regular"} fa-heart"></i>`;
    likeBtn.classList.toggle("is-liked", isLiked);
    likeBtn.disabled = isAdPlaying || !currentSongId;
  };

  const updateTrackInfo = (card) => {
    if (!card) {
      return;
    }

    selectedTrack = card;
    currentSongId = Number(card.dataset.songId || 0) || null;
    currentIndex = cards.indexOf(card);
    if (titleEl) {
      titleEl.textContent = card.dataset.title || "Cancion Actual";
    }
    if (artistEl) {
      artistEl.textContent = card.dataset.artist || "Artista Desconocido";
    }
    if (coverEl && card.dataset.cover) {
      coverEl.src = card.dataset.cover;
    }

    syncLike(currentSongId !== null && likedSongIds.has(currentSongId));
  };

  function clearAdTimers() {
    if (adTimeoutId) {
      clearTimeout(adTimeoutId);
      adTimeoutId = null;
    }
    if (adProgressId) {
      clearInterval(adProgressId);
      adProgressId = null;
    }
  }

  function finishAd(onComplete) {
    clearAdTimers();
    isAdPlaying = false;
    setControlState(false);
    currentTimeEl.textContent = "0:00";
    totalTimeEl.textContent = "0:00";
    progress.style.width = "0%";
    if (window.speechSynthesis) {
      window.speechSynthesis.cancel();
    }
    onComplete?.();
  }

  const AD_SRC = "assets/audio/ads/anuncio-free.mp3";

  function playInterstitial(onComplete) {
    if (isPremium || isAdPlaying) {
      onComplete?.();
      return;
    }

    isAdPlaying = true;
    clearAdTimers();
    setPlayIcon(false);
    setControlState(true);
    currentSongId = null;
    if (titleEl) titleEl.textContent = "Anuncio";
    if (artistEl)
      artistEl.textContent = "Pasa a Premium para escuchar sin interrupciones";
    if (coverEl) coverEl.src = defaultCoverSrc;
    syncLike(false);

    // Cargamos el MP3 del anuncio en el mismo elemento <audio>
    audio.src = AD_SRC;
    progress.style.width = "0%";
    currentTimeEl.textContent = "0:00";
    totalTimeEl.textContent = "0:00";

    audio.play().catch(() => {
      // Si el navegador bloquea el autoplay, terminamos igualmente tras 9 s
      adTimeoutId = setTimeout(() => finishAd(onComplete), 9000);
    });

    // El evento 'ended' del audio nativo disparará finishAd via el listener de abajo
    audio.addEventListener(
      "ended",
      function onAdEnded() {
        audio.removeEventListener("ended", onAdEnded);
        finishAd(onComplete);
      },
      { once: true },
    );
  }

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
      if (isAdPlaying) {
        return;
      }
      if (
        event.target.closest(
          ".admin-song-actions, .card-menu-btn, .remove-from-playlist-btn, .sidebar-playlist-delete",
        )
      ) {
        return;
      }
      loadTrack(card);
    });

    card.addEventListener("dblclick", async (event) => {
      if (isAdPlaying) {
        return;
      }
      if (
        event.target.closest(
          ".admin-song-actions, .card-menu-btn, .remove-from-playlist-btn",
        )
      ) {
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
    if (isAdPlaying) {
      // Durante el anuncio solo pausamos/reanudamos el audio del anuncio
      if (audio.paused) {
        audio.play().catch(() => {});
      } else {
        audio.pause();
      }
      return;
    }
    if (audio.paused) {
      await playCurrentTrack();
    } else {
      audio.pause();
    }
  });

  audio.addEventListener("play", () => setPlayIcon(true));
  audio.addEventListener("pause", () => setPlayIcon(false));
  audio.addEventListener("ended", () => {
    if (isAdPlaying) {
      return;
    }
    setPlayIcon(false);
    if (repeatMode) {
      audio.currentTime = 0;
      audio.play().catch(() => {});
      return;
    }

    if (cards.length === 0) {
      return;
    }

    songsSinceLastAd += 1;
    const nextIndex = (currentIndex + 1) % cards.length;
    const startNextTrack = () => {
      if (loadTrack(cards[nextIndex])) {
        audio.play().catch(() => {});
      }
    };

    if (!isPremium && songsSinceLastAd >= nextAdAfter) {
      songsSinceLastAd = 0;
      nextAdAfter = randomBetween(3, 5);
      playInterstitial(startNextTrack);
      return;
    }

    startNextTrack();
  });

  audio.addEventListener("seeking", (event) => {
    if (!isAdPlaying) {
      return;
    }
    event.preventDefault();
    audio.currentTime = 0;
  });

  // Anterior
  prevBtn?.addEventListener("click", () => {
    if (isAdPlaying) return;
    if (cards.length === 0) return;
    const idx = currentIndex <= 0 ? cards.length - 1 : currentIndex - 1;
    if (loadTrack(cards[idx])) {
      if (!audio.paused) audio.play().catch(() => {});
    }
  });

  // Siguiente
  nextBtn?.addEventListener("click", () => {
    if (isAdPlaying) return;
    if (cards.length === 0) return;
    const idx = currentIndex < 0 ? 0 : (currentIndex + 1) % cards.length;
    if (loadTrack(cards[idx])) {
      if (!audio.paused) audio.play().catch(() => {});
    }
  });

  // Repetir
  repeatBtn?.addEventListener("click", () => {
    repeatMode = !repeatMode;
    repeatBtn.classList.toggle("is-active", repeatMode);
    repeatBtn.style.color = repeatMode ? "var(--accent-color)" : "";
  });

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
    if (isAdPlaying) {
      return;
    }
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
  syncLike(false);

  likeBtn?.addEventListener("click", async () => {
    if (isAdPlaying || !currentSongId) return;
    likeBtn.disabled = true;
    try {
      const res = await fetch("like.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: new URLSearchParams({ song_id: currentSongId }),
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.message);
      data.liked
        ? likedSongIds.add(currentSongId)
        : likedSongIds.delete(currentSongId);
      syncLike(data.liked);
      if (currentView === "likes" && !data.liked) location.reload();
    } catch (err) {
      console.error("Error al actualizar el like:", err);
    } finally {
      likeBtn.disabled = !currentSongId;
    }
  });
});
