<?php

    include_once 'connectDb.php';
    $connector = connect();

    $Request = json_decode(@file_get_contents('php://input'),true);
    //get all games passed from interpreter
    $codegames = getData($Request);

    //load all avaliable bettypes
    $gametypes = stored_game_types($connector);
    $checked = checktypes($gametypes,$codegames[0]);
    $dbgames = get_games($checked,$connector);
    generate_code($dbgames);

    function getData($data){
        $gamex = [];
        $homebookmaker = '';
        foreach ($data as $key=>$list){
            $homebookmaker = $key;
             foreach ($list as $game){
                 $gamex[] = [$game];
             }
        }
        $games = $gamex[0];
        return $games;
    }

    function stored_game_types($connect){
        $types = [];
        $sql = $connect->query('Select standard From bettype');
        foreach ($sql as $row) {
            array_push($types,$row['standard']);
        }
        return $types;
    }

    function checktypes($types, $games){
        $new_games = [];

        foreach ($games as $list){
        if(in_array($list['type'],$types)){
            $list['typemsg'] = true;
            $new_games[] = [
                $list
            ];
        }else{
            $list['typemsg'] = false;
            $new_games[] = [
                $list
            ];
        }
    }
        return $new_games;
    }

    function get_games($data,$connect){

        try {
            $final_detail = [];
            foreach ($data as $games){

                if ($games[0]['typemsg'] == true) {
                    $bet = $games[0]['type'];
                    $away = $games[0]['away'];
                    $home = $games[0]['home'];

                    $sql = "SELECT matchid, kind, $bet FROM onexbet WHERE awayteam = '{$away}' AND hometeam = '{$home}'";

                    $result = $connect->query($sql);
                    if ($result->rowCount() > 0){
                        foreach ($result as $list) {

                            $outcome = json_decode($list[$bet], true);

                            $final_detail[] = [
                                'GameId' => intval($list['matchid']),
                                'Kind' => intval($list['kind']),
                                'Param'=>($outcome[$games[0]['outcome']]['param'])? floatval($outcome[$games[0]['outcome']]['param']) : 0,
                                'Coef' => floatval($outcome[$games[0]['outcome']]['odd']),
                                'Type' => intval($outcome[$games[0]['outcome']]['type'])
                            ];
                            break;
                        }
                    }
                }
            }

             return $final_detail;

        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }

    function generate_code($detail){
       $games = json_encode($detail);
       $url = "https://1xbet.ng/LiveUtil/SaveCoupon";
       $handle = curl_init();
        curl_setopt_array($handle,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => '{
                        "Live":false,
                        "Events":'.$games.',
                        "Summ":1,
                        "Lng":"en",
                        "Vid":0,
                        "CfView":0,
                        "notWait":true,
                        "CheckCf":2,
                        "partner":159   
                        }',
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json, text/plain, */*",
                    "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:80.0) Gecko/20100101 Firefox/80.0",
                    "Accept-Language: en-US,en;q=0.5",
                    "X-Requested-With: XMLHttpRequest",
                    "Content-Type: application/json;charset=utf-8"
                ),
            )
        );
        $data = curl_exec($handle);
        $decode = json_decode($data, true);
        print_r($decode);
    }