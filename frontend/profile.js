let currentUser = null;
let uploadStatusElement = null;

// Profil kezelés
document.addEventListener('DOMContentLoaded', async function() {
    uploadStatusElement = document.getElementById('upload-status');
    const fileInput = document.getElementById('file-upload');

    if (fileInput) {
        fileInput.addEventListener('change', handleProfileImageUpload);
    }

    await loadUserProfile();
    setupNavbarFromLocalStorage();
});

async function loadUserProfile() {
    try {
        const result = await API.getProfile();

        if (result.success && result.data && result.data.user) {
            currentUser = normalizeUser(result.data.user);
            displayUserProfile(currentUser);
            cacheUser(currentUser);
            return;
        }
    } catch (error) {
        console.error('Profile load error:', error);
    }

    // Fallback: localStorage vagy átirányítás
    const cachedUser = localStorage.getItem('user');
    if (cachedUser) {
        currentUser = JSON.parse(cachedUser);
        displayUserProfile(currentUser);
    } else {
        window.location.href = 'bejelentkezes.html';
    }
}

function normalizeUser(user) {
    if (!user) {
        return null;
    }

    return {
        ...user,
        felhasznalonev: user.felhasznalonev || user.name || '',
        email: user.email || '',
        profilkep_url: user.profilkep_url || user.profile_image || null,
        jogosultsag: user.jogosultsag || user.szerepkor || 'user'
    };
}

function cacheUser(user) {
    if (!user) return;
    const existing = JSON.parse(localStorage.getItem('user') || '{}');
    const merged = { ...existing, ...user };
    localStorage.setItem('user', JSON.stringify(merged));
}

function displayUserProfile(user) {
    if (!user) return;

    currentUser = { ...currentUser, ...user };

    // Név megjelenítése
    const nameElements = document.querySelectorAll('[data-user-name]');
    nameElements.forEach(el => {
        el.textContent = currentUser.felhasznalonev || 'Felhasználó neve';
    });

    // Email megjelenítése
    const emailElements = document.querySelectorAll('[data-user-email]');
    emailElements.forEach(el => {
        el.textContent = currentUser.email || 'user@domain.com';
    });

    // Szerepkör megjelenítése (ha van ilyen elem)
    const roleElements = document.querySelectorAll('[data-user-role]');
    roleElements.forEach(el => {
        el.textContent = currentUser.jogosultsag || 'user';
    });

    // Profilkép megjelenítése
    const profileImages = document.querySelectorAll('.profile-picture');
    const profileSrc = currentUser.profilkep_url || 'img/profil.jpg';
    profileImages.forEach(img => {
        img.src = profileSrc;
    });

    // Form mezők kitöltése (ha van szerkesztő form)
    const nameInput = document.querySelector('input[name="felhasznalonev"]');
    const emailInput = document.querySelector('input[name="email"]');
    
    if (nameInput) nameInput.value = currentUser.felhasznalonev || '';
    if (emailInput) emailInput.value = currentUser.email || '';
}

async function handleProfileImageUpload(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) {
        return;
    }

    showUploadStatus('Kép feltöltése folyamatban...', false);

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}/upload/image`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });

        const uploadResult = await response.json();

        if (!response.ok || !uploadResult.url) {
            throw new Error(uploadResult.message || 'A kép feltöltése sikertelen.');
        }

        if (!currentUser) {
            await loadUserProfile();
        }

        const payload = {
            felhasznalonev: currentUser?.felhasznalonev || '',
            email: currentUser?.email || '',
            profilkep_url: uploadResult.url
        };

        const updateResult = await API.updateProfile(payload);

        if (!updateResult.success) {
            throw new Error(updateResult.error || updateResult.data?.message || 'A profil frissítése sikertelen.');
        }

        currentUser = { ...currentUser, profilkep_url: uploadResult.url };
        cacheUser(currentUser);
        displayUserProfile(currentUser);
        showUploadStatus('Profilkép sikeresen frissítve.', false);
        event.target.value = '';
    } catch (error) {
        console.error('Profile image upload error:', error);
        showUploadStatus(error.message || 'Hiba történt a feltöltés közben.', true);
    }
}

function showUploadStatus(message, isError = false) {
    if (!uploadStatusElement) return;
    uploadStatusElement.textContent = message;
    uploadStatusElement.style.color = isError ? '#ff3b3b' : '#4dbf00';
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

function setupNavbarFromLocalStorage() {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    
    if (!user) {
        return;
    }

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
    if (menuList) {
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
