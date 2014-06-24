<?php
$website = $_GET["website"];
$website = (int)$website;
$sandbox = $_GET["sandbox"];
$sandbox = (int)$sandbox;
$category = 0;

if ($sandbox == 1)
	$list_categories = "https://test-payment.hipay.com/order/list-categories/id/" . $website;
else
	$list_categories = "https://payment.hipay.com/order/list-categories/id/" . $website;

$result = file_get_contents($list_categories);
try {
	$data = simplexml_load_string($result);	
	$categories = $data->categoriesList->category;
	if (!is_null($categories)){
		foreach ($categories as $xcategory) {
			$category = $xcategory["id"];
		}
	}	
	echo $category;
	return;
} catch (Exception $e) {
	echo "0";
	return;
}