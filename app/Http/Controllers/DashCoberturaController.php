<?php

namespace App\Http\Controllers;

use App\VistaVentas;
use App\PuntosVentas;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;

class DashCoberturaController extends Controller
{
    public $message = "houston tenemos un problema!";
    public $result = false;
    public $records = [];
    public $cdh=0;
    public $cdv=0;
    public $total=0;
    public $modelo="";
    public $semana=0;
    public $tmodelos=0;
    public $tmodelo=[];

    public function index()
    {
        try {
            $this->message = "Consulta realizada con exito";
            $this->result = true;
            //$this->records= VistaTop15ModelSellout::all();
        } catch (\Exception $e) {
            $this->message = env("APP_DEBUG") ? $e->getMessage() : "Error al consultar registros";
            $this->result = false;
        } finally {
            $response = [
            "message" => $this->message,
            "result" => $this->result,
            "records" => $this->records
            ];
            return response()->json($response);
        }
    }

    public function create()
    {
    }

    public function store(Request $request)
    {

    }

    public function show($id)
    {

    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }

    public function calcularCobertura(Request $request)
    {
 
 
        /**
          SELECT
          pdv.nombre,
          ifnull(sum(v.sellout),0) as sellout,
          ifnull(sum(v.inventory),0) as inventory,
          ifnull((select cp.cantidad from categoriasplantillas cp inner join plantillas p ON p.idcategoria_plantilla=cp.id where p.idpuntoventa=pdv.id),0) as plantilla
          FROM pdv.puntosventas AS pdv
          left JOIN pdv.vistaventas as v ON v.idpuntoventa= pdv.id and v.semana=51 and v.idmodelo=60
          where pdv.idsucursal=1
          group by pdv.id

         */   set_time_limit(300000);
            $modelos= $request->input('modelos');

            if($modelos==null){
                $modelos = DB::table('modelos')->select('id')->get();
                try {
                    $calculo1 = array();
                    $calculo2 = array();
                    $nombrem = array();
                    $this->semana= $request->input('semana');
                    $sucursal= $request->input('idsucursal');
                   // $modelos= $request->input('modelos');
                    $consulta="puntosventas.nombre as PDV, ";
                     for ($i = 0; $i < count($modelos); $i++) {
                        $modelo=$modelos[$i]->id;
                        $ultimo= count($modelos)-1;
                        $consulta.="sum(case when vistaventas.idmodelo=".$modelo." ) as sellout_".$modelo.", ";
                        $consulta.="sum(case when vistaventas.idmodelo=".$modelo." ) as inventory_".$modelo.", ";
                        $consulta.="(select cp.nombre from categoriasplantillas cp where cp.id=1) as plantilla_A_".$modelo.", ";
                        $consulta.="(select cp.cantidad from categoriasplantillas cp where cp.id=1) as Cantidadplantilla_A_".$modelo.", ";
                        $consulta.="(select cp.nombre from categoriasplantillas cp where cp.id=2) as plantilla_B_".$modelo.", ";
                        $consulta.="(select cp.cantidad from categoriasplantillas cp where cp.id=2) as Cantidadplantilla_B_".$modelo.", ";
                        $consulta.="(select cp.nombre from categoriasplantillas cp where cp.id=3) as plantilla_C_".$modelo.", ";
                        $consulta.="(select cp.cantidad from categoriasplantillas cp where cp.id=3) as Cantidadplantilla_C_".$modelo.", ";
                        if($ultimo==$i)
                            $consulta.="0 as xvender_".$modelo."";
                        else
                            $consulta.="0 as xvender_".$modelo.", ";  
                    }
                    $registros = PuntosVentas::selectRaw($consulta)->leftJoin('vistaventas', function ($join) {
                        $join->on('vistaventas.idpuntoventa', '=', 'puntosventas.id')->where('vistaventas.semana', '=', $this->semana);
                    })->where('puntosventas.idsucursal', $sucursal)->groupBy('puntosventas.id')->get();
                    
                    $temp= array();
                    for ($i = 0; $i < count($modelos); $i++) {
                        $cantidadDiasExhibicion=0;
                        $cantidadDiasVentas=0;
                        $cantidadTiendas=count($registros); 
                         foreach ($registros as $key => $tregistro) {                    
                            $modelo=$modelos[$i]->id;
                            if($tregistro['inventory_'.$modelo]>0 && $tregistro['sellout_'.$modelo]>0){
                                $tempDiasExhibicion= (($tregistro['sellout_'.$modelo] / $tregistro['inventory_'.$modelo])*7);
                                $tregistro['diasexhibicion_'.$modelo]=  $tempDiasExhibicion>0?'Si':'No';
                                if($tempDiasExhibicion>0){
                                    $cantidadDiasExhibicion++;
                                }
                            }
                            else{
                                $tregistro['diasexhibicion_'.$modelo]= 'No';
                            }
                            if($tregistro['inventory_'.$modelo]>1 && $tregistro['sellout_'.$modelo]>0){
                                $tempDiasCobertura = (($tregistro['sellout_'.$modelo] / $tregistro['inventory_'.$modelo])*7);
                                $tregistro['diasventas_'.$modelo]=      $tempDiasCobertura>1?'Si':'No';
                                if($tempDiasCobertura>1)
                                    $cantidadDiasVentas++;
                            }
                            else{
                                $tregistro['diasventas_'.$modelo]= 'No';
                            }
                            
                            $calculo1[$i]= round(($cantidadDiasExhibicion/$cantidadTiendas)*100,2);
                            $calculo2[$i]= round(($cantidadDiasVentas/$cantidadTiendas)*100,2);
                            $nombrem[$i]   =  DB::table('vistaventas')->where('idmodelo', $modelos[$i]->id)->value('modelo');
                           
                        array_push($temp, $tregistro);
                         
         
                        }
                     
                    }
                    //$coberturaDisponible= round(($cantidadDiasExhibicion/$cantidadTiendas)*100,2);
                //$coberturaVentas= round(($cantidadDiasVentas/$cantidadTiendas)*100,2);
              
     
     
                $this->cdh=$calculo1;
                $this->cdv=$calculo2;
                $this->tmodelos=count($modelos);
                $this->tmodelo=$nombrem;
                $this->message = "Consulta realizada con exito";
                $this->result = true;
                $this->records = $temp;
                } catch (Exception $e) {
                    $this->message = env("APP_DEBUG") ? $e->getMessage() : "Error al consultar registros";
                    $this->result = false;
                }finally{
                     $response = [
                "message" => $this->message,
                "result" => $this->result,
                "records" => $this->records,
                "cde" => $this->cdh,
                "cdv" => $this->cdv,
                "tmodelos"=>$this->tmodelos,
                "tmodelo"=>$this->tmodelo,
     
                ];
                }


            }else{
                try {
                    $calculo1 = array();
                    $calculo2 = array();
                    $nombrem = array();
                    $this->semana= $request->input('semana');
                    $sucursal= $request->input('idsucursal');
                   $modelos= $request->input('modelos');
                    $consulta="puntosventas.nombre as PDV, ";
                     for ($i = 0; $i < count($modelos); $i++) {
                         $modelo=$modelos[$i];
                        $ultimo= count($modelos)-1;
                        $consulta.="sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end) as sellout_".$modelo.", ";

                        $consulta.="sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.inventory else 0 end) as inventory_".$modelo.", ";

                        $consulta.="(select cp.nombre from categoriasplantillas cp where cp.id=1) as plantilla_A_".$modelo.", ";

                        $consulta.="(select cp.cantidad from categoriasplantillas cp where cp.id=1) as Cantidadplantilla_A_".$modelo.", ";


                        $consulta.="if(((select cp.cantidad from categoriasplantillas cp where cp.id=1) - sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end)) >=0, ((select cp.cantidad from categoriasplantillas cp where cp.id=1) - sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end)), 'wil')   as Comprar_A_".$modelo.", ";

                        $consulta.="(select cp.nombre from categoriasplantillas cp where cp.id=2) as plantilla_B_".$modelo.", ";

                        $consulta.="(select cp.cantidad from categoriasplantillas cp where cp.id=2) as Cantidadplantilla_B_".$modelo.", ";

                        $consulta.="if((select cp.cantidad from categoriasplantillas cp where cp.id=2)- sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end) >=0,(select cp.cantidad from categoriasplantillas cp where cp.id=2) - sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end),0)   as Comprar_B_".$modelo.", ";

                        $consulta.="(select cp.nombre from categoriasplantillas cp where cp.id=3) as plantilla_C_".$modelo.", ";

                        $consulta.="(select cp.cantidad from categoriasplantillas cp where cp.id=3) as Cantidadplantilla_C_".$modelo.", ";

                        $consulta.="if((select cp.cantidad from categoriasplantillas cp where cp.id=3)- sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end) >=0,(select cp.cantidad from categoriasplantillas cp where cp.id=3) - sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end),0)   as Comprar_C_".$modelo." ";
                        
                    }
                    $registros = PuntosVentas::selectRaw($consulta)->leftJoin('vistaventas', function ($join) {
                        $join->on('vistaventas.idpuntoventa', '=', 'puntosventas.id')->where('vistaventas.semana', '=', $this->semana);
                    })->where('puntosventas.idsucursal', $sucursal)->groupBy('puntosventas.id')->get();
                    $temp= array();
                    for ($i = 0; $i < count($modelos); $i++) {
                        $cantidadDiasExhibicion=0;
                        $cantidadDiasVentas=0;
                        $cantidadTiendas=count($registros); 
                         foreach ($registros as $key => $tregistro) {                    
                            $modelo=$modelos[$i];
                            if($tregistro['inventory_'.$modelo]>0 ){
                                $tempDiasExhibicion= (($tregistro['sellout_'.$modelo] / $tregistro['inventory_'.$modelo])*7);
                                 //$tregistro['diasexhibicion_'.$modelo]=  $tempDiasExhibicion>0?'Si':'No';
                                 //if($tempDiasExhibicion>0){
                                    $tregistro['diasexhibicion_'.$modelo]="Si";
                                 /*}else{
                                    $tregistro['diasexhibicion_'.$modelo]="Niet";
                                 }*/

                                if($tempDiasExhibicion>0){
                                    $cantidadDiasExhibicion++;
                                     
         
                                }
         
                                 
                            }
                            else{
                                $tregistro['diasexhibicion_'.$modelo]= 'No';
                            }
                            if($tregistro['inventory_'.$modelo]>1 ){
                                $tempDiasCobertura = (($tregistro['sellout_'.$modelo] / $tregistro['inventory_'.$modelo])*7);
                               // $tregistro['diasventas_'.$modelo]=      $tempDiasCobertura>1?'Si':'No';
                                //if ($tempDiasCobertura>1) {
                                    $tregistro['diasventas_'.$modelo]="Si";
                               /* }else{
                                    $tregistro['diasventas_'.$modelo]="NOT";
                                }*/
                                if($tempDiasCobertura>1)
                                    $cantidadDiasVentas++;
                                
                            }
                            else{
                                $tregistro['diasventas_'.$modelo]= 'No';
                            }
                            if($tregistro['plantilla_A_'.$modelo]>0){
                                $calculo = $tregistro['plantilla_A_'.$modelo] - $tregistro['inventory_'.$modelo];
                                if($calculo>0)
                                    $tregistro['xvender_'.$modelo]=$calculo;
                            }
                            $calculo1[$i]= round(($cantidadDiasExhibicion/$cantidadTiendas)*100,2);
                            $calculo2[$i]= round(($cantidadDiasVentas/$cantidadTiendas)*100,2);
                            $nombrem[$i]   =  DB::table('vistaventas')->where('idmodelo', $modelos[$i])->value('modelo');
                           
                        array_push($temp, $tregistro);
                         
         
                        }
                     
                    }
                    //$coberturaDisponible= round(($cantidadDiasExhibicion/$cantidadTiendas)*100,2);
                //$coberturaVentas= round(($cantidadDiasVentas/$cantidadTiendas)*100,2);
              
     
     
                $this->cdh=$calculo1;
                $this->cdv=$calculo2;
                $this->tmodelos=count($modelos);
                $this->tmodelo=$nombrem;
                $this->message = "Consulta realizada con exito";
                $this->result = true;
                $this->records = $temp;
                } catch (Exception $e) {
                    $this->message = env("APP_DEBUG") ? $e->getMessage() : "Error al consultar registros";
                    $this->result = false;
                }finally{
                     $response = [
                "message" => $this->message,
                "result" => $this->result,
                "records" => $this->records,
                "cde" => $this->cdh,
                "cdv" => $this->cdv,
                "tmodelos"=>$this->tmodelos,
                "tmodelo"=>$this->tmodelo,
     
                ];
                }
            }




            return response()->json($response);



        
        
        /*
        try {

            $this->semana= $request->input('semana');
            $sucursal= $request->input('idsucursal');
            $modelos= $request->input('modelos');
            $consulta="puntosventas.nombre as PDV, ";
            for ($i = 0; $i < count($modelos); $i++) {
                $modelo=$modelos[$i];
                $ultimo= count($modelos)-1;
                $consulta.="sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.sellout else 0 end) as sellout_".$modelo.", ";
                $consulta.="sum(case when vistaventas.idmodelo=".$modelo." then vistaventas.inventory else 0 end) as inventory_".$modelo.", ";
                $consulta.="ifnull((select cp.cantidad from categoriasplantillas cp inner join plantillas p ON p.idcategoria_plantilla=cp.id where p.idpuntoventa=puntosventas.id and p.idmodelo=".$modelo."),0) as plantilla_".$modelo.", ";
                $consulta.="0 as xvender_".$modelo.", ";
                $consulta.="case when vistaventas.idmodelo=".$modelo." then if(vistaventas.inventory>0, if(vistaventas.sellout>0,((vistaventas.sellout/vistaventas.inventory)*7),vistaventas.inventory),0) else 0 end as diasexhibicion_".$modelo.",";
                if($ultimo==$i)
                    $consulta.="case when vistaventas.idmodelo=".$modelo." then if(vistaventas.inventory>1, if(vistaventas.sellout>0,((vistaventas.sellout/vistaventas.inventory)*7),vistaventas.inventory),0) else 0 end as diasventas_".$modelo;
                else
                    $consulta.="case when vistaventas.idmodelo=".$modelo." then if(vistaventas.inventory>1, if(vistaventas.sellout>0,((vistaventas.sellout/vistaventas.inventory)*7),vistaventas.inventory),0) else 0 end as diasventas_".$modelo.", ";

            }
            $registros = PuntosVentas::
            selectRaw($consulta)
                ->leftJoin('vistaventas', function ($join) {
                    $join->on('vistaventas.idpuntoventa', '=', 'puntosventas.id')
                        ->where('vistaventas.semana', '=', $this->semana);
                })
                ->where('puntosventas.idsucursal', $sucursal)
                ->groupBy('puntosventas.id')
                ->get();

            $this->message = "Consulta realizada con exito";
            $this->result = true;
            $this->records = $registros;
        } catch (\Exception $e) {
            $this->message = env("APP_DEBUG") ? $e->getMessage() : "Error al consultar registros";
            $this->result = false;
        } finally {
            $response = [
                "message" => $this->message,
                "result" => $this->result,
                "records" => $this->records
            ];
            return response()->json($response);
        }
        */

    }

    public function exportarexcel()
    {
        try {
            \Excel::create('Reporte', function ($excel) {

                $excel->sheet('reporte', function ($sheet) {

                    $sheet->loadView('reportecobertura');

                });

            })->export('xls');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}