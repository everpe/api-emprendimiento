<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Activity;
use App\Section;
use App\Test;
use App\User;
class PruebasController extends Controller
{
   

    public function probar(){
        $user = User::find(1);//este user se obtiene request
        $sectionA=Section::where('name','A')->first();
        $sectionB=Section::where('name','B')->first();
        $sectionC=Section::where('name','C')->first();
        $sectionD=Section::where('name','D')->first();
    
        // $test=new Test();
        // $test->name="Test Herrmann";
        // $test->type="Autoconocimiento";
        // $test->state=1;
        // $test->save();
        // $test->users()->attach($user,['interpretation'=>'']);
    
        // $activity=new Activity();
        // $activity->name="Seleccionar8";
        // $activity->test_id=1;
        // $activity->save();
        // $activity->sections()->attach( $sectionA,['score'=>7]);
        // $activity->sections()->attach( $sectionB,['score'=>1]);
        // $activity->sections()->attach( $sectionC,['score'=>4]);
        // $activity->sections()->attach( $sectionD,['score'=>6]);
        



        $test = Test::find(1);
        $activities=$test->activities;
    
        foreach( $activities as $activity){
            echo $activity->name." :".$activity->id."<br>"; 
            foreach ($activity->sections as $section) {
                echo $section->name."-".$section->pivot->score."<br>";
            }
        }
        return "si";
    }
  

}
