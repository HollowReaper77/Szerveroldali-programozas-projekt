// Film keresés funkció
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchButton = document.getElementById('search-button');
    const filmsContainer = document.getElementById('films');
    const statusElement = document.getElementById('status');

    let currentPage = 1;
    let allFilms = [];

    // Filmek betöltése az API-ból
    async function loadFilms() {
        statusElement.textContent = 'Filmek betöltése...';
        statusElement.style.color = '#4dbf00';

        const result = await API.getFilms(1, 100); // Nagy limit, hogy minden filmet megkapjunk

        if (result.success) {
            allFilms = result.data.data || [];
            displayFilms(allFilms);
            statusElement.textContent = `${allFilms.length} film betöltve`;
        } else {
            statusElement.textContent = 'Hiba: ' + result.error;
            statusElement.style.color = '#ff3b3b';
            filmsContainer.innerHTML = '<p style="color: white;">Nem sikerült betölteni a filmeket.</p>';
        }
    }

    // Filmek megjelenítése
    function displayFilms(films) {
        if (!films || films.length === 0) {
            filmsContainer.innerHTML = '<p style="color: white; padding: 20px;">Nincs találat.</p>';
            return;
        }

        filmsContainer.innerHTML = films.map(film => `
            <div class="movie-card" style="
                background-color: var(--card);
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                transition: transform 0.3s ease;
                cursor: pointer;
            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="${film.plakat_url || 'img/placeholder.jpg'}" alt="${film.cim}" style="
                    width: 100%;
                    height: 300px;
                    object-fit: cover;
                ">
                <div style="padding: 15px;">
                    <h3 style="color: #4dbf00; margin: 0 0 10px 0; font-size: 18px;">${film.cim}</h3>
                    <p style="color: lightgray; font-size: 14px; margin: 5px 0;">
                        <strong>Megjelenés:</strong> ${film.megjelenes_eve || 'Ismeretlen'}
                    </p>
                    <p style="color: lightgray; font-size: 14px; margin: 5px 0;">
                        <strong>Hossz:</strong> ${film.hossz || 'N/A'} perc
                    </p>
                    <p style="color: lightgray; font-size: 13px; margin: 10px 0; line-height: 1.4;">
                        ${film.leiras ? (film.leiras.substring(0, 100) + '...') : 'Nincs leírás'}
                    </p>
                    ${film.trailer_url ? `
                        <a href="${film.trailer_url}" target="_blank" style="text-decoration: none;">
                            <button class="featured-button" style="width: 100%; margin-top: 10px; background-color: #007AFF;">
                                Trailer megtekintése
                            </button>
                        </a>
                    ` : ''}
                </div>
            </div>
        `).join('');

        // CSS Grid alkalmazása
        filmsContainer.style.display = 'grid';
        filmsContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(250px, 1fr))';
        filmsContainer.style.gap = '20px';
        filmsContainer.style.padding = '20px 0';
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

    // Event listeners
    searchButton.addEventListener('click', searchFilms);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchFilms();
        }
    });

    // Kezdeti betöltés
    loadFilms();
});
