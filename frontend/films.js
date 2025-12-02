let featuredFilms = [];
let featuredIndex = 0;
let featuredTimer = null;

// Főoldal filmek betöltése
document.addEventListener('DOMContentLoaded', async () => {
    const { films, error } = await fetchHomepageFilms();
    renderMovieList(films, error);
    renderFeaturedSlider(films, error);
});

async function fetchHomepageFilms() {
    try {
        const result = await API.getFilms(1, 20);
        if (result.success && result.data?.filmek) {
            return { films: result.data.filmek, error: null };
        }
        return { films: [], error: result.error || 'Ismeretlen hiba történt a filmek lekérésekor.' };
    } catch (error) {
        console.error('Hálózati hiba a filmek betöltésekor:', error);
        return { films: [], error: 'Hálózati hiba miatt nem sikerült betölteni a filmeket.' };
    }
}

function renderMovieList(films, errorMessage) {
    const movieListContainer = document.querySelector('.movie-list');
    if (!movieListContainer) {
        return;
    }

    if (errorMessage && !films.length) {
        movieListContainer.innerHTML = `
            <div style="color: white; padding: 20px;">
                <p>Nem sikerült betölteni a filmeket.</p>
                <p style="color: #ff3b3b; font-size: 14px;">${errorMessage}</p>
            </div>
        `;
        return;
    }

    if (!films.length) {
        movieListContainer.innerHTML = '<div style="color: white; padding: 20px;">Még nincsenek rögzített filmek.</div>';
        return;
    }

    movieListContainer.innerHTML = '';
    films.slice(0, 10).forEach(film => {
        const movieItem = createMovieItem(film);
        movieListContainer.appendChild(movieItem);
    });
}

function renderFeaturedSlider(films, errorMessage) {
    const featuredSection = document.getElementById('featured-content');
    const titleEl = document.getElementById('featured-title');
    const metaEl = document.getElementById('featured-meta');
    const descEl = document.getElementById('featured-desc');
    const counterEl = document.getElementById('featured-index');
    const tagEl = document.getElementById('featured-tag');
    const watchBtn = document.getElementById('featured-watch-btn');
    const moreBtn = document.getElementById('featured-more-btn');

    if (!featuredSection || !titleEl || !metaEl || !descEl) {
        return;
    }

    if (errorMessage && !films.length) {
        featuredSection.style.backgroundImage = "linear-gradient(120deg, #0d0d0d, #1f1f1f)";
        titleEl.textContent = 'Nem sikerült betölteni a filmeket';
        metaEl.textContent = errorMessage;
        descEl.textContent = 'Próbáld újra később vagy ellenőrizd az internetkapcsolatot.';
        if (counterEl) counterEl.textContent = '';
        disableFeaturedButtons(watchBtn, moreBtn);
        return;
    }

    featuredFilms = films.slice(0, 5);
    featuredIndex = 0;

    if (!featuredFilms.length) {
        titleEl.textContent = 'Még nincsenek kiemelt filmek';
        metaEl.textContent = 'Adj hozzá filmeket az admin felületen, hogy itt is megjelenjenek.';
        descEl.textContent = 'Amint rendelkezésre állnak adatok, a slider automatikusan frissül.';
        if (counterEl) counterEl.textContent = '';
        disableFeaturedButtons(watchBtn, moreBtn);
        return;
    }

    enableFeaturedButtons(watchBtn, moreBtn);

    const prevBtn = document.getElementById('featured-prev');
    const nextBtn = document.getElementById('featured-next');

    if (prevBtn) {
        prevBtn.onclick = () => changeFeaturedFilm(-1);
    }
    if (nextBtn) {
        nextBtn.onclick = () => changeFeaturedFilm(1);
    }

    updateFeaturedContent({
        featuredSection,
        titleEl,
        metaEl,
        descEl,
        counterEl,
        tagEl,
        watchBtn,
        moreBtn
    });
    startFeaturedAutoplay();
}

function updateFeaturedContent(elements) {
    if (!featuredFilms.length) {
        return;
    }

    const film = featuredFilms[featuredIndex];
    const {
        featuredSection,
        titleEl,
        metaEl,
        descEl,
        counterEl,
        tagEl,
        watchBtn,
        moreBtn
    } = elements;

    featuredSection.style.backgroundImage = `linear-gradient(115deg, rgba(5, 5, 5, 0.9), rgba(7, 7, 7, 0.2)), url('${getPosterUrl(film)}')`;
    titleEl.textContent = film.cim || 'Ismeretlen cím';
    metaEl.textContent = formatMeta(film);
    descEl.textContent = film.leiras ? truncateText(film.leiras, 220) : 'Ehhez a filmhez még nem tartozik részletes leírás.';
    if (counterEl) {
        counterEl.textContent = `${featuredIndex + 1} / ${featuredFilms.length}`;
    }
    if (tagEl) {
        tagEl.textContent = film.kiadasi_ev ? `Premier • ${film.kiadasi_ev}` : 'Ajánló';
    }

    if (watchBtn) {
        watchBtn.onclick = () => openFilmSearch(film);
    }
    if (moreBtn) {
        moreBtn.onclick = () => openFilmSearch(film);
    }
}

function changeFeaturedFilm(direction) {
    if (!featuredFilms.length) {
        return;
    }

    featuredIndex = (featuredIndex + direction + featuredFilms.length) % featuredFilms.length;
    updateFeaturedContent({
        featuredSection: document.getElementById('featured-content'),
        titleEl: document.getElementById('featured-title'),
        metaEl: document.getElementById('featured-meta'),
        descEl: document.getElementById('featured-desc'),
        counterEl: document.getElementById('featured-index'),
        tagEl: document.getElementById('featured-tag'),
        watchBtn: document.getElementById('featured-watch-btn'),
        moreBtn: document.getElementById('featured-more-btn')
    });
    startFeaturedAutoplay();
}

function startFeaturedAutoplay() {
    if (featuredTimer) {
        clearInterval(featuredTimer);
    }

    if (featuredFilms.length <= 1) {
        return;
    }

    featuredTimer = setInterval(() => changeFeaturedFilm(1), 9000);
}

function disableFeaturedButtons(...buttons) {
    buttons.filter(Boolean).forEach(btn => {
        btn.disabled = true;
        btn.onclick = null;
    });
}

function enableFeaturedButtons(...buttons) {
    buttons.filter(Boolean).forEach(btn => {
        btn.disabled = false;
    });
}

function openFilmSearch(film) {
    if (!film || !film.cim) {
        return;
    }
    const targetUrl = `kereses.html?title=${encodeURIComponent(film.cim)}`;
    window.location.href = targetUrl;
}

function formatMeta(film) {
    const parts = [];
    if (film.kiadasi_ev) {
        parts.push(film.kiadasi_ev);
    }
    if (film.idotartam) {
        parts.push(`${film.idotartam} perc`);
    }
    return parts.length ? parts.join(' • ') : 'Nincs elérhető részletes adat';
}

function truncateText(text, maxLength) {
    if (!text) {
        return '';
    }
    return text.length > maxLength ? `${text.substring(0, maxLength).trim()}...` : text;
}

function getPosterUrl(film) {
    return film.poszter_url || 'img/f-1.jpg';
}

// Film elem létrehozása
function createMovieItem(film) {
    const div = document.createElement('div');
    div.className = 'movie-list-item';
    const description = film.leiras ? truncateText(film.leiras, 90) : 'Nincs leírás';

    div.innerHTML = `
        <img class="movie-list-item-img" src="${getPosterUrl(film)}" alt="${escapeHtml(film.cim || 'Film poszter')}">
        <span class="movie-list-item-title">${escapeHtml(film.cim || 'Ismeretlen cím')}</span>
        <p class="movie-list-item-desc">${escapeHtml(description)}</p>
        <p class="movie-list-item-desc" style="font-size: 12px; color: #ccc;">${escapeHtml(formatMeta(film))}</p>
    `;
    
    return div;
}

function escapeHtml(value = '') {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// További műfaj szerinti szekciók betöltése (opcionális)
async function loadFilmsByGenre(genreId, containerSelector) {
    const container = document.querySelector(containerSelector);
    
    if (!container) return;

    try {
        const result = await API.getFilmsByGenre(genreId);
        
        if (result.success && result.data.filmek) {
            container.innerHTML = '';
            result.data.filmek.forEach(film => {
                const movieItem = createMovieItem(film);
                container.appendChild(movieItem);
            });
        }
    } catch (error) {
        console.error('Hiba a műfaj szerinti filmek betöltésekor:', error);
    }
}
