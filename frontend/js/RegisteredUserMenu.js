import { fetchWithAuth } from './fetchWithAuth.js';
const accessToken = localStorage.getItem('access_token');
const IDuser= localStorage.getItem('IDuser');
const Name = localStorage.getItem('Name');
const Surname = localStorage.getItem('Surname');
const Email = localStorage.getItem('Email');


document.getElementById('userDropdown').textContent = Name + ' ' +Surname +' ▼';

document.addEventListener('DOMContentLoaded', () => {
    const logoutLink = document.getElementById('logoutLink');

    logoutLink.addEventListener('click', (e) => {
        e.preventDefault();  // prevent default link behavior

        // Your logout logic here, for example:
        localStorage.clear();  // Clear tokens or user data
        alert('You have been logged out.');

        // Redirect to login page or homepage
        window.location.href = 'login.html';
    });
});
