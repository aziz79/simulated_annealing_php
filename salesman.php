<?php

require_once("lib/sim.php");

function distance($a, $b){
   // """Calculates distance between two latitude-longitude coordinates."""
    $R = 3963;  # radius of Earth (miles)
    $lat1 =deg2rad($a[0]);
    $lon1 = deg2rad($a[1]);
    $lat2 = deg2rad($b[0]);
    $lon2 = deg2rad($b[1]);
    return acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon1 - $lon2)) * $R;
}

class TravellingSalesmanProblem extends Annealer{

    // """Test annealer with a travelling salesman problem.
    // """

    var $distance_matrix = array();
    
    # pass extra data (the distance matrix) into the constructor
    public function __construct($state, $distance_matrix){
        $this->distance_matrix = $distance_matrix;

        //parent::test($state);
        parent::__construct($state);

        

        // var_dump($this->state);
        // $this->move();
        // var_dump($this->state);

        //$ener  = $this->energy();
        //print "$ener \n";

        //super(TravellingSalesmanProblem, self).__init__(state)  # important! 
    }

    // def __init__(self, state, distance_matrix):
    //     self.distance_matrix = distance_matrix
    //     super(TravellingSalesmanProblem, self).__init__(state)  # important! 

    function move(){
       // """Swaps two cities in the route."""
        // $a = randint(0, count($this->state) - 1);
        // $b = randint(0, count($this->state) - 1);
        $a = rand(0, count($this->state)-1);
        $b = rand(0, count($this->state)-1);
        $swap = $this->state[$a];
        $this->state[$a] = $this->state[$b];
        $this->state[$b] = $swap;
    }
        

    function energy(){
        //"""Calculates the length of the route."""
        $e = 0;
        for($i = 0; $i < count($this->state); $i++){
            $e += $this->distance_matrix[$this->state[$i-1]][$this->state[$i]];
        }
        return $e;
    }

}

//if __name__ == '__main__':

    # latitude and longitude for the twenty largest U.S. cities
    $cities = array(
        'New York City'=> array(40.72, 74.00),
        'Los Angeles'=> array(34.05, 118.25),
        'Chicago'=> array(41.88, 87.63),
        'Houston'=> array(29.77, 95.38),
        'Phoenix'=> array(33.45, 112.07),
        'Philadelphia'=> array(39.95, 75.17),
        'San Antonio'=> array(29.53, 98.47),
        'Dallas'=> array(32.78, 96.80),
        'San Diego'=> array(32.78, 117.15),
        'San Jose'=> array(37.30, 121.87),
        'Detroit'=> array(42.33, 83.05),
        'San Francisco' => array(37.78, 122.42),
        'Jacksonville'=> array(30.32, 81.70),
        'Indianapolis'=> array(39.78, 86.15),
        'Austin'=> array(30.27, 97.77),
        'Columbus'=> array(39.98, 82.98),
        'Fort Worth'=> array(32.75, 97.33),
        'Charlotte'=> array(35.23, 80.85),
        'Memphis'=> array(35.12, 89.97),
        'Baltimore'=> array(39.28, 76.62)
    );

    # initial state, a randomly-ordered itinerary
    //$init_state = list(array_keys($cities));
    $init_state = array_keys($cities);
    shuffle($init_state);

    
    // # create a distance matrix
    $distance_matrix = array();
    foreach ($cities as $ka => $va) {
        $distance_matrix[$ka] = array();
        foreach ($cities as $kb => $vb) {
            if($kb == $ka)
                $distance_matrix[$ka][$kb] = 0.0;
            else
                $distance_matrix[$ka][$kb] = distance($va, $vb);
        }
    }
    //var_dump($distance_matrix);

    $tsp = new TravellingSalesmanProblem($init_state, $distance_matrix);
    // # since our state is just a list, slice is the fastest way to copy
    //$tsp->copy_strategy = "slice"  
    $rlt = $tsp->anneal();
    // $splitted = split(";", $rlt);
    // $state = $rlt[0];
    // $e = $rlt[1];
    $state = $rlt[0];
    $energy = $rlt[1];
    while($state[0] != 'New York City'){
        $len = count($state);
        array_push($state, array_shift($state));
        //$state = state[1:] + state[:1]  # rotate NYC to start
    }
    echo("\n$energy mile route: \n");
    foreach ($state as $key => $value) {
         echo "".$value."\n";
    }
?>