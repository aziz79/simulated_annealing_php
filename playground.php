<?php

//$t = mt_rand() / mt_getrandmax();
// $t = (float)rand()/(float)getrandmax();
// echo "$t \n";

$MAX = 20;

class Val{
	var $x = 3;
	//$y = 6;
	function doit(){
		$USERSN = $GLOBALS['MAX'];
		return "(".$this->x." ... ".$USERSN.")";
		//return $y;
	}

}


echo "string\n";
$v = new Val();
$x = $v->doit();

echo "$x\n";
?>