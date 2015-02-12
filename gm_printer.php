<?php

function checkGlobalOverlapByState($state){

    $activities = $GLOBALS['activities'];
    $distances = $GLOBALS['distances'];
    for ($phaseB=1; $phaseB < MAXSEQUENCE; $phaseB++) { 

      $users = $state[$phaseB][STATE_ACT];
      $phaseA = $phaseB - 1;
      foreach ($users as $user => $act) {
        $actOfPhaseA = $state[$phaseA][STATE_ACT][$user];
        $actOfPhaseB = $act;

        $actASource = $activities[$phaseA][$actOfPhaseA];
        $actBSource = $activities[$phaseB][$actOfPhaseB];

        $startA = $state[$phaseA][STATE_START][$user];
        $startB = $state[$phaseB][STATE_START][$user];
        $intervalAB = $startA + $actASource[ACTIVITY_DURATION] + $distances[$actASource[ACTIVITY_CELL]][$actBSource[ACTIVITY_CELL]];
        

        // echo "\n =====================  \n ";
        // echo "act A($actOfPhaseA) start: {$startA} \n";
        // echo "act A($actOfPhaseA) duration: {$actASource[ACTIVITY_DURATION]} \n";
        // echo "A B distance:".$distances[$actASource[ACTIVITY_CELL]][$actBSource[ACTIVITY_CELL]]."\n";
        // echo "intervalAB: $intervalAB \n";
        // echo "act B($actOfPhaseB) start: ".$startB."\n";
        // echo "\n =====================  \n ";
        // }

        // if ($intervalAB > $startB ) {
        //   echo "(;_;) Time constraint violated for user $user, activity conflict $actOfPhaseA and $actOfPhaseB, $intervalAB is bigger than $startB \n";
        //   die();
        // }
        if ($intervalAB > $startB ) {
            return FALSE;
        }else{
          return  TRUE;
        }

      }
    }
  }


function print_state($state){
  $USERSN = $GLOBALS['USERSN'];
  $distances = $GLOBALS['distances'];
  $activities = $GLOBALS['activities'];

  $USERSN = $GLOBALS['USERSN'];
  $GROUPSN = $GLOBALS['GROUPSN'];
  $MAXGROUPSIZE = $GLOBALS['MAXGROUPSIZE'];
  $MINGROUPSIZE = $GLOBALS['MINGROUPSIZE'];
  
  echo "\n---------- Solution -----------\n";
  for($i = 0; $i < $USERSN; $i++){
    echo "\nUser ".$i.": ";
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
          echo "\ttraveling distance  ".$distances[$activities[$prev_phase][$prev_act][ACTIVITY_CELL]][$activities[$j][$act][ACTIVITY_CELL]];
        }else{
          echo "";
          $prev_act = $act;
          $prev_phase = $j;
          echo "\t\tPreferences: " + $weight;
        }
      }
    }
  }

  echo "\n\n---------- Act_Map -----------\n\n";

  $actMap = generate_act_map_from_state($state);
  for ($i=0; $i < MAXSEQUENCE; $i++) { 
    $groupCount = 0;
    $userCount = 0;
    echo "Phase $i: \n";
    $phaseActs = $actMap[$i];
    foreach ($phaseActs as $act => $actUsers) {
      echo "Acivity:$act done by ".count($actUsers)." users \n";
      $groupCount++;
      $userCount += count($actUsers);
    }
    echo "total groups: $groupCount, total users: $userCount\n";
  }
 
  echo "\n\n---------- Global Overlap -----------\n\n";
  if(checkGlobalOverlapByState($state)){
    echo "Passed! \n";
  }else{
    echo "Failed! \n";
  }

  echo "\n---------- Requirments -----------\n";
  echo "\nPeople:$USERSN  Groups:$GROUPSN  GroupMax:$MAXGROUPSIZE  GroupMin:$MINGROUPSIZE \n";

}



?>