<?php

header('Content-Type:application/json');

include 'Module_basketball.php';
$data = new Module_basketball();
$data->initLoad();
$data->getOutcomes();