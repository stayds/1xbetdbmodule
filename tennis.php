<?php

header('Content-Type:application/json');

include_once 'Module_tennis.php';
$data = new Module_tennis();

//$data->initLoad();
$data->getOutcomes();