<?php

   header('Content-Type:application/json');

   include_once 'Dbmodule.php';
   $data = new Dbmodule();

    $data->initLoad();
    //$data->getOutcomes();
