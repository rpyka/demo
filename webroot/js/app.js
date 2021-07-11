window.onload = function() {
    document.getElementById("countryInput").focus();
}

function fetchCountries() {
    let user_input = document.getElementById('countryInput').value

    let url = new URL('http://localhost:8765/api/index.php')
    let params = {input: user_input}

    url.search = new URLSearchParams(params).toString();

    showSpinner();

    fetch(url)
        .then(response => response.json())
        .then(data => displayResults(data));
}

function displayResults(results) {
    const container = document.getElementById('response');
    //clear old results
    container.innerHTML = null;

    if ("error" in results) {
        displayError(container, results['error'])
        return;
    }

    //success, now make a table
    const table = document.createElement('table');
    table.className = "table table-striped";
    container.appendChild(table);

    //Add header
    const thead = document.createElement('thead');
    let tr = document.createElement('tr');
    results['headers'].forEach(title => {
        let th = document.createElement('th');
        tr.appendChild(th);
        th.innerHTML = title;
    });
    thead.appendChild(tr);
    table.appendChild(thead);

    //Add data
    const tbody = document.createElement('tbody');
    results['data'].forEach(country => {
        let tr = document.createElement('tr');
        country.forEach(value => {
            let td = document.createElement('td');
            tr.appendChild(td);
            td.innerHTML = value;
        });
        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
}

function displayError(container, error) {
    row = document.createElement('div');
    row.className = "row justify-content-center";
    cell = document.createElement('div');
    cell.className = "col-4 error";
    cell.innerHTML = "<strong>" + error + "</strong>";
    row.appendChild(cell);
    container.appendChild(row);
}

function showSpinner() {
    const container = document.getElementById('response');
    container.innerHTML = null;

    row = document.createElement('div');
    row.className = "row justify-content-center";
    cell = document.createElement('div');
    cell.className = "col-4 spinner-border";
    row.appendChild(cell);
    container.appendChild(row);
}
