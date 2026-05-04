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
  const defaultCoverSrc =
    coverEl?.getAttribute("src") || "image.php?file=placeholder.png";
  const AD_SRC = "assets/audio/ads/anuncio-free.mp3";

  let selectedTrack = null;
  let currentSongId = null;
  let currentIndex = -1;
  let repeatMode = false;

  // ── Anuncios ──────────────────────────────────────────────────────────────
  let songsSinceLastAd = 0;
  let nextAdAfter = isPremium ? Infinity : randomBetween(3, 5);
  let isAdPlaying = false;
  let adFallbackTimer = null;

  // Botones de navegación
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

  // ── Helpers ───────────────────────────────────────────────────────────────
  function randomBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  function formatTime(seconds) {
    const min = Math.floor(seconds / 60);
    const sec = Math.floor(seconds % 60).toString().padStart(2, "0");
    return `${min}:${sec}`;
  }

  const setPlayIcon = (isPlaying) => {
    playBtn.innerHTML = isPlaying
      ? '<i class="fa-solid fa-circle-pause"></i>'
      : '<i class="fa-solid fa-circle-play"></i>';
  };

  // Bloquea prev/next/shuffle/like pero NO play/pause (el usuario puede pausar el anuncio)
  function setNavBlocked(blocked) {
    [prevBtn, nextBtn, shuffleBtn].forEach((btn) => {
      if (btn) btn.disabled = blocked;
    });
    progressBar.style.pointerEvents = blocked ? "none" : "auto";
    if (likeBtn) likeBtn.disabled = blocked || !currentSongId;
  }

  const syncLike = (isLiked) => {
    if (likesCountEl) likesCountEl.textContent = likedSongIds.size;
    if (!likeBtn) return;
    likeBtn.innerHTML = `<i class="${isLiked ? "fa-solid" : "fa-regular"} fa-heart"></i>`;
    likeBtn.classList.toggle("is-liked", isLiked);
    likeBtn.disabled = isAdPlaying || !currentSongId;
  };

  // ── Ad system ─────────────────────────────────────────────────────────────
  // Incrementa el contador; devuelve true si toca anuncio ahora
  function countSongAndCheckAd() {
    if (isPremium) return false;
    songsSinceLastAd += 1;
    if (songsSinceLastAd >= nextAdAfter) {
      songsSinceLastAd = 0;
      nextAdAfter = randomBetween(3, 5);
      return true;
    }
    return false;
  }

  function finishAd(onComplete) {
    if (adFallbackTimer) {
      clearTimeout(adFallbackTimer);
      adFallbackTimer = null;
    }
    isAdPlaying = false;
    setNavBlocked(false);
    progress.style.width = "0%";
    currentTimeEl.textContent = "0:00";
    totalTimeEl.textContent = "0:00";
    onComplete?.();
  }

  function playInterstitial(onComplete) {
    if (isPremium || isAdPlaying) { onComplete?.(); return; }

    isAdPlaying = true;
    audio.pause();
    setPlayIcon(false);
    setNavBlocked(true);
    currentSongId = null;
    if (titleEl) titleEl.textContent = "Anuncio";
    if (artistEl) artistEl.textContent = "Hazte Premium para escuchar sin interrupciones";
    if (coverEl) coverEl.src = defaultCoverSrc;
    syncLike(false);

    progress.style.width = "0%";
    currentTimeEl.textContent = "0:00";
    totalTimeEl.textContent = "0:00";

    audio.src = AD_SRC;

    function onAdEnded() { finishAd(onComplete); }
    audio.addEventListener("ended", onAdEnded, { once: true });

    audio.play().catch(() => {
      audio.removeEventListener("ended", onAdEnded);
      adFallbackTimer = setTimeout(() => finishAd(onComplete), 9000);
    });
  }

  // Cambia de pista contando para ads; showAd antes de la pista si toca
  function switchTrack(card, shouldPlay) {
    const adNeeded = countSongAndCheckAd();
    if (adNeeded) {
      playInterstitial(() => {
        if (loadTrack(card) && shouldPlay) audio.play().catch(() => {});
      });
    } else {
      if (loadTrack(card) && shouldPlay) audio.play().catch(() => {});
    }
  }

  // ── Track info / carga ────────────────────────────────────────────────────
  const updateTrackInfo = (card) => {
    if (!card) return;
    selectedTrack = card;
    currentSongId = Number(card.dataset.songId || 0) || null;
    currentIndex = cards.indexOf(card);
    if (titleEl) titleEl.textContent = card.dataset.title || "Cancion Actual";
    if (artistEl) artistEl.textContent = card.dataset.artist || "Artista Desconocido";
    if (coverEl && card.dataset.cover) coverEl.src = card.dataset.cover;
    syncLike(currentSongId !== null && likedSongIds.has(currentSongId));
  };

  const loadTrack = (card) => {
    if (!card) return false;
    const audioSrc = card.dataset.audio;
    if (!audioSrc) return false;
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
      if (!loadTrack(firstTrack)) return;
    }
    try { await audio.play(); }
    catch (err) { console.error("No se pudo reproducir el audio:", err); }
  };

  const getSeekDuration = () => {
    if (Number.isFinite(audio.duration) && audio.duration > 0) return audio.duration;
    if (audio.seekable && audio.seekable.length > 0)
      return audio.seekable.end(audio.seekable.length - 1);
    return 0;
  };

  // ── Eventos de cards ──────────────────────────────────────────────────────
  cards.forEach((card) => {
    card.addEventListener("click", (event) => {
      if (isAdPlaying) return;
      if (event.target.closest(
        ".admin-song-actions, .card-menu-btn, .remove-from-playlist-btn, .sidebar-playlist-delete"
      )) return;
      // Click simple: cuenta como cambio de canción
      countSongAndCheckAd();
      loadTrack(card);
    });

    card.addEventListener("dblclick", async (event) => {
      if (isAdPlaying) return;
      if (event.target.closest(
        ".admin-song-actions, .card-menu-btn, .remove-from-playlist-btn"
      )) return;
      // Doble clic: cuenta y puede mostrar ad antes de reproducir
      const adNeeded = countSongAndCheckAd();
      if (adNeeded) {
        playInterstitial(() => {
          if (loadTrack(card)) audio.play().catch(() => {});
        });
      } else {
        if (loadTrack(card)) await playCurrentTrack();
      }
    });
  });

  // ── Play / Pause ──────────────────────────────────────────────────────────
  playBtn.addEventListener("click", async () => {
    if (isAdPlaying) {
      // Durante el anuncio solo pausamos/reanudamos el audio del anuncio
      if (audio.paused) audio.play().catch(() => {});
      else audio.pause();
      return;
    }
    if (audio.paused) await playCurrentTrack();
    else audio.pause();
  });

  audio.addEventListener("play", () => setPlayIcon(true));
  audio.addEventListener("pause", () => setPlayIcon(false));

  // ── Auto-avance al terminar canción ───────────────────────────────────────
  audio.addEventListener("ended", () => {
    if (isAdPlaying) return; // el listener del ad se encarga solo
    setPlayIcon(false);
    if (repeatMode) { audio.currentTime = 0; audio.play().catch(() => {}); return; }
    if (cards.length === 0) return;

    const nextIndex = (currentIndex + 1) % cards.length;
    const adNeeded = countSongAndCheckAd();
    if (adNeeded) {
      playInterstitial(() => {
        if (loadTrack(cards[nextIndex])) audio.play().catch(() => {});
      });
    } else {
      if (loadTrack(cards[nextIndex])) audio.play().catch(() => {});
    }
  });

  // ── Prev / Next (cuentan para ads) ────────────────────────────────────────
  prevBtn?.addEventListener("click", () => {
    if (isAdPlaying || cards.length === 0) return;
    const idx = currentIndex <= 0 ? cards.length - 1 : currentIndex - 1;
    switchTrack(cards[idx], !audio.paused);
  });

  nextBtn?.addEventListener("click", () => {
    if (isAdPlaying || cards.length === 0) return;
    const idx = currentIndex < 0 ? 0 : (currentIndex + 1) % cards.length;
    switchTrack(cards[idx], !audio.paused);
  });

  // ── Repetir ───────────────────────────────────────────────────────────────
  repeatBtn?.addEventListener("click", () => {
    repeatMode = !repeatMode;
    repeatBtn.classList.toggle("is-active", repeatMode);
    repeatBtn.style.color = repeatMode ? "var(--accent-color)" : "";
  });

  // ── Progreso (funciona siempre, incluso durante el anuncio) ───────────────
  audio.addEventListener("timeupdate", () => {
    const duration = getSeekDuration();
    if (!duration) return;
    progress.style.width = `${(audio.currentTime / duration) * 100}%`;
    currentTimeEl.textContent = formatTime(audio.currentTime);
  });

  audio.addEventListener("loadedmetadata", () => {
    const duration = getSeekDuration();
    if (duration) totalTimeEl.textContent = formatTime(duration);
  });

  // Seekbar bloqueada durante el anuncio
  progressBar.addEventListener("click", (e) => {
    if (isAdPlaying) return;
    const duration = getSeekDuration();
    if (!duration) return;
    const rect = progressBar.getBoundingClientRect();
    audio.currentTime =
      Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width)) * duration;
  });

  // ── Volumen ───────────────────────────────────────────────────────────────
  volumeBar.addEventListener("click", (e) => {
    const rect = volumeBar.getBoundingClientRect();
    audio.volume = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
    volumeFill.style.width = audio.volume * 100 + "%";
  });

  audio.volume = 0.7;
  volumeFill.style.width = "70%";
  setPlayIcon(false);
  syncLike(false);

  // ── Like ──────────────────────────────────────────────────────────────────
  likeBtn?.addEventListener("click", async () => {
    if (isAdPlaying || !currentSongId) return;
    likeBtn.disabled = true;
    try {
      const res = await fetch("like.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: new URLSearchParams({ song_id: currentSongId }),
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.message);
      data.liked ? likedSongIds.add(currentSongId) : likedSongIds.delete(currentSongId);
      syncLike(data.liked);
      if (currentView === "likes" && !data.liked) location.reload();
    } catch (err) {
      console.error("Error al actualizar el like:", err);
    } finally {
      likeBtn.disabled = !currentSongId;
    }
  });
});