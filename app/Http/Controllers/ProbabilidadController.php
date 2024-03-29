<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProbabilidadController extends Controller
{
    public function index(Request $request, Animal $animal)
    {
        $idUser=Auth::user()->id;

        $foundidUser = DB::table('adopcion')
            ->where('usuarios_id_usuario', '=', $idUser)
            ->where('adoption_status','En proceso')
            ->get();
        //$foundidUser = Adoption::find($idUser); //Busco por id

        if($foundidUser->isEmpty()){

            $Pt=0;
            // Acceder al ID del animal seleccionado
            $animal_id = $request->input('animal_id');
            $animal = Animal::find($animal_id); //Busco por id


            // Acceder a los demás datos del formulario
            $p1 = $request->input('p1');
            if ($p1 == 'noe' || $p1== 'no'){
                $p1 = 0;
            }else{
                $p1 = 100;
            }


            $p2 = $request->input('p2');
            if ($p2 == 'noe' || $p2== 'si'){
                $p2 = 0;
            }else{
                $p2 = 100;
            }

            $p3 = $request->input('p3');
            if ($p3 == 'noe' || $p3== 'no'){
                $p3 = 0;
            }else{
                $p3 = 100;
            }

            $p4 = $request->input('p4');
            if ($p4 == 'noe' || $p4== 'no'){
                $p4 = 0;
            }else{
                $p4 = 100;
            }

            $p5 = $request->input('p5');
            if ($p5 == 'noe' || $p5== 'no'){
                $p5 = 0;
            }else{
                $p5 = 100;
            }

            $p6 = $request->input('p6');
            if ($p6 == 'noe' || $p6== 'no'){
                $p6 = 0;
            }else{
                $p6 = 100;
            }

            $p7 = $request->input('p7');
            if ($p7 == 'noe' || $p7== 'no'){
                $p7 = 0;
            }else{
                $p7 = 100;
            }

            $p8 = $request->input('p8');
            if ($p8 == 'noe' || $p8== 'no'){
                $p8 = 0;
            }else{
                $p8 = 100;
            }

            $p9 = $request->input('p9');
            if ($p9 == 'noe' || $p9== 'no'){
                $p9 = 0;
            }else{
                $p9 = 100;
            }

            $p10 = $request->input('p10');
            if ($p10 == 'noe' || $p10== 'no'){
                $p10 = 0;
            }else{
                $p10 = 100;
            }

            $Pt=($p1+$p2+$p3+$p4+$p5+$p6+$p7+$p8+$p9+$p10)/10;


            //***SUBIR DOCUMENTO***
            $request->validate([
                'adjunto' => 'required|mimes:pdf|max:2048',
            ]);

            $usuario = Auth::user(); // Obtener el cliente autenticado

            $documento = $request->file('adjunto');
            $nombreDocumento = time() . '_' . $documento->getClientOriginalName();
            $rutaDocumento = $documento->storeAs('public/documents/' .$nombreDocumento);
            $rutaDocumento=str_replace('public','storage',$rutaDocumento);
            //***FIN DOCUMENTO***


            $adoption= Adoption::create([
                //La introduce el admin al momento de aceptar la peticion 'fecha_adopcion' =>  'animal_age',
                'animal_adopcioncol' => $animal_id,
                'usuarios_id_usuario'=> Auth::user()->id,
                'img' => $animal->img,
                //Inserccion automaticamente 'created_at',
                // Inserccion automatica 'updated_at',
                'probabilidad' => $Pt,
                'adoption_status' =>"En proceso",
                'motivo' => $request->get('motivo'),
                'file' =>$rutaDocumento
                //'user_id' => auth()->id() ,//Trae el ID de quien se esta logueando COD2
            ]);

            //$adoption = Adoption::find($idUser);
            //***EVNIO DE CORREOS*****
            // Crear una instancia del controlador de correo

            $correo = new InformacionAdopcionMail(
                $adoption,
                'Solicitud Recibida',
                'emails.solicitud-adoption'
            );

            // Llamar al método buildEnvioSolicitud() para construir el correo de rechazo
            //$correo = $correoController->buildEnvioSolicitud();

            // Enviar el correo
            Mail::to($correo->adoption->users->email)->send($correo);
            //***FIN ENVIO DE CORREOS***

            //***ENVIO EMAIL ADMINISTRADORES***
            $adminEmails = User::where('roles_idroles', '1')->pluck('email')->toArray(); //Encontrar correos de admninistracion

            $correoAdmins = new InformacionAdopcionMail(
                $adoption,
                'Nueva solicitud de adopcion ',
                'emails.alertNuevaSolicitud-adoption'
            );
            Mail::to($adminEmails)->send($correoAdmins);
            //**FIN ENVIO DE CORREO**********

            //****ACTUALIZAR ESTADO EN TABLA ANIMALES EN ADOPCION****
            $status="Solicitado";
            DB::statement('CALL updateStatusAnimalList(?,?)',[$animal_id,$status]);
            //*** FIN ACTUALIZACION DE TABLA**************

            return  to_route('AdopcionUser.index')->with('status','Se ha enviado tu solicitud de adopcion');

        }else{
            return  redirect()->route('AdopcionUser.index')->with('statusred','No puedes tener dos adopciones en curso');
        }


    }



}
