// Admin oldal funkciók
let currentUserRole = 'user';
const adminState = {
    actors: [],
    directors: [],
    films: []
};
let editingActorId = null;
let editingDirectorId = null;

function escapeHtml(value = '') {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDateForInput(value = '') {
    if (!value) {
        return '';
    }
    return value.split('T')[0].split(' ')[0];
}

document.addEventListener('DOMContentLoaded', async function() {
    await checkAccess();
});

// Hozzáférés ellenőrzése
async function checkAccess() {
    const result = await API.getProfile();
    const user = result.success && result.data ? result.data.user : null;
    const role = user ? (user.jogosultsag || user.szerepkor || 'user') : null;
    
    if (!result.success || !user || (role !== 'admin' && role !== 'moderator')) {
        // Nincs jogosultság
        document.getElementById('access-denied').classList.remove('hidden');
        document.getElementById('admin-content').classList.add('hidden');
        currentUserRole = 'user';
        return;
    }

    currentUserRole = role;

    // Van jogosultság
    document.getElementById('access-denied').classList.add('hidden');
    document.getElementById('admin-content').classList.remove('hidden');
    
    // Felhasználó kezelés fül csak admin-oknak
    const usersTab = document.getElementById('users-tab');
    if (role === 'admin' && usersTab) {
        usersTab.style.display = 'inline-block';
    } else if (usersTab) {
        usersTab.style.display = 'none';
        document.getElementById('users-section').style.display = 'none';
    }
    
    // Funkciók inicializálása
    await Promise.all([
        loadFilms(),
        loadFilmsForEdit(),
        loadActors(),
        loadDirectors()
    ]);
    setupEventListeners();
}

// Tab váltás
function switchTab(evt, tab) {
    if (tab === 'users' && currentUserRole !== 'admin') {
        if (evt && typeof evt.preventDefault === 'function') {
            evt.preventDefault();
        }
        return;
    }

    // Tab gombok
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    if (evt && evt.target) {
        evt.target.classList.add('active');
    }
    
    // Tab szekciók
    if (tab === 'films') {
        document.getElementById('films-section').style.display = 'block';
        document.getElementById('users-section').style.display = 'none';
    } else if (tab === 'users') {
        document.getElementById('films-section').style.display = 'none';
        document.getElementById('users-section').style.display = 'block';
        loadUsers();
    }
}

// Event listeners beállítása
function setupEventListeners() {
    // Új film hozzáadása
    document.getElementById('add-film-form').addEventListener('submit', handleAddFilm);
    
    // Film szerkesztése
    document.getElementById('edit-film-select').addEventListener('change', handleFilmSelect);
    document.getElementById('edit-film-form').addEventListener('submit', handleEditFilm);

    const addActorForm = document.getElementById('add-actor-form');
    if (addActorForm) {
        addActorForm.addEventListener('submit', handleAddActor);
    }

    const cancelActorBtn = document.getElementById('actor-cancel-edit');
    if (cancelActorBtn) {
        cancelActorBtn.addEventListener('click', cancelActorEdit);
    }

    const addDirectorForm = document.getElementById('add-director-form');
    if (addDirectorForm) {
        addDirectorForm.addEventListener('submit', handleAddDirector);
    }

    const cancelDirectorBtn = document.getElementById('director-cancel-edit');
    if (cancelDirectorBtn) {
        cancelDirectorBtn.addEventListener('click', cancelDirectorEdit);
    }

    const castFilmSelect = document.getElementById('cast-film-select');
    if (castFilmSelect) {
        castFilmSelect.addEventListener('change', (e) => loadCastForFilm(e.target.value));
    }

    const addCastButton = document.getElementById('add-cast-button');
    if (addCastButton) {
        addCastButton.addEventListener('click', handleAddCast);
    }

    const directorFilmSelect = document.getElementById('director-film-select');
    if (directorFilmSelect) {
        directorFilmSelect.addEventListener('change', (e) => loadDirectorsForFilm(e.target.value));
    }

    const addDirectorLinkButton = document.getElementById('add-director-link');
    if (addDirectorLinkButton) {
        addDirectorLinkButton.addEventListener('click', handleAddDirectorLink);
    }
}

// Új film hozzáadása
async function handleAddFilm(e) {
    e.preventDefault();
    
    const statusEl = document.getElementById('add-status');
    const fileInput = document.getElementById('add-poszter-file');
    let poszterUrl = document.getElementById('add-poszter-url').value;

    // Ha van feltöltött fájl, először azt feltöltjük
    if (fileInput.files.length > 0) {
        statusEl.textContent = 'Kép feltöltése...';
        statusEl.className = 'status-message status-success';
        statusEl.style.display = 'block';

        const uploadResult = await uploadImage(fileInput.files[0]);
        if (!uploadResult.success) {
            showStatus(statusEl, uploadResult.message || uploadResult.error || 'Hiba a kép feltöltésekor!', 'error');
            return;
        }
        poszterUrl = uploadResult.url;
    }

    const filmData = {
        cim: document.getElementById('add-cim').value,
        leiras: document.getElementById('add-leiras').value,
        kiadasi_ev: parseInt(document.getElementById('add-kiadasi-ev').value),
        idotartam: parseInt(document.getElementById('add-idotartam').value),
        poszter_url: poszterUrl
    };

    try {
        const result = await API.createFilm(filmData);
        
        if (result.success) {
            showStatus(statusEl, 'Film sikeresen hozzáadva!', 'success');
            document.getElementById('add-film-form').reset();
            await loadFilms();
            await loadFilmsForEdit();
        } else {
            showStatus(statusEl, result.error || 'Hiba történt!', 'error');
        }
    } catch (error) {
        showStatus(statusEl, 'Hálózati hiba: ' + error.message, 'error');
    }
}

// Film kiválasztása szerkesztéshez
async function handleFilmSelect(e) {
    const filmId = e.target.value;
    const form = document.getElementById('edit-film-form');
    
    if (!filmId) {
        form.classList.add('hidden');
        return;
    }

    try {
        const result = await API.getFilm(filmId);
        
        if (result.success && result.data.film) {
            const film = result.data.film;
            
            document.getElementById('edit-film-id').value = film.film_id;
            document.getElementById('edit-cim').value = film.cim || '';
            document.getElementById('edit-leiras').value = film.leiras || '';
            document.getElementById('edit-kiadasi-ev').value = film.kiadasi_ev || '';
            document.getElementById('edit-idotartam').value = film.idotartam || '';
            document.getElementById('edit-poszter-url').value = film.poszter_url || '';
            
            form.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Hiba a film betöltésekor:', error);
    }
}

// Film szerkesztése
async function handleEditFilm(e) {
    e.preventDefault();
    
    const statusEl = document.getElementById('edit-status');
    const filmId = document.getElementById('edit-film-id').value;
    const fileInput = document.getElementById('edit-poszter-file');
    let poszterUrl = document.getElementById('edit-poszter-url').value;

    // Ha van feltöltött fájl, először azt feltöltjük
    if (fileInput.files.length > 0) {
        statusEl.textContent = 'Kép feltöltése...';
        statusEl.className = 'status-message status-success';
        statusEl.style.display = 'block';

        const uploadResult = await uploadImage(fileInput.files[0]);
        if (!uploadResult.success) {
            showStatus(statusEl, uploadResult.message || uploadResult.error || 'Hiba a kép feltöltésekor!', 'error');
            return;
        }
        poszterUrl = uploadResult.url;
    }

    const filmData = {
        cim: document.getElementById('edit-cim').value,
        leiras: document.getElementById('edit-leiras').value,
        kiadasi_ev: parseInt(document.getElementById('edit-kiadasi-ev').value),
        idotartam: parseInt(document.getElementById('edit-idotartam').value),
        poszter_url: poszterUrl
    };

    try {
        const result = await API.updateFilm(filmId, filmData);
        
        if (result.success) {
            showStatus(statusEl, 'Film sikeresen frissítve!', 'success');
            await loadFilms();
            await loadFilmsForEdit();
            cancelEdit();
        } else {
            showStatus(statusEl, result.error || 'Hiba történt!', 'error');
        }
    } catch (error) {
        showStatus(statusEl, 'Hálózati hiba: ' + error.message, 'error');
    }
}

// Film törlése
async function deleteFilm(filmId, filmCim) {
    if (!confirm(`Biztosan törölni szeretnéd a(z) "${filmCim}" című filmet?`)) {
        return;
    }

    const statusEl = document.getElementById('list-status');

    try {
        const result = await API.deleteFilm(filmId);
        
        if (result.success) {
            showStatus(statusEl, 'Film sikeresen törölve!', 'success');
            await loadFilms();
            await loadFilmsForEdit();
        } else {
            showStatus(statusEl, result.error || 'Hiba történt a törlés során!', 'error');
        }
    } catch (error) {
        showStatus(statusEl, 'Hálózati hiba: ' + error.message, 'error');
    }
}

// Filmek betöltése a táblázatba
async function loadFilms() {
    const tbody = document.getElementById('films-list');
    
    try {
        const result = await API.getFilms(1, 100);
        
        if (result.success && result.data.filmek) {
            const films = result.data.filmek;
            
            if (films.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Nincsenek filmek</td></tr>';
                return;
            }
            
            tbody.innerHTML = films.map(film => {
                const safeTitle = escapeHtml(film.cim);
                return `
                <tr>
                    <td>${film.film_id}</td>
                    <td>${safeTitle}</td>
                    <td>${film.kiadasi_ev || '-'}</td>
                    <td>${film.idotartam || '-'} perc</td>
                    <td>
                        <button class="btn btn-danger" onclick="deleteFilm(${film.film_id}, '${film.cim.replace(/'/g, "\\'")}')">
                            <i class="fas fa-trash"></i> Törlés
                        </button>
                    </td>
                </tr>
            `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #dc3545;">Hiba a filmek betöltésekor</td></tr>';
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #dc3545;">Hálózati hiba</td></tr>';
    }
}

// Filmek betöltése a select-be
async function loadFilmsForEdit() {
    const select = document.getElementById('edit-film-select');
    
    try {
        const result = await API.getFilms(1, 100);
        
        if (result.success && result.data.filmek) {
            const films = result.data.filmek;
            adminState.films = films;
            
            select.innerHTML = '<option value="">-- Válassz filmet --</option>' +
                films.map(film => `<option value="${film.film_id}">${escapeHtml(film.cim)}</option>`).join('');

            populateFilmRelationSelects();
        }
    } catch (error) {
        console.error('Hiba a filmek betöltésekor:', error);
    }
}

// Szerkesztés megszakítása
function cancelEdit() {
    document.getElementById('edit-film-select').value = '';
    document.getElementById('edit-film-form').classList.add('hidden');
    document.getElementById('edit-film-form').reset();
}

// Státusz üzenet megjelenítése
function showStatus(element, message, type) {
    element.textContent = message;
    element.className = 'status-message status-' + type;
    element.style.display = 'block';
    
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

async function loadActors() {
    const tableBody = document.getElementById('actors-table');
    if (!tableBody) return;

    try {
        const result = await API.getActors(1, 200);
        const actors = result.success ? (result.data.szineszek || []) : [];
        adminState.actors = actors;

        if (!actors.length) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Nincs rögzített színész</td></tr>';
        } else {
            tableBody.innerHTML = actors.map(actor => `
                <tr>
                    <td>${actor.szinesz_id}</td>
                    <td>${escapeHtml(actor.nev)}</td>
                    <td>${actor.szuletesi_datum || '-'}</td>
                    <td>
                        <button type="button" class="btn btn-secondary actor-edit-btn" data-id="${actor.szinesz_id}">Szerkesztés</button>
                        <button type="button" class="btn btn-danger actor-delete-btn" data-id="${actor.szinesz_id}">Törlés</button>
                    </td>
                </tr>
            `).join('');
        }

        populateActorSelects();
        bindActorTableEvents();

        const currentFilm = document.getElementById('cast-film-select')?.value;
        if (currentFilm) {
            loadCastForFilm(currentFilm);
        }
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc3545;">Hiba a színészek betöltésekor</td></tr>';
    }
}

async function loadDirectors() {
    const tableBody = document.getElementById('directors-table');
    if (!tableBody) return;

    try {
        const result = await API.getDirectors(1, 200);
        const directors = result.success ? (Array.isArray(result.data) ? result.data : (result.data.rendezok || [])) : [];
        adminState.directors = directors;

        if (!directors.length) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Nincs rögzített rendező</td></tr>';
        } else {
            tableBody.innerHTML = directors.map(director => `
                <tr>
                    <td>${director.rendezo_id}</td>
                    <td>${escapeHtml(director.nev)}</td>
                    <td>${director.szuletesi_datum || '-'}</td>
                    <td>
                        <button type="button" class="btn btn-secondary director-edit-btn" data-id="${director.rendezo_id}">Szerkesztés</button>
                        <button type="button" class="btn btn-danger director-delete-btn" data-id="${director.rendezo_id}">Törlés</button>
                    </td>
                </tr>
            `).join('');
        }

        populateDirectorSelects();
        bindDirectorTableEvents();

        const currentFilm = document.getElementById('director-film-select')?.value;
        if (currentFilm) {
            loadDirectorsForFilm(currentFilm);
        }
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc3545;">Hiba a rendezők betöltésekor</td></tr>';
    }
}

function bindActorTableEvents() {
    document.querySelectorAll('.actor-edit-btn').forEach((btn) => {
        btn.addEventListener('click', () => startActorEdit(parseInt(btn.dataset.id, 10)));
    });

    document.querySelectorAll('.actor-delete-btn').forEach((btn) => {
        btn.addEventListener('click', () => handleDeleteActor(parseInt(btn.dataset.id, 10)));
    });
}

function bindDirectorTableEvents() {
    document.querySelectorAll('.director-edit-btn').forEach((btn) => {
        btn.addEventListener('click', () => startDirectorEdit(parseInt(btn.dataset.id, 10)));
    });

    document.querySelectorAll('.director-delete-btn').forEach((btn) => {
        btn.addEventListener('click', () => handleDeleteDirector(parseInt(btn.dataset.id, 10)));
    });
}

function populateActorSelects() {
    const actorSelect = document.getElementById('cast-actor-select');
    if (!actorSelect) return;

    const options = ['<option value="">-- Válassz színészt --</option>'].concat(
        adminState.actors.map(actor => `<option value="${actor.szinesz_id}">${escapeHtml(actor.nev)}</option>`)
    );
    actorSelect.innerHTML = options.join('');
}

function populateDirectorSelects() {
    const directorSelect = document.getElementById('director-select');
    if (!directorSelect) return;

    const options = ['<option value="">-- Válassz rendezőt --</option>'].concat(
        adminState.directors.map(director => `<option value="${director.rendezo_id}">${escapeHtml(director.nev)}</option>`)
    );
    directorSelect.innerHTML = options.join('');
}

function populateFilmRelationSelects() {
    const optionHtml = ['<option value="">-- Válassz filmet --</option>'].concat(
        adminState.films.map(film => `<option value="${film.film_id}">${escapeHtml(film.cim)}</option>`)
    ).join('');

    const castFilmSelect = document.getElementById('cast-film-select');
    if (castFilmSelect) {
        castFilmSelect.innerHTML = optionHtml;
    }

    const directorFilmSelect = document.getElementById('director-film-select');
    if (directorFilmSelect) {
        directorFilmSelect.innerHTML = optionHtml;
    }
}

async function handleAddActor(event) {
    event.preventDefault();
    const statusEl = document.getElementById('actor-status');
    const payload = {
        nev: document.getElementById('actor-name').value.trim(),
        szuletesi_datum: document.getElementById('actor-birth').value,
        bio: document.getElementById('actor-bio').value.trim() || null
    };

    if (!payload.nev || !payload.szuletesi_datum) {
        showStatus(statusEl, 'A név és a születési dátum kötelező.', 'error');
        return;
    }

    const result = editingActorId
        ? await API.updateActor(editingActorId, payload)
        : await API.createActor(payload);
    if (result.success) {
        showStatus(statusEl, editingActorId ? 'Színész sikeresen frissítve.' : 'Színész sikeresen hozzáadva.', 'success');
        document.getElementById('add-actor-form').reset();
        cancelActorEdit();
        await loadActors();
    } else {
        showStatus(statusEl, result.error || 'Hiba történt a mentés során.', 'error');
    }
}

async function handleAddDirector(event) {
    event.preventDefault();
    const statusEl = document.getElementById('director-status');
    const payload = {
        nev: document.getElementById('director-name').value.trim(),
        szuletesi_datum: document.getElementById('director-birth').value,
        bio: document.getElementById('director-bio').value.trim() || null
    };

    if (!payload.nev || !payload.szuletesi_datum) {
        showStatus(statusEl, 'A név és a születési dátum kötelező.', 'error');
        return;
    }

    const result = editingDirectorId
        ? await API.updateDirector(editingDirectorId, payload)
        : await API.createDirector(payload);
    if (result.success) {
        showStatus(statusEl, editingDirectorId ? 'Rendező sikeresen frissítve.' : 'Rendező sikeresen hozzáadva.', 'success');
        document.getElementById('add-director-form').reset();
        cancelDirectorEdit();
        await loadDirectors();
    } else {
        showStatus(statusEl, result.error || 'Hiba történt a mentés során.', 'error');
    }
}

function startActorEdit(actorId) {
    const actor = adminState.actors.find((a) => a.szinesz_id === actorId);
    if (!actor) {
        return;
    }

    editingActorId = actorId;
    document.getElementById('actor-edit-id').value = actorId;
    document.getElementById('actor-name').value = actor.nev || '';
    document.getElementById('actor-birth').value = formatDateForInput(actor.szuletesi_datum);
    document.getElementById('actor-bio').value = actor.bio || '';
    document.getElementById('actor-submit-btn').textContent = 'Színész mentése';
    document.getElementById('actor-cancel-edit').classList.remove('hidden');
}

function cancelActorEdit() {
    editingActorId = null;
    document.getElementById('actor-edit-id').value = '';
    document.getElementById('actor-submit-btn').textContent = 'Színész hozzáadása';
    document.getElementById('actor-cancel-edit').classList.add('hidden');
    const form = document.getElementById('add-actor-form');
    if (form) {
        form.reset();
    }
}

function startDirectorEdit(directorId) {
    const director = adminState.directors.find((d) => d.rendezo_id === directorId);
    if (!director) {
        return;
    }

    editingDirectorId = directorId;
    document.getElementById('director-edit-id').value = directorId;
    document.getElementById('director-name').value = director.nev || '';
    document.getElementById('director-birth').value = formatDateForInput(director.szuletesi_datum);
    document.getElementById('director-bio').value = director.bio || '';
    document.getElementById('director-submit-btn').textContent = 'Rendező mentése';
    document.getElementById('director-cancel-edit').classList.remove('hidden');
}

function cancelDirectorEdit() {
    editingDirectorId = null;
    document.getElementById('director-edit-id').value = '';
    document.getElementById('director-submit-btn').textContent = 'Rendező hozzáadása';
    document.getElementById('director-cancel-edit').classList.add('hidden');
    const form = document.getElementById('add-director-form');
    if (form) {
        form.reset();
    }
}

async function handleDeleteActor(actorId) {
    if (!actorId) return;
    if (!confirm('Biztosan törölni szeretnéd ezt a színészt?')) {
        return;
    }

    const statusEl = document.getElementById('actor-status');
    const result = await API.deleteActor(actorId);
    if (result.success) {
        showStatus(statusEl, 'Színész törölve.', 'success');
        if (editingActorId === actorId) {
            cancelActorEdit();
        }
        await loadActors();
    } else {
        showStatus(statusEl, result.error || 'Nem sikerült törölni a színészt.', 'error');
    }
}

async function handleDeleteDirector(directorId) {
    if (!directorId) return;
    if (!confirm('Biztosan törölni szeretnéd ezt a rendezőt?')) {
        return;
    }

    const statusEl = document.getElementById('director-status');
    const result = await API.deleteDirector(directorId);
    if (result.success) {
        showStatus(statusEl, 'Rendező törölve.', 'success');
        if (editingDirectorId === directorId) {
            cancelDirectorEdit();
        }
        await loadDirectors();
    } else {
        showStatus(statusEl, result.error || 'Nem sikerült törölni a rendezőt.', 'error');
    }
}

async function loadCastForFilm(filmId) {
    const tableBody = document.getElementById('cast-table');
    if (!tableBody) return;

    if (!filmId) {
        tableBody.innerHTML = '<tr><td colspan="2" style="text-align:center;">Válassz filmet a listából.</td></tr>';
        return;
    }

    try {
        const result = await API.getActorsByFilm(filmId);
        const actors = result.success ? (Array.isArray(result.data) ? result.data : result.data.szineszek || []) : [];

        if (!actors.length) {
            tableBody.innerHTML = '<tr><td colspan="2" style="text-align:center;">Ehhez a filmhez még nincs színész társítva.</td></tr>';
            return;
        }

        tableBody.innerHTML = actors.map(actor => `
            <tr>
                <td>${escapeHtml(actor.nev)}</td>
                <td>
                    <button class="btn btn-danger" onclick="removeActorLink(${filmId}, ${actor.szinesz_id})">Eltávolítás</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="2" style="text-align:center;color:#dc3545;">Hiba a szereplők betöltésekor.</td></tr>';
    }
}

async function loadDirectorsForFilm(filmId) {
    const tableBody = document.getElementById('director-link-table');
    if (!tableBody) return;

    if (!filmId) {
        tableBody.innerHTML = '<tr><td colspan="2" style="text-align:center;">Válassz filmet a listából.</td></tr>';
        return;
    }

    try {
        const result = await API.getDirectorsByFilm(filmId);
        const directors = result.success ? (Array.isArray(result.data) ? result.data : result.data.rendezok || []) : [];

        if (!directors.length) {
            tableBody.innerHTML = '<tr><td colspan="2" style="text-align:center;">Ehhez a filmhez még nincs rendező társítva.</td></tr>';
            return;
        }

        tableBody.innerHTML = directors.map(director => `
            <tr>
                <td>${escapeHtml(director.nev)}</td>
                <td>
                    <button class="btn btn-danger" onclick="removeDirectorLink(${filmId}, ${director.rendezo_id})">Eltávolítás</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="2" style="text-align:center;color:#dc3545;">Hiba a rendezők betöltésekor.</td></tr>';
    }
}

async function handleAddCast() {
    const filmId = parseInt(document.getElementById('cast-film-select').value, 10);
    const actorId = parseInt(document.getElementById('cast-actor-select').value, 10);
    const statusEl = document.getElementById('cast-status');

    if (!filmId || !actorId) {
        showStatus(statusEl, 'Válassz ki egy filmet és egy színészt.', 'error');
        return;
    }

    const result = await API.addActorToFilm(filmId, actorId);
    if (result.success) {
        showStatus(statusEl, 'Színész hozzárendelve a filmhez.', 'success');
        await loadCastForFilm(filmId);
    } else {
        showStatus(statusEl, result.error || 'Hiba a hozzárendelés során.', 'error');
    }
}

async function handleAddDirectorLink() {
    const filmId = parseInt(document.getElementById('director-film-select').value, 10);
    const directorId = parseInt(document.getElementById('director-select').value, 10);
    const statusEl = document.getElementById('director-link-status');

    if (!filmId || !directorId) {
        showStatus(statusEl, 'Válassz ki egy filmet és egy rendezőt.', 'error');
        return;
    }

    const result = await API.addDirectorToFilm(filmId, directorId);
    if (result.success) {
        showStatus(statusEl, 'Rendező hozzárendelve a filmhez.', 'success');
        await loadDirectorsForFilm(filmId);
    } else {
        showStatus(statusEl, result.error || 'Hiba a hozzárendelés során.', 'error');
    }
}

async function removeActorLink(filmId, actorId) {
    const statusEl = document.getElementById('cast-status');
    const numericFilm = parseInt(filmId, 10);
    const numericActor = parseInt(actorId, 10);

    if (!numericFilm || !numericActor) {
        showStatus(statusEl, 'Érvénytelen azonosítók.', 'error');
        return;
    }

    const result = await API.removeActorFromFilm(numericFilm, numericActor);
    if (result.success) {
        showStatus(statusEl, 'Szereplő eltávolítva.', 'success');
        await loadCastForFilm(numericFilm);
    } else {
        showStatus(statusEl, result.error || 'Hiba az eltávolítás során.', 'error');
    }
}

async function removeDirectorLink(filmId, directorId) {
    const statusEl = document.getElementById('director-link-status');
    const numericFilm = parseInt(filmId, 10);
    const numericDirector = parseInt(directorId, 10);

    if (!numericFilm || !numericDirector) {
        showStatus(statusEl, 'Érvénytelen azonosítók.', 'error');
        return;
    }

    const result = await API.removeDirectorFromFilm(numericFilm, numericDirector);
    if (result.success) {
        showStatus(statusEl, 'Rendező eltávolítva.', 'success');
        await loadDirectorsForFilm(numericFilm);
    } else {
        showStatus(statusEl, result.error || 'Hiba az eltávolítás során.', 'error');
    }
}

window.removeActorLink = removeActorLink;
window.removeDirectorLink = removeDirectorLink;

// Kép feltöltése
async function uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}/upload/image`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });

        const result = await response.json();
        if (!response.ok) {
            return {
                success: false,
                message: result.message || 'Hiba történt a képfeltöltés során.'
            };
        }

        return result;
    } catch (error) {
        return {
            success: false,
            error: 'Hálózati hiba a képfeltöltés során'
        };
    }
}

// ========================================
// FELHASZNÁLÓ KEZELÉS (csak admin)
// ========================================

// Felhasználók betöltése
async function loadUsers() {
    const tbody = document.getElementById('users-list');
    
    try {
        const result = await API.getAllUsers();
        
        if (result.success && result.data.felhasznalok) {
            const users = result.data.felhasznalok;
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Nincsenek felhasználók</td></tr>';
                return;
            }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.felhasznalo_id}</td>
                    <td>${user.felhasznalonev}</td>
                    <td>${user.email}</td>
                    <td>
                        <select onchange="changeUserRole(${user.felhasznalo_id}, this.value)" 
                                ${user.jogosultsag === 'admin' ? 'disabled' : ''}>
                            <option value="user" ${user.jogosultsag === 'user' ? 'selected' : ''}>User</option>
                            <option value="moderator" ${user.jogosultsag === 'moderator' ? 'selected' : ''}>Moderátor</option>
                            <option value="admin" ${user.jogosultsag === 'admin' ? 'selected' : ''}>Admin</option>
                        </select>
                    </td>
                    <td>${new Date(user.regisztracio_ideje).toLocaleDateString('hu-HU')}</td>
                    <td>
                        ${user.jogosultsag !== 'admin' ? 
                            `<button class="btn btn-danger" onclick="deleteUser(${user.felhasznalo_id}, '${user.felhasznalonev}')">
                                <i class="fas fa-trash"></i> Törlés
                            </button>` 
                            : '<span style="color: #999;">Védett</span>'}
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #dc3545;">Hiba a felhasználók betöltésekor</td></tr>';
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #dc3545;">Hálózati hiba</td></tr>';
    }
}

// Felhasználó szerepkör módosítása
async function changeUserRole(userId, newRole) {
    const statusEl = document.getElementById('users-status');
    
    try {
        const result = await API.updateUserRole(userId, newRole);
        
        if (result.success) {
            showStatus(statusEl, 'Szerepkör sikeresen módosítva!', 'success');
            await loadUsers();
        } else {
            showStatus(statusEl, result.error || 'Hiba történt!', 'error');
            await loadUsers(); // Visszaállítja az eredeti értéket
        }
    } catch (error) {
        showStatus(statusEl, 'Hálózati hiba: ' + error.message, 'error');
        await loadUsers();
    }
}

// Felhasználó törlése
async function deleteUser(userId, username) {
    if (!confirm(`Biztosan törölni szeretnéd "${username}" felhasználót?`)) {
        return;
    }

    const statusEl = document.getElementById('users-status');

    try {
        const result = await API.deleteUser(userId);
        
        if (result.success) {
            showStatus(statusEl, 'Felhasználó sikeresen törölve!', 'success');
            await loadUsers();
        } else {
            showStatus(statusEl, result.error || 'Hiba történt a törlés során!', 'error');
        }
    } catch (error) {
        showStatus(statusEl, 'Hálózati hiba: ' + error.message, 'error');
    }
}
