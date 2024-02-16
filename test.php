<?php


$dateString = "2024-01-18 15:03:25";

// Create a DateTime object
$date = new DateTime($dateString);

// Get Unix timestamp
$timestamp = $date->getTimestamp();

echo $timestamp;
echo "\n";
echo strtotime($dateString);

echo "\n";

// $input = array(5,2,1,6);
// $key = array_rand($input);
// echo $input;


var_dump($category);
?>