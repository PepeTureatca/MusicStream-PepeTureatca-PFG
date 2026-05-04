/* playlists.js — gestión de listas de reproduccion */
document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;

  let playlists = [];
  try {
    playlists = JSON.parse(body.dataset.playlists || "[]");
  } catch {
    playlists = [];
  }

  const currentView = body.dataset.currentView || "all";
  const currentPLId = parseInt(body.dataset.playlistId || "0", 10);

  // ─── HELPERS ─────────────────────────────────────────────────────────────
  const escHtml = (s) =>
    String(s).replace(
      /[&<>"']/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[c]
    );

  async function postAction(params) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(params)) fd.append(k, v);
    const res = await fetch("playlist-action.php", {
      method: "POST",
      body: fd,
    });
    return res.json();
  }

  function showToast(msg, type = "success") {
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      document.body.appendChild(box);
    }
    const t = document.createElement("div");
    t.className = `toast toast-${type}`;
    t.textContent = msg;
    box.appendChild(t);
    requestAnimationFrame(() => t.classList.add("show"));
    setTimeout(() => {
      t.classList.remove("show");
      setTimeout(() => t.remove(), 300);
    }, 2800);
  }

  // ─── TRACKS ACTIVOS ───────────────────────────────────────────────────────
  const playlistTracks = Array.from(
    document.querySelectorAll(".pl-track.playlist-card")
  );

  const setActive = (track) =>
    playlistTracks.forEach((t) =>
      t.classList.toggle("is-playing", t === track)
    );

  playlistTracks.forEach((t) => {
    t.addEventListener("click", () => setActive(t));
    t.addEventListener("dblclick", () => setActive(t));
  });

  const audioEl = document.getElementById("audio-player");
  if (audioEl) {
    ["pause", "ended"].forEach((ev) =>
      audioEl.addEventListener(ev, () =>
        playlistTracks.forEach((t) => t.classList.remove("is-playing"))
      )
    );
  }

  // ─── MODAL CREAR PLAYLIST ────────────────────────────────────────────────
  const modal = document.getElementById("createPlaylistModal");
  const nameInput = document.getElementById("playlistNameInput");
  const descInput = document.getElementById("playlistDescInput");
  const modalErr = document.getElementById("modalError");
  const confirmBtn = document.getElementById("confirmCreatePlaylistBtn");

  const openModal = () => {
    if (!modal) return;
    nameInput.value = "";
    descInput.value = "";
    setModalErr();
    modal.hidden = false;
    nameInput.focus();
  };

  const closeModal = () => modal && (modal.hidden = true);

  const setModalErr = (msg) => {
    if (!modalErr) return;
    modalErr.textContent = msg || "";
    modalErr.hidden = !msg;
  };

  document
    .getElementById("createPlaylistBtn")
    ?.addEventListener("click", openModal);

  document
    .getElementById("cancelPlaylistBtn")
    ?.addEventListener("click", closeModal);

  modal?.addEventListener("click", (e) => e.target === modal && closeModal());

  document.addEventListener(
    "keydown",
    (e) => e.key === "Escape" && modal && !modal.hidden && closeModal()
  );

  confirmBtn?.addEventListener("click", async () => {
    const name = nameInput?.value.trim();
    if (!name) {
      setModalErr("El nombre es obligatorio.");
      nameInput?.focus();
      return;
    }
    confirmBtn.disabled = true;
    confirmBtn.textContent = "Creando…";
    try {
      const data = await postAction({
        action: "create",
        name,
        description: descInput?.value.trim() ?? "",
      });
      if (data.ok) {
        closeModal();
        addPlaylistToSidebar(data.id, data.name);
        playlists.push({ id: data.id, name: data.name });
        showToast("Playlist creada correctamente.");
      } else {
        setModalErr(data.message || "Error al crear la lista.");
      }
    } catch {
      setModalErr("Error de red. Inténtalo de nuevo.");
    } finally {
      confirmBtn.disabled = false;
      confirmBtn.textContent = "Crear";
    }
  });

  // ─── SIDEBAR: AÑADIR PLAYLIST ────────────────────────────────────────────
  function addPlaylistToSidebar(id, name) {
    let list = document.querySelector(".sidebar-playlists-list");
    if (!list) {
      const nav = document.querySelector(".playlist-menu");
      const div = document.createElement("div");
      div.className = "sidebar-playlists-divider";
      nav?.appendChild(div);
      list = document.createElement("ul");
      list.className = "sidebar-playlists-list";
      nav?.appendChild(list);
    }
    const li = document.createElement("li");
    li.className = "sidebar-playlist-item";
    li.innerHTML = `
      <a href="playlist.php?id=${id}" class="sidebar-playlist-link" title="${escHtml(name)}">
        <i class="fa-solid fa-music"></i>
        <span class="sidebar-playlist-name">${escHtml(name)}</span>
      </a>
      <button class="btn-icon sidebar-playlist-delete" data-playlist-id="${id}" title="Eliminar lista">
        <i class="fa-solid fa-trash"></i>
      </button>`;
    list.appendChild(li);
  }

  // ─── MENÚ CONTEXTUAL: AÑADIR A PLAYLIST ──────────────────────────────────
  const ctxMenu = document.getElementById("addToPlaylistMenu");
  const ctxList = document.getElementById("ctxPlaylistList");
  let ctxSongId = null;

  function buildCtxMenuItems() {
    if (!ctxList) return;
    ctxList.innerHTML = "";
    if (playlists.length === 0) {
      const li = document.createElement("li");
      li.className = "ctx-menu-empty";
      li.textContent = "No tienes listas creadas.";
      ctxList.appendChild(li);
    } else {
      playlists.forEach((pl) => {
        const li = document.createElement("li");
        li.className = "ctx-menu-item";
        li.textContent = pl.name;
        li.addEventListener("click", (e) => {
          e.stopPropagation();
          const songId = ctxSongId;
          closeCtxMenu();
          if (!songId) return;
          postAction({ action: "add_song", playlist_id: pl.id, song_id: songId })
            .then((data) => showToast(data.message || (data.ok ? "Cancion añadida." : "Error."), data.ok ? "success" : "error"))
            .catch(() => showToast("Error de red.", "error"));
        });
        ctxList.appendChild(li);
      });
    }
  }

  function openCtxMenu(songId, anchorEl) {
    if (!ctxMenu) return;
    ctxSongId = songId;
    buildCtxMenuItems();
    // Posicionar cerca del botón (position:fixed → coordenadas de viewport)
    const rect = anchorEl.getBoundingClientRect();
    let top = rect.bottom + 4;
    let left = rect.left;
    // Evitar que se salga por la derecha
    const menuWidth = 200;
    if (left + menuWidth > window.innerWidth) {
      left = window.innerWidth - menuWidth - 8;
    }
    ctxMenu.style.top = `${top}px`;
    ctxMenu.style.left = `${left}px`;
    ctxMenu.hidden = false;
  }

  function closeCtxMenu() {
    if (ctxMenu) ctxMenu.hidden = true;
    ctxSongId = null;
  }

  // Abrir menú al pulsar "···"
  document.addEventListener("click", (e) => {
    const menuBtn = e.target.closest(".card-menu-btn");
    if (menuBtn) {
      e.stopPropagation();
      const songId = parseInt(menuBtn.dataset.songId, 10);
      if (ctxMenu && !ctxMenu.hidden && ctxSongId === songId) {
        closeCtxMenu();
      } else {
        openCtxMenu(songId, menuBtn);
      }
      return;
    }
    // Cerrar menú al hacer clic fuera
    if (ctxMenu && !ctxMenu.hidden && !ctxMenu.contains(e.target)) {
      closeCtxMenu();
    }
  });

  // ─── QUITAR CANCIÓN DE PLAYLIST ───────────────────────────────────────────
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".remove-from-playlist-btn");
    if (!btn) return;
    e.stopPropagation();
    const songId = parseInt(btn.dataset.songId, 10);
    const playlistId = parseInt(btn.dataset.playlistId, 10);
    if (!confirm("¿Quitar esta cancion de la lista?")) return;
    postAction({ action: "remove_song", playlist_id: playlistId, song_id: songId })
      .then((data) => {
        if (data.ok) {
          const track = btn.closest("[data-song-id]");
          track?.remove();
          showToast("Cancion eliminada de la lista.");
        } else {
          showToast("No se pudo quitar la cancion.", "error");
        }
      })
      .catch(() => showToast("Error de red.", "error"));
  });

  // ─── ELIMINAR PLAYLIST ────────────────────────────────────────────────────
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".sidebar-playlist-delete, #plDeleteBtn");
    if (!btn) return;
    e.stopPropagation();
    const playlistId = parseInt(btn.dataset.playlistId, 10);
    if (!confirm("¿Eliminar esta lista de reproduccion?")) return;
    postAction({ action: "delete", playlist_id: playlistId })
      .then((data) => {
        if (data.ok) {
          // Eliminar de sidebar
          const sidebarItem = document.querySelector(
            `.sidebar-playlist-delete[data-playlist-id="${playlistId}"]`
          )?.closest(".sidebar-playlist-item");
          sidebarItem?.remove();
          // Actualizar array local
          playlists = playlists.filter((p) => p.id !== playlistId);
          showToast("Lista eliminada.");
          // Si estamos dentro de esa playlist, redirigir
          if (currentView === "playlist" && currentPLId === playlistId) {
            window.location.href = "dashboard.php";
          }
        } else {
          showToast("No se pudo eliminar la lista.", "error");
        }
      })
      .catch(() => showToast("Error de red.", "error"));
  });
});