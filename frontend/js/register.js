document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('loginForm');
    const errorDiv = document.getElementById('errorMessage');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        const data = {
            name: formData.get('Name'),
            surname: formData.get('Surname'),
            email: formData.get('Mail'),
            password: formData.get('Password')
        };

        try {
            const response = await fetch("/avtogvisn/register.php", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert("User registered successfully!");
                window.location.href ="meni.html"
            } 
            else {errorDiv.textContent = result.error || "Registration failed.";
            //const errorDiv = document.getElementById('errorMessage');
            }
        } catch (err) {
            errorDiv.textContent = "An error occurred.";
            console.error(err);
        }
    });
});
