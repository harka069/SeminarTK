const accessToken = localStorage.getItem('access_token');
const IDuser= localStorage.getItem('IDuser');
const Name = localStorage.getItem('Name');
const Surname = localStorage.getItem('Surname');
const Email = localStorage.getItem('Email');
console.log(accessToken);
console.log(IDuser);
console.log(Name);
console.log(Surname);
console.log(Email);

document.getElementById('userDropdown').textContent = Name + ' ' +Surname +' ▼';
