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
        ['except'=>['index','getScores','setMessage']]);
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
        $test->interpretation='Not Interpreted Yet';
        $test->save();
        $data=[
            'code'=>200,
            'status'=>'success',
            'messagge'=>'Has creado Un test Herrmann para resolver:Exitos',
            'id_test_creado'=>$test->id
        ];
        return response()->json($data,$data['code']);  
    }

/**
 * Define los puntajes totales de cada actividad,
 * e interpreta esos puntajes en un mensaje 
 */
    public function interpretHerrmann($id_test){
        $activities= Activity::where('test_id',$id_test)->get();
        $scores=$this->getScores($activities);
        if(!empty($scores) && ($scores!=null))
        {
            //Actualizar el estado e interpretaciín del Test.
            $test=Test::where('id',$id_test)->update(['state' => 1]);
           $interpretation=$this->setMessage($scores);
           $test=Test::where('id',$id_test)->update(['interpretation' => $interpretation]);
            $data=[
                'code'=>200,
                'status'=>'succes',
                'messagge'=>"Test Analizado Correctamente",
                'scors'=>$scores,
                'interpretation'=>$interpretation
            ];
        }else{      
            $data=[
                'code'=>400,
                'status'=>'error',
                'messagge'=>"Esta Prueba No ha completado las actividades Suficientes"
            ];
        }
        return  response()->json($data,$data['code']);
    }

    /**
     * Saca el puntaje numerico de cada sección de cada actividad y los suma.
     */
    public function getScores($activities){
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
            return $scores;
        }
        return null;
    }


    /**
     * Interpreta los puntajes totales de cada sección para dar una interpretación textual a la 
     * prueba.
     */
    public function setMessage($scores){
        $A=$scores['A'];
        $B=$scores['B'];
        $C=$scores['C'];
        $D=$scores['D'];
        $message='';
        $messages=array(
        'A_Mayor'=>[
            'Dominance'=>'Dominancia A',
            'Nickname'=>'Técnico-Financiero',
            'Description'=>'Eres Lógico muy racional,basado en hechos y cuantitativo,se te dan bien los números,
            la abstracción y resolución de Probremas Lógicos',
            'Characteristics'=>'Generalmente frio, distante, de pocos gestos,individualmente brillante, competitivo',
            'Weakness'=>'Indivudialista'],
        'AB_Mayor'=>[
            'Dominance'=>'Dominancia A-B',
            'Nickname'=>'Legalista-Coordinador',
            'Description'=>'Eres realista y de sentido común,es decir,una persona analítica, verbal, secuencial 
                y controlada',
            'Characteristics'=>'Rigor, métodico, solidez en su forma de accionar, capacidad de análisis y de razonamiento',
            'Weakness'=> 'falta de imaginación'],
        'AD_Mayor'=>[
            'Dominance'=>'Dominancia A-D',
            'Nickname'=>'Pragmatico-Cerebral',
            'Description'=>'Eres lógico, resuelves problemas, matemático, técnico, analista y imaginativo, artístico, conceptualizador',
            'Characteristics'=>'Inteligencia vivaz, técnico de alto nivel, buen nivel de análisis y de síntesis',
            'Weakness'=>'Incapacidad de ver a tus semejantes y de percibir el efecto que produces en los demás'],  
        'AC_Mayor'=>[
            'Dominance'=>'Dominancia A-C',
            'Nickname'=>'Técnico-Servicial',
            'Description'=>'Combinas a la perfección la brillantes intelectual, con las competencias de trabajo en equipo',
            'Characteristics'=>'Lógico muy racional,basado en hechos con excelentes relaciones interpersonales y 
                con tendencia al dialogo',
            'Weakness'=>'Sueles hacer demasiadas concesiones. Lo cual te puede producir gran insatisfacción de fondo'],      
        'B_Mayor'=>[
            'Dominance'=>'Dominancia B',
            'Nickname'=>'Administrativo-Controlador',
            'Description'=>'Eres muy administrativo, con buen sentido de organización y puesta en marcha',
            'Characteristics'=>'Secuencial, persistente, estructurado, controlado,detallista,organizado, planeador,conservador.',
            'Weakness'=>'Falta de asumir riegos y aventurarse'],
        'BC_Mayor'=>[
            'Dominance'=>'Dominancia B-C',
            'Nickname'=>'Instintivo-Visceral',
            'Description'=>'Eres buen organizador, planeador, controlador, conservador y excelente en 
                temas administrativos pero también espiritual, humanitario, afectivo, emocional y disfruta del contacto con otros',
            'Characteristics'=>'Lúdico tendencia a trabajar en equipo y buen lider',
            'Weakness'=>'Te fías demasiado de tu instinto y te sules confundir,te falta de asertividad y 
                puedes llegar a caer en incontinencia verbal.'],
        'BD_Mayor'=>[
            'Dominance'=> 'Dominancia B-D',
            'Nickname'=>'Organizador-Creativo',
            'Description'=>'Eres buen organizador, planeador, controlador, conservador creativo en innovador',
            'Characteristics'=>'combina a la perfección el sentido de la organización y el método con la creatividad y la innovación',
            'Weakness'=>'puede llegar a paralizarse entre el deseo de innovar y el miedo al cambio.'],
        
        'C_Mayor'=>[
            'Dominance'=> 'Dominancia C',
            'Nickname'=>'Servicial-Sociable',
            'Description'=>'Tiendes a ser extrovertido y muy emotivo, tendencia al dialogo y trabajo en equipo',
            'Characteristics'=>'empático, receptivo, cooperativo, expresivo, sensible, espiritual,amigable,confiable,emocional, sentimental',
            'Weakness'=>'Tiende a ser muy susceptible y a reaccionar espontaneamente frente a críticas negativas'],
    
        'CD_Mayor'=>[
            'Dominance'=> 'Dominancia C-D',
            'Nickname'=>'Desarrollador-Expresivo',
            'Description'=>'Eres  intuitivo, visual, sintético, receptivo, imaginativo, impulsivo,futurista',
            'Characteristics'=>'Espontaneidad, innovación, afectividad y gusto por el riesgo',
            'Weakness'=>'Relaciones Incompatibles'],
        'D_Mayor'=>[
            'Dominance'=>'Dominancia D',
            'Nickname'=>'Artistico-Emprendedor',
            'Description'=>'Eres alguien soñador, creativo que encuentra soluciones rápidas 
            a los problemas,buscas alternativas por diversos caminos',
            'Characteristics'=>'Muy visual, intuitivo, creativo, imaginativo,explorador, aventurero, 
                rompe reglas, curiosos,tomador de riesgos, mente abierta, especulador',
            'Weakness'=>'Falta de acertividad'],

        'A_B_C_D'=>[
            'Dominance'=>'Multidominancia',
            'Nickname'=>'Multidominante-Comunicador',
            'Description'=>'Puedes desenvolverte en ámbitos muy variados, pero se te dificulta un tanto 
            la toma de decisiones']
        );

        if($A>$C && $A>$D && $B>$C && $B>$D){
            $message=$messages['AB_Mayor'];
        }elseif($A>$B && $A>$C && $D>$C && $D>B){
            $message=$messages['AD_Mayor'];
        }elseif($A>$B && $A>$D && $C>$D && $C>$B){
            $message=$messages['AC_Mayor'];  
        }elseif($A>$B && $A>$C && $A>$D){
            $message=$messages['A_Mayor'];
        }
     
        if($B>$D && $B>$A && $C>$A && $C>$D){
            $message=$messages['BC_Mayor'];
        }elseif($B>$C && $B>$A && $D>$C && $D>$A){
            $message=$messages['BD_Mayor'];
        }elseif($B>$A && $B>$C && $B>$D){
            $message=$messages['B_Mayor'];
        }
      
        if($C>$A && $C>$B  && $D>$A && $D>$B ){
            $message=$messages['CD_Mayor'];
        }elseif($C>$A && $C>$B && $C>$D){
            $message=$messages['C_Mayor'];
        }elseif($D>$A && $D>$B && $D>$C){
            $message=$messages['D_Mayor'];
        }elseif($A==$B && $B==$C && $C==$D){
            $message=$messages['A_B_C_D'];
        }
        return $message;
    }
   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
    {   
    }

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
