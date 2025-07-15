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
        setupsaveQuery(blockId);
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
	const kmFrom = prefix('kmFrom').value;
	const kmTo = prefix('kmTo').value;
	const loader = prefix('loader');
	const resultSection = prefix('resultSection');
	const tbody = prefix('resultBody');
	const canvas = prefix('resultChart');


	const params = new URLSearchParams();

	if (make) params.append('znamka', make);
	if (model) params.append('model', model);

	if (fuelType) {
		const fuelMap = { Diesel: 'D', Petrol: 'P', LPG: 'LPG', Electric: '-' };
		params.append('fuel', fuelMap[fuelType] || fuelType);
	}

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
			'Authorization': 'Bearer ' + accessToken
		}
		});

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    data = await response.json();

    console.log(`Block ${blockId} data:`, data);
  	}catch (err) {
    	alert(`Block ${blockId} - Failed to fetch data: ${err.message}`);
    	loader.style.display = 'none'; 
    return;
  	}finally {
    	loader.style.display = 'none'; 
  	}

	if (data.length === 0) {
		alert(`Block ${blockId} - No data found for given filters.`);
		resultSection.style.display = 'none';
		return;
  	}

	const stats = data[0];
	resultSection.style.display = 'block';

	tbody.innerHTML = `
		<tr>
		<td>${stats.total_count}</td>
		<td>${stats.brezhiben_count}</td>
		<td>${stats.ne_brezhiben_count}</td>
		</tr>
	`;

	if (window[`pieChart${blockId}`]) {
		window[`pieChart${blockId}`].destroy();
	}

	const ctx = canvas.getContext('2d');
	window[`pieChart${blockId}`] = new Chart(ctx, {
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
			legend: { position: 'bottom' },
			title: { display: true  }
		}
	}
  });
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

      <label for="kmFrom-${id}">Kilometers (from)</label>
      <input type="number" id="kmFrom-${id}" placeholder="50000">

      <label for="kmTo-${id}">Kilometers (to)</label>
      <input type="number" id="kmTo-${id}" placeholder="300000">
      <div class="formBtnContainer">
        <button id="searchBtn-${id}" class="formBtn">Search     </button>
        <button id="saveBtn-${id}"   class="formBtn">Save Querry</button>
      </div>
      <div id="loader-${id}" class="loader"></div>
    </div>

    
 
      <div id="resultSection-${id}"  class="resultSection">
      <table id="resultTable-${id}" class="resultTable" border="1" >
        <caption style="caption-side: top; font-weight: bold; font-size: 1.2rem; padding: 0.5rem;">
          Results:
        </caption>
        <thead>
          <tr>
            <th>Total Cars</th>
            <th>Passed</th>
            <th>Failed</th>
          </tr>
        </thead>
        <tbody id="resultBody-${id}"></tbody>
      </table>

      <canvas id="resultChart-${id}" class="resultChart" ></canvas>
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
		}catch (err) {
			console.error(err);
			modelSelect.innerHTML = '<option disabled>Error loading models</option>';
		}
  });
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
function setupsaveQuery(blockId){
  const btn = document.getElementById(`saveBtn-${blockId}`);
  if(!btn){
    console.error("Button with id 'saveBtn-1' not found");
    return;
  }
  btn.addEventListener('click', async () => {
    console.log("klik na tipko save querry", blockId);

    const prefix = (id) => document.getElementById(`${id}-${blockId}`);

    const make = prefix('make').value;
    const model = prefix('model').value;
    const fuelType = prefix('fuelType').value;
    const yearFrom = prefix('yearFrom').value;
    const yearTo = prefix('yearTo').value;
    const kmFrom = prefix('kmFrom').value;
    const kmTo = prefix('kmTo').value;

    const payload = {
        znamka: make || undefined,
        model: model || undefined,
        fuel: fuelType ? { Diesel: 'D', Petrol: 'P', LPG: 'LPG', Electric: '-' }[fuelType] : undefined,
        start_date: yearFrom ? `${yearFrom}-01-01` : undefined,
        end_date: yearTo ? `${yearTo}-12-31` : undefined,
        min_km: kmFrom || undefined,
        max_km: kmTo || undefined
      };

    try {
        const response = await fetch('/avtogvisn/api/users/saveQuery', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        console.log(response);
        if (response.ok) {
          alert('Query saved successfully!');
        } else {
          alert(`Failed to save query: ${result.message || 'Unknown error'}`);
        }
    }catch (err) {
      console.log("ne dela");
    }
});
}
