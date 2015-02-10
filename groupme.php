<?php


/***
*
*
* This is a separated experiment, indipendent from salesman.php
*
*/

require 'lib/sim.php';

/**
* activities1 is films
* activities2 is restaurants
**/

define("STATE_START", 0);
define("STATE_ACT", 1);# map user -> action

define("ACTIVITY_CELL", 0);
define("ACTIVITY_START", 1);
define("ACTIVITY_END", 2);
define("ACTIVITY_DURATION", 3);
define("ACTIVITY_TYPE", 4);

define("MAXSEQUENCE", 2); # number of phases

$DZNINSTANCE = '../test/2_phases_simple_instance.dzn';

$USERSN = NULL;
$CELLSN = 0;
$MAXTIME = NULL;
$MINGROUPSIZE = NULL;
$MAXGROUPSIZE = NULL;
$GROUPSN = 0;
$MAXWAIT = NULL;

$activities = array();
$preferences = array();
$distances = array();

dzn_import($DZNINSTANCE);

function dzn_import($file_path){

  $USERSN = $GLOBALS['USERSN'];
  $MAXTIME = $GLOBALS['MAXTIME'];
  $MINGROUPSIZE = $GLOBALS['MINGROUPSIZE'];
  $MAXGROUPSIZE = $GLOBALS['MAXGROUPSIZE'];
  $MAXWAIT = $GLOBALS['MAXWAIT'];
  $activities = $GLOBALS['activities'];
  $preferences = $GLOBALS['preferences'];
  $distances =  $GLOBALS['distances'];
  $CELLSN =  $GLOBALS['CELLSN'];
  $GROUPSN =  $GLOBALS['GROUPSN'];

  for ($i=0;$i<MAXSEQUENCE; $i++){
   $activities[$i] = array();
 }

 $lines = file($file_path);
 foreach ($lines as $line_num => $line) {
  $splitted = split(" = ", $line);
  $content = $splitted[1];
  $lss = str_replace(";", "", $splitted[1]);
  $ls = split("\.", $lss);
  if ($splitted[0] == "user_ids") {
    $USERSN = intval($ls[2]);
  }else if ($splitted[0] == "activity1_ids") {
    $ACTIVITIES1N = intval($ls[2]);
  }else if ($splitted[0] == "activity2_ids") {
    $ACTIVITIES2N = intval($ls[2]);
  }else if ($splitted[0] == "cell_ids") {
    $CELLSN = intval($ls[2]);
  }else if ($splitted[0] == "group_ids") {
    $GROUPSN = intval($ls[2]);
  }else if ($splitted[0] == "type_ids") {
    $MAXTYPE = intval($ls[2]);
  }else if ($splitted[0] == "time_slot_ids") {
    $MAXTIME = intval($ls[2]);
  }else if ($splitted[0] == "min_group_size") {
    $MINGROUPSIZE = intval($ls[0]);
  }else if ($splitted[0] == "max_group_size") {
    $MAXGROUPSIZE = intval($ls[0]);
  }else if ($splitted[0] == "max_wait") {
    $MAXWAIT = intval($ls[0]);
  }else if ($splitted[0] == "preferences") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        $tmparr[] = trim($pieces[$j]);
      }
      $preferences[] = $tmparr;
    }
  }else if ($splitted[0] == "activities1") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        $tmparr[] = trim($pieces[$j]);
      }
      $activities[0][] = $tmparr;
    }
  }else if ($splitted[0] == "activities2") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        $tmparr[] = trim($pieces[$j]);
      }
      $activities[1][] = $tmparr;
    }
  }else if ($splitted[0] == "distances") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        if (trim($pieces[$j]) != "") {
         $tmparr[] = trim($pieces[$j]);
       }
     }
     $distances[] = $tmparr;
   }
 }
}
$GLOBALS['USERSN'] = $USERSN;
$GLOBALS['MAXTIME'] =$MAXTIME;
$GLOBALS['MINGROUPSIZE'] = $MINGROUPSIZE;
$GLOBALS['MAXGROUPSIZE'] = $MAXGROUPSIZE;
$GLOBALS['MAXWAIT'] = $MAXWAIT;
$GLOBALS['activities'] = $activities;
$GLOBALS['preferences'] = $preferences;
$GLOBALS['distances'] = $distances;
$GLOBALS['CELLSN'] = $CELLSN;
$GLOBALS['GROUPSN'] = $GROUPSN;

//debug
// echo "Debugging:\n";
// echo "USERSN:".$USERSN."\n";
// echo "MAXTIME:".$MAXTIME."\n";
// echo 'MINGROUPSIZE:'.$MINGROUPSIZE."\n";
// echo 'MAXGROUPSIZE:'.$MAXGROUPSIZE."\n";
// echo 'MAXWAIT:'.$MAXWAIT."\n";
// echo 'CELLSN:'.$CELLSN."\n";
// echo 'GROUPSN:'.$GROUPSN."\n";
// echo "activities[1][1]:\n";
// var_dump($activities[1][1]);
// echo "preferences[1][1]:\n";
// var_dump($preferences[1][1]);
// echo "distances[1][2]:\n";
// var_dump($distances[1][2]);


}


class GroupMeProblem extends Annealer {

  var $state = array();


/**
*
*$state = {
*  phase1(0): {
*           STATE_ACT(0): {
*               1:acti,
*               2:acti,
*               3:actj, 
*               ...
*           },
*           STATE_START(1): {
*               1:12:30,
*               2:12:30,
*               3:13:30, 
*               ...
*           },
*      },
* phase2(1): ...
*}
*
*
**/

  public function __construct($init_state){
    $this->state = $init_state;
  }

  function delete_activity($user,$phase){
   // echo "*****************************************\n";
  //var_dump($this->state[$phase]);
//echo "-------------------------------------------------\n";
   unset($this->state[$phase][STATE_ACT][$user]); 
 //var_dump($this->state[$phase]);   
   unset($this->state[$phase][STATE_START][$user]);
 }

  //check if exists activity overlap for a user
  function overlap($user,$activity,$start,$phase){

    //the start of next actvity should be suitable for the previous one, considering also distance.
    for ($i=$phase+1; $i < MAXSEQUENCE; $i++) { 
      //var_dump($this->state);
      $timeForTheNextActivity = $start + $activities[$phase][$activity][ACTIVITY_DURATION] + $distances[$activities[$phase][$activity][ACTIVITY_CELL]][$activities[$i][$this->state[$i][STATE_ACT][$user]][ACTIVITY_CELL]];
      //echo "\n state act:".STATE_ACT." state start:".STATE_START." user:".$user." timeForTheNextActivity:".$timeForTheNextActivity."\n";
      //var_dump($this->state);
      if (array_key_exists($user, $this->state[$i][STATE_ACT])) {
          if($timeForTheNextActivity > $this->state[$i][STATE_START][$user]) 
          return $i;
      }
    }
  
    //the start of last activity should be suitable for this one, considering also distance.
    for ($i=0; $i < $phase; $i++) { 
        $first = $activities[$i][$this->state[$i][STATE_ACT][$user]][ACTIVITY_CELL];
        $second = $activities[$phase][$activity][ACTIVITY_CELL];
        $timeForTheNextActivity = $this->state[$i][STATE_START][$user] + $activities[$i][$this->state[$i][STATE_ACT][$user]][ACTIVITY_DURATION] + $distances[$first][$second];
        if (array_key_exists($user, $this->state[$i][STATE_ACT]) && $timeForTheNextActivity > $start) {
          return $i;
        }
    }
    return NULL;  
  }

//Calculates the metric function.
 function energy(){
  $USERSN = $GLOBALS['USERSN'];
  $preferences = $GLOBALS['preferences'];
  $activities = $GLOBALS['activities'];

  $metric = 0;
  //for those users that have been assign an activity
  for ($i=0; $i < MAXSEQUENCE; $i++) { 
    foreach ($this->state[$i][STATE_ACT] as $j => $value) {
      $k = $activities[$i][$this->state[$i][STATE_ACT][$j]][ACTIVITY_TYPE];
      $metric -= $preferences[$j][$k];
      //echo "minus \n";
    }

  }
  # -20 if a user is not assigned to an activity
  for ($i=0; $i < MAXSEQUENCE; $i++){ 
    for ($j=0; $j < $USERSN; $j++){
      if(!array_key_exists($j, $this->state[$i][STATE_ACT])){
        $metric += 20;
        //echo "plus \n";
      }
    }
  }

  // echo "\n*************************************\n";
  // var_dump($this->state);
  //  echo "\n*************** $metric $s **********************\n";


// if ($s == 10) {
//   die("a");
// }
   
 // echo "energy $metric \n";
  return $metric;
}

function move(){
      #add an activity to the system
      #an activity should have a positive weight

  $USERSN = $GLOBALS['USERSN'];

  $MINGROUPSIZE = $GLOBALS['MINGROUPSIZE'];
  $MAXGROUPSIZE = $GLOBALS['MAXGROUPSIZE'];
  $preferences = $GLOBALS['preferences'];
  $activities = $GLOBALS['activities'];

  //move only one activity phase
  $phase = rand(0, MAXSEQUENCE-1);
  $gain = 0;
  while ($gain == 0) {

    //choose an activity from this phase
    $activity = rand(0,count($activities[$phase])-1);

    //choose set of users that will be influenced
    $n = rand($MINGROUPSIZE,$MAXGROUPSIZE);
    //$usersnarr = range(0,$USERSN);
    $users = array_rand(range(0,$USERSN-1), $n);


    foreach ($users as $keyi => $i) {
      $gain += $preferences[$i][$activities[$phase][$activity][ACTIVITY_TYPE]];

      //??? calculate activity starting time, very strange!!!  ???
      $intern = $activities[$phase][$activity][ACTIVITY_END] - $activities[$phase][$activity][ACTIVITY_DURATION];
      $starting_time = rand($activities[$phase][$activity][ACTIVITY_START],$intern);

      # set chosen activity for every user
      # possible deleting incompatible ones
      foreach ($users as $keyj => $j) {
        $this->state[$phase][STATE_ACT][$j] = $activity;
        $this->state[$phase][STATE_START][$j] = $starting_time;
        //get overlap
        // $phase_to_del = $this->overlap($j,$activity,$starting_time,$phase);
        // while($phase_to_del != NULL){
        //   $this->delete_activity($j,$phase_to_del);
        //   $phase_to_del = $this->overlap($j,$activity,$starting_time,$phase);
        // }
          # check if MINGROUPSIZE constraint is not satisfied
          # if so action are deleted
          //$act_map = {
          //  phase1: {
          //      activity1: {1,3,4, ...},
          //      activity2: {2,5,8, ...},
          //      },
          //  phase2: ...
          //}
          //
          
          //map user activities
          // $act_map = array();
          // for($m = 0; $m < MAXSEQUENCE; $m++){
          //   $act_map[$m] = array();
          //   foreach ($this->state[$m][STATE_ACT] as $n => $nact) {
          //    // $actOfUserJInState = $this->state[$i][STATE_ACT][$n];
          //     if(array_key_exists($nact,$act_map[$m]))
          //       $act_map[$m][$nact][] = $n;
          //     else
          //       $act_map[$m][$nact] = array($n);
          //     }
          //   }

          // //var_dump($act_map);
          // //check user activity
          // for($p = 0; $p < MAXSEQUENCE; $p++){
          //       foreach($act_map[$p] as $q => $qusrs) {
          //           if(count($qusrs) < $MINGROUPSIZE){
          //             foreach($act_map[$p][$q] as $qk => $qu){
          //               $this->delete_activity($qk,$p);
          //             }
          //             unset($act_map[$p][$q]);
          //           }
          //       }
          // }
                #try to reassign activites if possible
                #select the best among available
          // for($t = 0; $t < MAXSEQUENCE; $t++){
          //     for($s = 0; $s < $USERSN; $s++){
          //       //echo "111\n";
          //       if(!array_key_exists($s, $this->state[$t][STATE_ACT])){
          //         //echo "222\n";
                  
          //         foreach ($act_map[$t] as $amAct => $amUsers) {

          //           if (count($amUsers) == $MAXGROUPSIZE) {
          //             unset($act_map[$t][$amAct]);
          //           }else{
          //             $third = $this->state[$t][STATE_START][$act_map[$t][$amAct][0]];
          //             if (!$this->overlap($s,$amAct,$third,$t)) {
          //               //echo "lalalalal \n";
          //               $act_map[$t][$amAct][] = $s;
          //               $this->state[$t][STATE_ACT][$s] = $amAct;
          //               $this->state[$t][STATE_START][$s] = $this->state[$t][STATE_START][$act_map[$t][$amAct][0]];
          //               break;
          //             }
          //           }
          //         }
          //       }
          //     }
          //   }
          
        }
      }
    }
  }
}//end classe


# generate an state where no activity is assigned to users
function generate_inital_state(){
  $init_state = array();
  for($i = 0; $i < MAXSEQUENCE; $i++){
    $init_state[$i] = array();
    $init_state[$i][STATE_START] =array();
    $init_state[$i][STATE_ACT] = array();
  }
  return $init_state;
}

function print_state($state){
  $USERSN = $GLOBALS['USERSN'];
  $preferences = $GLOBALS['preferences'];
  $activities = $GLOBALS['activities'];

  
  for($i = 0; $i < $USERSN; $i++){
    echo "User ".$i.": \n";
    $weight = 0;
    $prev_act = NULL;
    $prev_phase = NULL;
    for($j = 0; $j < MAXSEQUENCE; $j++){
      //should be key in array - tong
      if (array_key_exists($i, $state[$j][STATE_ACT])) {
        $act = $state[$j][STATE_ACT][$i];
        $start = $state[$j][STATE_START][$i];
        $tmpidAct = $activities[$j][$act][ACTIVITY_TYPE];

        $weight += $preferences[$i][$tmpidAct];

        echo "\tactivity ".$act." from ".$start." to ".($start + $activities[$j][$act][ACTIVITY_DURATION]);
        if ($prev_act != NULL) {
          echo "\ttraveling distance ".$distances[$activities[$prev_phase][$prev_act][ACTIVITY_CELL]][$activities[$j][$act][ACTIVITY_CELL]];
        }else{
          echo "";
          $prev_act = $act;
          $prev_phase = $j;
          echo "\t\tPreferences: " + $weight;
        }
          # code...
      }
    }
  }
}


$best_solution = NULL;
$best_value = PHP_INT_MAX;
$metric = array();
for($rep = 0; $rep < 1; $rep++){
  srand((double)microtime()*1000000);

  echo "Start iteration ".$rep."\n";

  #init_state = generate_best_state()
  #problem = GroupMeProblem(init_state)
  #print "Initial metric value: " + str(problem.energy())    
  #print_state(init_state)
  #exit(0)
  
  $init_state = generate_inital_state();
  $problem = new GroupMeProblem($init_state);
  
  $problem->updates = 60;   # Number of updates (by default an update prints to stdout)

  #print "Trying to find automatic schedule",
  #auto_schedule = problem.auto(minutes=1)
  #print "Scedule for annealing",
  #print auto_schedule
  #problem.set_schedule(auto_schedule)

  $problem->Tmax = 2;  # Max (starting) temperature
  $problem->Tmin = 1;     # Min (ending) temperature
  $problem->steps = 5;   # Number of iterations
  //$problem->$steps = 100000;   # Number of iterations
  
  //solution, metric[rep] = problem->anneal()
  $rlt = $problem->anneal();
  $solution =  $rlt[0];
  $metric[$rep] =  $rlt[1];
  if($metric[$rep] < $best_value){
    //echo " \n  xx \n";
    $best_value = $metric[$rep];
    $best_solution = $solution;
  }
}
print_state($best_solution);

echo "\n";
echo "Best solution metric function value: ".$best_value."\n";
echo "Metric vector: \n";
var_dump($metric);


?>