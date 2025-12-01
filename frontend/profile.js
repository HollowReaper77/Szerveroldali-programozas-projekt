// Profil kezelés
document.addEventListener('DOMContentLoaded', async function() {
    const statusElement = document.getElementById('status');
    
    // Felhasználó betöltése
    await loadUserProfile();
});

async function loadUserProfile() {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}/users/profile`, {
            method: 'GET',
            headers: API_CONFIG.HEADERS,
            credentials: 'include'
        });

        const result = await response.json();

        if (response.ok && result.felhasznalo) {
            displayUserProfile(result.felhasznalo);
        } else {
            // Nincs bejelentkezve - átirányítás
            console.warn('Nincs bejelentkezve');
            // localStorage-ból próbáljuk meg betölteni
            const cachedUser = localStorage.getItem('user');
            if (cachedUser) {
                displayUserProfile(JSON.parse(cachedUser));
            } else {
                // Átirányítás bejelentkezéshez
                window.location.href = 'bejelentkezes.html';
            }
        }
    } catch (error) {
        console.error('Profile load error:', error);
        // Próbálkozás localStorage-ból
        const cachedUser = localStorage.getItem('user');
        if (cachedUser) {
            displayUserProfile(JSON.parse(cachedUser));
        }
    }
}

function displayUserProfile(user) {
    // Név megjelenítése
    const nameElements = document.querySelectorAll('[data-user-name]');
    nameElements.forEach(el => {
        el.textContent = user.felhasznalonev || 'Felhasználó neve';
    });

    // Email megjelenítése
    const emailElements = document.querySelectorAll('[data-user-email]');
    emailElements.forEach(el => {
        el.textContent = user.email || 'user@domain.com';
    });

    // Szerepkör megjelenítése (ha van ilyen elem)
    const roleElements = document.querySelectorAll('[data-user-role]');
    roleElements.forEach(el => {
        el.textContent = user.jogosultsag || 'user';
    });

    // Profilkép megjelenítése
    if (user.profilkep_url) {
        const profileImages = document.querySelectorAll('.profile-picture');
        profileImages.forEach(img => {
            img.src = user.profilkep_url;
        });
    }

    // Form mezők kitöltése (ha van szerkesztő form)
    const nameInput = document.querySelector('input[name="felhasznalonev"]');
    const emailInput = document.querySelector('input[name="email"]');
    
    if (nameInput) nameInput.value = user.felhasznalonev || '';
    if (emailInput) emailInput.value = user.email || '';
}

// Kijelentkezés
async function logout() {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}/users/logout`, {
            method: 'POST',
            headers: API_CONFIG.HEADERS,
            credentials: 'include'
        });

        if (response.ok) {
            // LocalStorage törlése
            localStorage.removeItem('user');
            
            // Átirányítás bejelentkezéshez
            window.location.href = 'bejelentkezes.html';
        }
    } catch (error) {
        console.error('Logout error:', error);
        // Mindenképp kijelentkeztetjük lokálisan
        localStorage.removeItem('user');
        window.location.href = 'bejelentkezes.html';
    }
}

// Profil frissítés
async function updateProfile(formData) {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}/users/profile`, {
            method: 'PUT',
            headers: API_CONFIG.HEADERS,
            credentials: 'include',
            body: JSON.stringify({
                felhasznalonev: formData.get('felhasznalonev'),
                email: formData.get('email'),
                profilkep_url: formData.get('profilkep_url') || null
            })
        });

        const result = await response.json();

        if (response.ok) {
            // LocalStorage frissítése
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            user.felhasznalonev = formData.get('felhasznalonev');
            user.email = formData.get('email');
            localStorage.setItem('user', JSON.stringify(user));

            return { success: true, message: result.message };
        } else {
            return { success: false, message: result.message };
        }
    } catch (error) {
        console.error('Update profile error:', error);
        return { success: false, message: 'Hálózati hiba történt.' };
    }
}

// Navbar felhasználó megjelenítése minden oldalon
document.addEventListener('DOMContentLoaded', function() {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    
    if (user) {
        // Profil linkek frissítése
        const profileLinks = document.querySelectorAll('a[href="profil.html"]');
        profileLinks.forEach(link => {
            const img = link.querySelector('.profile-picture');
            if (img && user.profilkep_url) {
                img.src = user.profilkep_url;
            }
        });

        // Navbar menü módosítása (bejelentkezés helyett kijelentkezés)
        const menuList = document.querySelector('.menu-list');
        if (menuList && user) {
            const loginItem = Array.from(menuList.querySelectorAll('a')).find(a => a.textContent === 'Bejelentkezés');
            const registerItem = Array.from(menuList.querySelectorAll('a')).find(a => a.textContent === 'Regisztráció');
            
            if (loginItem) {
                loginItem.textContent = 'Kijelentkezés';
                loginItem.href = '#';
                loginItem.addEventListener('click', (e) => {
                    e.preventDefault();
                    logout();
                });
            }
            
            if (registerItem && registerItem.parentElement) {
                registerItem.parentElement.style.display = 'none';
            }
        }
    }
});
