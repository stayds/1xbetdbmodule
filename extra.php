<?php

function fetchdata($id,$handle,$gid){

    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents=2500&partner=159&marketType=1";
    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
        )
    );
    $data = curl_exec($handle);
    // curl_close($this->handle);
    $decode = json_decode($data, true);
    $datax =  $decode['Value']['GE'];
    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }

}

function fetchdatahalf($id,$handle,$gid){
    $id = intval($id) + 1;
    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents=2500&partner=159&marketType=1";
    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        )
    );
    $data = curl_exec($handle);
    $decode = json_decode($data, true);
    $datax = $decode['Value']['GE'];

    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }
}

function fetchdata2half($id,$handle,$gid){
    $id = intval($id) + 2;
    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents2500&partner=159&marketType=1";
    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        )
    );
    $data = curl_exec($handle);
    $decode = json_decode($data, true);
    $datax = $decode['Value']['GE'];
    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }
}

function fetchcorner($id,$handle,$gid){

    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents2500&partner=159&marketType=1";

    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        )
    );
    $data = curl_exec($handle);
    $decode = json_decode($data, true);
    $datax = $decode['Value']['GE'];

    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }
}

function fetchcorner_half($id,$handle,$gid){
    $id = $id + 1;
    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents2500&partner=159&marketType=1";
    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        )
    );
    $data = curl_exec($handle);
    $decode = json_decode($data, true);
    $datax = $decode['Value']['GE'];
    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }
}
function fetchcorner_2half($id,$handle,$gid){
    $id = $id + 2;
    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents2500&partner=159&marketType=1";
    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        )
    );
    $data = curl_exec($handle);
    $decode = json_decode($data, true);
    $datax = $decode['Value']['GE'];
    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }
}
function fetchcard($id,$handle,$gid){
    $url = "https://1xbet.ng/LineFeed/GetGameZip?id=$id&lng=en&cfview=0&isSubGames=true&GroupEvents=true&allEventsGroupSubGames=true&countevents2500&partner=159&marketType=1";
    curl_setopt_array($handle,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        )
    );
    $data = curl_exec($handle);
    $decode = json_decode($data, true);
    $datax = $decode['Value']['GE'];
    foreach ($datax as $list){
        if($list['G'] == $gid){
            return $list['E'];
        }
    }
}


function correctorder($data=null){
    $ch = explode('.',$data);
    $len = strlen($ch[1]);
    if(is_int($data)){
        return $data.":0";
    }
    elseif ($data == null){
        return "0:0";
    }
    elseif ($len == 2){
        return $ch[0].":".$ch[1]*10;
    }
    elseif($len == 3){
        return $ch[0].":".$ch[1]*1;
    }
    else{
        $num = $data * 1000;
        if($num < 10){
            return "0:".$num;
        }
        else{
            $array_num  = array_map('intval', str_split($num));
            return $array_num[0].":".$array_num[count($array_num)-1];
        }

    }
}

function getLeagues(){
    $leagues = [];
    $url = "https://1xbet.ng/LineFeed/GetChampsZip?sport=1&lng=en&tf=2200000&tz=1&country=132&partner=159&virtualSports=true";
    $handles = curl_init();
    curl_setopt_array($handles,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        )
    );
    $data = curl_exec($handles);
    $decode = json_decode($data, true);

    foreach ($decode['Value'] as $key=>$list){
        $leagues[] = [
            'id'=> $list['LI'],
            'name'=>$l = str_replace('.','',$list['L']),
        ];
    }
    return $leagues;
}

function getGames($id,$league,$table){
    $url = "https://1xbet.ng/LineFeed/GetChampZip?champ=$id&lng=en&tf=2200000&tz=1&country=132&partner=159&virtualSports=true&group=173";
    $games = [];
    $handles = curl_init();
    curl_setopt_array($handles,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        )
    );
    $data = curl_exec($handles);
    $decode = json_decode($data, true);

    foreach ($decode['Value']['G'] as $record) {
        $games[] = [
            "matchid" => $record['CI'],
            "matchi" => $record['I'],
            "sport" => strtolower($decode['Value']['SN']),
            "kind" => $record['KI'],
            "league" => $league,
            "hometeam" => $record['O1'],
            "awayteam" => $record['O2'],
            "datestring" => $record['S'],
            "corner_id" => ($record['SG'][2]['TG'] == "Corners") ? $record['SG'][2]['I'] : null,
            "card_id" => ($record['SG'][9]['TG'] == "Cards") ? $record['SG'][9]['I'] : null
            ];
    }

    insertToDb($table,$games);
}