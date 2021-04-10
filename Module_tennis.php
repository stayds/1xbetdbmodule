<?php

include_once 'connectDb.php';
include_once 'extra.php';
class Module_tennis
{

    public $dbcon;
    public $handle;

    public function __construct(){
        $this->dbcon = connect();
        $this->handle = curl_init();
    }

    public function initLoad(){
        try{
            $sql = "TRUNCATE TABLE onexbet_tennis RESTART IDENTITY CASCADE";
            $this->dbcon->query($sql);

            return $this->getGamesOnline();
        }
        catch (PDOException $ex){
            echo $ex->getMessage();
        }
    }

    public function getGamesOnline(){
        $leagues = getLeagues('tennis');
        $table = 'onexbet_tennis';
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
        $sql = "select matchid,fsid from onexbet_tennis";
        $match = $this->dbcon->query($sql);
        $match_array = [];
        foreach ($match as $row) {
            $data = ["matchid"=>$row['matchid'],"fsid"=>$row['fsid']];
            array_push($match_array,$data);
        }
        return $match_array;
    }

    private function bettypes(){
        $sql = "select groupid,standard from bettype_tennis";
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

    public function f_set_game_handicap($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fsid'],$this->handle,$gid);
            $outcome[$row['fsid']] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                ]);
        }
        insertOutcomesOthers($outcome,$standard,'onexbet_tennis','fsid');
    }
    public function f_set_game_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['fsid'],$this->handle,$gid);
            $outcome[$row['fsid']] = json_encode([
                "over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
            ]);
        }

        insertOutcomesOthers($outcome,$standard,'onexbet_tennis','fsid');
    }
    public function f_set_game_correct($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row) {
            $records = fetch_others($row['fsid'],$this->handle,$gid);
            if(!is_null($records)) {
                $outcome[$row['fsid']] = json_encode([
                    correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                    correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],

                ]);
            }
        }

        insertOutcomesOthers($outcome,$standard,'onexbet_tennis','fsid');
    }
    public function game_handicap($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "1:".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "1:".$records[0][3]['P'] => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "2:".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                "2:".$records[1][3]['P'] => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard,'onexbet_tennis');
    }
    public function set_handicap($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "1:".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "1:".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "2:".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "2:".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
            ]);
        }
        insertOutcomes($outcome,$standard,'onexbet_tennis');
    }
    public function total_game($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                "over".$records[0][2]['P'] => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                "under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                "under".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                "under".$records[1][2]['P'] => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
            ]);
        }

        insertOutcomes($outcome,$standard,'onexbet_tennis');
    }

    public function home_game_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
             ]);
        }

        insertOutcomes($outcome,$standard,'onexbet_tennis');
    }
    public function away_game_total($match,$gid,$standard){
        $outcome = [];
        foreach ($match as $row){
            $records = fetch_others($row['matchid'],$this->handle,$gid);
            $outcome[$row['matchid']] = json_encode([
                "over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                "under".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
            ]);
        }

        insertOutcomes($outcome,$standard,'onexbet_tennis');
    }
    public function correct_Score($match,$gid,$standard){
        $outcome = [];
         foreach ($match as $row) {
             $records = fetch_others($row['matchid'],$this->handle,$gid);
             if(!is_null($records)) {
                 $outcome[$row['matchid']] = json_encode([
                     correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],

                 ]);
             }
         }

         insertOutcomes($outcome,$standard,'onexbet_tennis');
    }
    public function double_result_fset_match($match,$gid,$standard){}
    public function sh_home_over_under($match,$gid,$standard){}
    public function sh_away_over_under($match,$gid,$standard){}
    public function sh_over_under($match,$gid,$standard){}

}