//Write your javascript here, or roll your own. It's up to you.
//Make your ajax call to http://localhost:8765/api/index.php here

const attributes = [
    {attr:'name', title:'Country Name', type:'text'},
    {attr:'alpha2Code', title:'2-Letter Code', type:'text'},
    {attr:'alpha3Code', title:'3-Letter Code', type:'text'},
    {attr:'flag', title:'Flag', type:'image'},
    {attr:'region', title:'Region', type:'text'},
    {attr:'subregion', title:'Subregion', type:'text'},
    {attr:'population', title:'Population', type:'number'},
    {attr:'languages', title:'Language(s)', type:'other', function:'getLanguages'}
]

function fetchCountries() {
    var user_input = document.getElementById('countryInput').value

    var url = new URL('http://localhost:8765/api/index.php')
    var params = {input: user_input}

    url.search = new URLSearchParams(params).toString();

    fetch(url)
        .then(response => response.json())
        .then(data => displayResults(data));
}

function displayResults(results) {
    const table = document.getElementById('response');
    //clear old results
    table.innerHTML = null;

    //Add a header
    tr = document.createElement('tr');
    attributes.forEach(attr => {
        th = document.createElement('th');
        tr.appendChild(th);
        th.innerHTML = attr['title'];
    });
    table.appendChild(tr);

    //add the data
    results.forEach(country => {
        console.log(country)
        tr = document.createElement('tr');
        attributes.forEach(attr => {
            td = document.createElement('td');
            tr.appendChild(td);
            switch (attr['type']) {
                case 'text':
                    td.innerHTML = country[attr['attr']];
                    break;
                case 'number':
                    td.innerHTML = country[attr['attr']].toLocaleString("en-US");
                    break;
                case 'image':
                    td.innerHTML = '<img src=' + country[attr['attr']] + ' width=50px>';
                    break;
                case 'other':
                    td.innerHTML = window[attr['function']](country[attr['attr']]);
                    break;
                default:
                    td.innerHTML = "error";
                    break;
            }

        });
        table.appendChild(tr);
    });
}

function getLanguages() {
    return 'hello';
}


