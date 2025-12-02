// Film keresés + hozzászólás funkciók
document.addEventListener('DOMContentLoaded', function() {
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
    let currentUser = null;
    let selectedFilm = null;
    let initialQuery = new URLSearchParams(window.location.search).get('title') || '';

    async function init() {
        await loadProfile();
        await loadFilms();
    }

    async function loadProfile() {
        const profileResult = await API.getProfile();
        if (profileResult.success && profileResult.data && profileResult.data.user) {
            currentUser = profileResult.data.user;
        } else {
            currentUser = null;
        }
    }

    // Filmek betöltése az API-ból
    async function loadFilms() {
        statusElement.textContent = 'Filmek betöltése...';
        statusElement.style.color = '#4dbf00';

        const result = await API.getFilms(1, 200);

        if (result.success) {
            allFilms = result.data.filmek || [];
            displayFilms(allFilms);
            statusElement.textContent = `${allFilms.length} film betöltve`;

            if (initialQuery) {
                searchInput.value = initialQuery;
                searchFilms();
                initialQuery = '';
            }
        } else {
            statusElement.textContent = 'Hiba: ' + result.error;
            statusElement.style.color = '#ff3b3b';
            filmsContainer.innerHTML = '<p style="color: white;">Nem sikerült betölteni a filmeket.</p>';
        }
    }

    // Filmek megjelenítése
    function displayFilms(films) {
        visibleFilms = films || [];

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

            return `
            <div class="movie-card" style="
                background-color: var(--card);
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                transition: transform 0.3s ease;
                cursor: pointer;
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
                    <p class="movie-card-meta">
                        <strong>Gyártás:</strong> ${countries}
                    </p>
                    <p class="movie-card-meta">
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
            statusElement.textContent = `${allFilms.length} film`;
            return;
        }

        const filteredFilms = allFilms.filter(film => {
            const titleMatch = film.cim.toLowerCase().includes(searchTerm);
            const descMatch = film.leiras && film.leiras.toLowerCase().includes(searchTerm);
            return titleMatch || descMatch;
        });

        displayFilms(filteredFilms);
        statusElement.textContent = `${filteredFilms.length} találat`;
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

    init();
});
