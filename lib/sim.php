<?php

// """Returns x rounded to n significant figures."""
function round_figures($x, $n){
  return round($x, intval($n - ceil(log10(abs($x)))));
}

// """Returns time in seconds as a string formatted HHHH:MM:SS."""
function time_string($seconds){
    $s = intval(round($seconds));  # round to nearest second

    $init = $s;
    $hours = floor($init / 3600);
    $minutes = floor(($init / 60) % 60);
    $seconds = $init % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    //return sprintf("%4i:%02i:%02i", $hours, $minutes, $seconds);
}

function random(){
    // auxiliary function
    // returns random number with flat distribution from 0 to 1
    return (float)rand()/(float)getrandmax();
}

//$ck = new Annealer("hallo","bello");

//$ck->modify();
//echo $ck->get();

class Annealer{

    //   """Performs simulated annealing by calling functions to calculate
    // energy and make moves on a state.  The temperature schedule for
    // annealing may be provided manually or estimated automatically.
    // """

    var $Tmax = 25000.0;
    var $Tmin = 2.5;
    var $steps = 50000;
    var $updates = 100;
    var $copy_strategy = 'deepcopy';

    //added
    var $state = array();
    var $start;

    public function __construct($initial_state){
        $this->state = $this->copy_state($initial_state);
    }

    function copy_state($state){
    // """Returns an exact copy of the provided state
    // Implemented according to this.copy_strategy, one of
    // * deepcopy : use copy.deepcopy (slow but reliable)
    // * slice: use list slices (faster but only works if state is list-like)
    // * method: use the state's copy() method
    // """

    //array copy issue may not present in php language
        if($this->copy_strategy == 'deepcopy'){
        //return copy.deepcopy(state)
          return $state;
      }
    // else if($this->copy_strategy == 'slice'){
    //     return state[:]
    // }
    // else if($this->copy_strategy == 'method'){
    //     return state.copy()
    // }
  }
  function update($step, $T, $E, $acceptance, $improvement){
        // """Prints the current temperature, energy, acceptance rate,
        // improvement rate, elapsed time, and remaining time.
        // The acceptance rate indicates the percentage of moves since the last
        // update that were accepted by the Metropolis algorithm.  It includes
        // moves that decreased the energy, moves that left the energy
        // unchanged, and moves that increased the energy yet were reached by
        // thermal excitation.
        // The improvement rate indicates the percentage of moves since the
        // last update that strictly decreased the energy.  At high
        // temperatures it will include both moves that improved the overall
        // state and moves that simply undid previously accepted moves that
        // increased the energy by thermal excititation.  At low temperatures
        // it will tend toward zero as the moves that can decrease the energy
        // are exhausted and moves that would increase the energy are no longer
        // thermally accessible."""

        // potential problem
      $elapsed = time() - $this->start;
      if($step == 0){
          //print(' Temperature        Energy    Accept   Improve     Elapsed   Remaining \n');
          //printf("\r%12.2f  %12.2f                      %s            ", $T, $E, time_string($elapsed));

          echo " Temperature        Energy    Accept   Improve     Elapsed   Remaining \n";
          $toPrint1 = sprintf("\r%12.2f  %12.2f                      %s            ", $T, $E, time_string($elapsed));
          echo "$toPrint1 \n";
      }
      else{
          $remain = ($this->steps - $step) * ($elapsed / $step);
          $toPrint2 = sprintf("\r%12.2f  %12.2f  %7.2f%%  %7.2f%%  %s  %s", $T, $E, 100.0 * $acceptance, 100.0 * $improvement,time_string($elapsed), time_string($remain));
          echo "$toPrint2 \n";
      }
  }
  function anneal(){
    // """Minimizes the energy of a system by simulated annealing.
    // Parameters
    // state : an initial arrangement of the system
    // Returns
    // (state, energy): the best state and energy found.
    // """
    $step = 0;
    $this->start = time();

    # Precompute factor for exponential cooling from Tmax to Tmin
    if($this->Tmin <= 0.0){
        die("Exponential cooling requires a minimum temperature greater than zero. \n");
    }
    //???
    $Tfactor = -log($this->Tmax / $this->Tmin);

    # Note initial state
    $T = $this->Tmax;
    $E = $this->energy();
    $prevState = $this->copy_state($this->state);
    $prevEnergy = $E;
    $bestState = $this->copy_state($this->state);
    $bestEnergy = $E;

    
    $trials = 0;//total executions
    $accepts = 0;//effective executions
    $improves = 0;//executions with improvement

    $updateWavelength = 0;

    //??? alway true?
    if($this->updates > 0){
        $updateWavelength = $this->steps / $this->updates;//??? never read?
        $this->update($step, $T, $E, NULL, NULL);
    }

    # Attempt moves to new states
    while($step < $this->steps){
        $step += 1;
        $T = $this->Tmax * exp($Tfactor * $step / $this->steps);
        $this->move();
        $E = $this->energy();
        $dE = $E - $prevEnergy;
        $trials += 1; //???

        //??? seems strange
        if($dE > 0.0 && exp(-$dE / $T) < random()){
            # Restore previous state
            $this->state = $this->copy_state($prevState);
            $E = $prevEnergy;
        }
        else{
            # Accept new state and compare to best state
            $accepts += 1;
            if($dE < 0.0){
                $improves += 1;
            }
            $prevState = $this->copy_state($this->state);
            $prevEnergy = $E;
            if($E < $bestEnergy){
                $bestState = $this->copy_state($this->state);
                $bestEnergy = $E;
            }
            if($this->updates > 1 && $updateWavelength != 0){
                if(floor($step / $updateWavelength) > floor(($step - 1) / $updateWavelength)){
                    $this->update($step, $T, $E, $accepts / $trials, $improves / $trials);
                    $trials = 0;
                    $accepts = 0;
                    $improves = 0;
                }
            }
        }
    }

    # line break after progress output
    //print('');

    # Return best state and energy
    return array($bestState,$bestEnergy);
}

    // function set_schedule($this, $schedule){
    // // """Takes the output from `auto` and sets the attributes
    // // """
    //     $this->Tmax = $schedule['tmax'];
    //     $this->Tmin = $schedule['tmin'];
    //     $this->steps =intval($schedule['steps']);
    // }
    // function auto($this, $minutes, $steps=2000){
    //         // """Minimizes the energy of a system by simulated annealing with
    //         // automatic selection of the temperature schedule.
    //         // Keyword arguments:
    //         // state -- an initial arrangement of the system
    //         // minutes -- time to spend annealing (after exploring temperatures)
    //         // steps -- number of steps to spend on each stage of exploration
    //         // Returns the best state and energy found."""

    //     function run($T, $steps){
    //             // """Anneals a system at constant temperature and returns the state,
    //             // energy, rate of acceptance, and rate of improvement."""
    //         $E = $this->energy();
    //         $prevState = $this->copy_state($this->state);
    //         $prevEnergy = $E;

    //         $accepts = 0;
    //         $improves = 0;
    //             //for step in range(steps):
    //         for ($step=0;$step<$steps; $step++){
    //             $this->move();
    //             $E = $this->energy();
    //             $dE = $E - $prevEnergy;
    //             if($dE > 0.0 && exp(-$dE / $T) < random()){
    //                 $this->state = $this->copy_state($prevState);
    //                 $E = $prevEnergy;
    //             }
    //             else{
    //                 $accepts += 1;
    //                 if($dE < 0.0)
    //                     $improves += 1;

    //                 $prevState = $this->copy_state($this->state);
    //                 $prevEnergy = $E;
    //             }
    //         }
    //         return "E;".float($accepts) / $steps.";". float($improves) / $steps;
    //             //return E, float(accepts) / steps, float(improves) / steps
    //     }
    //     $step = 0;
    //     $this->start = time();

    //         # Attempting automatic simulated anneal...
    //         # Find an initial guess for temperature
    //     $T = 0.0;
    //     $E = $this->energy();
    //     $this->update($step, $T, $E, NULL, NULL);
    //     while($T == 0.0){
    //         $step += 1;
    //         $this->move();
    //         $T = abs($this->energy() - $E);
    //     }
    //         # Search for Tmax - a temperature that gives 98% acceptance
    //     $composed = run($T, $steps);
    //     $splitted = split(";", $composed);
    //     $E = $splitted[0];
    //     $acceptance = $splitted[1];
    //     $improvement = $splitted[2];

    //     $step += $steps;
    //     while($acceptance > 0.98){
    //         $T = round_figures($T / 1.5, 2);
    //         $composed1 = run($T, $steps);
    //         $splitted1 = split(";", $composed1);
    //         $E  = $splitted1[0];
    //         $acceptance = $splitted1[1];
    //         $improvement  = $splitted1[2];
    //         $step += $steps;
    //         $this->update($step, $T, $E, $acceptance, $improvement);
    //     }
    //     while($acceptance < 0.98){
    //         $T = round_figures($T * 1.5, 2);
    //         $composed2 = run($T, $steps);
    //         $splitted2 = split(";", $composed2);
    //         $E  = $splitted2[0];
    //         $acceptance = $splitted2[1];
    //         $improvement  = $splitted2[2];
    //         $step += $steps;
    //         $this->update($step, $T, $E, $acceptance,$improvement);
    //     }
    //     $Tmax = $T;

    //     # Search for Tmin - a temperature that gives 0% improvement
    //     while($improvement > 0.0){
    //         $T = round_figures($T / 1.5, 2);
    //         $composed3 =  run($T, $steps);
    //         $splitted3 = split(";", $composed3);
    //         $E  = $splitted3[0];
    //         $acceptance = $splitted3[1];
    //         $improvement = $splitted3[2];
    //         $step += $steps;
    //         $this->update($step, $T, $E, $acceptance, $improvement);
    //     }
    //     $Tmin = $T;

    //         # Calculate anneal duration
    //     $elapsed = time() - $this->start;
    //     $duration = round_figures(intval(60.0 * $minutes * $step / $elapsed), 2);

    //         # Don't perform anneal, just return params
    //     return array('tmax'=> $Tmax, 'tmin'=> $Tmin, 'steps'=>$duration);
    // }

}
?>