<?php

namespace App\Http\Controllers;

use App\Test;

use App\Activity;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Firebase\JWT\JWT;


class TestController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth',
        ['except'=>['index']]);
    }

    /**
     *Todas las pruebas creadasy le adjunto el user creador de cada test.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tests=Test::All()->load('user');
        return response()->json([
            'code'=>200,
            'status'=>'success',
            'tests'=>$tests
        ],200);
    }

    /**
     * Obtiene el user logueado, Crea un Test de Herrmann en blanco,
     * y se lo asigna a ese user logueado.
     */
    public function createHerrmann(Request $request){

        $token=$request->header('Authorization');
        $jwtAuth= new \JwtAuth();
        $user=$jwtAuth->checkToken($token,true);

        $test= new Test();
        $test->name="Test De Herrmann";
        $test->type="AUTOCONOCIMIENTO";
        $test->state=0;
        $test->user_id=$user->sub;
        $test->save();
        $data=[
            'code'=>200,
            'status'=>'success',
            'messagge'=>'Has creado Un test Herrmann para resolver:Exitos '
        ];
        return response()->json($data,$data['code']);  
    }

    public function interpretTest($id_test){
        $test=Test::where('id',$id_test)->get();
        	
        // $array = array('A'=>0, 'B'=>0,'C'=>0,'D'=>0);
        // $activities=$test->activities;
        $activities= Activity::where('test_id',$id_test)->get();

        if(count($activities)==3)
        {
            //en cada posición guarda las secciones de cada actividad
            $sections=array();
            $cont=0;
            foreach( $activities as $activity){        
                $sections[$cont]=$activity->sections;
                $cont++;
            }
            //se guarda en variables todas las secciones de cada actividad
            $sectionsA1=$sections[0];
            $sectionsA2=$sections[1];
            $sectionsA3=$sections[2];
            
            //Se saca cada sección por separado
            $sectionA1=$sectionsA1['0'];//SeccionA de la actividad 1...
            $sectionB1=$sectionsA1['1'];
            $sectionC1=$sectionsA1['2'];
            $sectionD1=$sectionsA1['3'];

            $sectionA2=$sectionsA2['0'];
            $sectionB2=$sectionsA2['1'];
            $sectionC2=$sectionsA2['2'];
            $sectionD2=$sectionsA2['3'];

            $sectionA3=$sectionsA3['0'];
            $sectionB3=$sectionsA3['1'];
            $sectionC3=$sectionsA3['2'];
            $sectionD3=$sectionsA3['3'];

            $scores=array(
                'A'=>$sectionA1->pivot->score+$sectionA2->pivot->score+$sectionA3->pivot->score,
                'B'=>$sectionB1->pivot->score+$sectionB2->pivot->score+$sectionB3->pivot->score,
                'C'=>$sectionC1->pivot->score+$sectionC2->pivot->score+$sectionC3->pivot->score,
                'D'=>$sectionD1->pivot->score+$sectionD2->pivot->score+$sectionD3->pivot->score
            );
            $data=[
                'code'=>200,
                'status'=>'succes',
                'messagge'=>"Test Analizado Correctamente",
                'scors'=>$scores
            ];
        }else{      
            $data=[
                'code'=>404,
                'status'=>'error',
                'messagge'=>"Esta Prueba No ha completado las actividades Requeridas"
            ];
        }
        
        return  response()->json($data,$data['code']);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     public function store(Request $request)
    {   
    }*/

    /**
     * Display the specified resource.
     *
     * @param  \App\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function show(Test $test)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Test $test)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function destroy(Test $test)
    {
        //
    }
}
