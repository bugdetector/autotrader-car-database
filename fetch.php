<?php

$data = json_decode(getRequestResult(getPostData()));
$mainData = $data->data->search->adverts->facets;
$makes = $mainData[1]->values;

$makeList = [];
foreach ($makes as $make) {
    $makeList[mb_convert_case($make->name, MB_CASE_TITLE)] = [];
}

foreach($makeList as $make => &$models){
    $data = json_decode(getRequestResult(getPostData($make)));
    $mainData = $data->data->search->adverts->facets;
    $modelsData = $mainData[2]->values;
    foreach($modelsData as $modelData){
        $models[] = $modelData->name;
    }
}

file_put_contents("makelist.json", json_encode($makeList));

function getRequestResult($postdata)
{
    $apiUrl = "https://www.autotrader.co.uk/at-graphql";

    $opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/json',
        'content' => $postdata
    ));

    $context  = stream_context_create($opts);

    return file_get_contents($apiUrl, false, $context);
}

function getPostData($makeName = null)
{
    return '{"operationName":"SearchFormFacetsQuery",
        "variables":{"advertQuery":{
            "advertisingLocations":["at_cars"],
            "advertClassification":["standard"],
            ' . ($makeName ? '"make":["'. $makeName .'"],' : "" ) . '
            "homeDeliveryAdverts":null},
            "facets":["distance","make","model","min_price","max_price"]},
            "query":"query SearchFormFacetsQuery($advertQuery: AdvertQuery!, $facets: [SearchFacetName]) {\n  search {\n    adverts(advertQuery: $advertQuery) {\n      advertList {\n        totalElements\n        __typename\n      }\n      facets(facets: $facets) {\n        name\n        values {\n          name\n          value\n          count\n          selected\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}\n"}';
}
