<?php
require_once('fwmon.class.php');

$fwmon = new fwmon();

if ($_GET['q'] === 'resources') {
	$fwmon->buildJSONResources();
	echo $fwmon->json;
} else {
	$fwmon->buildJSONTable($_GET['q']);
	echo $fwmon->json;
}