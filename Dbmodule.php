<?php

include_once 'connectDb.php' ;
include_once 'extra.php';

class Dbmodule
{
    public $dbcon;
    public $handle;

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
        $leagues = getLeagues('football');
        $list = [];
        $table = 'onexbet';
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
        $sql = "select matchid,corner_id,card_id from onexbet";
        $match = $this->dbcon->query($sql);
        $match_array = [];
        foreach ($match as $row) {
            $data = ["matchid"=>$row['matchid'],"corner_id"=>$row['corner_id'],'card_id'=>$row['card_id']];
            //array_push($match_array,$row['matchid']);
            array_push($match_array,$data);
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
             $records = fetchdatahalf($row['matchid'], $this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }

         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function m1x2_2ht($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'], $this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function m_1x2($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row) {
              $records = fetchdata($row['matchid'], $this->handle, $gid);
              $outcome[$row['matchid']] = json_encode([
                  "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function d_chance($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'], $this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function dc_first_half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'], $this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function dc_2half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'], $this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1x" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "12" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "x2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function gn_goal($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'], $this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
      public function over_under($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
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
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function handicap_norm($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function handicap_1half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function handicap_2half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function asain_hcap($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ahcap_1half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ahcap_2half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
      public function total_1($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
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
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function ou_home_ht($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ov_home_2ht($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
      public function total_2($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
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
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function ov_away_ht($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ov_away_2ht($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function a_highest_score($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1h" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "e" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "2h" => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
      public function hf_time($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
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
          insertOutcomes($outcome,$standard,'onexbet');
      }
      public function odd_even($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function even_odd_half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function even_odd_2half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
      public function h_score_half($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "1h" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "e" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                  "2h" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
      public function h_wins_eith($match,$gid,$standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
      public function a_wins_eith($match, $gid, $standard){
          $outcome = [];
          foreach ($match as $row){
              $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
              ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function handicap($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                         ($records[0][0]['P'] < 0) ? "1:0:".($records[0][0]['P']/-1) : "1:".$records[0][0]['P'].":0" => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                         ($records[0][1]['P'] < 0) ? "1:0:".($records[0][1]['P']/-1) : "1:".$records[0][1]['P'].":0" => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                         ($records[0][2]['P'] < 0) ? "1:0:".($records[0][2]['P']/-1) : "1:".$records[0][2]['P'].":0" => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                         ($records[0][3]['P'] < 0) ? "1:0:".($records[0][3]['P']/-1) : "1:".$records[0][3]['P'].":0" => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                         ($records[0][4]['P'] < 0) ? "1:0:".($records[0][4]['P']/-1) : "1:".$records[0][4]['P'].":0" => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                         ($records[0][5]['P'] < 0) ? "1:0:".($records[0][5]['P']/-1) : "1:".$records[0][5]['P'].":0" => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                         ($records[0][6]['P'] < 0) ? "1:0:".($records[0][6]['P']/-1) : "1:".$records[0][6]['P'].":0" => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                         ($records[1][0]['P'] < 0) ? "x:0:".($records[1][0]['P']/-1) : "x:".$records[1][0]['P'].":0" => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                         ($records[1][1]['P'] < 0) ? "x:0:".($records[1][1]['P']/-1) : "x:".$records[1][1]['P'].":0" => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                         ($records[1][2]['P'] < 0) ? "x:0:".($records[1][2]['P']/-1) : "x:".$records[1][2]['P'].":0" => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                         ($records[1][3]['P'] < 0) ? "x:0:".($records[1][3]['P']/-1) : "x:".$records[1][3]['P'].":0" => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                         ($records[1][4]['P'] < 0) ? "x:0:".($records[1][4]['P']/-1) : "x:".$records[1][4]['P'].":0" => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                         ($records[1][5]['P'] < 0) ? "x:0:".($records[1][5]['P']/-1) : "x:".$records[1][5]['P'].":0" => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                         ($records[1][6]['P'] < 0) ? "x:0:".($records[1][6]['P']/-1) : "x:".$records[1][6]['P'].":0" => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                         ($records[2][0]['P'] < 0) ? "2:0:".($records[2][0]['P']/-1) : "2:".$records[2][0]['P'].":0" => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                         ($records[2][1]['P'] < 0) ? "2:0:".($records[2][1]['P']/-1) : "2:".$records[2][1]['P'].":0" => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                         ($records[2][2]['P'] < 0) ? "2:0:".($records[2][2]['P']/-1) : "2:".$records[2][2]['P'].":0" => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                         ($records[2][3]['P'] < 0) ? "2:0:".($records[2][3]['P']/-1) : "2:".$records[2][3]['P'].":0" => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                         ($records[2][4]['P'] < 0) ? "2:0:".($records[2][4]['P']/-1) : "2:".$records[2][4]['P'].":0" => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                         ($records[2][5]['P'] < 0) ? "2:0:".($records[2][5]['P']/-1) : "2:".$records[2][5]['P'].":0" => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                         ($records[2][6]['P'] < 0) ? "2:0:".($records[2][6]['P']/-1) : "2:".$records[2][6]['P'].":0" => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
                     ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function handicap_half($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 ($records[0][0]['P'] < 0) ? "1:0:".($records[0][0]['P']/-1) : "1:".$records[0][0]['P'].":0" => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                 ($records[0][1]['P'] < 0) ? "1:0:".($records[0][1]['P']/-1) : "1:".$records[0][1]['P'].":0" => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                 ($records[0][2]['P'] < 0) ? "1:0:".($records[0][2]['P']/-1) : "1:".$records[0][2]['P'].":0" => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                 ($records[0][3]['P'] < 0) ? "1:0:".($records[0][3]['P']/-1) : "1:".$records[0][3]['P'].":0" => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                 ($records[0][4]['P'] < 0) ? "1:0:".($records[0][4]['P']/-1) : "1:".$records[0][4]['P'].":0" => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                 ($records[0][5]['P'] < 0) ? "1:0:".($records[0][5]['P']/-1) : "1:".$records[0][5]['P'].":0" => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                 ($records[0][6]['P'] < 0) ? "1:0:".($records[0][6]['P']/-1) : "1:".$records[0][6]['P'].":0" => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                 ($records[1][0]['P'] < 0) ? "x:0:".($records[1][0]['P']/-1) : "x:".$records[1][0]['P'].":0" => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                 ($records[1][1]['P'] < 0) ? "x:0:".($records[1][1]['P']/-1) : "x:".$records[1][1]['P'].":0" => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                 ($records[1][2]['P'] < 0) ? "x:0:".($records[1][2]['P']/-1) : "x:".$records[1][2]['P'].":0" => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                 ($records[1][3]['P'] < 0) ? "x:0:".($records[1][3]['P']/-1) : "x:".$records[1][3]['P'].":0" => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                 ($records[1][4]['P'] < 0) ? "x:0:".($records[1][4]['P']/-1) : "x:".$records[1][4]['P'].":0" => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                 ($records[1][5]['P'] < 0) ? "x:0:".($records[1][5]['P']/-1) : "x:".$records[1][5]['P'].":0" => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                 ($records[1][6]['P'] < 0) ? "x:0:".($records[1][6]['P']/-1) : "x:".$records[1][6]['P'].":0" => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                 ($records[2][0]['P'] < 0) ? "2:0:".($records[2][0]['P']/-1) : "2:".$records[2][0]['P'].":0" => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                 ($records[2][1]['P'] < 0) ? "2:0:".($records[2][1]['P']/-1) : "2:".$records[2][1]['P'].":0" => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                 ($records[2][2]['P'] < 0) ? "2:0:".($records[2][2]['P']/-1) : "2:".$records[2][2]['P'].":0" => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                 ($records[2][3]['P'] < 0) ? "2:0:".($records[2][3]['P']/-1) : "2:".$records[2][3]['P'].":0" => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                 ($records[2][4]['P'] < 0) ? "2:0:".($records[2][4]['P']/-1) : "2:".$records[2][4]['P'].":0" => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                 ($records[2][5]['P'] < 0) ? "2:0:".($records[2][5]['P']/-1) : "2:".$records[2][5]['P'].":0" => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                 ($records[2][6]['P'] < 0) ? "2:0:".($records[2][6]['P']/-1) : "2:".$records[2][6]['P'].":0" => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function handicap_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 ($records[0][0]['P'] < 0) ? "1:0:".($records[0][0]['P']/-1) : "1:".$records[0][0]['P'].":0" => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                 ($records[0][1]['P'] < 0) ? "1:0:".($records[0][1]['P']/-1) : "1:".$records[0][1]['P'].":0" => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                 ($records[0][2]['P'] < 0) ? "1:0:".($records[0][2]['P']/-1) : "1:".$records[0][2]['P'].":0" => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                 ($records[0][3]['P'] < 0) ? "1:0:".($records[0][3]['P']/-1) : "1:".$records[0][3]['P'].":0" => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                 ($records[0][4]['P'] < 0) ? "1:0:".($records[0][4]['P']/-1) : "1:".$records[0][4]['P'].":0" => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                 ($records[0][5]['P'] < 0) ? "1:0:".($records[0][5]['P']/-1) : "1:".$records[0][5]['P'].":0" => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                 ($records[0][6]['P'] < 0) ? "1:0:".($records[0][6]['P']/-1) : "1:".$records[0][6]['P'].":0" => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                 ($records[1][0]['P'] < 0) ? "x:0:".($records[1][0]['P']/-1) : "x:".$records[1][0]['P'].":0" => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                 ($records[1][1]['P'] < 0) ? "x:0:".($records[1][1]['P']/-1) : "x:".$records[1][1]['P'].":0" => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                 ($records[1][2]['P'] < 0) ? "x:0:".($records[1][2]['P']/-1) : "x:".$records[1][2]['P'].":0" => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                 ($records[1][3]['P'] < 0) ? "x:0:".($records[1][3]['P']/-1) : "x:".$records[1][3]['P'].":0" => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                 ($records[1][4]['P'] < 0) ? "x:0:".($records[1][4]['P']/-1) : "x:".$records[1][4]['P'].":0" => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                 ($records[1][5]['P'] < 0) ? "x:0:".($records[1][5]['P']/-1) : "x:".$records[1][5]['P'].":0" => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                 ($records[1][6]['P'] < 0) ? "x:0:".($records[1][6]['P']/-1) : "x:".$records[1][6]['P'].":0" => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                 ($records[2][0]['P'] < 0) ? "2:0:".($records[2][0]['P']/-1) : "2:".$records[2][0]['P'].":0" => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                 ($records[2][1]['P'] < 0) ? "2:0:".($records[2][1]['P']/-1) : "2:".$records[2][1]['P'].":0" => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                 ($records[2][2]['P'] < 0) ? "2:0:".($records[2][2]['P']/-1) : "2:".$records[2][2]['P'].":0" => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                 ($records[2][3]['P'] < 0) ? "2:0:".($records[2][3]['P']/-1) : "2:".$records[2][3]['P'].":0" => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                 ($records[2][4]['P'] < 0) ? "2:0:".($records[2][4]['P']/-1) : "2:".$records[2][4]['P'].":0" => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                 ($records[2][5]['P'] < 0) ? "2:0:".($records[2][5]['P']/-1) : "2:".$records[2][5]['P'].":0" => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                 ($records[2][6]['P'] < 0) ? "2:0:".($records[2][6]['P']/-1) : "2:".$records[2][6]['P'].":0" => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function last_goal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "2" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "none" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function h_clean_sheet($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function h_highest_score($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1h" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "e" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "2h" => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function h_win_nil($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function a_win_nil($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function first_goal_1x2($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "hgoal:1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "hgoal:2" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "hgoal:x" => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']],
                 "agoal:1" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "agoal:2" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
                 "agoal:x" => ['odd' => $records[1][2]['C'], 'type' => $records[1][2]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function a_win_both_half($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function h_win_both_half($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function c_score17($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function c_score17_half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function c_score17_2half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
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

         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function c_score($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row) {
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                 correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                 correctorder($records[0][2]['P']) => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                 correctorder($records[0][3]['P']) => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                 correctorder($records[0][4]['P']) => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                 correctorder($records[0][5]['P']) => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                 correctorder($records[0][6]['P']) => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                 correctorder($records[0][7]['P']) => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                 correctorder($records[0][8]['P']) => ['odd' => $records[0][8]['C'],'param'=>$records[0][8]['P'], 'type' => $records[0][8]['T']],
                 correctorder($records[0][9]['P']) => ['odd' => $records[0][9]['C'],'param'=>$records[0][9]['P'], 'type' => $records[0][9]['T']],
                 correctorder($records[0][10]['P']) => ['odd' => $records[0][10]['C'],'param'=>$records[0][10]['P'], 'type' => $records[0][10]['T']],
                 correctorder($records[0][11]['P']) => ['odd' => $records[0][11]['C'],'param'=>$records[0][11]['P'], 'type' => $records[0][11]['T']],
                 correctorder($records[0][12]['P']) => ['odd' => $records[0][12]['C'],'param'=>$records[0][12]['P'], 'type' => $records[0][12]['T']],
                 correctorder($records[0][13]['P']) => ['odd' => $records[0][13]['C'],'param'=>$records[0][13]['P'], 'type' => $records[0][13]['T']],
                 correctorder($records[0][14]['P']) => ['odd' => $records[0][14]['C'],'param'=>$records[0][14]['P'], 'type' => $records[0][14]['T']],
                 correctorder($records[0][15]['P']) => ['odd' => $records[0][15]['C'],'param'=>$records[0][15]['P'], 'type' => $records[0][15]['T']],
                 correctorder($records[0][16]['P']) => ['odd' => $records[0][16]['C'],'param'=>$records[0][16]['P'], 'type' => $records[0][16]['T']],
                 correctorder($records[0][17]['P']) => ['odd' => $records[0][17]['C'],'param'=>$records[0][17]['P'], 'type' => $records[0][17]['T']],
                 correctorder($records[0][18]['P']) => ['odd' => $records[0][18]['C'],'param'=>$records[0][18]['P'], 'type' => $records[0][18]['T']],
                 correctorder($records[0][19]['P']) => ['odd' => $records[0][19]['C'],'param'=>$records[0][19]['P'], 'type' => $records[0][19]['T']],
                 correctorder($records[0][20]['P']) => ['odd' => $records[0][20]['C'],'param'=>$records[0][20]['P'], 'type' => $records[0][20]['T']],
                 correctorder($records[0][21]['P']) => ['odd' => $records[0][21]['C'],'param'=>$records[0][21]['P'], 'type' => $records[0][21]['T']],
                 correctorder($records[0][22]['P']) => ['odd' => $records[0][22]['C'],'param'=>$records[0][22]['P'], 'type' => $records[0][22]['T']],
                 correctorder($records[0][23]['P']) => ['odd' => $records[0][23]['C'],'param'=>$records[0][23]['P'], 'type' => $records[0][23]['T']],
                 correctorder($records[0][24]['P']) => ['odd' => $records[0][24]['C'],'param'=>$records[0][24]['P'], 'type' => $records[0][24]['T']],
                 correctorder($records[0][25]['P']) => ['odd' => $records[0][25]['C'],'param'=>$records[0][25]['P'], 'type' => $records[0][25]['T']],
                 correctorder($records[0][26]['P']) => ['odd' => $records[0][26]['C'],'param'=>$records[0][26]['P'], 'type' => $records[0][26]['T']],
                 correctorder($records[0][27]['P']) => ['odd' => $records[0][27]['C'],'param'=>$records[0][27]['P'], 'type' => $records[0][27]['T']],
                 correctorder($records[0][28]['P']) => ['odd' => $records[0][28]['C'],'param'=>$records[0][28]['P'], 'type' => $records[0][28]['T']],
                 correctorder($records[0][29]['P']) => ['odd' => $records[0][29]['C'],'param'=>$records[0][29]['P'], 'type' => $records[0][29]['T']],
                 correctorder($records[0][30]['P']) => ['odd' => $records[0][30]['C'],'param'=>$records[0][30]['P'], 'type' => $records[0][30]['T']],
                 correctorder($records[0][31]['P']) => ['odd' => $records[0][31]['C'],'param'=>$records[0][31]['P'], 'type' => $records[0][31]['T']],
                 correctorder($records[0][32]['P']) => ['odd' => $records[0][32]['C'],'param'=>$records[0][32]['P'], 'type' => $records[0][32]['T']],
                 correctorder($records[0][33]['P']) => ['odd' => $records[0][33]['C'],'param'=>$records[0][33]['P'], 'type' => $records[0][33]['T']],
                 correctorder($records[0][34]['P']) => ['odd' => $records[0][34]['C'],'param'=>$records[0][34]['P'], 'type' => $records[0][34]['T']],
                 correctorder($records[0][35]['P']) => ['odd' => $records[0][35]['C'],'param'=>$records[0][35]['P'], 'type' => $records[0][35]['T']],
                 correctorder($records[0][36]['P']) => ['odd' => $records[0][36]['C'],'param'=>$records[0][36]['P'], 'type' => $records[0][36]['T']],
                 correctorder($records[0][37]['P']) => ['odd' => $records[0][37]['C'],'param'=>$records[0][37]['P'], 'type' => $records[0][37]['T']],
                 correctorder($records[0][38]['P']) => ['odd' => $records[0][38]['C'],'param'=>$records[0][38]['P'], 'type' => $records[0][38]['T']],
                 correctorder($records[0][39]['P']) => ['odd' => $records[0][39]['C'],'param'=>$records[0][39]['P'], 'type' => $records[0][39]['T']],
                 correctorder($records[0][40]['P']) => ['odd' => $records[0][40]['C'],'param'=>$records[0][40]['P'], 'type' => $records[0][40]['T']],
                 correctorder($records[0][41]['P']) => ['odd' => $records[0][41]['C'],'param'=>$records[0][41]['P'], 'type' => $records[0][41]['T']],
                 correctorder($records[0][42]['P']) => ['odd' => $records[0][42]['C'],'param'=>$records[0][42]['P'], 'type' => $records[0][42]['T']],
                 correctorder($records[0][43]['P']) => ['odd' => $records[0][43]['C'],'param'=>$records[0][43]['P'], 'type' => $records[0][43]['T']],
                 correctorder($records[0][44]['P']) => ['odd' => $records[0][44]['C'],'param'=>$records[0][44]['P'], 'type' => $records[0][44]['T']],
                 correctorder($records[0][45]['P']) => ['odd' => $records[0][45]['C'],'param'=>$records[0][45]['P'], 'type' => $records[0][45]['T']],
                 correctorder($records[0][46]['P']) => ['odd' => $records[0][46]['C'],'param'=>$records[0][46]['P'], 'type' => $records[0][46]['T']],
                 correctorder($records[0][47]['P']) => ['odd' => $records[0][47]['C'],'param'=>$records[0][47]['P'], 'type' => $records[0][47]['T']],
                 correctorder($records[0][48]['P']) => ['odd' => $records[0][48]['C'],'param'=>$records[0][48]['P'], 'type' => $records[0][48]['T']],
                 correctorder($records[0][49]['P']) => ['odd' => $records[0][49]['C'],'param'=>$records[0][49]['P'], 'type' => $records[0][49]['T']],
                 correctorder($records[0][50]['P']) => ['odd' => $records[0][50]['C'],'param'=>$records[0][50]['P'], 'type' => $records[0][50]['T']],
                 correctorder($records[0][51]['P']) => ['odd' => $records[0][51]['C'],'param'=>$records[0][51]['P'], 'type' => $records[0][51]['T']],
                 correctorder($records[0][52]['P']) => ['odd' => $records[0][52]['C'],'param'=>$records[0][52]['P'], 'type' => $records[0][52]['T']],
                 correctorder($records[0][53]['P']) => ['odd' => $records[0][53]['C'],'param'=>$records[0][53]['P'], 'type' => $records[0][53]['T']],
                 correctorder($records[0][54]['P']) => ['odd' => $records[0][54]['C'],'param'=>$records[0][54]['P'], 'type' => $records[0][54]['T']],
                 correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                 correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                 correctorder($records[1][2]['P']) => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                 correctorder($records[1][3]['P']) => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                 correctorder($records[1][4]['P']) => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                 correctorder($records[1][5]['P']) => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                 correctorder($records[1][6]['P']) => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                 correctorder($records[1][7]['P']) => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
                 correctorder($records[1][8]['P']) => ['odd' => $records[1][8]['C'],'param'=>$records[1][8]['P'], 'type' => $records[1][8]['T']],
                 correctorder($records[1][9]['P']) => ['odd' => $records[1][9]['C'],'param'=>$records[1][9]['P'], 'type' => $records[1][9]['T']],
                 correctorder($records[1][10]['P']) => ['odd' => $records[1][10]['C'],'param'=>$records[1][10]['P'], 'type' => $records[1][10]['T']],
                 correctorder($records[1][11]['P']) => ['odd' => $records[1][11]['C'],'param'=>$records[1][11]['P'], 'type' => $records[1][11]['T']],
                 correctorder($records[2][0]['P']) => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                 correctorder($records[2][1]['P']) => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                 correctorder($records[2][2]['P']) => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                 correctorder($records[2][3]['P']) => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                 correctorder($records[2][4]['P']) => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                 correctorder($records[2][5]['P']) => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                 correctorder($records[2][6]['P']) => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
                 correctorder($records[2][7]['P']) => ['odd' => $records[2][7]['C'],'param'=>$records[2][7]['P'], 'type' => $records[2][7]['T']],
                 correctorder($records[2][8]['P']) => ['odd' => $records[2][8]['C'],'param'=>$records[2][8]['P'], 'type' => $records[2][8]['T']],
                 correctorder($records[2][9]['P']) => ['odd' => $records[2][9]['C'],'param'=>$records[2][9]['P'], 'type' => $records[2][9]['T']],
                 correctorder($records[2][10]['P']) => ['odd' => $records[2][10]['C'],'param'=>$records[2][10]['P'], 'type' => $records[2][10]['T']],
                 correctorder($records[2][11]['P']) => ['odd' => $records[2][11]['C'],'param'=>$records[2][11]['P'], 'type' => $records[2][11]['T']],
                 correctorder($records[2][12]['P']) => ['odd' => $records[2][12]['C'],'param'=>$records[2][12]['P'], 'type' => $records[2][12]['T']],
                 correctorder($records[2][13]['P']) => ['odd' => $records[2][13]['C'],'param'=>$records[2][13]['P'], 'type' => $records[2][13]['T']],
                 correctorder($records[2][14]['P']) => ['odd' => $records[2][14]['C'],'param'=>$records[2][14]['P'], 'type' => $records[2][14]['T']],
                 correctorder($records[2][15]['P']) => ['odd' => $records[2][15]['C'],'param'=>$records[2][15]['P'], 'type' => $records[2][15]['T']],
                 correctorder($records[2][16]['P']) => ['odd' => $records[2][16]['C'],'param'=>$records[2][16]['P'], 'type' => $records[2][16]['T']],
                 correctorder($records[2][17]['P']) => ['odd' => $records[2][17]['C'],'param'=>$records[2][17]['P'], 'type' => $records[2][17]['T']],
                 correctorder($records[2][18]['P']) => ['odd' => $records[2][18]['C'],'param'=>$records[2][18]['P'], 'type' => $records[2][18]['T']],
                 correctorder($records[2][19]['P']) => ['odd' => $records[2][19]['C'],'param'=>$records[2][19]['P'], 'type' => $records[2][19]['T']],
                 correctorder($records[2][20]['P']) => ['odd' => $records[2][20]['C'],'param'=>$records[2][20]['P'], 'type' => $records[2][20]['T']],
                 correctorder($records[2][22]['P']) => ['odd' => $records[2][22]['C'],'param'=>$records[2][22]['P'], 'type' => $records[2][22]['T']],
                 correctorder($records[2][23]['P']) => ['odd' => $records[2][23]['C'],'param'=>$records[2][23]['P'], 'type' => $records[2][23]['T']],
                 correctorder($records[2][24]['P']) => ['odd' => $records[2][24]['C'],'param'=>$records[2][24]['P'], 'type' => $records[2][24]['T']],
                 correctorder($records[2][25]['P']) => ['odd' => $records[2][25]['C'],'param'=>$records[2][25]['P'], 'type' => $records[2][25]['T']],
                 correctorder($records[2][26]['P']) => ['odd' => $records[2][26]['C'],'param'=>$records[2][26]['P'], 'type' => $records[2][26]['T']],
                 correctorder($records[2][27]['P']) => ['odd' => $records[2][27]['C'],'param'=>$records[2][27]['P'], 'type' => $records[2][27]['T']],
                 correctorder($records[2][28]['P']) => ['odd' => $records[2][28]['C'],'param'=>$records[2][28]['P'], 'type' => $records[2][28]['T']],
                 correctorder($records[2][29]['P']) => ['odd' => $records[2][29]['C'],'param'=>$records[2][29]['P'], 'type' => $records[2][29]['T']],
                 correctorder($records[2][30]['P']) => ['odd' => $records[2][30]['C'],'param'=>$records[2][30]['P'], 'type' => $records[2][30]['T']],
                 correctorder($records[2][31]['P']) => ['odd' => $records[2][31]['C'],'param'=>$records[2][31]['P'], 'type' => $records[2][31]['T']],
                 correctorder($records[2][32]['P']) => ['odd' => $records[2][32]['C'],'param'=>$records[2][32]['P'], 'type' => $records[2][32]['T']],
                 correctorder($records[2][33]['P']) => ['odd' => $records[2][33]['C'],'param'=>$records[2][33]['P'], 'type' => $records[2][33]['T']],
                 correctorder($records[2][34]['P']) => ['odd' => $records[2][34]['C'],'param'=>$records[2][34]['P'], 'type' => $records[2][34]['T']],
                 correctorder($records[2][35]['P']) => ['odd' => $records[2][35]['C'],'param'=>$records[2][35]['P'], 'type' => $records[2][35]['T']],
                 correctorder($records[2][36]['P']) => ['odd' => $records[2][36]['C'],'param'=>$records[2][36]['P'], 'type' => $records[2][36]['T']],
                 correctorder($records[2][37]['P']) => ['odd' => $records[2][37]['C'],'param'=>$records[2][37]['P'], 'type' => $records[2][37]['T']],
                 correctorder($records[2][38]['P']) => ['odd' => $records[2][38]['C'],'param'=>$records[2][38]['P'], 'type' => $records[2][38]['T']],
                 correctorder($records[2][39]['P']) => ['odd' => $records[2][39]['C'],'param'=>$records[2][39]['P'], 'type' => $records[2][39]['T']],
                 correctorder($records[2][40]['P']) => ['odd' => $records[2][40]['C'],'param'=>$records[2][40]['P'], 'type' => $records[2][40]['T']],
                 correctorder($records[2][41]['P']) => ['odd' => $records[2][41]['C'],'param'=>$records[2][41]['P'], 'type' => $records[2][41]['T']],
                 correctorder($records[2][42]['P']) => ['odd' => $records[2][42]['C'],'param'=>$records[2][42]['P'], 'type' => $records[2][42]['T']],
                 correctorder($records[2][43]['P']) => ['odd' => $records[2][43]['C'],'param'=>$records[2][43]['P'], 'type' => $records[2][43]['T']],
                 correctorder($records[2][44]['P']) => ['odd' => $records[2][44]['C'],'param'=>$records[2][44]['P'], 'type' => $records[2][44]['T']],
                 correctorder($records[2][45]['P']) => ['odd' => $records[2][45]['C'],'param'=>$records[2][45]['P'], 'type' => $records[2][45]['T']],
                 correctorder($records[2][46]['P']) => ['odd' => $records[2][46]['C'],'param'=>$records[2][46]['P'], 'type' => $records[2][46]['T']],
                 correctorder($records[2][47]['P']) => ['odd' => $records[2][47]['C'],'param'=>$records[2][47]['P'], 'type' => $records[2][47]['T']],
                 correctorder($records[2][48]['P']) => ['odd' => $records[2][48]['C'],'param'=>$records[2][48]['P'], 'type' => $records[2][48]['T']],
                 correctorder($records[2][49]['P']) => ['odd' => $records[2][49]['C'],'param'=>$records[2][49]['P'], 'type' => $records[2][49]['T']],
                 correctorder($records[2][50]['P']) => ['odd' => $records[2][50]['C'],'param'=>$records[2][50]['P'], 'type' => $records[2][50]['T']],
                 correctorder($records[2][51]['P']) => ['odd' => $records[2][51]['C'],'param'=>$records[2][51]['P'], 'type' => $records[2][51]['T']],
                 correctorder($records[2][52]['P']) => ['odd' => $records[2][52]['C'],'param'=>$records[2][52]['P'], 'type' => $records[2][52]['T']],
                 correctorder($records[2][53]['P']) => ['odd' => $records[2][53]['C'],'param'=>$records[2][53]['P'], 'type' => $records[2][53]['T']],
                 correctorder($records[2][54]['P']) => ['odd' => $records[2][54]['C'],'param'=>$records[2][54]['P'], 'type' => $records[2][54]['T']],

             ]);
         }

         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function c_score_half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row) {
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                 correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                 correctorder($records[0][2]['P']) => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                 correctorder($records[0][3]['P']) => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                 correctorder($records[0][4]['P']) => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                 correctorder($records[0][5]['P']) => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                 correctorder($records[0][6]['P']) => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                 correctorder($records[0][7]['P']) => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                 correctorder($records[0][8]['P']) => ['odd' => $records[0][8]['C'],'param'=>$records[0][8]['P'], 'type' => $records[0][8]['T']],
                 correctorder($records[0][9]['P']) => ['odd' => $records[0][9]['C'],'param'=>$records[0][9]['P'], 'type' => $records[0][9]['T']],
                 correctorder($records[0][10]['P']) => ['odd' => $records[0][10]['C'],'param'=>$records[0][10]['P'], 'type' => $records[0][10]['T']],
                 correctorder($records[0][11]['P']) => ['odd' => $records[0][11]['C'],'param'=>$records[0][11]['P'], 'type' => $records[0][11]['T']],
                 correctorder($records[0][12]['P']) => ['odd' => $records[0][12]['C'],'param'=>$records[0][12]['P'], 'type' => $records[0][12]['T']],
                 correctorder($records[0][13]['P']) => ['odd' => $records[0][13]['C'],'param'=>$records[0][13]['P'], 'type' => $records[0][13]['T']],
                 correctorder($records[0][14]['P']) => ['odd' => $records[0][14]['C'],'param'=>$records[0][14]['P'], 'type' => $records[0][14]['T']],
                 correctorder($records[0][15]['P']) => ['odd' => $records[0][15]['C'],'param'=>$records[0][15]['P'], 'type' => $records[0][15]['T']],
                 correctorder($records[0][16]['P']) => ['odd' => $records[0][16]['C'],'param'=>$records[0][16]['P'], 'type' => $records[0][16]['T']],
                 correctorder($records[0][17]['P']) => ['odd' => $records[0][17]['C'],'param'=>$records[0][17]['P'], 'type' => $records[0][17]['T']],
                 correctorder($records[0][18]['P']) => ['odd' => $records[0][18]['C'],'param'=>$records[0][18]['P'], 'type' => $records[0][18]['T']],
                 correctorder($records[0][19]['P']) => ['odd' => $records[0][19]['C'],'param'=>$records[0][19]['P'], 'type' => $records[0][19]['T']],
                 correctorder($records[0][20]['P']) => ['odd' => $records[0][20]['C'],'param'=>$records[0][20]['P'], 'type' => $records[0][20]['T']],
                 correctorder($records[0][21]['P']) => ['odd' => $records[0][21]['C'],'param'=>$records[0][21]['P'], 'type' => $records[0][21]['T']],
                 correctorder($records[0][22]['P']) => ['odd' => $records[0][22]['C'],'param'=>$records[0][22]['P'], 'type' => $records[0][22]['T']],
                 correctorder($records[0][23]['P']) => ['odd' => $records[0][23]['C'],'param'=>$records[0][23]['P'], 'type' => $records[0][23]['T']],
                 correctorder($records[0][24]['P']) => ['odd' => $records[0][24]['C'],'param'=>$records[0][24]['P'], 'type' => $records[0][24]['T']],
                 correctorder($records[0][25]['P']) => ['odd' => $records[0][25]['C'],'param'=>$records[0][25]['P'], 'type' => $records[0][25]['T']],
                 correctorder($records[0][26]['P']) => ['odd' => $records[0][26]['C'],'param'=>$records[0][26]['P'], 'type' => $records[0][26]['T']],
                 correctorder($records[0][27]['P']) => ['odd' => $records[0][27]['C'],'param'=>$records[0][27]['P'], 'type' => $records[0][27]['T']],
                 correctorder($records[0][28]['P']) => ['odd' => $records[0][28]['C'],'param'=>$records[0][28]['P'], 'type' => $records[0][28]['T']],
                 correctorder($records[0][29]['P']) => ['odd' => $records[0][29]['C'],'param'=>$records[0][29]['P'], 'type' => $records[0][29]['T']],
                 correctorder($records[0][30]['P']) => ['odd' => $records[0][30]['C'],'param'=>$records[0][30]['P'], 'type' => $records[0][30]['T']],
                 correctorder($records[0][31]['P']) => ['odd' => $records[0][31]['C'],'param'=>$records[0][31]['P'], 'type' => $records[0][31]['T']],
                 correctorder($records[0][32]['P']) => ['odd' => $records[0][32]['C'],'param'=>$records[0][32]['P'], 'type' => $records[0][32]['T']],
                 correctorder($records[0][33]['P']) => ['odd' => $records[0][33]['C'],'param'=>$records[0][33]['P'], 'type' => $records[0][33]['T']],
                 correctorder($records[0][34]['P']) => ['odd' => $records[0][34]['C'],'param'=>$records[0][34]['P'], 'type' => $records[0][34]['T']],
                 correctorder($records[0][35]['P']) => ['odd' => $records[0][35]['C'],'param'=>$records[0][35]['P'], 'type' => $records[0][35]['T']],
                 correctorder($records[0][36]['P']) => ['odd' => $records[0][36]['C'],'param'=>$records[0][36]['P'], 'type' => $records[0][36]['T']],
                 correctorder($records[0][37]['P']) => ['odd' => $records[0][37]['C'],'param'=>$records[0][37]['P'], 'type' => $records[0][37]['T']],
                 correctorder($records[0][38]['P']) => ['odd' => $records[0][38]['C'],'param'=>$records[0][38]['P'], 'type' => $records[0][38]['T']],
                 correctorder($records[0][39]['P']) => ['odd' => $records[0][39]['C'],'param'=>$records[0][39]['P'], 'type' => $records[0][39]['T']],
                 correctorder($records[0][40]['P']) => ['odd' => $records[0][40]['C'],'param'=>$records[0][40]['P'], 'type' => $records[0][40]['T']],
                 correctorder($records[0][41]['P']) => ['odd' => $records[0][41]['C'],'param'=>$records[0][41]['P'], 'type' => $records[0][41]['T']],
                 correctorder($records[0][42]['P']) => ['odd' => $records[0][42]['C'],'param'=>$records[0][42]['P'], 'type' => $records[0][42]['T']],
                 correctorder($records[0][43]['P']) => ['odd' => $records[0][43]['C'],'param'=>$records[0][43]['P'], 'type' => $records[0][43]['T']],
                 correctorder($records[0][44]['P']) => ['odd' => $records[0][44]['C'],'param'=>$records[0][44]['P'], 'type' => $records[0][44]['T']],
                 correctorder($records[0][45]['P']) => ['odd' => $records[0][45]['C'],'param'=>$records[0][45]['P'], 'type' => $records[0][45]['T']],
                 correctorder($records[0][46]['P']) => ['odd' => $records[0][46]['C'],'param'=>$records[0][46]['P'], 'type' => $records[0][46]['T']],
                 correctorder($records[0][47]['P']) => ['odd' => $records[0][47]['C'],'param'=>$records[0][47]['P'], 'type' => $records[0][47]['T']],
                 correctorder($records[0][48]['P']) => ['odd' => $records[0][48]['C'],'param'=>$records[0][48]['P'], 'type' => $records[0][48]['T']],
                 correctorder($records[0][49]['P']) => ['odd' => $records[0][49]['C'],'param'=>$records[0][49]['P'], 'type' => $records[0][49]['T']],
                 correctorder($records[0][50]['P']) => ['odd' => $records[0][50]['C'],'param'=>$records[0][50]['P'], 'type' => $records[0][50]['T']],
                 correctorder($records[0][51]['P']) => ['odd' => $records[0][51]['C'],'param'=>$records[0][51]['P'], 'type' => $records[0][51]['T']],
                 correctorder($records[0][52]['P']) => ['odd' => $records[0][52]['C'],'param'=>$records[0][52]['P'], 'type' => $records[0][52]['T']],
                 correctorder($records[0][53]['P']) => ['odd' => $records[0][53]['C'],'param'=>$records[0][53]['P'], 'type' => $records[0][53]['T']],
                 correctorder($records[0][54]['P']) => ['odd' => $records[0][54]['C'],'param'=>$records[0][54]['P'], 'type' => $records[0][54]['T']],
                 correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                 correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                 correctorder($records[1][2]['P']) => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                 correctorder($records[1][3]['P']) => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                 correctorder($records[1][4]['P']) => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                 correctorder($records[1][5]['P']) => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                 correctorder($records[1][6]['P']) => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                 correctorder($records[1][7]['P']) => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
                 correctorder($records[1][8]['P']) => ['odd' => $records[1][8]['C'],'param'=>$records[1][8]['P'], 'type' => $records[1][8]['T']],
                 correctorder($records[1][9]['P']) => ['odd' => $records[1][9]['C'],'param'=>$records[1][9]['P'], 'type' => $records[1][9]['T']],
                 correctorder($records[1][10]['P']) => ['odd' => $records[1][10]['C'],'param'=>$records[1][10]['P'], 'type' => $records[1][10]['T']],
                 correctorder($records[1][11]['P']) => ['odd' => $records[1][11]['C'],'param'=>$records[1][11]['P'], 'type' => $records[1][11]['T']],
                 correctorder($records[2][0]['P']) => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                 correctorder($records[2][1]['P']) => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                 correctorder($records[2][2]['P']) => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                 correctorder($records[2][3]['P']) => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                 correctorder($records[2][4]['P']) => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                 correctorder($records[2][5]['P']) => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                 correctorder($records[2][6]['P']) => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
                 correctorder($records[2][7]['P']) => ['odd' => $records[2][7]['C'],'param'=>$records[2][7]['P'], 'type' => $records[2][7]['T']],
                 correctorder($records[2][8]['P']) => ['odd' => $records[2][8]['C'],'param'=>$records[2][8]['P'], 'type' => $records[2][8]['T']],
                 correctorder($records[2][9]['P']) => ['odd' => $records[2][9]['C'],'param'=>$records[2][9]['P'], 'type' => $records[2][9]['T']],
                 correctorder($records[2][10]['P']) => ['odd' => $records[2][10]['C'],'param'=>$records[2][10]['P'], 'type' => $records[2][10]['T']],
                 correctorder($records[2][11]['P']) => ['odd' => $records[2][11]['C'],'param'=>$records[2][11]['P'], 'type' => $records[2][11]['T']],
                 correctorder($records[2][12]['P']) => ['odd' => $records[2][12]['C'],'param'=>$records[2][12]['P'], 'type' => $records[2][12]['T']],
                 correctorder($records[2][13]['P']) => ['odd' => $records[2][13]['C'],'param'=>$records[2][13]['P'], 'type' => $records[2][13]['T']],
                 correctorder($records[2][14]['P']) => ['odd' => $records[2][14]['C'],'param'=>$records[2][14]['P'], 'type' => $records[2][14]['T']],
                 correctorder($records[2][15]['P']) => ['odd' => $records[2][15]['C'],'param'=>$records[2][15]['P'], 'type' => $records[2][15]['T']],
                 correctorder($records[2][16]['P']) => ['odd' => $records[2][16]['C'],'param'=>$records[2][16]['P'], 'type' => $records[2][16]['T']],
                 correctorder($records[2][17]['P']) => ['odd' => $records[2][17]['C'],'param'=>$records[2][17]['P'], 'type' => $records[2][17]['T']],
                 correctorder($records[2][18]['P']) => ['odd' => $records[2][18]['C'],'param'=>$records[2][18]['P'], 'type' => $records[2][18]['T']],
                 correctorder($records[2][19]['P']) => ['odd' => $records[2][19]['C'],'param'=>$records[2][19]['P'], 'type' => $records[2][19]['T']],
                 correctorder($records[2][20]['P']) => ['odd' => $records[2][20]['C'],'param'=>$records[2][20]['P'], 'type' => $records[2][20]['T']],
                 correctorder($records[2][22]['P']) => ['odd' => $records[2][22]['C'],'param'=>$records[2][22]['P'], 'type' => $records[2][22]['T']],
                 correctorder($records[2][23]['P']) => ['odd' => $records[2][23]['C'],'param'=>$records[2][23]['P'], 'type' => $records[2][23]['T']],
                 correctorder($records[2][24]['P']) => ['odd' => $records[2][24]['C'],'param'=>$records[2][24]['P'], 'type' => $records[2][24]['T']],
                 correctorder($records[2][25]['P']) => ['odd' => $records[2][25]['C'],'param'=>$records[2][25]['P'], 'type' => $records[2][25]['T']],
                 correctorder($records[2][26]['P']) => ['odd' => $records[2][26]['C'],'param'=>$records[2][26]['P'], 'type' => $records[2][26]['T']],
                 correctorder($records[2][27]['P']) => ['odd' => $records[2][27]['C'],'param'=>$records[2][27]['P'], 'type' => $records[2][27]['T']],
                 correctorder($records[2][28]['P']) => ['odd' => $records[2][28]['C'],'param'=>$records[2][28]['P'], 'type' => $records[2][28]['T']],
                 correctorder($records[2][29]['P']) => ['odd' => $records[2][29]['C'],'param'=>$records[2][29]['P'], 'type' => $records[2][29]['T']],
                 correctorder($records[2][30]['P']) => ['odd' => $records[2][30]['C'],'param'=>$records[2][30]['P'], 'type' => $records[2][30]['T']],
                 correctorder($records[2][31]['P']) => ['odd' => $records[2][31]['C'],'param'=>$records[2][31]['P'], 'type' => $records[2][31]['T']],
                 correctorder($records[2][32]['P']) => ['odd' => $records[2][32]['C'],'param'=>$records[2][32]['P'], 'type' => $records[2][32]['T']],
                 correctorder($records[2][33]['P']) => ['odd' => $records[2][33]['C'],'param'=>$records[2][33]['P'], 'type' => $records[2][33]['T']],
                 correctorder($records[2][34]['P']) => ['odd' => $records[2][34]['C'],'param'=>$records[2][34]['P'], 'type' => $records[2][34]['T']],
                 correctorder($records[2][35]['P']) => ['odd' => $records[2][35]['C'],'param'=>$records[2][35]['P'], 'type' => $records[2][35]['T']],
                 correctorder($records[2][36]['P']) => ['odd' => $records[2][36]['C'],'param'=>$records[2][36]['P'], 'type' => $records[2][36]['T']],
                 correctorder($records[2][37]['P']) => ['odd' => $records[2][37]['C'],'param'=>$records[2][37]['P'], 'type' => $records[2][37]['T']],
                 correctorder($records[2][38]['P']) => ['odd' => $records[2][38]['C'],'param'=>$records[2][38]['P'], 'type' => $records[2][38]['T']],
                 correctorder($records[2][39]['P']) => ['odd' => $records[2][39]['C'],'param'=>$records[2][39]['P'], 'type' => $records[2][39]['T']],
                 correctorder($records[2][40]['P']) => ['odd' => $records[2][40]['C'],'param'=>$records[2][40]['P'], 'type' => $records[2][40]['T']],
                 correctorder($records[2][41]['P']) => ['odd' => $records[2][41]['C'],'param'=>$records[2][41]['P'], 'type' => $records[2][41]['T']],
                 correctorder($records[2][42]['P']) => ['odd' => $records[2][42]['C'],'param'=>$records[2][42]['P'], 'type' => $records[2][42]['T']],
                 correctorder($records[2][43]['P']) => ['odd' => $records[2][43]['C'],'param'=>$records[2][43]['P'], 'type' => $records[2][43]['T']],
                 correctorder($records[2][44]['P']) => ['odd' => $records[2][44]['C'],'param'=>$records[2][44]['P'], 'type' => $records[2][44]['T']],
                 correctorder($records[2][45]['P']) => ['odd' => $records[2][45]['C'],'param'=>$records[2][45]['P'], 'type' => $records[2][45]['T']],
                 correctorder($records[2][46]['P']) => ['odd' => $records[2][46]['C'],'param'=>$records[2][46]['P'], 'type' => $records[2][46]['T']],
                 correctorder($records[2][47]['P']) => ['odd' => $records[2][47]['C'],'param'=>$records[2][47]['P'], 'type' => $records[2][47]['T']],
                 correctorder($records[2][48]['P']) => ['odd' => $records[2][48]['C'],'param'=>$records[2][48]['P'], 'type' => $records[2][48]['T']],
                 correctorder($records[2][49]['P']) => ['odd' => $records[2][49]['C'],'param'=>$records[2][49]['P'], 'type' => $records[2][49]['T']],
                 correctorder($records[2][50]['P']) => ['odd' => $records[2][50]['C'],'param'=>$records[2][50]['P'], 'type' => $records[2][50]['T']],
                 correctorder($records[2][51]['P']) => ['odd' => $records[2][51]['C'],'param'=>$records[2][51]['P'], 'type' => $records[2][51]['T']],
                 correctorder($records[2][52]['P']) => ['odd' => $records[2][52]['C'],'param'=>$records[2][52]['P'], 'type' => $records[2][52]['T']],
                 correctorder($records[2][53]['P']) => ['odd' => $records[2][53]['C'],'param'=>$records[2][53]['P'], 'type' => $records[2][53]['T']],
                 correctorder($records[2][54]['P']) => ['odd' => $records[2][54]['C'],'param'=>$records[2][54]['P'], 'type' => $records[2][54]['T']],

             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function c_score_2half($match,$gid,$standard){
         $outcome = [];
         foreach ($match as $row) {
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 correctorder($records[0][0]['P']) => ['odd' => $records[0][0]['C'],'param'=>$records[0][0]['P'], 'type' => $records[0][0]['T']],
                 correctorder($records[0][1]['P']) => ['odd' => $records[0][1]['C'],'param'=>$records[0][1]['P'], 'type' => $records[0][1]['T']],
                 correctorder($records[0][2]['P']) => ['odd' => $records[0][2]['C'],'param'=>$records[0][2]['P'], 'type' => $records[0][2]['T']],
                 correctorder($records[0][3]['P']) => ['odd' => $records[0][3]['C'],'param'=>$records[0][3]['P'], 'type' => $records[0][3]['T']],
                 correctorder($records[0][4]['P']) => ['odd' => $records[0][4]['C'],'param'=>$records[0][4]['P'], 'type' => $records[0][4]['T']],
                 correctorder($records[0][5]['P']) => ['odd' => $records[0][5]['C'],'param'=>$records[0][5]['P'], 'type' => $records[0][5]['T']],
                 correctorder($records[0][6]['P']) => ['odd' => $records[0][6]['C'],'param'=>$records[0][6]['P'], 'type' => $records[0][6]['T']],
                 correctorder($records[0][7]['P']) => ['odd' => $records[0][7]['C'],'param'=>$records[0][7]['P'], 'type' => $records[0][7]['T']],
                 correctorder($records[0][8]['P']) => ['odd' => $records[0][8]['C'],'param'=>$records[0][8]['P'], 'type' => $records[0][8]['T']],
                 correctorder($records[0][9]['P']) => ['odd' => $records[0][9]['C'],'param'=>$records[0][9]['P'], 'type' => $records[0][9]['T']],
                 correctorder($records[0][10]['P']) => ['odd' => $records[0][10]['C'],'param'=>$records[0][10]['P'], 'type' => $records[0][10]['T']],
                 correctorder($records[0][11]['P']) => ['odd' => $records[0][11]['C'],'param'=>$records[0][11]['P'], 'type' => $records[0][11]['T']],
                 correctorder($records[0][12]['P']) => ['odd' => $records[0][12]['C'],'param'=>$records[0][12]['P'], 'type' => $records[0][12]['T']],
                 correctorder($records[0][13]['P']) => ['odd' => $records[0][13]['C'],'param'=>$records[0][13]['P'], 'type' => $records[0][13]['T']],
                 correctorder($records[0][14]['P']) => ['odd' => $records[0][14]['C'],'param'=>$records[0][14]['P'], 'type' => $records[0][14]['T']],
                 correctorder($records[0][15]['P']) => ['odd' => $records[0][15]['C'],'param'=>$records[0][15]['P'], 'type' => $records[0][15]['T']],
                 correctorder($records[0][16]['P']) => ['odd' => $records[0][16]['C'],'param'=>$records[0][16]['P'], 'type' => $records[0][16]['T']],
                 correctorder($records[0][17]['P']) => ['odd' => $records[0][17]['C'],'param'=>$records[0][17]['P'], 'type' => $records[0][17]['T']],
                 correctorder($records[0][18]['P']) => ['odd' => $records[0][18]['C'],'param'=>$records[0][18]['P'], 'type' => $records[0][18]['T']],
                 correctorder($records[0][19]['P']) => ['odd' => $records[0][19]['C'],'param'=>$records[0][19]['P'], 'type' => $records[0][19]['T']],
                 correctorder($records[0][20]['P']) => ['odd' => $records[0][20]['C'],'param'=>$records[0][20]['P'], 'type' => $records[0][20]['T']],
                 correctorder($records[0][21]['P']) => ['odd' => $records[0][21]['C'],'param'=>$records[0][21]['P'], 'type' => $records[0][21]['T']],
                 correctorder($records[0][22]['P']) => ['odd' => $records[0][22]['C'],'param'=>$records[0][22]['P'], 'type' => $records[0][22]['T']],
                 correctorder($records[0][23]['P']) => ['odd' => $records[0][23]['C'],'param'=>$records[0][23]['P'], 'type' => $records[0][23]['T']],
                 correctorder($records[0][24]['P']) => ['odd' => $records[0][24]['C'],'param'=>$records[0][24]['P'], 'type' => $records[0][24]['T']],
                 correctorder($records[0][25]['P']) => ['odd' => $records[0][25]['C'],'param'=>$records[0][25]['P'], 'type' => $records[0][25]['T']],
                 correctorder($records[0][26]['P']) => ['odd' => $records[0][26]['C'],'param'=>$records[0][26]['P'], 'type' => $records[0][26]['T']],
                 correctorder($records[0][27]['P']) => ['odd' => $records[0][27]['C'],'param'=>$records[0][27]['P'], 'type' => $records[0][27]['T']],
                 correctorder($records[0][28]['P']) => ['odd' => $records[0][28]['C'],'param'=>$records[0][28]['P'], 'type' => $records[0][28]['T']],
                 correctorder($records[0][29]['P']) => ['odd' => $records[0][29]['C'],'param'=>$records[0][29]['P'], 'type' => $records[0][29]['T']],
                 correctorder($records[0][30]['P']) => ['odd' => $records[0][30]['C'],'param'=>$records[0][30]['P'], 'type' => $records[0][30]['T']],
                 correctorder($records[0][31]['P']) => ['odd' => $records[0][31]['C'],'param'=>$records[0][31]['P'], 'type' => $records[0][31]['T']],
                 correctorder($records[0][32]['P']) => ['odd' => $records[0][32]['C'],'param'=>$records[0][32]['P'], 'type' => $records[0][32]['T']],
                 correctorder($records[0][33]['P']) => ['odd' => $records[0][33]['C'],'param'=>$records[0][33]['P'], 'type' => $records[0][33]['T']],
                 correctorder($records[0][34]['P']) => ['odd' => $records[0][34]['C'],'param'=>$records[0][34]['P'], 'type' => $records[0][34]['T']],
                 correctorder($records[0][35]['P']) => ['odd' => $records[0][35]['C'],'param'=>$records[0][35]['P'], 'type' => $records[0][35]['T']],
                 correctorder($records[0][36]['P']) => ['odd' => $records[0][36]['C'],'param'=>$records[0][36]['P'], 'type' => $records[0][36]['T']],
                 correctorder($records[0][37]['P']) => ['odd' => $records[0][37]['C'],'param'=>$records[0][37]['P'], 'type' => $records[0][37]['T']],
                 correctorder($records[0][38]['P']) => ['odd' => $records[0][38]['C'],'param'=>$records[0][38]['P'], 'type' => $records[0][38]['T']],
                 correctorder($records[0][39]['P']) => ['odd' => $records[0][39]['C'],'param'=>$records[0][39]['P'], 'type' => $records[0][39]['T']],
                 correctorder($records[0][40]['P']) => ['odd' => $records[0][40]['C'],'param'=>$records[0][40]['P'], 'type' => $records[0][40]['T']],
                 correctorder($records[0][41]['P']) => ['odd' => $records[0][41]['C'],'param'=>$records[0][41]['P'], 'type' => $records[0][41]['T']],
                 correctorder($records[0][42]['P']) => ['odd' => $records[0][42]['C'],'param'=>$records[0][42]['P'], 'type' => $records[0][42]['T']],
                 correctorder($records[0][43]['P']) => ['odd' => $records[0][43]['C'],'param'=>$records[0][43]['P'], 'type' => $records[0][43]['T']],
                 correctorder($records[0][44]['P']) => ['odd' => $records[0][44]['C'],'param'=>$records[0][44]['P'], 'type' => $records[0][44]['T']],
                 correctorder($records[0][45]['P']) => ['odd' => $records[0][45]['C'],'param'=>$records[0][45]['P'], 'type' => $records[0][45]['T']],
                 correctorder($records[0][46]['P']) => ['odd' => $records[0][46]['C'],'param'=>$records[0][46]['P'], 'type' => $records[0][46]['T']],
                 correctorder($records[0][47]['P']) => ['odd' => $records[0][47]['C'],'param'=>$records[0][47]['P'], 'type' => $records[0][47]['T']],
                 correctorder($records[0][48]['P']) => ['odd' => $records[0][48]['C'],'param'=>$records[0][48]['P'], 'type' => $records[0][48]['T']],
                 correctorder($records[0][49]['P']) => ['odd' => $records[0][49]['C'],'param'=>$records[0][49]['P'], 'type' => $records[0][49]['T']],
                 correctorder($records[0][50]['P']) => ['odd' => $records[0][50]['C'],'param'=>$records[0][50]['P'], 'type' => $records[0][50]['T']],
                 correctorder($records[0][51]['P']) => ['odd' => $records[0][51]['C'],'param'=>$records[0][51]['P'], 'type' => $records[0][51]['T']],
                 correctorder($records[0][52]['P']) => ['odd' => $records[0][52]['C'],'param'=>$records[0][52]['P'], 'type' => $records[0][52]['T']],
                 correctorder($records[0][53]['P']) => ['odd' => $records[0][53]['C'],'param'=>$records[0][53]['P'], 'type' => $records[0][53]['T']],
                 correctorder($records[0][54]['P']) => ['odd' => $records[0][54]['C'],'param'=>$records[0][54]['P'], 'type' => $records[0][54]['T']],
                 correctorder($records[1][0]['P']) => ['odd' => $records[1][0]['C'],'param'=>$records[1][0]['P'], 'type' => $records[1][0]['T']],
                 correctorder($records[1][1]['P']) => ['odd' => $records[1][1]['C'],'param'=>$records[1][1]['P'], 'type' => $records[1][1]['T']],
                 correctorder($records[1][2]['P']) => ['odd' => $records[1][2]['C'],'param'=>$records[1][2]['P'], 'type' => $records[1][2]['T']],
                 correctorder($records[1][3]['P']) => ['odd' => $records[1][3]['C'],'param'=>$records[1][3]['P'], 'type' => $records[1][3]['T']],
                 correctorder($records[1][4]['P']) => ['odd' => $records[1][4]['C'],'param'=>$records[1][4]['P'], 'type' => $records[1][4]['T']],
                 correctorder($records[1][5]['P']) => ['odd' => $records[1][5]['C'],'param'=>$records[1][5]['P'], 'type' => $records[1][5]['T']],
                 correctorder($records[1][6]['P']) => ['odd' => $records[1][6]['C'],'param'=>$records[1][6]['P'], 'type' => $records[1][6]['T']],
                 correctorder($records[1][7]['P']) => ['odd' => $records[1][7]['C'],'param'=>$records[1][7]['P'], 'type' => $records[1][7]['T']],
                 correctorder($records[1][8]['P']) => ['odd' => $records[1][8]['C'],'param'=>$records[1][8]['P'], 'type' => $records[1][8]['T']],
                 correctorder($records[1][9]['P']) => ['odd' => $records[1][9]['C'],'param'=>$records[1][9]['P'], 'type' => $records[1][9]['T']],
                 correctorder($records[1][10]['P']) => ['odd' => $records[1][10]['C'],'param'=>$records[1][10]['P'], 'type' => $records[1][10]['T']],
                 correctorder($records[1][11]['P']) => ['odd' => $records[1][11]['C'],'param'=>$records[1][11]['P'], 'type' => $records[1][11]['T']],
                 correctorder($records[2][0]['P']) => ['odd' => $records[2][0]['C'],'param'=>$records[2][0]['P'], 'type' => $records[2][0]['T']],
                 correctorder($records[2][1]['P']) => ['odd' => $records[2][1]['C'],'param'=>$records[2][1]['P'], 'type' => $records[2][1]['T']],
                 correctorder($records[2][2]['P']) => ['odd' => $records[2][2]['C'],'param'=>$records[2][2]['P'], 'type' => $records[2][2]['T']],
                 correctorder($records[2][3]['P']) => ['odd' => $records[2][3]['C'],'param'=>$records[2][3]['P'], 'type' => $records[2][3]['T']],
                 correctorder($records[2][4]['P']) => ['odd' => $records[2][4]['C'],'param'=>$records[2][4]['P'], 'type' => $records[2][4]['T']],
                 correctorder($records[2][5]['P']) => ['odd' => $records[2][5]['C'],'param'=>$records[2][5]['P'], 'type' => $records[2][5]['T']],
                 correctorder($records[2][6]['P']) => ['odd' => $records[2][6]['C'],'param'=>$records[2][6]['P'], 'type' => $records[2][6]['T']],
                 correctorder($records[2][7]['P']) => ['odd' => $records[2][7]['C'],'param'=>$records[2][7]['P'], 'type' => $records[2][7]['T']],
                 correctorder($records[2][8]['P']) => ['odd' => $records[2][8]['C'],'param'=>$records[2][8]['P'], 'type' => $records[2][8]['T']],
                 correctorder($records[2][9]['P']) => ['odd' => $records[2][9]['C'],'param'=>$records[2][9]['P'], 'type' => $records[2][9]['T']],
                 correctorder($records[2][10]['P']) => ['odd' => $records[2][10]['C'],'param'=>$records[2][10]['P'], 'type' => $records[2][10]['T']],
                 correctorder($records[2][11]['P']) => ['odd' => $records[2][11]['C'],'param'=>$records[2][11]['P'], 'type' => $records[2][11]['T']],
                 correctorder($records[2][12]['P']) => ['odd' => $records[2][12]['C'],'param'=>$records[2][12]['P'], 'type' => $records[2][12]['T']],
                 correctorder($records[2][13]['P']) => ['odd' => $records[2][13]['C'],'param'=>$records[2][13]['P'], 'type' => $records[2][13]['T']],
                 correctorder($records[2][14]['P']) => ['odd' => $records[2][14]['C'],'param'=>$records[2][14]['P'], 'type' => $records[2][14]['T']],
                 correctorder($records[2][15]['P']) => ['odd' => $records[2][15]['C'],'param'=>$records[2][15]['P'], 'type' => $records[2][15]['T']],
                 correctorder($records[2][16]['P']) => ['odd' => $records[2][16]['C'],'param'=>$records[2][16]['P'], 'type' => $records[2][16]['T']],
                 correctorder($records[2][17]['P']) => ['odd' => $records[2][17]['C'],'param'=>$records[2][17]['P'], 'type' => $records[2][17]['T']],
                 correctorder($records[2][18]['P']) => ['odd' => $records[2][18]['C'],'param'=>$records[2][18]['P'], 'type' => $records[2][18]['T']],
                 correctorder($records[2][19]['P']) => ['odd' => $records[2][19]['C'],'param'=>$records[2][19]['P'], 'type' => $records[2][19]['T']],
                 correctorder($records[2][20]['P']) => ['odd' => $records[2][20]['C'],'param'=>$records[2][20]['P'], 'type' => $records[2][20]['T']],
                 correctorder($records[2][22]['P']) => ['odd' => $records[2][22]['C'],'param'=>$records[2][22]['P'], 'type' => $records[2][22]['T']],
                 correctorder($records[2][23]['P']) => ['odd' => $records[2][23]['C'],'param'=>$records[2][23]['P'], 'type' => $records[2][23]['T']],
                 correctorder($records[2][24]['P']) => ['odd' => $records[2][24]['C'],'param'=>$records[2][24]['P'], 'type' => $records[2][24]['T']],
                 correctorder($records[2][25]['P']) => ['odd' => $records[2][25]['C'],'param'=>$records[2][25]['P'], 'type' => $records[2][25]['T']],
                 correctorder($records[2][26]['P']) => ['odd' => $records[2][26]['C'],'param'=>$records[2][26]['P'], 'type' => $records[2][26]['T']],
                 correctorder($records[2][27]['P']) => ['odd' => $records[2][27]['C'],'param'=>$records[2][27]['P'], 'type' => $records[2][27]['T']],
                 correctorder($records[2][28]['P']) => ['odd' => $records[2][28]['C'],'param'=>$records[2][28]['P'], 'type' => $records[2][28]['T']],
                 correctorder($records[2][29]['P']) => ['odd' => $records[2][29]['C'],'param'=>$records[2][29]['P'], 'type' => $records[2][29]['T']],
                 correctorder($records[2][30]['P']) => ['odd' => $records[2][30]['C'],'param'=>$records[2][30]['P'], 'type' => $records[2][30]['T']],
                 correctorder($records[2][31]['P']) => ['odd' => $records[2][31]['C'],'param'=>$records[2][31]['P'], 'type' => $records[2][31]['T']],
                 correctorder($records[2][32]['P']) => ['odd' => $records[2][32]['C'],'param'=>$records[2][32]['P'], 'type' => $records[2][32]['T']],
                 correctorder($records[2][33]['P']) => ['odd' => $records[2][33]['C'],'param'=>$records[2][33]['P'], 'type' => $records[2][33]['T']],
                 correctorder($records[2][34]['P']) => ['odd' => $records[2][34]['C'],'param'=>$records[2][34]['P'], 'type' => $records[2][34]['T']],
                 correctorder($records[2][35]['P']) => ['odd' => $records[2][35]['C'],'param'=>$records[2][35]['P'], 'type' => $records[2][35]['T']],
                 correctorder($records[2][36]['P']) => ['odd' => $records[2][36]['C'],'param'=>$records[2][36]['P'], 'type' => $records[2][36]['T']],
                 correctorder($records[2][37]['P']) => ['odd' => $records[2][37]['C'],'param'=>$records[2][37]['P'], 'type' => $records[2][37]['T']],
                 correctorder($records[2][38]['P']) => ['odd' => $records[2][38]['C'],'param'=>$records[2][38]['P'], 'type' => $records[2][38]['T']],
                 correctorder($records[2][39]['P']) => ['odd' => $records[2][39]['C'],'param'=>$records[2][39]['P'], 'type' => $records[2][39]['T']],
                 correctorder($records[2][40]['P']) => ['odd' => $records[2][40]['C'],'param'=>$records[2][40]['P'], 'type' => $records[2][40]['T']],
                 correctorder($records[2][41]['P']) => ['odd' => $records[2][41]['C'],'param'=>$records[2][41]['P'], 'type' => $records[2][41]['T']],
                 correctorder($records[2][42]['P']) => ['odd' => $records[2][42]['C'],'param'=>$records[2][42]['P'], 'type' => $records[2][42]['T']],
                 correctorder($records[2][43]['P']) => ['odd' => $records[2][43]['C'],'param'=>$records[2][43]['P'], 'type' => $records[2][43]['T']],
                 correctorder($records[2][44]['P']) => ['odd' => $records[2][44]['C'],'param'=>$records[2][44]['P'], 'type' => $records[2][44]['T']],
                 correctorder($records[2][45]['P']) => ['odd' => $records[2][45]['C'],'param'=>$records[2][45]['P'], 'type' => $records[2][45]['T']],
                 correctorder($records[2][46]['P']) => ['odd' => $records[2][46]['C'],'param'=>$records[2][46]['P'], 'type' => $records[2][46]['T']],
                 correctorder($records[2][47]['P']) => ['odd' => $records[2][47]['C'],'param'=>$records[2][47]['P'], 'type' => $records[2][47]['T']],
                 correctorder($records[2][48]['P']) => ['odd' => $records[2][48]['C'],'param'=>$records[2][48]['P'], 'type' => $records[2][48]['T']],
                 correctorder($records[2][49]['P']) => ['odd' => $records[2][49]['C'],'param'=>$records[2][49]['P'], 'type' => $records[2][49]['T']],
                 correctorder($records[2][50]['P']) => ['odd' => $records[2][50]['C'],'param'=>$records[2][50]['P'], 'type' => $records[2][50]['T']],
                 correctorder($records[2][51]['P']) => ['odd' => $records[2][51]['C'],'param'=>$records[2][51]['P'], 'type' => $records[2][51]['T']],
                 correctorder($records[2][52]['P']) => ['odd' => $records[2][52]['C'],'param'=>$records[2][52]['P'], 'type' => $records[2][52]['T']],
                 correctorder($records[2][53]['P']) => ['odd' => $records[2][53]['C'],'param'=>$records[2][53]['P'], 'type' => $records[2][53]['T']],
                 correctorder($records[2][54]['P']) => ['odd' => $records[2][54]['C'],'param'=>$records[2][54]['P'], 'type' => $records[2][54]['T']],

             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
      public function home_oddeven($match, $gid, $standard){
          $outcome = [];
          foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
             ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
      public function away_oddeven($match, $gid, $standard){
          $outcome = [];
          foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
              $outcome[$row['matchid']] = json_encode([
                  "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                  "odd" => ['odd' => $records[1][0]['C'],'type' => $records[1][0]['T']],
             ]);
          }
          insertOutcomes($outcome,$standard,'onexbet');
      }
     public function a_clean_sheet($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "no" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "yes" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function un_over_half($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
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

         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function un_over_2half($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
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
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function away_win_nil_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function away_win_nil_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function home_win_nil_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function home_win_nil_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function penalty_yes_no($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function f5min_first_happen($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function penalty_scored_missed($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "scored" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "missed" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function at_least_half_x($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function home_to_score2row($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function home_to_score3row($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function away_score_2row($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function away_score_3row($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function gn_goal_2plus($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T'],'param'=>$records[0][1]['P']],
                 "no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T'],'param'=>$records[0][1]['P']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function first_goal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "2" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "none" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ggng_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'], $this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ggng_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'], $this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function m1x2_corner($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                     "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                     "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
                 ]);
             }
         }
        insertOutcomes($outcome,$standard,'onexbet');
     }
     public function m1x2_corner_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner_2half($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                     "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                     "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function m1x2_corner_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner_half($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "1" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                     "x" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                     "2" => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']]
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function over_under_card($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row) {
             if (isset($row['card_id'])) {
                 $records = fetchcard($row['card_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "over" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "under" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function over_under_corner($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "over" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "over" . $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "over" . $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                     "over" . $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                     "over" . $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                     "over" . $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                     "over" . $records[0][7]['P'] => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                     "over" . $records[0][8]['P'] => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
                     "under" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "under" . $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                     "under" . $records[1][3]['P'] => ['odd' => $records[1][3]['C'], 'param' => $records[1][3]['P'], 'type' => $records[1][3]['T']],
                     "under" . $records[1][4]['P'] => ['odd' => $records[1][4]['C'], 'param' => $records[1][4]['P'], 'type' => $records[1][4]['T']],
                     "under" . $records[1][5]['P'] => ['odd' => $records[1][5]['C'], 'param' => $records[1][5]['P'], 'type' => $records[1][5]['T']],
                     "under" . $records[1][6]['P'] => ['odd' => $records[1][6]['C'], 'param' => $records[1][6]['P'], 'type' => $records[1][6]['T']],
                     "under" . $records[1][7]['P'] => ['odd' => $records[1][7]['C'], 'param' => $records[1][7]['P'], 'type' => $records[1][7]['T']],
                     "under" . $records[1][8]['P'] => ['odd' => $records[1][8]['C'], 'param' => $records[1][8]['P'], 'type' => $records[1][8]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function odd_even_corner($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row) {
             if (isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "even" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                     "odd" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ht_ou($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "over" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "over" . $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "under" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "under" . $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                    ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function corner_ou_first_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner_half($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "over" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "over" . $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "under" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "under" . $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function corner_ou_second_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner_2half($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "over" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "over" . $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "under" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "under" . $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function corner_handicap($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "1h(" . $records[0][0]['P'].")" => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "1h(" . $records[0][1]['P'].")" => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "1h(" . $records[0][2]['P'].")" => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "1h(" . $records[0][3]['P'].")" => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                     "1h(" . $records[0][4]['P'].")" => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                     "1h(" . $records[0][5]['P'].")" => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                     "1h(" . $records[0][6]['P'].")" => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                     "2h(" . $records[1][0]['P'].")" => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "2h(" . $records[1][1]['P'].")" => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "2h(" . $records[1][2]['P'].")" => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                     "2h(" . $records[1][3]['P'].")" => ['odd' => $records[1][3]['C'], 'param' => $records[1][3]['P'], 'type' => $records[1][3]['T']],
                     "2h(" . $records[1][4]['P'].")" => ['odd' => $records[1][4]['C'], 'param' => $records[1][4]['P'], 'type' => $records[1][4]['T']],
                     "2h(" . $records[1][5]['P'].")" => ['odd' => $records[1][5]['C'], 'param' => $records[1][5]['P'], 'type' => $records[1][5]['T']],
                     "2h(" . $records[1][6]['P'].")" => ['odd' => $records[1][6]['C'], 'param' => $records[1][6]['P'], 'type' => $records[1][6]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function cor_htft($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     addslashes("1/1") => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                     addslashes("1/x") => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                     addslashes("1/2") => ['odd' => $records[0][2]['C'], 'type' => $records[0][2]['T']],
                     addslashes("x/1") => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                     addslashes("x/x") => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
                     addslashes("x/2") => ['odd' => $records[1][2]['C'], 'type' => $records[1][2]['T']],
                     addslashes("2/1") => ['odd' => $records[2][0]['C'], 'type' => $records[2][0]['T']],
                     addslashes("2/x") => ['odd' => $records[2][1]['C'], 'type' => $records[2][1]['T']],
                     addslashes("2/2") => ['odd' => $records[2][2]['C'], 'type' => $records[2][2]['T']]
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function home_ou_corner($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             if(isset($row['corner_id'])) {
                 $records = fetchcorner($row['corner_id'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "over" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "over" . $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "under" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "under" . $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                 ]);
             }
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function multi_goal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 $this->calc($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 $this->calc($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 $this->calc($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 $this->calc($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 $this->calc($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 $this->calc($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 $this->calc($records[0][6]['P']) => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 $this->calc($records[0][7]['P']) => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                 $this->calc($records[0][8]['P']) => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function multigoal_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 $this->calc($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 $this->calc($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 $this->calc($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 $this->calc($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 $this->calc($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 $this->calc($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 $this->calc($records[0][6]['P']) => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 $this->calc($records[0][7]['P']) => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                 $this->calc($records[0][8]['P']) => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function multigoal_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 $this->calc($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 $this->calc($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 $this->calc($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 $this->calc($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 $this->calc($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 $this->calc($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 $this->calc($records[0][6]['P']) => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 $this->calc($records[0][7]['P']) => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                 $this->calc($records[0][8]['P']) => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function home_mgoal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 $this->calc($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 $this->calc($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 $this->calc($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 $this->calc($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 $this->calc($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 $this->calc($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 $this->calc($records[0][6]['P']) => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
              ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function away_mgoal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 $this->calc($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 $this->calc($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 $this->calc($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 $this->calc($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 $this->calc($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 $this->calc($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 $this->calc($records[0][6]['P']) => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function team_to_score($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 "onlyhome" => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 "onlyaway" => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 "bothteams" => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                 "none" => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
              ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function h_score_home($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 (is_float($records[0][0]['P']) ) ? floor($records[0][0]['P'])."+" : $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 (is_float($records[0][1]['P']) ) ? floor($records[0][1]['P'])."+" : $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 (is_float($records[0][2]['P']) ) ? floor($records[0][2]['P'])."+" : $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 (is_float($records[0][3]['P']) ) ? floor($records[0][3]['P'])."+" : $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 (is_float($records[0][4]['P']) ) ? floor($records[0][4]['P'])."+" : $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 (is_float($records[0][5]['P']) ) ? floor($records[0][5]['P'])."+" : $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 (is_float($records[0][6]['P']) ) ? floor($records[0][6]['P'])."+" : $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 (is_float($records[1][0]['P']) ) ? floor($records[1][0]['P'])."+" : $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                 (is_float($records[1][1]['P']) ) ? floor($records[1][1]['P'])."+" : $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                 (is_float($records[1][2]['P']) ) ? floor($records[1][2]['P'])."+" : $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                 (is_float($records[1][3]['P']) ) ? floor($records[1][3]['P'])."+" : $records[1][3]['P'] => ['odd' => $records[1][3]['C'], 'param' => $records[1][3]['P'], 'type' => $records[1][3]['T']],
                 (is_float($records[1][4]['P']) ) ? floor($records[1][4]['P'])."+" : $records[1][4]['P'] => ['odd' => $records[1][4]['C'], 'param' => $records[1][4]['P'], 'type' => $records[1][4]['T']],
                 (is_float($records[1][5]['P']) ) ? floor($records[1][5]['P'])."+" : $records[1][5]['P'] => ['odd' => $records[1][5]['C'], 'param' => $records[1][5]['P'], 'type' => $records[1][5]['T']],
                 (is_float($records[1][6]['P']) ) ? floor($records[1][6]['P'])."+" : $records[1][6]['P'] => ['odd' => $records[1][6]['C'], 'param' => $records[1][6]['P'], 'type' => $records[1][6]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function a_score_away($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 (is_float($records[0][0]['P']) ) ? floor($records[0][0]['P'])."+" : $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 (is_float($records[0][1]['P']) ) ? floor($records[0][1]['P'])."+" : $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 (is_float($records[0][2]['P']) ) ? floor($records[0][2]['P'])."+" : $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 (is_float($records[0][3]['P']) ) ? floor($records[0][3]['P'])."+" : $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 (is_float($records[0][4]['P']) ) ? floor($records[0][4]['P'])."+" : $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 (is_float($records[0][5]['P']) ) ? floor($records[0][5]['P'])."+" : $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 (is_float($records[0][6]['P']) ) ? floor($records[0][6]['P'])."+" : $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 (is_float($records[1][0]['P']) ) ? floor($records[1][0]['P'])."+" : $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                 (is_float($records[1][1]['P']) ) ? floor($records[1][1]['P'])."+" : $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                 (is_float($records[1][2]['P']) ) ? floor($records[1][2]['P'])."+" : $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                 (is_float($records[1][3]['P']) ) ? floor($records[1][3]['P'])."+" : $records[1][3]['P'] => ['odd' => $records[1][3]['C'], 'param' => $records[1][3]['P'], 'type' => $records[1][3]['T']],
                 (is_float($records[1][4]['P']) ) ? floor($records[1][4]['P'])."+" : $records[1][4]['P'] => ['odd' => $records[1][4]['C'], 'param' => $records[1][4]['P'], 'type' => $records[1][4]['T']],
                 (is_float($records[1][5]['P']) ) ? floor($records[1][5]['P'])."+" : $records[1][5]['P'] => ['odd' => $records[1][5]['C'], 'param' => $records[1][5]['P'], 'type' => $records[1][5]['T']],
                 (is_float($records[1][6]['P']) ) ? floor($records[1][6]['P'])."+" : $records[1][6]['P'] => ['odd' => $records[1][6]['C'], 'param' => $records[1][6]['P'], 'type' => $records[1][6]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function ggng_ou($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
                 $records = fetchdata($row['matchid'], $this->handle, $gid);
                 $outcome[$row['matchid']] = json_encode([
                     "yes:over" . $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                     "yes:under" . $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                     "yes:over" . $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                     "yes:under" . $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                     "yes:over" . $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                     "yes:under" . $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                     "yes:over" . $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                     "yes:under" . $records[0][7]['P'] => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                     "no:over" . $records[1][0]['P'] => ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                     "no:under" . $records[1][1]['P'] => ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                     "no:over" . $records[1][2]['P'] => ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                     "no:under" . $records[1][3]['P'] => ['odd' => $records[1][3]['C'], 'param' => $records[1][3]['P'], 'type' => $records[1][3]['T']],
                     "no:over" . $records[1][4]['P'] => ['odd' => $records[1][4]['C'], 'param' => $records[1][4]['P'], 'type' => $records[1][4]['T']],
                     "no:under" . $records[1][5]['P'] => ['odd' => $records[1][5]['C'], 'param' => $records[1][5]['P'], 'type' => $records[1][5]['T']],
                     "no:over" . $records[1][6]['P'] => ['odd' => $records[1][6]['C'], 'param' => $records[1][6]['P'], 'type' => $records[1][6]['T']],
                     "no:under" . $records[1][7]['P'] => ['odd' => $records[1][7]['C'], 'param' => $records[1][7]['P'], 'type' => $records[1][7]['T']],
                 ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function exact_goal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 (is_float($records[0][0]['P']) ) ? floor($records[0][0]['P'])."+" : $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 (is_float($records[0][1]['P']) ) ? floor($records[0][1]['P'])."+" : $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 (is_float($records[0][2]['P']) ) ? floor($records[0][2]['P'])."+" : $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 (is_float($records[0][3]['P']) ) ? floor($records[0][3]['P'])."+" : $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 (is_float($records[0][4]['P']) ) ? floor($records[0][4]['P'])."+" : $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 (is_float($records[0][5]['P']) ) ? floor($records[0][5]['P'])."+" : $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 (is_float($records[0][6]['P']) ) ? floor($records[0][6]['P'])."+" : $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 (is_float($records[0][7]['P']) ) ? floor($records[0][7]['P'])."+" : $records[0][7]['P'] => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                 (is_float($records[0][8]['P']) ) ? floor($records[0][8]['P'])."+" : $records[0][8]['P'] => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
                 (is_float($records[0][9]['P']) ) ? floor($records[0][9]['P'])."+" : $records[0][9]['P'] => ['odd' => $records[0][9]['C'], 'param' => $records[0][9]['P'], 'type' => $records[0][9]['T']],
                 (is_float($records[0][10]['P']) ) ? floor($records[0][10]['P'])."+" : $records[0][10]['P'] => ['odd' => $records[0][10]['C'], 'param' => $records[0][10]['P'], 'type' => $records[0][10]['T']],
                 (is_float($records[0][11]['P']) ) ? floor($records[0][11]['P'])."+" : $records[0][11]['P'] => ['odd' => $records[0][11]['C'], 'param' => $records[0][11]['P'], 'type' => $records[0][11]['T']],
                 (is_float($records[0][12]['P']) ) ? floor($records[0][12]['P'])."+" : $records[0][12]['P'] => ['odd' => $records[0][12]['C'], 'param' => $records[0][12]['P'], 'type' => $records[0][12]['T']],
                 (is_float($records[0][13]['P']) ) ? floor($records[0][13]['P'])."+" : $records[0][13]['P'] => ['odd' => $records[0][13]['C'], 'param' => $records[0][13]['P'], 'type' => $records[0][13]['T']],
                 (is_float($records[0][14]['P']) ) ? floor($records[0][14]['P'])."+" : $records[0][14]['P'] => ['odd' => $records[0][14]['C'], 'param' => $records[0][14]['P'], 'type' => $records[0][14]['T']],
                 (is_float($records[0][15]['P']) ) ? floor($records[0][15]['P'])."+" : $records[0][15]['P'] => ['odd' => $records[0][15]['C'], 'param' => $records[0][15]['P'], 'type' => $records[0][15]['T']],
                 (is_float($records[0][16]['P']) ) ? floor($records[0][16]['P'])."+" : $records[0][16]['P'] => ['odd' => $records[0][16]['C'], 'param' => $records[0][16]['P'], 'type' => $records[0][16]['T']],
                 (is_float($records[0][17]['P']) ) ? floor($records[0][17]['P'])."+" : $records[0][17]['P'] => ['odd' => $records[0][17]['C'], 'param' => $records[0][17]['P'], 'type' => $records[0][17]['T']],
                 (is_float($records[0][18]['P']) ) ? floor($records[0][18]['P'])."+" : $records[0][18]['P'] => ['odd' => $records[0][18]['C'], 'param' => $records[0][18]['P'], 'type' => $records[0][18]['T']],
                 (is_float($records[0][19]['P']) ) ? floor($records[0][19]['P'])."+" : $records[0][17]['P'] => ['odd' => $records[0][19]['C'], 'param' => $records[0][19]['P'], 'type' => $records[0][19]['T']],
                 (is_float($records[0][20]['P']) ) ? floor($records[0][20]['P'])."+" : $records[0][20]['P'] => ['odd' => $records[0][20]['C'], 'param' => $records[0][20]['P'], 'type' => $records[0][20]['T']],
                 (is_float($records[0][21]['P']) ) ? floor($records[0][21]['P'])."+" : $records[0][21]['P'] => ['odd' => $records[0][21]['C'], 'param' => $records[0][21]['P'], 'type' => $records[0][21]['T']],
                 (is_float($records[0][22]['P']) ) ? floor($records[0][22]['P'])."+" : $records[0][22]['P'] => ['odd' => $records[0][22]['C'], 'param' => $records[0][22]['P'], 'type' => $records[0][22]['T']]
                 ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function exact_goal_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 (is_float($records[0][0]['P']) ) ? floor($records[0][0]['P'])."+" : $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 (is_float($records[0][1]['P']) ) ? floor($records[0][1]['P'])."+" : $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 (is_float($records[0][2]['P']) ) ? floor($records[0][2]['P'])."+" : $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 (is_float($records[0][3]['P']) ) ? floor($records[0][3]['P'])."+" : $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 (is_float($records[0][4]['P']) ) ? floor($records[0][4]['P'])."+" : $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 (is_float($records[0][5]['P']) ) ? floor($records[0][5]['P'])."+" : $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 (is_float($records[0][6]['P']) ) ? floor($records[0][6]['P'])."+" : $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 (is_float($records[0][7]['P']) ) ? floor($records[0][7]['P'])."+" : $records[0][7]['P'] => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                 (is_float($records[0][8]['P']) ) ? floor($records[0][8]['P'])."+" : $records[0][8]['P'] => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
                 (is_float($records[0][9]['P']) ) ? floor($records[0][9]['P'])."+" : $records[0][9]['P'] => ['odd' => $records[0][9]['C'], 'param' => $records[0][9]['P'], 'type' => $records[0][9]['T']],
                 (is_float($records[0][10]['P']) ) ? floor($records[0][10]['P'])."+" : $records[0][10]['P'] => ['odd' => $records[0][10]['C'], 'param' => $records[0][10]['P'], 'type' => $records[0][10]['T']],
                 (is_float($records[0][11]['P']) ) ? floor($records[0][11]['P'])."+" : $records[0][11]['P'] => ['odd' => $records[0][11]['C'], 'param' => $records[0][11]['P'], 'type' => $records[0][11]['T']],
                 (is_float($records[0][12]['P']) ) ? floor($records[0][12]['P'])."+" : $records[0][12]['P'] => ['odd' => $records[0][12]['C'], 'param' => $records[0][12]['P'], 'type' => $records[0][12]['T']],
                 (is_float($records[0][13]['P']) ) ? floor($records[0][13]['P'])."+" : $records[0][13]['P'] => ['odd' => $records[0][13]['C'], 'param' => $records[0][13]['P'], 'type' => $records[0][13]['T']],
                 (is_float($records[0][14]['P']) ) ? floor($records[0][14]['P'])."+" : $records[0][14]['P'] => ['odd' => $records[0][14]['C'], 'param' => $records[0][14]['P'], 'type' => $records[0][14]['T']],
                 (is_float($records[0][15]['P']) ) ? floor($records[0][15]['P'])."+" : $records[0][15]['P'] => ['odd' => $records[0][15]['C'], 'param' => $records[0][15]['P'], 'type' => $records[0][15]['T']],
                 (is_float($records[0][16]['P']) ) ? floor($records[0][16]['P'])."+" : $records[0][16]['P'] => ['odd' => $records[0][16]['C'], 'param' => $records[0][16]['P'], 'type' => $records[0][16]['T']],
                 (is_float($records[0][17]['P']) ) ? floor($records[0][17]['P'])."+" : $records[0][17]['P'] => ['odd' => $records[0][17]['C'], 'param' => $records[0][17]['P'], 'type' => $records[0][17]['T']],
                 (is_float($records[0][18]['P']) ) ? floor($records[0][18]['P'])."+" : $records[0][18]['P'] => ['odd' => $records[0][18]['C'], 'param' => $records[0][18]['P'], 'type' => $records[0][18]['T']],
                 (is_float($records[0][19]['P']) ) ? floor($records[0][19]['P'])."+" : $records[0][17]['P'] => ['odd' => $records[0][19]['C'], 'param' => $records[0][19]['P'], 'type' => $records[0][19]['T']],
                 (is_float($records[0][20]['P']) ) ? floor($records[0][20]['P'])."+" : $records[0][20]['P'] => ['odd' => $records[0][20]['C'], 'param' => $records[0][20]['P'], 'type' => $records[0][20]['T']],
                 (is_float($records[0][21]['P']) ) ? floor($records[0][21]['P'])."+" : $records[0][21]['P'] => ['odd' => $records[0][21]['C'], 'param' => $records[0][21]['P'], 'type' => $records[0][21]['T']],
                 (is_float($records[0][22]['P']) ) ? floor($records[0][22]['P'])."+" : $records[0][22]['P'] => ['odd' => $records[0][22]['C'], 'param' => $records[0][22]['P'], 'type' => $records[0][22]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function exact_goal_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                 (is_float($records[0][0]['P']) ) ? floor($records[0][0]['P'])."+" : $records[0][0]['P'] => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 (is_float($records[0][1]['P']) ) ? floor($records[0][1]['P'])."+" : $records[0][1]['P'] => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 (is_float($records[0][2]['P']) ) ? floor($records[0][2]['P'])."+" : $records[0][2]['P'] => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                 (is_float($records[0][3]['P']) ) ? floor($records[0][3]['P'])."+" : $records[0][3]['P'] => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                 (is_float($records[0][4]['P']) ) ? floor($records[0][4]['P'])."+" : $records[0][4]['P'] => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                 (is_float($records[0][5]['P']) ) ? floor($records[0][5]['P'])."+" : $records[0][5]['P'] => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                 (is_float($records[0][6]['P']) ) ? floor($records[0][6]['P'])."+" : $records[0][6]['P'] => ['odd' => $records[0][6]['C'], 'param' => $records[0][6]['P'], 'type' => $records[0][6]['T']],
                 (is_float($records[0][7]['P']) ) ? floor($records[0][7]['P'])."+" : $records[0][7]['P'] => ['odd' => $records[0][7]['C'], 'param' => $records[0][7]['P'], 'type' => $records[0][7]['T']],
                 (is_float($records[0][8]['P']) ) ? floor($records[0][8]['P'])."+" : $records[0][8]['P'] => ['odd' => $records[0][8]['C'], 'param' => $records[0][8]['P'], 'type' => $records[0][8]['T']],
                 (is_float($records[0][9]['P']) ) ? floor($records[0][9]['P'])."+" : $records[0][9]['P'] => ['odd' => $records[0][9]['C'], 'param' => $records[0][9]['P'], 'type' => $records[0][9]['T']],
                 (is_float($records[0][10]['P']) ) ? floor($records[0][10]['P'])."+" : $records[0][10]['P'] => ['odd' => $records[0][10]['C'], 'param' => $records[0][10]['P'], 'type' => $records[0][10]['T']],
                 (is_float($records[0][11]['P']) ) ? floor($records[0][11]['P'])."+" : $records[0][11]['P'] => ['odd' => $records[0][11]['C'], 'param' => $records[0][11]['P'], 'type' => $records[0][11]['T']],
                 (is_float($records[0][12]['P']) ) ? floor($records[0][12]['P'])."+" : $records[0][12]['P'] => ['odd' => $records[0][12]['C'], 'param' => $records[0][12]['P'], 'type' => $records[0][12]['T']],
                 (is_float($records[0][13]['P']) ) ? floor($records[0][13]['P'])."+" : $records[0][13]['P'] => ['odd' => $records[0][13]['C'], 'param' => $records[0][13]['P'], 'type' => $records[0][13]['T']],
                 (is_float($records[0][14]['P']) ) ? floor($records[0][14]['P'])."+" : $records[0][14]['P'] => ['odd' => $records[0][14]['C'], 'param' => $records[0][14]['P'], 'type' => $records[0][14]['T']],
                 (is_float($records[0][15]['P']) ) ? floor($records[0][15]['P'])."+" : $records[0][15]['P'] => ['odd' => $records[0][15]['C'], 'param' => $records[0][15]['P'], 'type' => $records[0][15]['T']],
                 (is_float($records[0][16]['P']) ) ? floor($records[0][16]['P'])."+" : $records[0][16]['P'] => ['odd' => $records[0][16]['C'], 'param' => $records[0][16]['P'], 'type' => $records[0][16]['T']],
                 (is_float($records[0][17]['P']) ) ? floor($records[0][17]['P'])."+" : $records[0][17]['P'] => ['odd' => $records[0][17]['C'], 'param' => $records[0][17]['P'], 'type' => $records[0][17]['T']],
                 (is_float($records[0][18]['P']) ) ? floor($records[0][18]['P'])."+" : $records[0][18]['P'] => ['odd' => $records[0][18]['C'], 'param' => $records[0][18]['P'], 'type' => $records[0][18]['T']],
                 (is_float($records[0][19]['P']) ) ? floor($records[0][19]['P'])."+" : $records[0][17]['P'] => ['odd' => $records[0][19]['C'], 'param' => $records[0][19]['P'], 'type' => $records[0][19]['T']],
                 (is_float($records[0][20]['P']) ) ? floor($records[0][20]['P'])."+" : $records[0][20]['P'] => ['odd' => $records[0][20]['C'], 'param' => $records[0][20]['P'], 'type' => $records[0][20]['T']],
                 (is_float($records[0][21]['P']) ) ? floor($records[0][21]['P'])."+" : $records[0][21]['P'] => ['odd' => $records[0][21]['C'], 'param' => $records[0][21]['P'], 'type' => $records[0][21]['T']],
                 (is_float($records[0][22]['P']) ) ? floor($records[0][22]['P'])."+" : $records[0][22]['P'] => ['odd' => $records[0][22]['C'], 'param' => $records[0][22]['P'], 'type' => $records[0][22]['T']]
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function dc_btts($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1/x:yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "2/x:yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "1/x:no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2/x:no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function dc_both_score_ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdatahalf($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1/x:yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "2/x:yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "1/x:no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2/x:no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function dc_both_score_2ht($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata2half($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1/x:yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "2/x:yes" => ['odd' => $records[0][1]['C'], 'type' => $records[0][1]['T']],
                 "1/x:no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
                 "2/x:no" => ['odd' => $records[1][1]['C'], 'type' => $records[1][1]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function htft_ov($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "1/1:over".$records[0][0]['P'] => ['odd' => $records[0][0]['C'],'param'=> $records[0][0]['P'], 'type' => $records[0][0]['T']],
                 "2/2:over".$records[0][1]['P'] => ['odd' => $records[0][1]['C'],'param'=> $records[0][1]['P'], 'type' => $records[0][1]['T']],
                 "1/1:over".$records[1][0]['P'] => ['odd' => $records[1][0]['C'],'param'=> $records[1][0]['P'], 'type' => $records[1][0]['T']],
                 "2/2:over".$records[1][1]['P'] => ['odd' => $records[1][1]['C'],'param'=> $records[1][1]['P'], 'type' => $records[1][1]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
    public function dc_ov_goal($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'], $this->handle, $gid);
             $outcome[$row['matchid']] = json_encode([
                  "1/2:under".$records[0][0]['P']=> ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                  "1/2:over".$records[0][1]['P']=> ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                  "1/2:under".$records[0][2]['P']=> ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                  "1/2:over".$records[0][3]['P']=> ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                  "1/2:under".$records[0][4]['P']=> ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                  "1/2:over".$records[0][5]['P']=> ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                  "1/2:under".$records[1][0]['P']=> ['odd' => $records[1][0]['C'], 'param' => $records[1][0]['P'], 'type' => $records[1][0]['T']],
                  "1/2:over".$records[1][1]['P']=> ['odd' => $records[1][1]['C'], 'param' => $records[1][1]['P'], 'type' => $records[1][1]['T']],
                  "1/2:under".$records[1][2]['P']=> ['odd' => $records[1][2]['C'], 'param' => $records[1][2]['P'], 'type' => $records[1][2]['T']],
                  "1/2:over".$records[1][3]['P']=> ['odd' => $records[1][3]['C'], 'param' => $records[1][3]['P'], 'type' => $records[1][3]['T']],
                  "1/2:under".$records[1][4]['P']=> ['odd' => $records[1][4]['C'], 'param' => $records[1][4]['P'], 'type' => $records[1][4]['T']],
                  "1/2:over".$records[1][5]['P']=> ['odd' => $records[1][5]['C'], 'param' => $records[1][5]['P'], 'type' => $records[1][5]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function red_card($match, $gid, $standard){
         $outcome = [];
         foreach ($match as $row){
             $records = fetchdata($row['matchid'],$this->handle,$gid);
             $outcome[$row['matchid']] = json_encode([
                 "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                 "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
             ]);
         }
         insertOutcomes($outcome,$standard,'onexbet');
     }
     public function win_margin($match, $gid, $standard){
          $outcome = [];
          foreach ($match as $row){

                  $records = fetchdata($row['matchid'], $this->handle, $gid);

                  $outcome[$row['matchid']] = json_encode([
                      "1by:" . $this->checkflt($records[0][0]['P']) => ['odd' => $records[0][0]['C'], 'param' => $records[0][0]['P'], 'type' => $records[0][0]['T']],
                      "2by:" . $this->checkflt($records[0][1]['P']) => ['odd' => $records[0][1]['C'], 'param' => $records[0][1]['P'], 'type' => $records[0][1]['T']],
                      "1by:" . $this->checkflt($records[0][2]['P']) => ['odd' => $records[0][2]['C'], 'param' => $records[0][2]['P'], 'type' => $records[0][2]['T']],
                      "2by:" . $this->checkflt($records[0][3]['P']) => ['odd' => $records[0][3]['C'], 'param' => $records[0][3]['P'], 'type' => $records[0][3]['T']],
                      "1by:" . $this->checkflt($records[0][4]['P']) => ['odd' => $records[0][4]['C'], 'param' => $records[0][4]['P'], 'type' => $records[0][4]['T']],
                      "2by:" . $this->checkflt($records[0][5]['P']) => ['odd' => $records[0][5]['C'], 'param' => $records[0][5]['P'], 'type' => $records[0][5]['T']],
                  ]);

          }
          insertOutcomes($outcome,$standard,'onexbet');
     }
     public function a_score_both($match, $gid, $standard){
           $outcome = [];
           foreach ($match as $row){
               $records = fetchdata($row['matchid'],$this->handle,$gid);
               $outcome[$row['matchid']] = json_encode([
                   "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                   "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
               ]);
           }
           insertOutcomes($outcome,$standard,'onexbet');
     }

     public function h_score_both($match, $gid, $standard){
           $outcome = [];
           foreach ($match as $row){
               $records = fetchdata($row['matchid'],$this->handle,$gid);
               $outcome[$row['matchid']] = json_encode([
                   "yes" => ['odd' => $records[0][0]['C'], 'type' => $records[0][0]['T']],
                   "no" => ['odd' => $records[1][0]['C'], 'type' => $records[1][0]['T']],
               ]);
           }
           insertOutcomes($outcome,$standard,'onexbet');
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



//    public function multiscore($match, $gid, $standard){}
//

}
