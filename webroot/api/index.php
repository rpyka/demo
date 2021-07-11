<?php
header('Content-Type: application/json');

$config = [
    ['attr'=>'name', 'title'=>'Country Name', 'type'=>'text'],
    ['attr'=>'alpha2Code', 'title'=>'2-Letter Code', 'type'=>'text'],
    ['attr'=>'alpha3Code', 'title'=>'3-Letter Code', 'type'=>'text'],
    ['attr'=>'flag', 'title'=>'Flag', 'type'=>'image', 'alt_text'=>'name'],
    ['attr'=>'region', 'title'=>'Region', 'type'=>'text'],
    ['attr'=>'subregion', 'title'=>'Subregion', 'type'=>'text'],
    ['attr'=>'population', 'title'=>'Population', 'type'=>'number'],
    ['attr'=>'languages', 'title'=>'Language(s)', 'type'=>'other', 'function'=>'get_languages']
];

$input = filter_var($_GET['input'], FILTER_SANITIZE_SPECIAL_CHARS); //Just make sure nothing crazy comes through

fetch_countries($input, $config);

/**
 * @param $input
 * @param $config
 */
function fetch_countries($input, $config)
{
    $code_results = null;
    switch (strlen($input)) {
        case 0:
            send_error('Please enter some value to search');
            return;
        case 2: //add 2-letter code results
        case 3: //add 3-letter code results
            if (get_http_response_code('https://restcountries.eu/rest/v2/alpha/' . $input) != "200") {
                break;
            } else {
                $code_results = file_get_contents('https://restcountries.eu/rest/v2/alpha/' . $input);
            }
            break;
    }

    if (get_http_response_code('https://restcountries.eu/rest/v2/name/' . $input) != "200") {
        $countries = [];
    } else {
        $name_results = file_get_contents('https://restcountries.eu/rest/v2/name/' . $input);
        $countries = json_decode($name_results, true);
    }

    //combine results if needed, remove duplicates
    if ($code_results !== null) {
        $code_results = json_decode($code_results, true);
        $countries = array_unique(array_merge($countries, [$code_results]), SORT_REGULAR);
    }

    //If no results, return error
    if (count($countries) === 0) {
        send_error('No results found');
        return;
    }

    //sort by population in descending order
    usort($countries, function($a, $b) {
        return $b['population'] - $a['population'];
    });

    send_results($countries, $config);
}

/**
 * @param $countries
 * @param $config
 */
function send_results($countries, $config) {
    foreach ($countries as $country) {
        $fields = [];
        foreach ($config as $attr) {

            switch ($attr['type']) {
                case 'text':
                    $fields[] = $country[$attr['attr']];
                    break;
                case 'number':
                    $fields[] = number_format($country[$attr['attr']]);
                    break;
                case 'image':
                    $fields[] = '<img src=' . $country[$attr['attr']] . ' alt="' . $country[$attr['alt_text']] . '" title="' . $country[$attr['alt_text']] . '" width=50px>'; //TODO: maybe make this width more dynamic later?
                    break;
                case 'other':
                    $fields[] = $attr['function']($country[$attr['attr']]);
                    break;
                default:
                    $fields[] = '<strong>Error</strong>';
                    break;
            }
        }
        $rows[] = $fields;
    }

    $headers = [];
    foreach ($config as $attr) {
        $headers[] = $attr['title'];
    }

    $total_countries = count($countries);
    $regions = implode(', ', array_unique(array_map(function ($item) { return $item['region']; }, $countries)));
    $subregions = implode(', ', array_unique(array_map(function ($item) { return $item['subregion']; }, $countries)));
    $summary = [
        ['Total countries', $total_countries],
        ['Regions', $regions],
        ['Subregions', $subregions]
    ];

    echo json_encode(['headers' => $headers, 'data' => $rows, 'summary' => $summary]);
}

/**
 * @param $url
 * @return false|string
 */
function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}

/**
 * @param $error_text
 */
function send_error($error_text) {
    echo json_encode(['error' => $error_text]);
}

/**
 * @param $languages
 * @return string
 */
function get_languages($languages)
{
    $list = array_map(function ($item) { return $item['name']; }, $languages);
    return implode(', ', $list);
}
