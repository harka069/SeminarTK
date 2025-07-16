import { fetchWithAuth } from './fetchWithAuth.js';
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('access_token');
    if (!token) {
        alert('You are not logged in.');
        window.location.href = 'login.html';
        return;
    }

    const errorDiv = document.getElementById('errorMessage');
    const form = document.getElementById('editProfileForm');
    const accessToken = localStorage.getItem('access_token');
    const IDuser= localStorage.getItem('IDuser');

    try {
        // Load user data
        const response = await fetch(`/avtogvisn/api/users/?IDuser=${IDuser}`, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + accessToken,
            'Content-Type': 'application/json'
    }
});

        if (!response.ok) {
            throw new Error('Failed to load user data');
        }

        const userData = await response.json();

        // Prefill form inputs
        form.name.value = userData.Name || '';
        form.surname.value = userData.Surname || '';
        form.email.value = userData.Mail || '';
        form.password.value = ''; // Leave blank for password

    } catch (err) {
        errorDiv.textContent = err.message;
        console.error(err);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorDiv.textContent = '';
        

        const action = e.submitter ? e.submitter.value : null;
        if (!action) {
            errorDiv.textContent = 'No action specified.';
            return;
        }
        if (action === 'save') {
            const data = {
                name: form.name.value.trim(),
                surname: form.surname.value.trim(),
                mail: form.email.value.trim(),
                password: form.password.value, 
                oldPassword: form.oldPassword.value
            };

            try {
                const response = await fetchWithAuth('/avtogvisn/api/users', {
                    method: 'PUT',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Profile updated successfully!');
                    localStorage.setItem('Name', data.name);
                    localStorage.setItem('Surname', data.surname);
                    localStorage.setItem('Email', data.mail);
                   
                    form.password.value = ''; // Clear password input after success

                    window.location.href = "../html/RegisteredUserMenu.html";
                } else {
                    errorDiv.textContent = result.error || 'Failed to update profile.';
                }
            } catch (err) {
                errorDiv.textContent = 'An error occurred.';
                console.error(err);
            }
        }else if (action === 'delete'){
            window.location.href = "../html/deleteUser.html";
        }else{
            errorDiv.textContent = 'Unknown action.';
        }
    });
});
