import { fetchWithAuth } from './fetchWithAuth.js';

const accessToken = localStorage.getItem('access_token');
const IDuser= localStorage.getItem('IDuser');
const Name = localStorage.getItem('Name');
const Surname = localStorage.getItem('Surname');
const Email = localStorage.getItem('Email');

document.getElementById('userDropdown').textContent = Name + ' ' +Surname +' ▼';


document.addEventListener('DOMContentLoaded', () => {
  //logic for logout
	const logoutLink = document.getElementById('logoutLink');
	logoutLink.addEventListener('click', (e) => {
		e.preventDefault();  
		alert('You have been logged out.');
		window.location.href = 'login.html';
	});
	//logic for querry
	const blocks = document.querySelectorAll('.compare-block');
	blocks.forEach((block, index) => {
		block.innerHTML = getCompareBlockHTML(index + 1);
	});
	Promise.all(
		Array.from(blocks).map((_, i) => populateMakeModelDropdowns(i + 1))
	).then(() => {
		console.log('All dropdowns populated');
		blocks.forEach((block, index) => {
			const blockId = index + 1;
			setupSearchButton(blockId);
			//setupsaveQuery(blockId);
		});
		});
	
});

//real querry
async function fetchAndRenderStats(blockId) {

	const prefix = (id) => document.getElementById(`${id}-${blockId}`);
	const make = prefix('make').value;
	const model = prefix('model').value;
	const fuelType = prefix('fuelType').value;
	const yearFrom = prefix('yearFrom').value;
    const yearTo = prefix('yearTo').value;
	const loader = prefix('loader');
	const resultSection = document.querySelector('.resultSection')
	const tbody = prefix('resultBody');
	const canvas = prefix('resultChart');


	const params = new URLSearchParams();

	if (make) params.append('znamka', make);
	if (model) params.append('model', model);

	if (fuelType) {
		const fuelMap = { Diesel: 'D', Petrol: 'P', LPG: 'LPG', Electric: '-' };
		params.append('fuel', fuelMap[fuelType] || fuelType);
	}

	if (yearFrom) params.append('start_date', `${yearFrom}`);
	if (yearTo) params.append('end_date', `${yearTo}`);
	

  	let data;
    console.log(params)   ;
	try {
		loader.style.display = 'block'; 
		const response = await fetchWithAuth(`https://localhost/avtogvisn/api/cars?${params.toString()}`, {
		method: 'GET',
		headers: {
			'Content-Type': 'application/json',
		}
		});

		if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
		data = await response.json();
		console.log(data)   ;
  	}catch (err) {
    	alert(`Failed to fetch data: ${err.message}`);
    	loader.style.display = 'none'; 
   		return;
  	}finally {
		resultSection.style.display = 'block';
    	loader.style.display = 'none'; 
  	}

	if (data.length === 0) {
		alert(`Block ${blockId} - No data found for given filters.`);
		//resultSection.style.display = 'none';
		return;
  	}

	const stats = data[0];

	resultSection.style.display = 'block';
    renderChart(data);
    
}

function getCompareBlockHTML(id) {
  return `

    <div class="form-container">
      <label for="make-${id}" class=label>Make</label>
      <select id="make-${id}">
        <option disabled selected>Loading...</option>
      </select>

      <label for="model-${id}">Model</label>
      <select id="model-${id}" disabled>
        <option disabled selected>Select a model</option>
      </select>

      <label for="fuelType-${id}">Fuel type</label>
      <select id="fuelType-${id}">
        <option>Diesel</option>
        <option>Petrol</option>
        <option>LPG</option>
        <option>Electric</option>
      </select>

      <label for="yearFrom-${id}">Year of manufacture (from)</label>
      <input type="number" id="yearFrom-${id}" placeholder="2000">

      <label for="yearTo-${id}">Year of manufacture (to)</label>
      <input type="number" id="yearTo-${id}" placeholder="2015">

      <div class="formBtnContainer">
        <button id="searchBtn-${id}" class="formBtn">Search     </button>
      </div>
      <div id="loader-${id}" class="loader"></div>
    </div>

  
  `;
}
//populate dropdown menus with car brands, and once clicked with models from brands
async function populateMakeModelDropdowns(blockId) {
	const makeSelect = document.getElementById(`make-${blockId}`);
	const modelSelect = document.getElementById(`model-${blockId}`);

	if (!makeSelect || !modelSelect) {
		console.error(`Select elements for block ${blockId} not found`);
		return;
	}

	try {
		const response = await fetchWithAuth('https://localhost/avtogvisn/api/cars', {
		method: 'GET',
		headers: {
			'Content-Type': 'application/json',
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
	makeSelect.addEventListener('change', () => {
		const selectedBrand = makeSelect.value;
	loadModelsForBrand(blockId,selectedBrand);
});
	
}

async function loadModelsForBrand(blockId,selectedBrand) {
	const modelSelect = document.getElementById(`model-${blockId}`);
	modelSelect.disabled = true;
	modelSelect.innerHTML = '<option disabled selected>Loading models...</option>';

	try {
		const response = await fetchWithAuth(`https://localhost/avtogvisn/api/cars?znamka=${encodeURIComponent(selectedBrand)}`, {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',

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
}


//listener on search button
function setupSearchButton(blockId) {
	const btn = document.getElementById(`searchBtn-${blockId}`);
	if (!btn) return;
	
	btn.addEventListener('click', async () => {
		const loader = document.getElementById(`loader-${blockId}`);
		if (loader) loader.style.display = 'block';

		try {
		await fetchAndRenderStats(blockId);
		} catch (err) {
		console.error('Error fetching stats:', err);
		} finally {
		if (loader) loader.style.display = 'none';
		}
	});
	}


async function applySearch(queryId, blockId) {
	const makeSelect = document.getElementById(`make-${blockId}`);
  //	const modelSelect = document.getElementById(`model-${blockId}`);	
  	
    const query = savedQueries.find(q => q.query_id == queryId);
	makeSelect.value = query.znamka;
    if (!query) {
        console.error("Query not found:", queryId);
        return;
    }

	const fuelMap = {
		'D': 'Diesel',
		'P': 'Petrol',
		'LPG': 'LPG',
		'E': 'Electric'
	};


	const fuelValue = fuelMap[query.fuel] || ''; 
	document.getElementById(`make-${blockId}`).value = query.znamka;
	//document.getElementById(`make-${targetBlock}`).dispatchEvent(new Event('change'));
	await loadModelsForBrand(blockId,query.znamka, );
    document.getElementById(`model-${blockId}`).value = query.model;
	document.getElementById(`fuelType-${blockId}`).value = fuelValue;
	document.getElementById(`yearFrom-${blockId}`).value = query.start_date.slice(0, 4);
	document.getElementById(`yearTo-${blockId}`).value = query.end_date.slice(0, 4);
	document.getElementById(`kmFrom-${blockId}`).value = query.min_km;
	document.getElementById(`kmTo-${blockId}`).value = query.max_km;
    
}

let brezhibenChart = null; // Global variable to store the chart instance
function renderChart(data) {
  // Extract labels and data
  const labels = data.map(d => d.year);
  const brezhibenPercentages = data.map(d =>
    d.total_count > 0 ? (d.brezhiben_count / d.total_count * 100).toFixed(2) : 0
  );

  // Destroy old chart if it exists
  if (brezhibenChart) {
    brezhibenChart.destroy();
  }

  // Create new chart
  const ctx = document.getElementById('brezhibenChart').getContext('2d');
  brezhibenChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Brezhiben %',
        data: brezhibenPercentages,
        borderColor: 'green',
        backgroundColor: 'rgba(0, 128, 0, 0.1)',
        fill: true,
        tension: 0.2,
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        tooltip: {
          callbacks: {
            label: context => `${context.parsed.y}% brezhiben`
          }
        },
        legend: { display: true }
      },
      scales: {
        y: {
          title: { display: true, text: 'Brezhiben (%)' },
          min: 0,
          max: 110
        },
        x: {
          title: { display: true, text: 'Year' }
        }
      }
    }
  });
}
