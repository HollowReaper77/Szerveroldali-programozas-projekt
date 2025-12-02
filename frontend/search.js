// Film keresés + hozzászólás funkciók
document.addEventListener('DOMContentLoaded', function() {
    if (typeof API !== 'undefined' && typeof API.configureBaseUrlFromLocation === 'function') {
        API.configureBaseUrlFromLocation();
    }

    const searchInput = document.getElementById('search');
    const searchButton = document.getElementById('search-button');
    const filmsContainer = document.getElementById('films');
    const statusElement = document.getElementById('status');

    const modal = document.getElementById('film-modal');
    const modalCloseBtn = modal ? modal.querySelector('.film-modal__close') : null;
    const modalTitle = document.getElementById('film-modal-title');
    const modalMeta = document.getElementById('film-modal-meta');
    const modalDescription = document.getElementById('film-modal-description');
    const modalPoster = document.getElementById('film-modal-poster');
    const modalComments = document.getElementById('film-modal-comments');
    const modalFormContainer = document.getElementById('film-modal-comment-form');
    const modalCommentCount = document.getElementById('film-modal-comment-count');
    const modalCountries = document.getElementById('film-modal-countries');
    const modalViews = document.getElementById('film-modal-views');
    const modalDirectors = document.getElementById('film-modal-directors');
    const modalActors = document.getElementById('film-modal-actors');
    const watchWrapper = document.getElementById('film-watch-wrapper');
    const watchToggle = document.getElementById('film-watch-toggle');
    const watchStatus = document.getElementById('film-watch-status');
    const placeholderPoster = 'img/placeholder.jpg';

    const escapeHtml = (value = '') =>
        value
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    if (!searchInput || !searchButton || !filmsContainer || !statusElement || !modal) {
        return;
    }

    let allFilms = [];
    let visibleFilms = [];
    let selectedFilm = null;
    let commentWatchCheckbox = null;
    let currentUser = null;
    let isUpdatingWatch = false;
    let isSyncingWatchInputs = false;
    const watchedMap = new Map();
    const initialQueryParams = new URLSearchParams(window.location.search);

    function updateStatus(message, color = '#b3b3b3') {
        if (!statusElement) {
            return;
        }
        statusElement.textContent = message;
        statusElement.style.color = color;
    }

    function displayFilms(films = []) {
        visibleFilms = Array.isArray(films) ? films : [];

        if (!visibleFilms.length) {
            filmsContainer.innerHTML = '<p style="color: white; padding: 20px;">Nincs találat.</p>';
            return;
        }

        filmsContainer.innerHTML = visibleFilms.map(film => {
            const title = escapeHtml(film.cim || 'Ismeretlen cím');
            const release = escapeHtml(film.kiadasi_ev ? `${film.kiadasi_ev}` : 'Ismeretlen');
            const runtime = escapeHtml(film.idotartam ? `${film.idotartam} perc` : 'N/A');
            const countries = escapeHtml(getCountryLabel(film));
            const watchText = escapeHtml(getWatchCountText(film));
            const shortDesc = escapeHtml(
                film.leiras
                    ? film.leiras.substring(0, 100) + (film.leiras.length > 100 ? '...' : '')
                    : 'Nincs leírás'
            );
            const poster = film.poszter_url || placeholderPoster;
            const posterAlt = escapeHtml(film.cim || 'Film poszter');
            const isWatchedByUser = isFilmWatched(film.film_id);
            const metaStyle = 'style="color: lightgray; font-size: 14px; margin: 5px 0;"';

            return `
            <div class="movie-card" style="
                background-color: var(--card);
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                transition: transform 0.3s ease;
                cursor: pointer;
                ${isWatchedByUser ? 'border: 1px solid rgba(77,191,0,0.5); box-shadow: 0 6px 14px rgba(77,191,0,0.25);' : ''}
            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="${poster}" alt="${posterAlt}" style="
                    width: 100%;
                    height: 300px;
                    object-fit: cover;
                ">
                <div style="padding: 15px;">
                    <h3 style="color: #4dbf00; margin: 0 0 10px 0; font-size: 18px;">${title}</h3>
                    <p style="color: lightgray; font-size: 14px; margin: 5px 0;">
                        <strong>Megjelenés:</strong> ${release}
                    </p>
                    <p style="color: lightgray; font-size: 14px; margin: 5px 0;">
                        <strong>Hossz:</strong> ${runtime}
                    </p>
                    <p class="movie-card-meta" ${metaStyle}>
                        <strong>Gyártás:</strong> ${countries}
                    </p>
                    <p class="movie-card-meta" ${metaStyle}>
                        <strong>Megnézte:</strong> ${watchText}
                    </p>
                    <p style="color: lightgray; font-size: 13px; margin: 10px 0; line-height: 1.4;">
                        ${shortDesc}
                    </p>
                </div>
            </div>`;
        }).join('');

        filmsContainer.style.display = 'grid';
        filmsContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(250px, 1fr))';
        filmsContainer.style.gap = '20px';
        filmsContainer.style.padding = '20px 0';

        filmsContainer.querySelectorAll('.movie-card').forEach((card, index) => {
            card.addEventListener('click', () => openFilmModal(visibleFilms[index]));
        });
    }

    // Keresés funkció
    function searchFilms() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        if (!searchTerm) {
            displayFilms(allFilms);
            updateStatus(`${allFilms.length} film`, '#4dbf00');
            return;
        }

        const filteredFilms = allFilms.filter(film => {
            const titleMatch = film.cim.toLowerCase().includes(searchTerm);
            const descMatch = film.leiras && film.leiras.toLowerCase().includes(searchTerm);
            return titleMatch || descMatch;
        });

        displayFilms(filteredFilms);
        updateStatus(`${filteredFilms.length} találat`, '#4dbf00');
    }

    async function loadComments(filmId) {
        if (!modalComments) return;
        modalComments.innerHTML = '<p style="color: #b0b0b0;">Vélemények betöltése...</p>';
        updateCommentCount(0);

        const result = await API.getReviewsByFilm(filmId);
        if (result.success) {
            const reviews = (result.data && result.data.reviews) || [];
            renderComments(reviews);
        } else {
            modalComments.innerHTML = '<p style="color: #ff8a8a;">Nem sikerült betölteni a véleményeket.</p>';
            updateCommentCount(0);
        }
    }

    function renderComments(reviews) {
        updateCommentCount(reviews.length);

        if (!reviews.length) {
            modalComments.innerHTML = `
                <div class="film-modal__empty">
                    <span class="film-modal__empty-icon" aria-hidden="true">🎬</span>
                    <p>Még nincs vélemény.<br><span>Légy te az első, aki megosztja a gondolatait!</span></p>
                </div>
            `;
            return;
        }

        modalComments.innerHTML = reviews.map(review => {
            const author = escapeHtml(review.felhasznalonev || 'Ismeretlen felhasználó');
            const comment = escapeHtml(review.komment || '');
            const rating = Number(review.ertekeles);
            const ratingLabel = Number.isFinite(rating) ? rating.toFixed(1) : '–';
            const createdAt = formatDate(review.letrehozas_ideje);

            return `
                <article class="film-modal__comment">
                    <span class="film-modal__comment-quote" aria-hidden="true">“</span>
                    <header class="film-modal__comment-header">
                        <div class="film-modal__comment-identity">
                            <span class="film-modal__comment-author">${author}</span>
                            <span class="film-modal__comment-date">${createdAt}</span>
                        </div>
                        <span class="film-modal__comment-rating" aria-label="Értékelés ${ratingLabel}">⭐ ${ratingLabel}</span>
                    </header>
                    <p class="film-modal__comment-text">${comment}</p>
                </article>
            `;
        }).join('');
    }

    function renderCommentForm() {
        if (!modalFormContainer) return;

        commentWatchCheckbox = null;

        if (!currentUser) {
            modalFormContainer.innerHTML = '<p class="film-modal__empty">A hozzászóláshoz kérlek jelentkezz be.</p>';
            return;
        }

        const ratingOptions = [5, 4.5, 4, 3.5, 3, 2.5, 2, 1.5, 1];
        const optionsHtml = ratingOptions.map(value => `<option value="${value}">${value.toFixed(1)}</option>`).join('');

        modalFormContainer.innerHTML = `
            <form id="comment-form" class="film-modal__form-body">
                <div class="film-modal__form-row">
                    <label for="comment-text">Írd meg a véleményed</label>
                    <textarea id="comment-text" placeholder="Mi tetszett vagy nem tetszett a filmben?" required></textarea>
                </div>
                <div class="film-modal__form-row film-modal__form-row--inline">
                    <label for="comment-rating">Értékelés</label>
                    <div class="film-modal__rating-select">
                        <span aria-hidden="true">⭐</span>
                        <select id="comment-rating" required>
                            ${optionsHtml}
                        </select>
                    </div>
                </div>
                <div class="film-modal__form-row film-modal__form-row--inline film-modal__form-row--checkbox">
                    <label class="film-modal__watch-form">
                        <input type="checkbox" id="comment-watch-toggle">
                        <span>Megjelölöm megnézettként</span>
                    </label>
                </div>
                <p class="film-modal__form-note">Légy társszerzője a Cinematár közösségének – a visszajelzéseddel másoknak is segítesz.</p>
                <div class="film-modal__form-actions">
                    <button type="submit">Vélemény elküldése</button>
                    <p id="comment-form-status" class="film-modal__form-status" role="status" aria-live="polite"></p>
                </div>
            </form>
        `;

        const ratingSelect = document.getElementById('comment-rating');
        if (ratingSelect) {
            ratingSelect.value = '5';
        }

        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', handleCommentSubmit);
        }

        commentWatchCheckbox = document.getElementById('comment-watch-toggle');
        if (commentWatchCheckbox) {
            commentWatchCheckbox.checked = selectedFilm ? isFilmWatched(selectedFilm.film_id) : false;
            commentWatchCheckbox.addEventListener('change', (event) => handleWatchInputChange(event.target, 'comment'));
        }
    }

    function setFormStatus(message, isError = false) {
        const statusNode = document.getElementById('comment-form-status');
        if (!statusNode) return;
        statusNode.textContent = message;
        statusNode.style.color = isError ? '#ff8a8a' : '#4dbf00';
    }

    async function handleCommentSubmit(event) {
        event.preventDefault();
        if (!selectedFilm) return;

        const commentInput = document.getElementById('comment-text');
        const ratingSelect = document.getElementById('comment-rating');
        const submitBtn = event.target.querySelector('button[type="submit"]');

        if (!commentInput || !ratingSelect || !submitBtn) return;

        const commentText = commentInput.value.trim();
        if (commentText.length < 3) {
            setFormStatus('A hozzászólás túl rövid.', true);
            return;
        }

        setFormStatus('Vélemény küldése folyamatban...');
        submitBtn.disabled = true;

        const payload = {
            film_id: selectedFilm.film_id,
            komment: commentText,
            ertekeles: ratingSelect.value
        };

        const result = await API.createReview(payload);
        submitBtn.disabled = false;

        if (result.success) {
            setFormStatus('Köszönjük a hozzászólást!');
            commentInput.value = '';
            ratingSelect.value = '5';
            loadComments(selectedFilm.film_id);
        } else {
            setFormStatus(result.error || 'Nem sikerült elmenteni a véleményt.', true);
        }
    }

    function updateCommentCount(count) {
        if (!modalCommentCount) return;
        modalCommentCount.textContent = count;
        modalCommentCount.setAttribute('aria-label', `${count} vélemény`);
    }

    function openFilmModal(film) {
        if (!film || !modal) return;
        selectedFilm = film;
        updateWatchControls(film.film_id);

        modalTitle.textContent = film.cim;
        const year = film.kiadasi_ev || 'ismeretlen év';
        const runtime = film.idotartam ? `${film.idotartam} perc` : 'ismeretlen hossz';
        modalMeta.textContent = `${year} • ${runtime}`;
        if (modalCountries) {
            if (film.orszagok && film.orszagok.length) {
                modalCountries.textContent = `Gyártás: ${film.orszagok.join(', ')}`;
                modalCountries.classList.remove('hidden');
            } else {
                modalCountries.classList.add('hidden');
            }
        }
        if (modalViews) {
            modalViews.textContent = getWatchCountText(film, true);
            modalViews.classList.remove('hidden');
        }
        if (modalDirectors) {
            modalDirectors.textContent = formatPersonList(film.rendezok);
        }
        if (modalActors) {
            modalActors.textContent = formatPersonList(film.szineszek);
        }
        modalDescription.textContent = film.leiras || 'Ehhez a filmhez még nincs leírás.';
        modalPoster.src = film.poszter_url || placeholderPoster;
        modalPoster.onerror = () => {
            modalPoster.onerror = null;
            modalPoster.src = placeholderPoster;
        };

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        renderCommentForm();
        loadComments(film.film_id);
    }

    function closeFilmModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        selectedFilm = null;
        if (modalComments) {
            modalComments.innerHTML = '';
        }
        if (modalFormContainer) {
            modalFormContainer.innerHTML = '';
        }
        if (modalCountries) {
            modalCountries.classList.add('hidden');
            modalCountries.textContent = '';
        }
        if (modalViews) {
            modalViews.classList.add('hidden');
            modalViews.textContent = '';
        }
        if (modalDirectors) {
            modalDirectors.textContent = 'Nincs adat';
        }
        if (modalActors) {
            modalActors.textContent = 'Nincs adat';
        }
        if (watchWrapper) {
            watchWrapper.classList.add('hidden');
        }
        setWatchStatus('');
        commentWatchCheckbox = null;
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'ismeretlen dátum';
        const date = new Date(dateStr);
        if (Number.isNaN(date.getTime())) {
            return dateStr;
        }
        return date.toLocaleDateString('hu-HU');
    }

    function getCountryLabel(film) {
        return film.orszagok && film.orszagok.length ? film.orszagok.join(', ') : 'Nincs adat';
    }

    function getWatchCountText(film, detailed = false) {
        const count = Number(film.megnezve_db);
        if (!Number.isFinite(count) || count < 0) {
            return detailed ? 'Még nincs adat a megtekintésekről.' : 'Nincs adat';
        }
        if (count === 0) {
            return detailed ? 'Még senki sem jelölte megnézettnek.' : '0 felhasználó';
        }
        return detailed ? `Megnézte ${count} felhasználó` : `${count} felhasználó`;
    }

    function formatPersonList(entries) {
        if (!Array.isArray(entries) || !entries.length) {
            return 'Nincs adat';
        }
        return entries.join(', ');
    }

    function isFilmWatched(filmId) {
        if (!filmId) {
            return false;
        }
        const record = watchedMap.get(filmId);
        return Boolean(record && record.megnezve_e);
    }

    function setWatchStatus(message, isError = false) {
        if (!watchStatus) return;
        watchStatus.textContent = message || '';
        watchStatus.style.color = isError ? '#ff8a8a' : '#a5b29a';
    }

    function setWatchInputsDisabled(disabled) {
        const finalState = disabled || !currentUser;
        if (watchToggle) {
            watchToggle.disabled = finalState;
        }
        if (commentWatchCheckbox) {
            commentWatchCheckbox.disabled = finalState;
        }
    }

    function setWatchInputValues(isWatched) {
        isSyncingWatchInputs = true;
        if (watchToggle) {
            watchToggle.checked = Boolean(isWatched);
        }
        if (commentWatchCheckbox) {
            commentWatchCheckbox.checked = Boolean(isWatched);
        }
        isSyncingWatchInputs = false;
    }

    function updateWatchControls(filmId) {
        if (!watchWrapper) return;

        if (!selectedFilm) {
            watchWrapper.classList.add('hidden');
            setWatchStatus('');
            return;
        }

        watchWrapper.classList.remove('hidden');

        if (!currentUser) {
            setWatchInputsDisabled(true);
            setWatchInputValues(false);
            setWatchStatus('A megjelöléshez jelentkezz be.', true);
            return;
        }

        const watched = isFilmWatched(filmId);
        setWatchInputsDisabled(isUpdatingWatch);
        setWatchInputValues(watched);
        setWatchStatus(watched ? 'Megjelölted megnézettként.' : 'Még nem jelölted meg megnézettként.');
    }

    async function handleWatchInputChange(target, source = 'toggle') {
        if (isSyncingWatchInputs) {
            return;
        }

        if (!currentUser) {
            isSyncingWatchInputs = true;
            target.checked = false;
            isSyncingWatchInputs = false;
            setWatchStatus('A megjelöléshez be kell jelentkezned.', true);
            return;
        }

        if (!selectedFilm) {
            return;
        }

        const desiredState = target.checked;

        try {
            await persistWatchState(desiredState, { source });
        } catch (error) {
            isSyncingWatchInputs = true;
            target.checked = !desiredState;
            isSyncingWatchInputs = false;
        }
    }

    async function persistWatchState(isWatched, { silent = false } = {}) {
        if (!currentUser || !selectedFilm) {
            throw new Error('auth-required');
        }

        const currentState = isFilmWatched(selectedFilm.film_id);
        if (currentState === isWatched) {
            updateWatchControls(selectedFilm.film_id);
            return;
        }

        if (!silent) {
            setWatchStatus('Állapot frissítése...');
        }

        isUpdatingWatch = true;
        setWatchInputsDisabled(true);

        const result = await API.updateWatchedStatus(selectedFilm.film_id, isWatched);

        isUpdatingWatch = false;
        setWatchInputsDisabled(false);

        if (!result.success) {
            if (!silent) {
                setWatchStatus(result.error || 'Nem sikerült frissíteni.', true);
            }
            throw new Error(result.error || 'watch-update-failed');
        }

        const record = result.data && result.data.record ? result.data.record : null;
        watchedMap.set(selectedFilm.film_id, {
            film_id: selectedFilm.film_id,
            megnezve_e: record && record.megnezve_e ? 1 : (isWatched ? 1 : 0),
            hozzaadas_datuma: record ? record.hozzaadas_datuma : null
        });

        displayFilms(visibleFilms);
        updateWatchControls(selectedFilm.film_id);

        if (!silent) {
            setWatchStatus(isWatched ? 'Megjelölted megnézettként.' : 'Eltávolítottad a jelölést.');
        }
    }

    async function loadCurrentUser() {
        currentUser = null;
        try {
            const result = await API.getProfile();
            if (result.success && result.data && result.data.user) {
                currentUser = result.data.user;
            }
        } catch (error) {
            currentUser = null;
        }
    }

    async function loadWatchedFilms() {
        watchedMap.clear();
        try {
            const result = await API.getWatchedFilms({ includeAll: true });
            if (result.success && result.data && Array.isArray(result.data.watched)) {
                result.data.watched.forEach(record => {
                    if (record && record.film_id) {
                        watchedMap.set(record.film_id, record);
                    }
                });
            }
        } catch (error) {
            // Vendég felhasználónál nem szükséges hibát jelezni
        }
    }

    async function loadAllFilms() {
        const collected = [];
        const limit = 100;
        let page = 1;
        let totalPages = 1;

        while (page <= totalPages) {
            const result = await API.getFilms(page, limit);
            if (!result.success || !result.data) {
                throw new Error(result.error || 'Nem sikerült betölteni a filmeket.');
            }

            const films = Array.isArray(result.data.filmek) ? result.data.filmek : [];
            collected.push(...films);

            const totalCount = Number(result.data.count || films.length);
            totalPages = Math.max(totalPages, Math.ceil(totalCount / limit));

            if (films.length < limit) {
                break;
            }

            page += 1;
        }

        allFilms = collected;
        displayFilms(allFilms);
        updateStatus(`${allFilms.length} film`, '#4dbf00');
    }

    function applyInitialSearchTerm() {
        const initialTitle = initialQueryParams.get('title');
        if (initialTitle) {
            searchInput.value = initialTitle;
            searchFilms();
        }
    }

    async function init() {
        updateStatus('Filmek betöltése...');
        try {
            await loadCurrentUser();
            if (currentUser) {
                await loadWatchedFilms();
            } else {
                watchedMap.clear();
            }
            await loadAllFilms();
            applyInitialSearchTerm();
        } catch (error) {
            console.error('Search init error:', error);
            updateStatus('Nem sikerült betölteni a filmeket.', '#ff3b3b');
            filmsContainer.innerHTML = '<p style="color: white;">Nem sikerült betölteni a filmeket.</p>';
        }
    }

    // Event listeners
    searchButton.addEventListener('click', searchFilms);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchFilms();
        }
    });

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeFilmModal);
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeFilmModal();
        }
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeFilmModal();
        }
    });

    if (watchToggle) {
        watchToggle.addEventListener('change', (event) => handleWatchInputChange(event.target, 'toggle'));
    }

    init();
});
