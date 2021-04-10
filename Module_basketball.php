<?php

include_once 'connectDb.php';
include_once 'extra.php';

class Module_basketball
{
    public $dbcon;
    public $handle;

    public function __construct(){
        $this->dbcon = connect();
        $this->handle = curl_init();
    }

    public function initLoad(){
        try{
            $sql = "TRUNCATE TABLE onexbet_basketball RESTART IDENTITY CASCADE";
            $this->dbcon->query($sql);

            return $this->getGamesOnline();
        }
        catch (PDOException $ex){
            echo $ex->getMessage();
        }
    }

    public function getGamesOnline(){
        $leagues = getLeagues('basketball');
        $table = 'onexbet_basketball';
        foreach ($leagues as $games){
            getGames($games['id'],$games['name'], $table);
        }
        return "Avaliable Games have been inserted";
    }

    public function getOutcomes(){
        ini_set('max_execution_time', 100000);
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
        $sql = "select matchid,fhid,shid,fqid,sqid,tqid,ftqid from onexbet_basketball";
        $match = $this->dbcon->query($sql);
        $match_array = [];
        foreach ($match as $row) {
            $data = ["matchid"=>$row['matchid'],"fhid"=>$row['fhid'],
                     "shid"=>$row['shid'],"fqid"=>$row['fqid'],
                     "sqid"=>$row['sqid'],"tqid"=>$row['tqid'],
                     "ftqid"=>$row['ftqid']
            ];
            array_push($match_array,$data);
        }

        return $match_array;
    }

    private function bettypes(){
        $sql = "select groupid,standard from bettype_basketball";
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

    public function m_1x2($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row) {
              $records = fetch_others($row['matchid'], $this->handle, $gid);
              $outcome[$row['matchid']] = json_encode([
                  "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function fh_1x2($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetch_others($row['fhid'], $this->handle,$gid);
             $outcome[$row['fhid']] = json_encode([
                 "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }

        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fhid');
    }
    public function sh_1x2($match,$gid,$standard){
         $outcome = [];

         foreach ($match as $row){
             $records = fetch_others($row['shid'], $this->handle,$gid);

             $outcome[$row['shid']] = json_encode([
                 "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }
    public function first_quarter_1x2($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fqid'], $this->handle,$gid);
            $outcome[$row['fqid']] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fqid');
    }
    public function second_quarter_1x2($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['sqid'], $this->handle,$gid);
            $outcome[$row['sqid']] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','sqid');
    }
    public function third_quarter_1x2($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['tqid'], $this->handle,$gid);
            $outcome[$row['tqid']] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','tqid');
    }
    public function fourth_quarter_1x2($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['ftqid'], $this->handle,$gid);
            $outcome[$row['ftqid']] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
            ]);
        }

        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','ftqid');
    }
    public function winner($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'], $this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "2" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']]
            ]);
        }
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }

    public function handicap_norm($match,$gid,$standard){
        $outcome = [];
         foreach ($match as $row){
             $records = fetch_others($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function first_quarter_handicap($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fqid'],$this->handle,$gid);
            $outcome[$row['fqid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fqid');

    }
    public function second_quarter_handicap($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['sqid'],$this->handle,$gid);
            $outcome[$row['sqid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','sqid');
    }
    public function over_under($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetch_others($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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

         insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function fh_over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fhid'],$this->handle,$gid);
            $outcome[$row['fhid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fhid');
    }
    public function sh_over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['shid'],$this->handle,$gid);
            $outcome[$row['shid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }
    public function first_quarter_over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fqid'],$this->handle,$gid);
            $outcome[$row['fqid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fqid');
    }

    public function fh_home_over_under($match,$gid,$standard){
        $outcome = [];
         foreach ($match as $row){
             $records = fetch_others($row['fhid'],$this->handle,$gid);
             $outcome[$row['fhid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fhid');
    }
    public function fh_away_over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fhid'],$this->handle,$gid);
            $outcome[$row['fhid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fhid');
    }
    public function sh_home_over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['shid'],$this->handle,$gid);
            $outcome[$row['shid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }
    public function sh_away_over_under($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['shid'],$this->handle,$gid);
            $outcome[$row['shid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }
    public function home_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
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
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function first_quarter_home_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fqid'],$this->handle,$gid);
            $outcome[$row['fqid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fqid');
    }
    public function second_quarter_home_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['sqid'],$this->handle,$gid);
            $outcome[$row['sqid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','sqid');
    }
    public function away_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
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
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function first_quarter_away_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['shid'],$this->handle,$gid);
            $outcome[$row['shid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }
    public function second_quarter_away_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['shid'],$this->handle,$gid);
            $outcome[$row['shid']] = json_encode([
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
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }

    public function home_odd_even($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
             $records = fetch_others($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
             ]);
        }
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function away_odd_even($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function odd_even($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function fh_odd_even($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['fhid'],$this->handle,$gid);
              $outcome[$row['fhid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fhid');
    }
    public function sh_odd_even($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['shid'],$this->handle,$gid);
              $outcome[$row['shid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomesOthers($outcome,$standard,'onexbet_basketball','shid');
    }
    public function first_quarter_odd_even($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['fqid'],$this->handle,$gid);
              $outcome[$row['fqid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fqid');
    }
    public function second_quarter_odd_even($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['sqid'],$this->handle,$gid);
              $outcome[$row['sqid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomesOthers($outcome,$standard,'onexbet_basketball','sqid');
    }
    public function third_quarter_odd_even($match,$gid,$standard){
        $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['tqid'],$this->handle,$gid);
              $outcome[$row['tqid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomesOthers($outcome,$standard,'onexbet_basketball','tqid');
    }

    public function will_there_be_overtime($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'], $this->handle, $gid);
            $outcome[$row['matchid']] = json_encode([
                "yes" => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                "no" => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
            ]);

        }
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }

    public function highest_scoring_half($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'], $this->handle, $gid);
            $outcome[$row['matchid']] = json_encode([
                "1stquarter" => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                "2ndquarter" => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                "3rdquarter" => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                "4thquarter:" => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],

            ]);

        }
        insertOutcomes($outcome,$standard,'onexbet_basketball');
    }

    public function htft($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  addslashes("1/1") => ['odd' => $records[0][0]['C'],'type' => $records[0][0]['T']],
                  addslashes("1/x") => ['odd' => $records[0][1]['C'],'type' => $records[0][1]['T']],
                  addslashes("1/2") => ['odd' => $records[0][2]['C'],'type' => $records[0][2]['T']],
                  addslashes("x/1") => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
                  addslashes("x/x") => ['odd' => $records[1][1]['C'],'type' => $records[1][1]['T']],
                  addslashes("x/2") => ['odd' => $records[1][2]['C'],'type' => $records[1][2]['T']],
                  addslashes("2/1") => ['odd' => $records[2][0]['C'],'type' => $records[2][0]['T']],
                  addslashes("2/x") => ['odd' => $records[2][1]['C'],'type' => $records[2][1]['T']],
                  addslashes("2/2") => ['odd' => $records[2][2]['C'],'type' => $records[2][2]['T']],
                  addslashes("1/2/x") => ['odd' => $records[2][3]['C'],'type' => $records[2][3]['T']],
                  addslashes("2/1/x") => ['odd' => $records[2][4]['C'],'type' => $records[2][4]['T']],
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet_basketball');
    }
    public function win_margin($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['matchid'], $this->handle, $gid);
              $outcome[$row['matchid']] = json_encode([
                  "1by:" . $this->checkflt($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                  "2by:" . $this->checkflt($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                  "1by:" . $this->checkflt($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                  "2by:" . $this->checkflt($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                  "1by:" . $this->checkflt($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                  "2by:" . $this->checkflt($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
              ]);

          }
          insertOutcomes($outcome,$standard,'onexbet_basketball');
    }

    public function double_chance($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['matchid'], $this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet_basketball');
    }

    public function double_chance_1q($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['fqid'], $this->handle,$gid);
              $outcome[$row['fqid']] = json_encode([
                  "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fqid');
      }
    public function double_chance_2q($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['sqid'], $this->handle,$gid);
              $outcome[$row['sqid']] = json_encode([
                  "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
        insertOutcomesOthers($outcome,$standard,'onexbet_basketball','sqid');
      }

      public function double_chance_ht($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetch_others($row['fhid'], $this->handle,$gid);
              $outcome[$row['fhid']] = json_encode([
                  "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
          insertOutcomesOthers($outcome,$standard,'onexbet_basketball','fhid');
      }

    private function checkflt($data){
        return  (is_float($data) ) ? floor($data)."+" : $data;
    }
    private function calc($p){
        $data = explode(".",$p);
        $data[1] = str_replace(00,"",$data[1]);
        if($data == 0){
            return "0-".$data[1];
        }
        else{
            return $data[0]."-".$data[1];
        }

    }


}