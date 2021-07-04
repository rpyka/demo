<?php
/**
 * This is a template php file for your countries search.
 * Use as you will, or start over. It's up to you.
 */
header('Content-Type: application/json');

$input = $_GET['input'];
$response = file_get_contents("https://restcountries.eu/rest/v2/name/" . $input);

echo $response;


//echo json_encode(['data' => ['Your data']]);
