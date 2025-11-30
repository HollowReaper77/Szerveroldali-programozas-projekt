// Bejelentkezés funkció
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const statusElement = document.getElementById('status');

    if (!loginForm) {
        console.error('Login form not found');
        return;
    }

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(loginForm);
        const email = formData.get('email');
        const password = formData.get('password');

        // Validáció
        if (!email || !password) {
            statusElement.textContent = 'Email és jelszó kötelező!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        statusElement.textContent = 'Bejelentkezés...';
        statusElement.style.color = '#4dbf00';

        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/users/login`, {
                method: 'POST',
                headers: API_CONFIG.HEADERS,
                credentials: 'include',
                body: JSON.stringify({
                    email: email,
                    jelszo: password
                })
            });

            const result = await response.json();

            if (response.ok) {
                // Sikeres bejelentkezés
                statusElement.textContent = result.message;
                statusElement.style.color = '#4dbf00';

                // Felhasználó adatok mentése localStorage-ba
                localStorage.setItem('user', JSON.stringify(result.user));

                // Átirányítás a főoldalra 1 másodperc múlva
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1000);
            } else {
                // Hiba
                statusElement.textContent = result.message || 'Bejelentkezés sikertelen.';
                statusElement.style.color = '#ff3b3b';
            }
        } catch (error) {
            console.error('Login error:', error);
            statusElement.textContent = 'Hálózati hiba történt.';
            statusElement.style.color = '#ff3b3b';
        }
    });
});
