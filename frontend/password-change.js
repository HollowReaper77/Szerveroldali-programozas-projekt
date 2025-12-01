// Jelszó módosítás funkció
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('password-form');
    const statusElement = document.getElementById('status');

    if (!passwordForm) {
        console.error('Password form not found');
        return;
    }

    passwordForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(passwordForm);
        const oldPassword = formData.get('regi_jelszo');
        const newPassword = formData.get('uj_jelszo');
        const confirmPassword = formData.get('uj_jelszo_confirm');

        // Validáció
        if (!oldPassword || !newPassword || !confirmPassword) {
            statusElement.textContent = 'Minden mező kitöltése kötelező!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        // Új jelszó egyezés ellenőrzése
        if (newPassword !== confirmPassword) {
            statusElement.textContent = 'Az új jelszavak nem egyeznek!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        // Jelszó hossz ellenőrzése
        if (newPassword.length < 6) {
            statusElement.textContent = 'Az új jelszó legalább 6 karakter hosszú kell legyen!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        // Azonosság ellenőrzése
        if (oldPassword === newPassword) {
            statusElement.textContent = 'Az új jelszó nem egyezhet meg a régivel!';
            statusElement.style.color = '#ff3b3b';
            return;
        }

        statusElement.textContent = 'Jelszó módosítása...';
        statusElement.style.color = '#4dbf00';

        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/users/password`, {
                method: 'PUT',
                headers: API_CONFIG.HEADERS,
                credentials: 'include',
                body: JSON.stringify({
                    regi_jelszo: oldPassword,
                    uj_jelszo: newPassword
                })
            });

            const result = await response.json();

            if (response.ok) {
                // Sikeres jelszó módosítás
                statusElement.textContent = result.message || 'Jelszó sikeresen megváltoztatva!';
                statusElement.style.color = '#4dbf00';

                // Form mezők törlése
                passwordForm.reset();

                // Átirányítás a profilra 2 másodperc múlva
                setTimeout(() => {
                    window.location.href = 'profil.html';
                }, 2000);
            } else {
                // Hiba
                statusElement.textContent = result.message || 'Jelszó módosítása sikertelen.';
                statusElement.style.color = '#ff3b3b';
            }
        } catch (error) {
            console.error('Password change error:', error);
            statusElement.textContent = 'Hálózati hiba történt. Ellenőrizd, hogy be vagy-e jelentkezve.';
            statusElement.style.color = '#ff3b3b';
        }
    });

    // Jelszó megerősítés valós idejű ellenőrzése
    const newPasswordInput = document.querySelector('input[name="uj_jelszo"]');
    const confirmPasswordInput = document.querySelector('input[name="uj_jelszo_confirm"]');

    if (confirmPasswordInput && newPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== newPasswordInput.value) {
                this.setCustomValidity('A jelszavak nem egyeznek');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
