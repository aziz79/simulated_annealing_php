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


$DZNINSTANCE = '../test/2_phases_simple_instance.dzn';

$USERSN = NULL;
#MAXTYPE = 0
$CELLSN = 0;
$MAXTIME = NULL;
$MINGROUPSIZE = NULL;
$MAXGROUPSIZE = NULL;
$GROUPSN = 0;
$MAXWAIT = NULL;

# number of phases
$MAXSEQUENCE = NULL;


// $ACTIVITY_CELL = 0;
// $ACTIVITY_START = 1;
// $ACTIVITY_END = 2;
// $ACTIVITY_DURATION = 3;
// $ACTIVITY_TYPE = 4;
#ACTIVITY_MINGROUP = 5
#ACTIVITY_MAXGROUP = 6

$activities = array();
$preferences = array();
$distances = array();


dzn_import($DZNINSTANCE);

# !!! NOT MUCH AGREED!!!
# Create random generator for actions in a way that the
# probablity of chosing that action depends on the sum of 
# the preferences divided by the duration
// $act_generator = array();
// for ($i=0;$i<$MAXSEQUENCE; $i++){

//  $act_weight = array();
//  $sum_weights = 0;
//  foreach ($activities[$i] as $act => $values) {
//    $s = 0;
//    for ($j=0;$j<$USERSN; $j++){
//        // $s += $preferences[$j][$values[$ACTIVITY_TYPE]];
//      $s += $preferences[$j][$activities[$i][$act][$ACTIVITY_TYPE]];
//    }

//    $act_weight[$act] = floatval($s) / $activities[$i][$act][$ACTIVITY_DURATION];
//    $sum_weights += $act_weight[$act];
//  }

//  $probabilities = array();
//  for ($j=0;$j<count($activities[$i]); $j++){
//    $probabilities[$j] = $act_weight[$j] / $sum_weights;
//  }
//    //echo $probabilities[10]." \n";
//    //$act_generator[$i] = $stats.rv_discrete( values=( np.arange(len(activities[i])), probabilities ) );
// }







$best_solution = NULL;
$best_value = PHP_INT_MAX;
$metric = array();
for($rep = 0; $rep < 1; $rep++){
  srand((double)microtime()*1000000);

  echo " Start iteration ".$rep;

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

  $problem->$Tmax = 2;  # Max (starting) temperature
  $problem->$Tmin = 1;     # Min (ending) temperature
  $problem->$steps = 100;   # Number of iterations
  //$problem->$steps = 100000;   # Number of iterations
  
  //solution, metric[rep] = problem->anneal()
  $rlt = $problem->anneal();
  $solution =  $rlt[0];
  $value =  $rlt[1];
  if($metric[$rep] < $best_value){
    $best_value = $metric[$rep]
    $best_solution = $solution;
  }
}
print_state($best_solution);

echo "";
echo "Best solution metric function value: ".$best_value;
echo "Metric vector: " . var_dump($metric);


function dzn_import($file_path){

  $USERSN = $GLOBALS['USERSN'];
  $MAXTIME = $GLOBALS['MAXTIME'];
  $MINGROUPSIZE = $GLOBALS['MINGROUPSIZE'];
  $MAXGROUPSIZE = $GLOBALS['MAXGROUPSIZE'];
  $MAXWAIT = $GLOBALS['MAXWAIT'];
  $ACTIVITY_CELL = $GLOBALS['ACTIVITY_CELL'];
  $ACTIVITY_START = $GLOBALS['ACTIVITY_START'];
  $ACTIVITY_END = $GLOBALS['ACTIVITY_END'];
  $ACTIVITY_DURATION = $GLOBALS['ACTIVITY_DURATION'];
  $ACTIVITY_TYPE = $GLOBALS['ACTIVITY_TYPE'];
  $activities = $GLOBALS['activities'];
  $preferences = $GLOBALS['preferences'];
  $distances =  $GLOBALS['distances'];
  $MAXSEQUENCE =  $GLOBALS['MAXSEQUENCE'];
  $CELLSN =  $GLOBALS['CELLSN'];
  $GROUPSN =  $GLOBALS['GROUPSN'];
  $MAXSEQUENCE = 2;
  for ($i=0;$i<$MAXSEQUENCE; $i++){
   $activities[$i] = array();
 }
  // echo count($activities)."la";
 $lines = file($file_path);
 foreach ($lines as $line_num => $line) {
  $splitted = split(" = ", $line);
  $content = $splitted[1];
  $lss = str_replace(";", "", $splitted[1]);
  $ls = split("\.", $lss);
  if ($splitted[0] == "user_ids") {
    $USERSN = intval($ls[2]);
      // echo "string '$USERSN' \n";
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
      // echo "string '$MINGROUPSIZE' \n ";
  }else if ($splitted[0] == "max_group_size") {
    $MAXGROUPSIZE = intval($ls[0]);
       //echo "string '$MAXGROUPSIZE' \n ";
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
      // $last1 = count($preferences);
      // $last2 = count($preferences[$last1-1]);
      // echo "C: ".$csplitted[1]." and \n".$preferences[$last1-1][$last2-12]."\n";
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
      //echo "la ".$activities[0][0][3]." ".$activities[0][1][2]." la\n";
      //$activities[0]
      # code...
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
     // echo "la ".$activities[1][0][3]." ".$activities[1][1][2]." la\n";
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
$GLOBALS['ACTIVITY_CELL'] = $ACTIVITY_CELL;
$GLOBALS['ACTIVITY_START'] = $ACTIVITY_START;
$GLOBALS['ACTIVITY_END'] = $ACTIVITY_END;
$GLOBALS['ACTIVITY_DURATION'] = $ACTIVITY_DURATION;
$GLOBALS['ACTIVITY_TYPE'] = $ACTIVITY_TYPE;
$GLOBALS['activities'] = $activities;
$GLOBALS['preferences'] = $preferences;
$GLOBALS['distances'] = $distances;
$GLOBALS['MAXSEQUENCE'] = $MAXSEQUENCE;
$GLOBALS['CELLSN'] = $CELLSN;
$GLOBALS['GROUPSN'] = $GROUPSN;
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

  // public function __construct(){
  //      $this->strvar = "changed in constructor";
  //  }

  public function delete_activity($user,$phase){
   unset($this->state[$phase][STATE_ACT][$user]);     
   unset($this->state[$phase][STATE_START][$user]);
 }

//check if exists activity overlap for a user
 public function overlap($user,$activity,$start,$phase){
  for ($i=$phase+1; $i < $MAXSEQUENCE; $i++) { 
    $cmpVal = $start + $activities[$phase][$activity][$ACTIVITY_DURATION] + $distances[$activities[$phase][$activity][$ACTIVITY_CELL]][$activities[$i][$this->state[$i][STATE_ACT][$user]][$ACTIVITY_CELL]];
    if (array_key_exists($user, $this->state[$i][STATE_ACT]) && $cmpVal > $this->$tate[$i][STATE_START][$user]) {
      return $i;
    }
  }
  for ($i=0; $i < $phase; $i++) { 
    $first = $activities[$i][$this->state[$i]STATE_ACT][$user]][$ACTIVITY_CELL];
    $second = $activities[$phase][$activity][$ACTIVITY_CELL];
    $cmpVal = $this->state[$i][STATE_START][$user] + $activities[$i][$this->state[$i][STATE_ACT][$user]][$ACTIVITY_DURATION] + $distances[$first][$second];
    if (array_key_exists($user, $this->state[$i][STATE_ACT]) && $cmpVal > $start) {
      return $i;
    }
  }
  return NULL;  
}

//Calculates the metric function.
public function energy($self){
  $metric = 0;
  //for those users that have been assign an activity
  for ($i=0; $i < $MAXSEQUENCE; $i++) { 
    foreach ($this->state[$i][STATE_ACT] as $j => $value) {
      $k = $activities[$i][$this->state[$i][STATE_ACT][$j]][$ACTIVITY_TYPE];
      $metric -= $preferences[$j][$k];
    }

  }
  # -20 if a user is not assigned to an activity
  for ($i=0; $i < $MAXSEQUENCE; $i++){ 
    for ($j=0; $j < $USERSN; $j++){
      if(!array_key_exists($j, $this->state[$i][STATE_ACT])){
        $metric += 20;
      }
    }
  }
  return $metric;
}

function move(){
      #add an activity to the system
      #an activity should have a positive weight

  //move only one activity phase
  $phase = rand(0, $MAXSEQUENCE-1);
  $gain = 0;
  while ($gain == 0) {

    //choose an activity from this phase
    $activity = rand(0,count($activities[$phase])-1);

    //choose set of users that will be influenced
    $n = rand($MINGROUPSIZE,$MAXGROUPSIZE);
    $usersnarr = range(0,$USERSN);
    $users = array_rand($usersnarr, $n);


    foreach ($users as $keyi => $i) {
      $gain += $preferences[$i][$activities[$phase][$activity][$ACTIVITY_TYPE]];

      //??? calculate activity starting time, very strange!!!  ???
      $intern = $activities[$phase][$activity][$ACTIVITY_END] - $activities[$phase][$activity][$ACTIVITY_DURATION];
      $starting_time = rand($activities[$phase][$activity][$ACTIVITY_START],$intern);

      # set chosen activity for every user
      # possible deleting incompatible ones
      foreach ($users as $keyj => $j) {
        $this->state[$phase][STATE_ACT][$i] = $activity;
        $this->state[$phase][STATE_START][$i] = $starting_time;
        //get overlap
        $phase_to_del = $this->overlap($i,$activity,$starting_time,$phase);
        while($phase_to_del != NULL){
          $this->delete_activity($i,$phase_to_del);
          $phase_to_del = $this->overlap($i,$activity,$starting_time,$phase);

          # check if MINGROUPSIZE constraint is not satisfied
          # if so action are deleted
          //$act_map = {
          //  phase1: {
          //      activityForUserX1: {1,3,4, ...},
          //      activityForUserX2: {1,3,4, ...},
          //      },
          //  phase2: ...
          //}
          //
          
          //map user activities
          $act_map = array();
          for($i = 0; $i < $MAXSEQUENCE; $i++){
            $act_map[$i] = array();
            foreach ($this->state[$i][STATE_ACT] as $j => $values) {
              $actOfUserJInState = $this->state[$i][STATE_ACT][$j];
              if(array_rand($act_map[$i], $actOfUserJInState))
                $act_map[$i][$actOfUserJInState][] = $j;
              else
                $act_map[$i][$actOfUserJInState] = array($j);
              }
            }

          //check user activity
          foreach($act_map[$i] as $j => $values) {
              if(count($act_map[$i][$j]) < $MINGROUPSIZE){
                foreach($act_map[$i][$j] as $key => $k){
                  $this->delete_activity($k,$i);
                  unset($act_map[$i][$j]);
                }
              }
          }
                #try to reassign activites if possible
                #select the best among available
          for($i = 0; $i < $MAXSEQUENCE; $i++){
              for($j = 0; $j < $USERSN; $j++){
                if(array_key_exists($i, $this->state[$i][STATE_ACT])){
                  foreach ($act_map[$i] as $k => $value) {
                    if (count($act_map[$i][$k]) == $MAXGROUPSIZE) {
                      unset($act_map[$i][$k]);
                    }else{
                      $third = $this->state[$i][STATE_START][$act_map[$i][$k][0]];
                      if (!$this->overlap($j,$k,$third,$i)) {
                        $act_map[$i][$k][] = $j;
                        $this->state[$i][STATE_ACT][$j] = $k;
                        $this->state[$i][STATE_START][$j] = $this->state[$i][STATE_START][$act_map[$i][$k][0]];
                        break;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}//end classe


# generate an state where no activity is assigned to users
function generate_inital_state(){
  $init_state = array();
  for($i = 0; $i < $MAXSEQUENCE; $i++){
    $init_state[$i] = array();
    $init_state[$i][STATE_START] =array();
    $init_state[$i][STATE_ACT] = array();
  }
  return $init_state;
}

function print_state($state){
  for($i = 0; $i < $USERSN; $i++){
    echo "User ".str(i).":";
    $weight = 0;
    $prev_act = NULL;
    $prev_phase = NULL;
    for($j = 0; $j < $MAXSEQUENCE; $j++){
      //should be key in array - tong
      if (array_key_exists($i, $state[$j][STATE_ACT])) {
        $act = $state[$j][STATE_ACT][$i];
        $start = $state[$j][STATE_START][$i];
        $weight += $preferences[($i,$activities[$j][$act][$ACTIVITY_TYPE])];
        echo "\tactivity ".$act." from ".$start." to ".($start + $activities[$j][$act][$ACTIVITY_DURATION]);
        if ($prev_act != NULL) {
          echo "\ttraveling distance ".$distances[$activities[$prev_phase][$prev_act][$ACTIVITY_CELL]][$activities[$j][$act][$ACTIVITY_CELL]];
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

// function generate_best_state(){
//   $group_act_map = array();
//   $group_act_map[0] = array(965, 831, 511);
//   $group_act_map[1] = array(826, 641, 893);

//   $user_group_map = array();
//   $user_group_map[0] = array(1, 1, 3, 2, 3, 2, 2, 3, 1, 2);
//   $user_group_map[1] = array(1, 1, 2, 3, 2, 3, 3, 2, 1, 3);

//   $user_start_time_map = array();
//   $user_start_time_map[0] = array(4, 4, 8, 17, 8, 17, 17, 8, 4, 17);
//   $user_start_time_map[1] = array(31, 31, 28, 38, 28, 38, 38, 28, 31, 38);
  
//   $init_state = array();
//   for($i = 0; $i < $MAXSEQUENCE; $i++){
//     $init_state[$i] = array();
//     $init_state[$i][STATE_START] = array();
//     $init_state[$i][STATE_ACT] = array();
//     for($j = 0; $j < $USERSN; $j++){
//       $group = $user_group_map[$i][$j] - 1;
//       $init_state[$i][STATE_ACT][$j] = $group_act_map[$i][$group] - 1;
//       $init_state[$i][STATE_START][$j] = $user_start_time_map[$i][$group] - 1;
//     }
//   }
//   return $init_state;
// }

?>