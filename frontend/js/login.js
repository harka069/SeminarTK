 const form = document.getElementById('loginForm');
        const errorDiv = document.getElementById('errorMessage');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };

            const response = await fetch("/avtogvisn/api/login.php", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // Store JWT or redirect
                localStorage.setItem('access_token', result.access_token);
                localStorage.setItem('refresh_token', result.refresh_token);
                localStorage.setItem('Name', result.Name);
                localStorage.setItem('Surname', result.Surname);
                localStorage.setItem('Email', result.Email);
                localStorage.setItem('IDuser', result.IDuser);

                window.location.href = "../html/RegisteredUserMenu.html";
        
            } else {
                errorDiv.textContent = result.error || "Login failed.";
            }
        });