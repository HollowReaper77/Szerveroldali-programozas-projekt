document.addEventListener('DOMContentLoaded', () => {
    const statusElement = document.getElementById('films-status');
    const filmGrid = document.getElementById('film-grid');

    const showStatus = (message, isError = false) => {
        if (!statusElement) return;
        statusElement.textContent = message;
        statusElement.style.color = isError ? '#ff6961' : '#b3b3b3';
    };

    const redirectToLogin = () => {
        window.location.href = 'bejelentkezes.html';
    };

    const createFilmCard = (film) => {
        const card = document.createElement('article');
        card.classList.add('movie-card');

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
        directors.textContent = film.rendezok ? `Rendező: ${film.rendezok}` : 'Rendező: -';

        const cast = document.createElement('p');
        if (film.szineszek) {
            const actorList = film.szineszek.split(',').map((name) => name.trim()).filter(Boolean);
            const topActors = actorList.slice(0, 3);
            const remaining = actorList.length - topActors.length;
            cast.textContent = topActors.length
                ? `Szereplők: ${topActors.join(', ')}${remaining > 0 ? ' …' : ''}`
                : 'Szereplők: -';
        } else {
            cast.textContent = 'Szereplők: -';
        }

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

        card.appendChild(poster);
        card.appendChild(info);

        return card;
    };

    const loadFilms = async () => {
        try {
            showStatus('Filmek betöltése folyamatban...');
            const response = await API.getFilms(1, 50);

            if (!response.success || !response.data) {
                throw new Error(response.message || 'A filmek nem tölthetők be.');
            }

            const films = Array.isArray(response.data.filmek) ? response.data.filmek : [];

            if (!films.length) {
                showStatus('Nem található egyetlen film sem.');
                return;
            }

            filmGrid.innerHTML = '';
            films.forEach((film) => filmGrid.appendChild(createFilmCard(film)));
            showStatus(`Összesen ${films.length} film található.`);
        } catch (error) {
            console.error('Film betöltési hiba:', error);
            showStatus('Hiba történt a filmek betöltése közben.', true);
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
