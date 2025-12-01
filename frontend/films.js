// Főoldal filmek betöltése
document.addEventListener('DOMContentLoaded', async function() {
    // Filmek betöltése az API-ból
    await loadNewFilms();
});

async function loadNewFilms() {
    const movieListContainer = document.querySelector('.movie-list');
    
    if (!movieListContainer) {
        console.warn('Movie list container not found');
        return;
    }

    try {
        // API hívás a filmekhez
        const result = await API.getFilms(1, 10); // Első 10 film

        if (result.success && result.data.filmek) {
            const films = result.data.filmek;
            
            // Meglévő tartalom törlése
            movieListContainer.innerHTML = '';

            // Filmek hozzáadása
            films.forEach(film => {
                const movieItem = createMovieItem(film);
                movieListContainer.appendChild(movieItem);
            });
        } else {
            console.error('Filmek betöltése sikertelen:', result.error);
            movieListContainer.innerHTML = `
                <div style="color: white; padding: 20px;">
                    <p>Nem sikerült betölteni a filmeket.</p>
                    <p style="color: #ff3b3b; font-size: 14px;">${result.error || 'Ismeretlen hiba'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Hiba történt a filmek betöltése során:', error);
        movieListContainer.innerHTML = `
            <div style="color: white; padding: 20px;">
                <p>Hiba történt a filmek betöltése során.</p>
            </div>
        `;
    }
}

// Film elem létrehozása
function createMovieItem(film) {
    const div = document.createElement('div');
    div.className = 'movie-list-item';
    
    div.innerHTML = `
        <img class="movie-list-item-img" src="${film.poszter_url || 'img/placeholder.jpg'}" alt="${film.cim}">
        <span class="movie-list-item-title">${film.cim}</span>
        <p class="movie-list-item-desc">${film.leiras ? film.leiras.substring(0, 80) + '...' : 'Nincs leírás'}</p>
        <p class="movie-list-item-desc" style="font-size: 12px; color: #ccc;">${film.kiadasi_ev || ''} ${film.idotartam ? '• ' + film.idotartam + ' perc' : ''}</p>
    `;
    
    return div;
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
