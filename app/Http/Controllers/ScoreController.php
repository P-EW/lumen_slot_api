<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    private $reel1 = array(5, 3, 2, 1, 2, 0, 2, 4, 2, 1, 2, 0, 2, 4, 2, 1, 2, 0, 2, 4, 2, 1);
    private $reel2 = array(5, 2, 4, 0, 4, 1, 4, 3, 4, 0, 4, 1, 4, 3, 4, 0, 4, 1, 4, 3, 4, 0);
    private $reel3 = array(5, 2, 3, 1, 3, 2, 3, 1, 3, 2, 3, 4, 3, 1, 3, 2, 3, 1, 3, 2, 3, 1);

    private $loosedRoll = array("Eh...you can't always win", 'Sucks to be you !', 'Maybe you should try again', 'Sometimes you get it, sometimes you get got.', 'You won nothing !', 'Casino funding !');

    public function index(){
        //TODO retourner le top
        return Score::all();
    }

    public function roll(){
        $roll1 = rand(0,21);
        $roll2 = rand(0,21);
        $roll3 = rand(0,21);

        $spinResult = array($this->reel1[$roll1], $this->reel2[$roll2], $this->reel3[$roll3]);
        $rollResult = $this->getRollResult($spinResult);
        $rollResult[0]--; //decrement cost of game

        //retrieve user
        $userId = auth()->user()->id;
        //update in database
        $score = Score::query()->where('id_user', $userId)->first();

        if($this->array_count_values_of(4,$spinResult) == 3){
            $score->jackpot_hits++;
        }
        $score->credits = $score->credits + +$rollResult[0];
        $score->played_rolls = $score->played_rolls + 1;
        $score->save();

        //Score::query()->where('id_user', $userId)->update(array('credits'=> $tempscore['credits']+$rollResult[0] , 'played_rolls' => $tempscore['played_rolls']++, 'jackpot_hits' => $tempscore['jackpot_hits']));
        //$score->update(array('credits'=> $score['credits']+$rollResult[0] , 'played_rolls' => $score['played_rolls']++, 'jackpot_hits' => $score['jackpot_hits']));

        return response()->json(['statusCode' => 200, 'score' => $rollResult[0], 'sentence' => $rollResult[1], 'roll' => $spinResult]);
    }

    public function freeroll(){
        $roll1 = rand(0,21);
        $roll2 = rand(0,21);
        $roll3 = rand(0,21);

        $spinResult = array($this->reel1[$roll1], $this->reel2[$roll2], $this->reel3[$roll3]);
        $rollResult = $this->getRollResult($spinResult);
        $rollResult[0]--; //decrement cost of game

        return response()->json(['statusCode' => 200, 'score' => $rollResult[0], 'sentence' => $rollResult[1], 'roll' => $spinResult]);
    }

    private function getRollResult($spinResult){
        // check the winnings and set a funny text
        if($this->array_count_values_of(5,$spinResult) > 0){
            //diamond win
            switch ($this->array_count_values_of(5,$spinResult)) {
                case 1:
                    return array(2, 'Congratulation, x2 !');
                case 2:
                    return array(5, 'Shine bright ! x5 !');
                case 3:
                    return array(500, 'Epic win ! x500 !');
            }
        }
        elseif ($this->array_count_values_of(4,$spinResult) == 3) {
            // Dollar win
            return array(1000, 'JACKPOT !!!!!! x1000 !!');
        }
        elseif ($this->array_count_values_of(3,$spinResult) == 3) {
            // Bell win
            return array(250, 'Wow ! Big Win ! x250 !');
        }
        elseif ($this->array_count_values_of(2,$spinResult) == 3) {
            // Grape win
            return array(100, 'Grappe that ! x100 !');
        }
        elseif ($this->array_count_values_of(1,$spinResult) == 3) {
            // Orange win
            return array(75, 'Nice one ! x75 !');
        }
        elseif ($this->array_count_values_of(0,$spinResult) == 3) {
            // Cherry win
            return array(50, 'You just popped the cherry ! x50 !');
        }
        //lost roll
        return array(0, $this->loosedRoll[rand(0,(count($this->loosedRoll)-1))]);
    }

    private function array_count_values_of($value, $array) {
        $counts = array_count_values($array);
        return array_key_exists($value, $counts) ? $counts[$value] : 0;
    }
}
