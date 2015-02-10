<?php

//$t = mt_rand() / mt_getrandmax();
// $t = (float)rand()/(float)getrandmax();
// echo "$t \n";

// $MAX = 20;

// class Val{
// 	var $x = 3;
// 	//$y = 6;
// 	function doit(){
// 		$USERSN = $GLOBALS['MAX'];
// 		return "(".$this->x." ... ".$USERSN.")";
// 		//return $y;
// 	}

// }


// echo "string\n";
// $v = new Val();
// $x = $v->doit();

// echo "$x\n";



// $vvv = rand(1,2);
// echo "\n $vvv \n";

// $input = array("Neo", "Morpheus", "Trinity", "Cypher", "Tank");
// $rand_keys = array_rand($input, 2);
// var_dump($rand_keys);

//var_dump(range(0,10));

 $users = array_rand(range(0,10-1), 3);

var_dump($users);

    foreach ($users as $keyi => $i) {

    	echo "\n key:$keyi v:$i \n";
    }

// echo $input[$rand_keys[0]] . "\n";
// echo $input[$rand_keys[1]] . "\n";


?>