import { fetchWithAuth } from './fetchWithAuth.js'; 

const IDuser = localStorage.getItem('IDuser');
const token = localStorage.getItem('access_token');

const form = document.getElementById('DeleteProfileForm');
const errorDiv = document.getElementById('errorMessage');

form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Prevent page reload on form submit
    errorDiv.textContent = '';

    if (!token || !IDuser) {
        errorDiv.textContent = 'Uporabnik ni prijavljen.';
        return;
    }

    const Password = document.getElementById('Password').value.trim();

    if (!Password) {
        errorDiv.textContent = 'Prosimo, vnesite geslo.';
        return;
    }

    const data = { Password };

    try {
        const response = await fetchWithAuth('/avtogvisn/api/users/', {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Profil uspešno izbrisan.');
            localStorage.clear();
            window.location.href = '../html/meni.html';
        } else {
            const result = await response.json();
            errorDiv.textContent = result.error || 'Napaka pri brisanju profila.';
        }
    } catch (err) {
        console.error(err);
        errorDiv.textContent = 'Napaka na strežniku.';
    }
});
