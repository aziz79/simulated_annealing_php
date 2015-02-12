<?php


/***
* Array structor used:
* 
* * $state = {
*    phase1(0): {
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
*    phase2(1): ...
*  }
*
*  // Array Exampe
*  $act_map = {
*    phase1: {
*        activity1: {1,3,4, ...},
*        activity2: {2,5,8, ...},
*        },
*    phase2: ...
*  }
*
*/

require_once 'lib/sim.php';
require_once 'gm_printer.php';
require_once 'gm_reader.php';

/**
* activities1 is films
* activities2 is restaurants
**/

define("DEVELOPER_MODE", 1);

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




class GroupMeProblem extends Annealer {

  var $state = array();

  public function __construct($init_state){
    $this->state = $init_state;
    //Activate it to track ,,,
    if (DEVELOPER_MODE) {
      srand(32423423);
    }
  }

  //delete activity of a user in a specific phase
  function delete_activity($user,$phase){
   unset($this->state[$phase][STATE_ACT][$user]); 
   unset($this->state[$phase][STATE_START][$user]);
 }

  //check if exists activity overlap for a user
  function overlap($user,$activity,$start,$phase){
    $activities = $GLOBALS['activities'];
    $distances = $GLOBALS['distances'];
    for ($i=$phase+1; $i < MAXSEQUENCE; $i++) { 
      
      $timeForTheNextActivity = $start + $activities[$phase][$activity][ACTIVITY_DURATION] + $distances[$activities[$phase][$activity][ACTIVITY_CELL]][$activities[$i][$this->state[$i][STATE_ACT][$user]][ACTIVITY_CELL]];

      if (array_key_exists($user, $this->state[$i][STATE_ACT])) {
          if($timeForTheNextActivity > $this->state[$i][STATE_START][$user]) {
          return $i;
        }
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
    }

  }
  # -20 if a user is not assigned to an activity
  for ($i=0; $i < MAXSEQUENCE; $i++){ 
    for ($j=0; $j < $USERSN; $j++){
      if(!array_key_exists($j, $this->state[$i][STATE_ACT])){
        $metric += 20;
        // echo "plus \n";
      }
    }
  }
  return $metric;
}

  /************************************/
  /**  ---------    MOVE  ---------- **/
  /************************************/

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
  $activity;

  /**-----------------------------------------**/
  /**  randomly choose ACT and set of USERS **/
  /**-----------------------------------------**/
  while ($gain == 0) {

    //randomly choose an activity from this phase
    $activity = rand(0,count($activities[$phase])-1);

    //choose set of users that will be influenced
    $j = rand($MINGROUPSIZE,$MAXGROUPSIZE);

    $users = array_rand(range(0,$USERSN-1), $j);

    foreach ($users as $keyi => $i) {
      $gain += $preferences[$i][$activities[$phase][$activity][ACTIVITY_TYPE]];
    }
  }
  
  //calculate activity starting time(for example resaurants)
  $intern = $activities[$phase][$activity][ACTIVITY_END] - $activities[$phase][$activity][ACTIVITY_DURATION];
  $starting_time = rand($activities[$phase][$activity][ACTIVITY_START],$intern);

  # set chosen activity for every user
  # possible deleting incompatible ones
  foreach ($users as $keyj => $i) {

      $this->state[$phase][STATE_ACT][$i] = $activity;
      $this->state[$phase][STATE_START][$i] = $starting_time;

      $phase_to_del = $this->overlap($i,$activity,$starting_time,$phase);

      while($phase_to_del !== NULL){
          $this->delete_activity($i,$phase_to_del);
          $phase_to_del = $this->overlap($i,$activity,$starting_time,$phase);
      }
  }

  /**-------------------------------------------------*/
  /**  create Action Map and check group constraints **/
  /**-------------------------------------------------*/

  //map user activities, checked
  $act_map = generate_act_map_from_state($this->state);

  # check if MINGROUPSIZE constraint is not satisfied
  # if so action are deleted
  for($i = 0; $i < MAXSEQUENCE; $i++){
        foreach($act_map[$i] as $act => $actUsers) {
            if(count($actUsers) < $MINGROUPSIZE){
              foreach($act_map[$i][$act] as $act2 => $act2user){
                $this->delete_activity($act2user,$i);
              }
              unset($act_map[$i][$act]);
            }
        }
  }

  /**--------------------------------------------**/
  /**  Assign activities to the rest of users **/
  /**--------------------------------------------**/

  #try to reassign activites if possible
  #select the best among available
  for($i = 0; $i < MAXSEQUENCE; $i++){
      for($j = 0; $j < $USERSN; $j++){
        if(!array_key_exists($j, $this->state[$i][STATE_ACT])){

          foreach ($act_map[$i] as $act => $actUsers) {
            if (count($actUsers) == $MAXGROUPSIZE) {
              unset($act_map[$i][$act]);//cancella dalla mappa, cosi' non associamo quest'azione
            }else{
              $actStartTime = $this->state[$i][STATE_START][$actUsers[0]];
              if ($this->overlap($j,$act,$actStartTime,$i) === NULL) {
                $actUsers[] = $j;
                $this->state[$i][STATE_ACT][$j] = $act;
                $this->state[$i][STATE_START][$j] = $this->state[$i][STATE_START][$actUsers[0]];
                break;
              }
            }
          }
        }
      }
    }   

  }//end function move
}//end classe GROUPME problem


function generate_act_map_from_state($state){
  $act_map = array();
  for($i = 0; $i < MAXSEQUENCE; $i++){
    $act_map[$i] = array();
    foreach ($state[$i][STATE_ACT] as $j => $jact) {
      if(array_key_exists($jact,$act_map[$i]))
        $act_map[$i][$jact][] = $j;
      else
        $act_map[$i][$jact] = array($j);
    }
  }
  return $act_map;
}

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


/************************************/
/**  ---------    MAIN  ---------- **/
/************************************/

dzn_import($DZNINSTANCE);
$best_solution = NULL;
$best_value = PHP_INT_MAX;
$metric = array();
//for($rep = 0; $rep < 3; $rep++){
  srand((double)microtime()*1000000);

  echo "Start iteration ".$rep."\n";

  $init_state = generate_inital_state();
  $problem = new GroupMeProblem($init_state);
  
  $problem->updates = 60;   # Number of updates (by default an update prints to stdout)
  $problem->Tmax = 2;  # Max (starting) temperature
  $problem->Tmin = 1;     # Min (ending) temperature
  $problem->steps = 10000;   # Number of iterations
  //$problem->steps = 100000;   # Number of iterations


  $rlt = $problem->anneal();
  $solution =  $rlt[0];
  $metric[$rep] =  $rlt[1];
  if($metric[$rep] < $best_value){
    $best_value = $metric[$rep];
    $best_solution = $solution;
  }
  print_state($best_solution);

  echo "\n";
  echo "Best solution metric function value: ".$best_value."\n\n";
 // }

 // var_dump($metric)

?>