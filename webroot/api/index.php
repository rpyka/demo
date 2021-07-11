<?php
header('Content-Type: application/json');

$attributes = [
    ['attr'=>'name', 'title'=>'Country Name', 'type'=>'text'],
    ['attr'=>'alpha2Code', 'title'=>'2-Letter Code', 'type'=>'text'],
    ['attr'=>'alpha3Code', 'title'=>'3-Letter Code', 'type'=>'text'],
    ['attr'=>'flag', 'title'=>'Flag', 'type'=>'image'],
    ['attr'=>'region', 'title'=>'Region', 'type'=>'text'],
    ['attr'=>'subregion', 'title'=>'Subregion', 'type'=>'text'],
    ['attr'=>'population', 'title'=>'Population', 'type'=>'number'],
    ['attr'=>'languages', 'title'=>'Language(s)', 'type'=>'other', 'function'=>'get_languages']
];

fetch_countries();

function fetch_countries()
{
    $input = $_GET['input'];

    $input = filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS); //Just make sure nothing crazy comes through

    $code_results = null;
    $name_results = null;
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

    if ($code_results !== null) {
        $code_results = json_decode($code_results, true);
        $countries = array_merge($countries, [$code_results]);
    }

    if (count($countries) === 0) {
        send_error('No results found');
        return;
    }

    send_results($countries);
}

function send_results($countries) {
    global $attributes;

    foreach ($countries as $country) {
        $fields = [];
        foreach ($attributes as $attr) {

            switch ($attr['type']) {
                case 'text':
                    $fields[] = $country[$attr['attr']];
                    break;
                case 'number':
                    $fields[] = number_format($country[$attr['attr']]);
                    break;
                case 'image':
                    $fields[] = '<img src=' . $country[$attr['attr']] . ' width=50px>';
                    break;
                case 'other':
                    $fields[] = $attr['function']($country[$attr['attr']]); //TODO: add function handling
                    break;
                default:
                    $fields[] = "error";
                    break;
            }
        }
        $rows[] = $fields;
    }

    $headers = [];
    foreach ($attributes as $attr) {
        $headers[] = $attr['title'];
    }

    echo json_encode(['headers' => $headers, 'data' => $rows]);
}

function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}

function send_error($error_text) {
    echo json_encode(['error' => $error_text]);
}

function get_languages($languages)
{
    $list = array_map(function ($item) { return $item['name']; }, $languages);
    return implode(', ', $list);
}
