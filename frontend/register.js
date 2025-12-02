// Regisztráció funkció
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const statusElement = document.getElementById('status');

    if (!registerForm) {
        console.error('Register form not found');
        return;
    }

    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(registerForm);
        const name = formData.get('name');
        const email = formData.get('email');
        const password = formData.get('password');
        const password2 = formData.get('password2');

        // Validáció
        if (!name || !email || !password || !password2) {
            statusElement.textContent = 'Minden mező kitöltése kötelező!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        if (password !== password2) {
            statusElement.textContent = 'A jelszavak nem egyeznek!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        if (password.length < 6) {
            statusElement.textContent = 'A jelszónak legalább 6 karakter hosszúnak kell lennie!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        statusElement.textContent = 'Regisztráció...';
        statusElement.style.color = '#4dbf00';

        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/users/register`, {
                method: 'POST',
                headers: API_CONFIG.HEADERS,
                credentials: 'include',
                body: JSON.stringify({
                    felhasznalonev: name,
                    email: email,
                    jelszo: password
                })
            });

            const result = await response.json();

            if (response.ok) {
                // Sikeres regisztráció
                statusElement.textContent = result.message;
                statusElement.style.color = '#4dbf00';

                // Felhasználó adatok mentése localStorage-ba
                localStorage.setItem('user', JSON.stringify(result.felhasznalo));

                // Átirányítás a főoldalra 1.5 másodperc múlva
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1500);
            } else {
                // Hiba
                statusElement.textContent = result.message || 'Regisztráció sikertelen.';
                statusElement.style.color = '#ff3b3b';
            }
        } catch (error) {
            console.error('Register error:', error);
            statusElement.textContent = 'Hálózati hiba történt.';
            statusElement.style.color = '#ff3b3b';
        }
    });
});
