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
document.addEventListener('DOMContentLoaded', async () => {
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');

    // Load car brands on page load
    try {
        const response = await fetch('http://localhost/avtogvisn/api/cars', {
            method: 'GET',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + accessToken,    
            }
        });

        if (!response.ok) throw new Error('Failed to fetch brands.');
        const brands = await response.json();

        makeSelect.innerHTML = '<option disabled selected>Select a make</option>';
        brands.forEach(brand => {
            const option = document.createElement('option');
            option.value = brand;
            option.textContent = brand;
            makeSelect.appendChild(option);
        });
    } catch (err) {
        console.error(err);
        makeSelect.innerHTML = '<option disabled>Error loading brands</option>';
    }

    // When user selects a brand, fetch models
    makeSelect.addEventListener('change', async () => {
        const selectedBrand = makeSelect.value;

        modelSelect.disabled = true;
        modelSelect.innerHTML = '<option disabled selected>Loading models...</option>';

        try {
            const response = await fetch(`http://localhost/avtogvisn/api/cars?znamka=${encodeURIComponent(selectedBrand)}`, {
                method: 'GET',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + accessToken,    
                 }
            });

            if (!response.ok) throw new Error('Failed to fetch models.');
            const models = await response.json(); 

            modelSelect.innerHTML = '<option disabled selected>Select a model</option>';
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });
            modelSelect.disabled = false;
        } catch (err) {
            console.error(err);
            modelSelect.innerHTML = '<option disabled>Error loading models</option>';
        }
    });
});

const loader = document.getElementById('loader');

document.getElementById('searchBtn').addEventListener('click', async () => {
    const make = document.getElementById('make').value;
    const model = document.getElementById('model').value;
    const fuelType = document.getElementById('fuelType').value;
    const yearFrom = document.getElementById('yearFrom').value;
    const yearTo = document.getElementById('yearTo').value;
    const kmFrom = document.getElementById('kmFrom').value;
    const kmTo = document.getElementById('kmTo').value;
    const loader = document.getElementById('loader');

    const params = new URLSearchParams();

    if (make) params.append('znamka', make);
    if (model) params.append('model', model);
    if (fuelType) params.append('fuel', "P"); // Consider mapping actual value
    if (yearFrom) params.append('start_date', `${yearFrom}-01-01`);
    if (yearTo) params.append('end_date', `${yearTo}-12-31`);
    if (kmFrom) params.append('min_km', kmFrom);
    if (kmTo) params.append('max_km', kmTo);

    let data;

    try {
        loader.style.display = 'block'; 

        const response = await fetch(`http://localhost/avtogvisn/api/cars?${params.toString()}`, {
            method: 'GET',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + accessToken,    
            }
        });

        data = await response.json();
        console.log('Filtered cars:', data);
    } catch (err) {
        console.error('Failed to fetch cars:', err);
        return;
    } finally {
        loader.style.display = 'none'; 
    }

    if (data.length > 0) {
        const stats = data[0];

        // Show table
        document.getElementById('resultSection').style.display = 'block';

        // Populate table
        const tbody = document.querySelector('#resultTable tbody');
        if (tbody) {
            tbody.innerHTML = `
              <tr>
                <td>${stats.total_count}</td>
                <td>${stats.brezhiben_count}</td>
                <td>${stats.ne_brezhiben_count}</td>
              </tr>
            `;
        }

        // Draw chart
        const ctx = document.getElementById('resultChart').getContext('2d');
        if (window.pieChart) window.pieChart.destroy(); // Remove old chart if needed

        window.pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Passed', 'Failed'],
                datasets: [{
                    data: [stats.brezhiben_count, stats.ne_brezhiben_count],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Technical Check Results'
                    }
                }
            }
        });
    } else {
        alert('No data found for given filters.');
    }
});