<?php


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
  $splitted = split("=", $line);
  $arg = trim($splitted[0]);
  $content = trim($splitted[1]);
  $lss = str_replace(";", "", $content);
  $ls = split("\.", $lss);
  if ($arg == "user_ids") {
    $USERSN = intval($ls[2]);
  }else if ($arg == "activity1_ids") {
    $ACTIVITIES1N = intval($ls[2]);
  }else if ($arg == "activity2_ids") {
    $ACTIVITIES2N = intval($ls[2]);
  }else if ($arg == "cell_ids") {
    $CELLSN = intval($ls[2]);
  }else if ($arg == "group_ids") {
    $GROUPSN = intval($ls[2]);
  }else if ($arg == "type_ids") {
    $iAXTYPE = intval($ls[2]);
  }else if ($arg == "time_slot_ids") {
    $MAXTIME = intval($ls[2]);
  }else if ($arg == "min_group_size") {
    $MINGROUPSIZE = intval($ls[0]);
  }else if ($arg == "max_group_size") {
    $MAXGROUPSIZE = intval($ls[0]);
  }else if ($arg == "max_wait") {
    $MAXWAIT = intval($ls[0]);
  }else if ($arg == "preferences") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        $tmparr[] = trim($pieces[$j]);
      }
      $preferences[] = $tmparr;
    }
  }else if ($arg == "activities1") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        $tmparr[] = trim($pieces[$j]);
      }
      $activities[0][] = $tmparr;
    }
  }else if ($arg == "activities2") {
    $csplitted = split("\|", $content);
    for ($i=1; $i < count($csplitted) -1; $i++) { 
      $pieces = split(",", $csplitted[$i]);
      $tmparr = array();
      for ($j=0; $j < count($pieces); $j++) { 
        $tmparr[] = trim($pieces[$j]);
      }
      $activities[1][] = $tmparr;
    }
  }else if ($arg == "distances") {
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

?>