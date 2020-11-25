<?php

include_once 'connectDb.php' ;
include_once 'extra.php';

class Dbmodule
{
    public $dbcon;
    public $handle;
    public $dbinsert;
    public $ip;

    public function __construct()
    {
        $this->dbcon = connect();
        $this->handle = curl_init();
    }



    public function initLoad(){
        try{
            $sql = "TRUNCATE TABLE onexbet RESTART IDENTITY CASCADE";
            $this->dbcon->query($sql);

            return $this->getGamesOnline();
        }
        catch (PDOException $ex){
            echo $ex->getMessage();
        }
    }

    public function getGamesOnline(){
        try {
            $url = "https://1xbet.ng/LineFeed/Get1x2_VZip?sports=1&count=1000&lng=en&tf=2200000&mode=4&country=132&partner=159&getEmpty=true";
            curl_setopt_array($this->handle,
                array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                )
            );
            $data = curl_exec($this->handle);
            $decode = json_decode($data, true);
            $games = [];
            $table = 'onexbet';
            //build an array of the data that needs to be inserted
            foreach ($decode['Value'] as $record) {
                if (($record['O1'] != 'Home (Goals)' || $record['O2'] != 'Away (Goals)' ) && ($record['O1'] != 'Home (Special bets)' || $record['O2'] != 'Away (Special bets)' ) && !empty($record['E'])) {

                    $games[] = [
                        "matchid" => $record['I'],
                        "sport" => strtolower($record['SN']),
                        "kind" => $record['KI'],
                        "league" => $record['L'],
                        "hometeam" => $record['O1'],
                        "awayteam" => $record['O2'],
                        "datestring" => $record['S'],

                    ];

                }else {
                    continue;
                }
            }

            //passing data to the method that inserts the batch into the db
            $data = insertToDb($table,$games);
            if ($data){
                curl_close($this->handle);
                return "Avaliable Games have been inserted";
            }


        }
        catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function getOutcomes(){
        ini_set('max_execution_time', 7200);
        $match = $this->matchId();
        $bettype = $this->bettypes();

        foreach($bettype as $bet) {

                if (method_exists($this, $bet['standard'])) {
                    $method = $bet['standard'];
                    $this->$method($match, $bet['groupid'], $method);
                } else {
                    continue;
                }


        }


        return 'Data upload complete';
    }

    private function matchId(){
        $sql = "select matchid from onexbet";
        $match = $this->dbcon->query($sql);
        $match_array = [];
        foreach ($match as $row) {
            array_push($match_array,$row['matchid']);
        }
        return $match_array;
    }

    private function bettypes(){
        $sql = "select groupid,standard from bettype";
        $bet = [];
        $result = $this->dbcon->query($sql);
        foreach ($result as $row) {
           $bet[] = [
               'groupid' => $row['groupid'],
               'standard' => $row['standard'],
           ];
        }
        return $bet;
    }

    public function m1x2_ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row, $this->handle,$gid);
            $outcome[$row] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function m1x2_2ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row, $this->handle,$gid);
            $outcome[$row] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function m_1x2($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row) {
            $records = fetchdata($row, $this->handle, $gid);
            $outcome[$row] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function d_chance($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row, $this->handle,$gid);
            $outcome[$row] = json_encode([
                "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function dc_first_half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row, $this->handle,$gid);
            $outcome[$row] = json_encode([
                "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function dc_2half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row, $this->handle,$gid);
            $outcome[$row] = json_encode([
                "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function gn_goal($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row, $this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T'],'param'=>$records[0][1]['P']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T'],'param'=>$records[1][1]['P']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                        "over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                        "over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                        "over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                        "over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                        "over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                        "over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                        "over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                        "over".$records[0][7]['P'] => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                        "over".$records[0][8]['P'] => ['odd' => $records[0][8]['C'],'param'=>$records[0][8]['P'], 'type' => $records[0][8]['T']],
                        "over".$records[0][9]['P'] => ['odd' => $records[0][9]['C'],'param'=>$records[0][9]['P'], 'type' => $records[0][9]['T']],
                        "under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                        "under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                        "under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                        "under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                        "under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                        "under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                        "under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                        "under".$records[1][7]['P'] => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
                        "under".$records[1][8]['P'] => ['odd' => $records[1][8]['C'],'param'=>$records[1][8]['P'], 'type' => $records[1][8]['T']],
                        "under".$records[1][9]['P'] => ['odd' => $records[1][9]['C'],'param'=>$records[1][9]['P'], 'type' => $records[1][9]['T']],
                    ]);
            }
        insertOutcomes($outcome,$standard);
    }

    public function handicap_norm($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function handicap_1half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function handicap_2half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function asain_hcap($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "1:".$records[0][7]['P'] => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                "2:".$records[1][7]['P'] => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function ahcap_1half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "1:".$records[0][7]['P'] => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                "2:".$records[1][7]['P'] => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function ahcap_2half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "1:".$records[0][7]['P'] => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                "2:".$records[1][7]['P'] => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function total_1($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                        "1:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                        "1:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                        "1:over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                        "1:over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                        "1:over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                        "1:over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                        "1:over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                        "1:under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                        "1:under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                        "1:under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                        "1:under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                        "1:under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                        "1:under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                        "1:under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],

                    ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function ou_home_ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "1:under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "1:under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "1:under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "1:under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "1:under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "1:under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "1:under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],

            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function ov_home_2ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "1:over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "1:over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "1:over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "1:under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "1:under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "1:under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "1:under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "1:under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "1:under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "1:under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],

            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function total_2($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                        "2:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                        "2:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                        "2:over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                        "2:over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                        "2:over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                        "2:over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                        "2:over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                        "2:under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                        "2:under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                        "2:under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                        "2:under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                        "2:under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                        "2:under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                        "2:under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],

                    ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function ov_away_ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "2:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "2:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "2:over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "2:over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "2:over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "2:over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "2:over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "2:under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],

            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function ov_away_2ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "2:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "2:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "2:over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "2:over".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "2:over".$records[0][4]['P'] => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                "2:over".$records[0][5]['P'] => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                "2:over".$records[0][6]['P'] => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                "2:under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:under".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                "2:under".$records[1][4]['P'] => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                "2:under".$records[1][5]['P'] => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                "2:under".$records[1][6]['P'] => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],

            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function a_highest_score($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1h" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "e" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                "2h" => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function hf_time($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1/1" => ['odd' => $records[0][0]['C'],'type' => $records[0][0]['T']],
                "1/x" => ['odd' => $records[0][1]['C'],'type' => $records[0][1]['T']],
                "1/2" => ['odd' => $records[0][2]['C'],'type' => $records[0][2]['T']],
                "x/1" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
                "x/x" => ['odd' => $records[1][1]['C'],'type' => $records[1][1]['T']],
                "x/2" => ['odd' => $records[1][2]['C'],'type' => $records[1][2]['T']],
                "2/1" => ['odd' => $records[2][0]['C'],'type' => $records[2][0]['T']],
                "2/x" => ['odd' => $records[2][1]['C'],'type' => $records[2][1]['T']],
                "2/2" => ['odd' => $records[2][2]['C'],'type' => $records[2][2]['T']],
                "1/2/x" => ['odd' => $records[2][3]['C'],'type' => $records[2][3]['T']],
                "2/1/x" => ['odd' => $records[2][4]['C'],'type' => $records[2][4]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function odd_even($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }
    public function odd_even_ht($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }
    public function even_odd_2half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function h_score_half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1h" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "e" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2h" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function h_wins_eith($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function a_wins_eith($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function handicap($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);

            $outcome[$row] = json_encode([
                        ($records[0][0]['P'] < 0) ? "0:".($records[0][0]['P']/-1) : $records[0][0]['P'].":0" => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                        ($records[0][1]['P'] < 0) ? "0:".($records[0][1]['P']/-1) : $records[0][1]['P'].":0" => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                        ($records[0][2]['P'] < 0) ? "0:".($records[0][2]['P']/-1) : $records[0][2]['P'].":0" => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                        ($records[0][3]['P'] < 0) ? "0:".($records[0][3]['P']/-1) : $records[0][3]['P'].":0" => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                        ($records[0][4]['P'] < 0) ? "0:".($records[0][4]['P']/-1) : $records[0][4]['P'].":0" => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                        ($records[0][5]['P'] < 0) ? "0:".($records[0][5]['P']/-1) : $records[0][5]['P'].":0" => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                        ($records[0][6]['P'] < 0) ? "0:".($records[0][6]['P']/-1) : $records[0][6]['P'].":0" => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                        ($records[1][0]['P'] < 0) ? "0:".($records[1][0]['P']/-1) : $records[1][0]['P'].":0" => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                        ($records[1][1]['P'] < 0) ? "0:".($records[1][1]['P']/-1) : $records[1][1]['P'].":0" => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                        ($records[1][2]['P'] < 0) ? "0:".($records[1][2]['P']/-1) : $records[1][2]['P'].":0" => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                        ($records[1][3]['P'] < 0) ? "0:".($records[1][3]['P']/-1) : $records[1][3]['P'].":0" => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                        ($records[1][4]['P'] < 0) ? "0:".($records[1][4]['P']/-1) : $records[1][4]['P'].":0" => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                        ($records[1][5]['P'] < 0) ? "0:".($records[1][5]['P']/-1) : $records[1][5]['P'].":0" => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                        ($records[1][6]['P'] < 0) ? "0:".($records[1][6]['P']/-1) : $records[1][6]['P'].":0" => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                        ($records[2][0]['P'] < 0) ? "0:".($records[2][0]['P']/-1) : $records[2][0]['P'].":0" => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                        ($records[2][1]['P'] < 0) ? "0:".($records[2][1]['P']/-1) : $records[2][1]['P'].":0" => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                        ($records[2][2]['P'] < 0) ? "0:".($records[2][2]['P']/-1) : $records[2][2]['P'].":0" => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                        ($records[2][3]['P'] < 0) ? "0:".($records[2][3]['P']/-1) : $records[2][3]['P'].":0" => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                        ($records[2][4]['P'] < 0) ? "0:".($records[2][4]['P']/-1) : $records[2][4]['P'].":0" => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                        ($records[2][5]['P'] < 0) ? "0:".($records[2][5]['P']/-1) : $records[2][5]['P'].":0" => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                        ($records[2][6]['P'] < 0) ? "0:".($records[2][6]['P']/-1) : $records[2][6]['P'].":0" => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
                    ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function handicap_half($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                ($records[0][0]['P'] < 0) ? "0:".($records[0][0]['P']/-1) : $records[0][0]['P'].":0" => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                ($records[0][1]['P'] < 0) ? "0:".($records[0][1]['P']/-1) : $records[0][1]['P'].":0" => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                ($records[0][2]['P'] < 0) ? "0:".($records[0][2]['P']/-1) : $records[0][2]['P'].":0" => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                ($records[0][3]['P'] < 0) ? "0:".($records[0][3]['P']/-1) : $records[0][3]['P'].":0" => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                ($records[0][4]['P'] < 0) ? "0:".($records[0][4]['P']/-1) : $records[0][4]['P'].":0" => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                ($records[0][5]['P'] < 0) ? "0:".($records[0][5]['P']/-1) : $records[0][5]['P'].":0" => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                ($records[0][6]['P'] < 0) ? "0:".($records[0][6]['P']/-1) : $records[0][6]['P'].":0" => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                ($records[1][0]['P'] < 0) ? "0:".($records[1][0]['P']/-1) : $records[1][0]['P'].":0" => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                ($records[1][1]['P'] < 0) ? "0:".($records[1][1]['P']/-1) : $records[1][1]['P'].":0" => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                ($records[1][2]['P'] < 0) ? "0:".($records[1][2]['P']/-1) : $records[1][2]['P'].":0" => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                ($records[1][3]['P'] < 0) ? "0:".($records[1][3]['P']/-1) : $records[1][3]['P'].":0" => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                ($records[1][4]['P'] < 0) ? "0:".($records[1][4]['P']/-1) : $records[1][4]['P'].":0" => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                ($records[1][5]['P'] < 0) ? "0:".($records[1][5]['P']/-1) : $records[1][5]['P'].":0" => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                ($records[1][6]['P'] < 0) ? "0:".($records[1][6]['P']/-1) : $records[1][6]['P'].":0" => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                ($records[2][0]['P'] < 0) ? "0:".($records[2][0]['P']/-1) : $records[2][0]['P'].":0" => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                ($records[2][1]['P'] < 0) ? "0:".($records[2][1]['P']/-1) : $records[2][1]['P'].":0" => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                ($records[2][2]['P'] < 0) ? "0:".($records[2][2]['P']/-1) : $records[2][2]['P'].":0" => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                ($records[2][3]['P'] < 0) ? "0:".($records[2][3]['P']/-1) : $records[2][3]['P'].":0" => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                ($records[2][4]['P'] < 0) ? "0:".($records[2][4]['P']/-1) : $records[2][4]['P'].":0" => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                ($records[2][5]['P'] < 0) ? "0:".($records[2][5]['P']/-1) : $records[2][5]['P'].":0" => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                ($records[2][6]['P'] < 0) ? "0:".($records[2][6]['P']/-1) : $records[2][6]['P'].":0" => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function handicap_2ht($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                ($records[0][0]['P'] < 0) ? "0:".($records[0][0]['P']/-1) : $records[0][0]['P'].":0" => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                ($records[0][1]['P'] < 0) ? "0:".($records[0][1]['P']/-1) : $records[0][1]['P'].":0" => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                ($records[0][2]['P'] < 0) ? "0:".($records[0][2]['P']/-1) : $records[0][2]['P'].":0" => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                ($records[0][3]['P'] < 0) ? "0:".($records[0][3]['P']/-1) : $records[0][3]['P'].":0" => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                ($records[0][4]['P'] < 0) ? "0:".($records[0][4]['P']/-1) : $records[0][4]['P'].":0" => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                ($records[0][5]['P'] < 0) ? "0:".($records[0][5]['P']/-1) : $records[0][5]['P'].":0" => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                ($records[0][6]['P'] < 0) ? "0:".($records[0][6]['P']/-1) : $records[0][6]['P'].":0" => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                ($records[1][0]['P'] < 0) ? "0:".($records[1][0]['P']/-1) : $records[1][0]['P'].":0" => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                ($records[1][1]['P'] < 0) ? "0:".($records[1][1]['P']/-1) : $records[1][1]['P'].":0" => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                ($records[1][2]['P'] < 0) ? "0:".($records[1][2]['P']/-1) : $records[1][2]['P'].":0" => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                ($records[1][3]['P'] < 0) ? "0:".($records[1][3]['P']/-1) : $records[1][3]['P'].":0" => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                ($records[1][4]['P'] < 0) ? "0:".($records[1][4]['P']/-1) : $records[1][4]['P'].":0" => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                ($records[1][5]['P'] < 0) ? "0:".($records[1][5]['P']/-1) : $records[1][5]['P'].":0" => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                ($records[1][6]['P'] < 0) ? "0:".($records[1][6]['P']/-1) : $records[1][6]['P'].":0" => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                ($records[2][0]['P'] < 0) ? "0:".($records[2][0]['P']/-1) : $records[2][0]['P'].":0" => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                ($records[2][1]['P'] < 0) ? "0:".($records[2][1]['P']/-1) : $records[2][1]['P'].":0" => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                ($records[2][2]['P'] < 0) ? "0:".($records[2][2]['P']/-1) : $records[2][2]['P'].":0" => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                ($records[2][3]['P'] < 0) ? "0:".($records[2][3]['P']/-1) : $records[2][3]['P'].":0" => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                ($records[2][4]['P'] < 0) ? "0:".($records[2][4]['P']/-1) : $records[2][4]['P'].":0" => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                ($records[2][5]['P'] < 0) ? "0:".($records[2][5]['P']/-1) : $records[2][5]['P'].":0" => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                ($records[2][6]['P'] < 0) ? "0:".($records[2][6]['P']/-1) : $records[2][6]['P'].":0" => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function last_goal($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "2" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "none" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function h_clean_sheet($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function h_highest_score($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "1h" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "e" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                "2h" => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function h_win_nil($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function a_win_nil($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function first_goal_1x2($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "hgoal:1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "hgoal:2" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                "hgoal:x" => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']],
                "agoal:1" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "agoal:2" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
                "agoal:x" => ['odd' => $records[1][2]['C'], 'type' => $records[1][2]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function a_win_both_half($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function h_win_both_half($match, $gid, $standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function c_score_17($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                        correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                        correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                        correctorder($records[0][2]['P']) => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                        correctorder($records[0][3]['P']) => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                        correctorder($records[0][4]['P']) => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                        correctorder($records[0][5]['P']) => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                        correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                        correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                        correctorder($records[1][2]['P']) => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                        correctorder($records[1][3]['P']) => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                        correctorder($records[1][4]['P']) => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                        correctorder($records[2][0]['P']) => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                        correctorder($records[2][1]['P']) => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                        correctorder($records[2][2]['P']) => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                        correctorder($records[2][3]['P']) => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                        correctorder($records[2][4]['P']) => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                        correctorder($records[2][5]['P']) => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],

                    ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function correct_score_17_1half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdatahalf($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                        correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                        correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                        correctorder($records[0][2]['P']) => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                        correctorder($records[0][3]['P']) => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                        correctorder($records[0][4]['P']) => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                        correctorder($records[0][5]['P']) => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                        correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                        correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                        correctorder($records[1][2]['P']) => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                        correctorder($records[1][3]['P']) => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                        correctorder($records[1][4]['P']) => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                        correctorder($records[2][0]['P']) => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                        correctorder($records[2][1]['P']) => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                        correctorder($records[2][2]['P']) => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                        correctorder($records[2][3]['P']) => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                        correctorder($records[2][4]['P']) => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                        correctorder($records[2][5]['P']) => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],

                    ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function correct_score_17_2half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetchdata2half($row,$this->handle,$gid);
            $outcome[$row] = json_encode([
                correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                correctorder($records[0][2]['P']) => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                correctorder($records[0][3]['P']) => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                correctorder($records[0][4]['P']) => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                correctorder($records[0][5]['P']) => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                correctorder($records[1][2]['P']) => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                correctorder($records[1][3]['P']) => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                correctorder($records[1][4]['P']) => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                correctorder($records[2][0]['P']) => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                correctorder($records[2][1]['P']) => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                correctorder($records[2][2]['P']) => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                correctorder($records[2][3]['P']) => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                correctorder($records[2][4]['P']) => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                correctorder($records[2][5]['P']) => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],

            ]);
        }
        insertOutcomes($outcome,$standard);
    }

    public function c_score($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row) {
            $records = fetchdata($row,$this->handle,$gid);
            foreach ($records as $list){
                foreach ($list as $rec){
                    $outcome[$row] = json_encode([
                        correctorder($rec['P']) => ['odd' => $rec['C'], 'param' => $rec['P'], 'type' => $rec['T']],
                    ]);
                }
            }
        }
        insertOutcomes($outcome,$standard);
    }

    public function c_score_half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row) {
            $records = fetchdatahalf($row,$this->handle,$gid);
            foreach ($records as $list){
                foreach ($list as $rec){
                    $outcome[$row] = json_encode([
                        correctorder($rec['P']) => ['odd' => $rec['C'], 'param' => $rec['P'], 'type' => $rec['T']],
                    ]);
                }
            }
        }
        insertOutcomes($outcome,$standard);
    }

    public function c_score_2half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row) {
            $records = fetchdata2half($row,$this->handle,$gid);
            foreach ($records as $list){
                foreach ($list as $rec){
                    $outcome[$row] = json_encode([
                        correctorder($rec['P']) => ['odd' => $rec['C'], 'param' => $rec['P'], 'type' => $rec['T']],
                    ]);
                }
            }
        }
        insertOutcomes($outcome,$standard);
    }

//    public function h_score_home($match, $gid, $standard){
//
//    }
//
//    public function a_score_away($match, $gid, $standard){
//
//    }
//
//    public function home_oddeven($match, $gid, $standard){
//
//    }
//
//    public function away_oddeven($match, $gid, $standard){
//
//    }
//
//    public function exact_goal($match, $gid, $standard){
//
//    }


}
