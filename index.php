<?php

function getJsonFromUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $results = curl_exec($ch);
    $json = json_decode($results, true);
    curl_close($ch);

    return $json;
}

function getPrices($years) {
    $prices = [];
    $pricesTmp = [];

    for ($i = 0; $i < $years; $i++) {
        $today = date('Y-m-d', strtotime('-' . $i . ' years'));
        $from = date('Y-m-d', strtotime('-' . $i-1 . ' years'));
        $url = 'http://api.nbp.pl/api/cenyzlota/' . $from . '/' . $today . '?format=json';

        array_unshift($pricesTmp, getJsonFromUrl($url));
    }

    foreach ($pricesTmp as $result) {
        foreach ($result as $index => $price) {
            array_push($prices, $price);
        }
    }

    return $prices;
}

function showMessage($details) {
    $diff = $details['maxPrice'] - $details['minPrice'];
    $earn = $diff * $details['amount'] - $details['amount'];

    echo 'Years: ' . $details['years'];
    echo '<br />';
    echo 'Amount of investmet: ' . $details['amount'] . ' PLN';
    echo '<br />';
    echo '<br />';
    echo 'The best date to buy: ' . $details['minDate'];
    echo ' - price was ' . $details['minPrice'] . ' PLN';
    echo '<br />';
    echo 'The best date to sell: ' . $details['maxDate'];
    echo ' - price was ' . $details['maxPrice'] . ' PLN';
    echo '<br />';
    echo 'Prices difference: ' . $diff . ' PLN';
    echo '<br />';
    echo '<br />';
    echo 'If you invested ' . $details['amount'] . ' PLN on ' . $details['minDate'] . ' and sold ' . 
        ' on ' . $details['maxDate'] . ' you could earn ' . $earn . ' PLN';
}

function findBestInvestmentDates($years, $amount) {
    $allPrices = getPrices($years);
    
    $prices = [];
    $dates = [];

    foreach ($allPrices as $res) {
        array_push($prices, $res['cena']);
        array_push($dates, $res['data']);
    }

    /** Find minimum price and index */
    $min = min($prices);
    $minIndex = array_search($min, $prices);
    $minDate = $dates[$minIndex];

    /** Slice prices by minimum price index */
    $slicedPrices = array_slice($prices, $minIndex);

    /** Find max price and index */
    $max = max($slicedPrices);
    $maxIndex = array_search($max, $prices);
    $maxDate = $dates[$maxIndex];
    
    /** Create details */
    $investDetails = [
        'years' => $years,
        'amount' => $amount,
        'minPrice' => $min,
        'minDate' => $minDate,
        'maxPrice' => $max,
        'maxDate' => $maxDate
    ];

    showMessage($investDetails);
}

$years = 5;
$investmentAmount = 600000;
findBestInvestmentDates($years, $investmentAmount);