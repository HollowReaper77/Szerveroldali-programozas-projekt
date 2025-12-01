// Admin oldal funkciók
document.addEventListener('DOMContentLoaded', async function() {
    await checkAccess();
});

// Hozzáférés ellenőrzése
async function checkAccess() {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    
    if (!user || (user.jogosultsag !== 'admin' && user.jogosultsag !== 'moderator')) {
        // Nincs jogosultság
        document.getElementById('access-denied').classList.remove('hidden');
        document.getElementById('admin-content').classList.add('hidden');
        return;
    }

    // Van jogosultság
    document.getElementById('access-denied').classList.add('hidden');
    document.getElementById('admin-content').classList.remove('hidden');
    
    // Funkciók inicializálása
    await loadFilms();
    await loadFilmsForEdit();
    setupEventListeners();
}

// Event listeners beállítása
function setupEventListeners() {
    // Új film hozzáadása
    document.getElementById('add-film-form').addEventListener('submit', handleAddFilm);
    
    // Film szerkesztése
    document.getElementById('edit-film-select').addEventListener('change', handleFilmSelect);
    document.getElementById('edit-film-form').addEventListener('submit', handleEditFilm);
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
            showStatus(statusEl, uploadResult.error || 'Hiba a kép feltöltésekor!', 'error');
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
            showStatus(statusEl, uploadResult.error || 'Hiba a kép feltöltésekor!', 'error');
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
            
            tbody.innerHTML = films.map(film => `
                <tr>
                    <td>${film.film_id}</td>
                    <td>${film.cim}</td>
                    <td>${film.kiadasi_ev || '-'}</td>
                    <td>${film.idotartam || '-'} perc</td>
                    <td>
                        <button class="btn btn-danger" onclick="deleteFilm(${film.film_id}, '${film.cim.replace(/'/g, "\\'")}')">
                            <i class="fas fa-trash"></i> Törlés
                        </button>
                    </td>
                </tr>
            `).join('');
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
            
            select.innerHTML = '<option value="">-- Válassz filmet --</option>' +
                films.map(film => `<option value="${film.film_id}">${film.cim}</option>`).join('');
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
        return result;
    } catch (error) {
        return {
            success: false,
            error: 'Hálózati hiba a képfeltöltés során'
        };
    }
}
