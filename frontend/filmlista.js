document.addEventListener('DOMContentLoaded', () => {
    const statusElement = document.getElementById('films-status');
    const filmGrid = document.getElementById('film-grid');

    const showStatus = (message, isError = false) => {
        if (!statusElement) return;
        statusElement.textContent = message;
        statusElement.style.color = isError ? '#ff6961' : '#b3b3b3';
    };

    const formatList = (value) => {
        if (Array.isArray(value)) {
            return value.join(', ');
        }
        if (typeof value === 'string') {
            return value;
        }
        return '-';
    };

    const formatDateLabel = (value) => {
        if (!value) {
            return 'ismeretlen dátum';
        }
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleDateString('hu-HU');
    };

    const redirectToLogin = () => {
        window.location.href = 'bejelentkezes.html';
    };

    const createFilmCard = (film) => {
        const card = document.createElement('article');
        card.classList.add('movie-card');
        card.classList.add('movie-card--watched');

        const poster = document.createElement('div');
        poster.classList.add('movie-card-poster');
        poster.style.backgroundImage = film.poszter_url ? `url(${film.poszter_url})` : 'linear-gradient(135deg, #444, #222)';

        const info = document.createElement('div');
        info.classList.add('movie-card-info');

        const title = document.createElement('h3');
        title.textContent = film.cim || 'Ismeretlen cím';

        const year = document.createElement('p');
        year.textContent = film.kiadasi_ev ? `Megjelenés: ${film.kiadasi_ev}` : 'Megjelenési év ismeretlen';

        const runtime = document.createElement('p');
        runtime.textContent = film.idotartam ? `Időtartam: ${film.idotartam} perc` : 'Hossz ismeretlen';

        const directors = document.createElement('p');
        directors.textContent = `Rendezők: ${formatList(film.rendezok)}`;

        const cast = document.createElement('p');
        const actorList = Array.isArray(film.szineszek)
            ? film.szineszek
            : (film.szineszek ? film.szineszek.split(',').map((name) => name.trim()).filter(Boolean) : []);
        const topActors = actorList.slice(0, 3);
        const remaining = actorList.length - topActors.length;
        cast.textContent = topActors.length
            ? `Szereplők: ${topActors.join(', ')}${remaining > 0 ? ' …' : ''}`
            : 'Szereplők: -';

        const watchedInfo = document.createElement('p');
        watchedInfo.classList.add('movie-card-meta');
        watchedInfo.textContent = `Megjelölve: ${film.hozzaadas_datuma ? formatDateLabel(film.hozzaadas_datuma) : 'nincs dátum'}`;

        info.appendChild(title);
        info.appendChild(year);
        info.appendChild(runtime);
        info.appendChild(directors);
        info.appendChild(cast);

        if (film.leiras) {
            const description = document.createElement('p');
            description.textContent = film.leiras.length > 140 ? `${film.leiras.slice(0, 137)}...` : film.leiras;
            info.appendChild(description);
        }

        info.appendChild(watchedInfo);

        card.appendChild(poster);
        card.appendChild(info);

        return card;
    };

    const loadFilms = async () => {
        try {
            showStatus('Megnézett filmek betöltése...');
            const response = await API.getWatchedFilms();

            if (!response.success || !response.data) {
                throw new Error(response.error || 'A megnézett filmek nem tölthetők be.');
            }

            const films = Array.isArray(response.data.watched) ? response.data.watched : [];
            const watchedFilms = films.filter((film) => film.megnezve_e);

            if (!watchedFilms.length) {
                filmGrid.innerHTML = '<p style="color: #b3b3b3;">Még nem jelöltél meg megnézett filmet. A keresésnél a filmeknél kapcsolhatod be.</p>';
                showStatus('Még nincs megnézett filmed.');
                return;
            }

            filmGrid.innerHTML = '';
            watchedFilms.forEach((film) => filmGrid.appendChild(createFilmCard(film)));
            showStatus(`Összesen ${watchedFilms.length} megnézett filmed van.`);
        } catch (error) {
            console.error('Film betöltési hiba:', error);
            showStatus('Hiba történt a megnézett filmek betöltése közben.', true);
        }
    };

    const initPage = async () => {
        try {
            const profileResult = await API.getProfile();
            if (!profileResult.success || !profileResult.data || !profileResult.data.user) {
                redirectToLogin();
                return;
            }
        } catch (error) {
            console.warn('Felhasználói ellenőrzés sikertelen:', error);
            redirectToLogin();
            return;
        }

        loadFilms();
    };

    initPage();
});
