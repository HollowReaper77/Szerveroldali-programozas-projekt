// API Configuration
const API_CONFIG = {
    // Backend API base URL - változtasd meg, ha más porton fut
    BASE_URL: 'http://localhost/php/PHP%20projekt/Szerveroldali-programozas-projekt/public',
    
    // API Endpoints
    ENDPOINTS: {
        FILMS: '/films',
        ACTORS: '/actors',
        DIRECTORS: '/directors',
        GENRES: '/genres',
        COUNTRIES: '/countries',
        FILM_ACTORS: '/film-actors',
        FILM_GENRES: '/film-genres',
        USERS: '/users',
        UPLOAD: '/upload'
    },
    
    // HTTP Headers
    HEADERS: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

// Helper függvény API hívásokhoz
async function apiRequest(endpoint, options = {}) {
    // Távolítsd el a kezdő / jelet
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    const url = `${API_CONFIG.BASE_URL}/index.php?url=${cleanEndpoint}`;
    
    console.log('API Request URL:', url); // DEBUG
    
    const config = {
        ...options,
        headers: {
            ...API_CONFIG.HEADERS,
            ...options.headers
        }
    };
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'API hiba történt');
        }
        
        return { success: true, data };
    } catch (error) {
        // Ne írjunk hibát a konzolra, ha csak nincs bejelentkezve
        const isAuthError = error.message && (
            error.message.includes('Nincs bejelentkezve') || 
            error.message.includes('nem található')
        );
        
        if (!isAuthError) {
            console.error('API Error:', error);
        }
        
        return { success: false, error: error.message };
    }
}

// API függvények exportálása
const API = {
    // Films
    getFilms: (page = 1, limit = 10) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILMS}&page=${page}&limit=${limit}`),
    
    getFilm: (id) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILMS}/${id}`),
    
    createFilm: (filmData) => 
        apiRequest(API_CONFIG.ENDPOINTS.FILMS, {
            method: 'POST',
            body: JSON.stringify(filmData)
        }),
    
    updateFilm: (id, filmData) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILMS}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(filmData)
        }),
    
    deleteFilm: (id) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILMS}/${id}`, {
            method: 'DELETE'
        }),
    
    // Actors
    getActors: (page = 1, limit = 10) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.ACTORS}?page=${page}&limit=${limit}`),
    
    getActor: (id) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.ACTORS}/${id}`),
    
    // Directors
    getDirectors: (page = 1, limit = 10) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.DIRECTORS}?page=${page}&limit=${limit}`),
    
    getDirector: (id) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.DIRECTORS}/${id}`),
    
    // Genres
    getGenres: () => 
        apiRequest(API_CONFIG.ENDPOINTS.GENRES),
    
    // Countries
    getCountries: () => 
        apiRequest(API_CONFIG.ENDPOINTS.COUNTRIES),
    
    // Film-Actors relationships
    getActorsByFilm: (filmId) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILM_ACTORS}/film/${filmId}`),
    
    getFilmsByActor: (actorId) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILM_ACTORS}/actor/${actorId}`),
    
    // Film-Genres relationships
    getGenresByFilm: (filmId) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILM_GENRES}/film/${filmId}`),
    
    getFilmsByGenre: (genreId) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.FILM_GENRES}/genre/${genreId}`),
    
    // Users (Authentication)
    register: (userData) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/register`, {
            method: 'POST',
            credentials: 'include',
            body: JSON.stringify(userData)
        }),
    
    login: (credentials) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/login`, {
            method: 'POST',
            credentials: 'include',
            body: JSON.stringify(credentials)
        }),
    
    logout: () => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/logout`, {
            method: 'POST',
            credentials: 'include'
        }),
    
    getProfile: () => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/profile`, {
            method: 'GET',
            credentials: 'include'
        }),
    
    updateProfile: (userData) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/profile`, {
            method: 'PUT',
            credentials: 'include',
            body: JSON.stringify(userData)
        }),
    
    changePassword: (passwordData) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/change-password`, {
            method: 'PUT',
            credentials: 'include',
            body: JSON.stringify(passwordData)
        }),
    
    // Admin - User Management
    getAllUsers: () => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/all`, {
            method: 'GET',
            credentials: 'include'
        }),
    
    updateUserRole: (userId, newRole) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/${userId}/role`, {
            method: 'PUT',
            credentials: 'include',
            body: JSON.stringify({ jogosultsag: newRole })
        }),
    
    deleteUser: (userId) => 
        apiRequest(`${API_CONFIG.ENDPOINTS.USERS}/${userId}`, {
            method: 'DELETE',
            credentials: 'include'
        })
};
