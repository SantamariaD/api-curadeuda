<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use App\Pokemon;
use App\historial;

class GeneralController extends Controller
{
    
    //INICIO METODO DE REGISTRO
    public function registro(Request $request){
        
        //INICIO RECOGER DATOS DEL USUARIO POR POST
        $json = $request->input('json', null);
        $parametros_objeto = json_decode($json);
        $parametros_array = json_decode($json, true);
        //FIN RECOGER DATOS DEL USUARIO POR POST

        if (!empty($parametros_array && $parametros_objeto)) {
            $parametros_array = array_map('trim', $parametros_array);
            //INICIO VALIDAR DATOS DEL USUARIO
            
            $validador = \Validator::make($parametros_array, [
                        'nombre' => 'required',
                        'email' => 'required|email|unique:usuarios', //COMPROBAR USUARIO DUPLICADO
                        'contrasena' => 'required|min:5'
            ]);
            
            //FIN VALIDAR DATOS DEL USUARIO
                
            if ($validador->fails()) {
                //MENSAJE DE ERROR
                $datos = array(
                    'status' => "error",
                    'codigo' => 404,
                    'mensaje' => 'El usuario no se creo',
                    'errores' => $validador->errors()
                );
            } else {
                //INICIO CIFRAR LA CONTRASEÑA
                $contra = hash('sha256', $parametros_objeto->contrasena);
                //FIN CIFRAR LA CONTRASEÑA

                //INICIO CREAR EL USUARIO
                    $usuario = new User();
                    $usuario->nombre = $parametros_array['nombre'];
                    $usuario->email = $parametros_array['email'];
                    $usuario->contrasena = $contra;


                    //GUARDAR USUARIO
                    $usuario->save();

                //FIN CREAR EL USUARIO

                //MENSAJE DE AGREGADO
                $datos = array(
                    'status' => "Agregado",
                    'codigo' => 200,
                    'mensaje' => 'El usuario se ha creado correctamente',
                    'usuario' => $usuario
                );
            }
        } else {
            //MENSAJE DE ERROR
            $datos = array(
                'status' => "error",
                'codigo' => 500,
                'mensaje' => 'Los datos enviados no son correctos'
            );
        }

        //RETORNAR MENSAJE
        return response()->json($datos);
        
    }
    //FIN METODO DE REGISTRO

    //INICION METODO PARA LOGUEO
    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        //INICIO RECOGER DATOS DEL USUARIO POR POST
        $json = $request->input('json', null);
        $parametros_objeto = json_decode($json);
        $parametros_array = json_decode($json, true);
        //FIN RECOGER DATOS DEL USUARIO POR POST
        
        //INICIO VALIDAR DATOS DEL USUARIO
        $validador = \Validator::make($parametros_array, [
                    'email' => 'required|email', //COMPROBAR USUARIO DUPLICADO
                    'contrasena' => 'required'
        ]);
        //FIN VALIDAR DATOS DEL USUARIO

        if ($validador->fails()) {
            //INICIO MENSAJE DE ERROR
            $login = array(
                'status' => "Error",
                'codigo' => 404,
                'mensaje' => 'El usuario no se ha podido loguear',
                'errores' => $validador->errors()
            );
            //FIN MENSAJE DE ERROR
        } else {

            //INICIO CIFRAR CONTRASEÑA
            $contras = hash('sha256', $parametros_objeto->contrasena);
            //FIN CIFRAR CONTRASEÑA

            //INICIO DEVOLVER EL TOKEN
            $login = $jwtAuth->login($parametros_objeto->email, $contras);

            if (!empty($parametros_objeto->gettoken)) {
                $login = $jwtAuth->login($parametros_objeto->email, $contras, true);
            }
            //FIN DEVOLVER EL TOKEN
        }

        return response()->json($login, 200);
    }
    //INICION METODO PARA LOGUEO

    //INICION METODO PARA COMPROBAR EL TOKEN
    public function checkToken($jwt, $getIdentify = false) {
        $autenticacion = false;

        try {
            $decodificado = JWT::decode($jwt, $this->key, ['HS256']);
        }  catch (\DomainException $e) {
            $autenticacion = false;
        }
        
        
        if (!empty($decodificado) && is_object($decodificado) && isset($decodificado->sub)) {
            $autenticacion = true;
        } else {
            $autenticacion = false;
        }


        if ($getIdentify) {
            return $decodificado;
        }
        return $autenticacion;
    }
    //FIN METODO PARA COMPROBAR EL TOKEN

    //INICIO METODO PARA SUBIR IMAGEN
    public function upload(Request $request) {
        //INICIO RECOGER DATOS DE LA PETICION
        $image = $request->file('file0');
        //FIN RECOGER DATOS DE LA PETICION

        //INICIO VALIDAR SI ES UNA IMAGEN
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        //FIN VALIDAR SI ES UNA IMAGEN

        //INICIO GUARDAR IMAGEN
        if ($image == null || $validate->fails()) {
            $data = array(
                'status' => "Error",
                'codigo' => 404,
                'mensaje' => 'No se ha subido imagen'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('pokemons')->put($image_name, \File::get($image));
            $data = array(
                'status' => "Correcto",
                'codigo' => 200,
                'image' => $image_name
            );
        }
        //FIN GUARDAR IMAGEN
        return response()->json($data, $data['codigo']);
    }
    //FIN METODO PARA SUBIR IMAGEN

    //INICIO TRAER NOMBRE DE IMAGEN POKEMON
    public function nombreImg($nombre){
        $pokemon = Pokemon::all();
        $parametros_objeto = json_decode($pokemon);
        for($i=0; $i<count($parametros_objeto);$i++){
            if($nombre == $parametros_objeto[$i]->nombre){
                $imagenPokemon=$parametros_objeto[$i]->imagen;
                $data = array(
                    'nombre' => $nombre,
                    'imagen' => $imagenPokemon
                );
                return $data;
            break;
            }
        }
    }
    //INICIO TRAER NOMBRE DE IMAGEN POKEMON

    //INICIO METODO PARA TRAER IMAGEN
    public function getImage($nombre) {

        $imgNombre= $this->nombreImg($nombre);
        $imagenPokemon=$imgNombre['imagen'];
        //INICIO BLOQUE PARA TRAER IMAGEN DEL STORAGE DEL BACKEND
        $isset = \Storage::disk('pokemons')->exists($imagenPokemon);
        if ($isset) {
            $file = \Storage::disk('pokemons')->get($imagenPokemon);
            return new Response($file, 200);
        } else {
            $data = array(
                'status' => "Error",
                'codigo' => 404,
                'mensaje' => 'La imagen no existe.'
            );
            return response()->json($data, $data['codigo']);
        }
        //FIN BLOQUE PARA TRAER IMAGEN DEL STORAGE DEL BACKEND
    }
    //FIN METODO PARA TRAER IMAGEN

    //INICIO METODO GUARDAR POKEMON
    public function guardarPokemon(Request $request){
        
        //INICIO RECOGER DATOS DEL POKEMON POR POST
        $json = $request->input('json', null);
        $parametros_objeto = json_decode($json);
        $parametros_array = json_decode($json, true);
        //FIN RECOGER DATOS DEL POKEMON POR POST

        if (!empty($parametros_array && $parametros_objeto)) {
            $parametros_array = array_map('trim', $parametros_array);
            //INICIO VALIDAR DATOS DEL POKEMON
            
            $validador = \Validator::make($parametros_array, [
                        'nombre' => 'required|unique:pokemons',
                        'imagen' => 'required'
            ]);
            
            //FIN VALIDAR DATOS DEL POKEMON
                
            if ($validador->fails()) {
                //MENSAJE DE ERROR
                $datos = array(
                    'status' => "error",
                    'codigo' => 404,
                    'mensaje' => 'El pokemon no se creo',
                    'errores' => $validador->errors()
                );
            } else {
                //INICIO CREAR EL POKEMON
                    $pokemon = new Pokemon();
                    $pokemon->nombre = $parametros_array['nombre'];
                    $pokemon->imagen = $parametros_array['imagen'];

                    //GUARDAR pokemon
                    $pokemon->save();

                //FIN CREAR EL POKEMON

                //MENSAJE DE AGREGADO
                $datos = array(
                    'status' => "agregado",
                    'codigo' => 200,
                    'mensaje' => 'El pokémon se ha creado correctamente',
                    'usuario' => $pokemon
                );
            }
        } else {
            //MENSAJE DE ERROR
            $datos = array(
                'status' => "error",
                'codigo' => 500,
                'mensaje' => 'Los datos enviados no son correctos'
            );
        }

        //RETORNAR MENSAJE
        return response()->json($datos);
        
    }
    //FIN METODO GUARDAR POKEMON

    //INICIO METODO PARA GUARDAR EL HISTORIAL
    public function historial(Request $request){
        
        //INICIO RECOGER DATOS DEL POKEMON POR POST
        $json = $request->input('json', null);
        $parametros_objeto = json_decode($json);
        $parametros_array = json_decode($json, true);
        //FIN RECOGER DATOS DEL POKEMON POR POST

        if (!empty($parametros_array && $parametros_objeto)) {
            $parametros_array = array_map('trim', $parametros_array);
            //INICIO VALIDAR DATOS DEL POKEMON
            
            $validador = \Validator::make($parametros_array, [
                        'nombre' => 'required'
            ]);
            
            //FIN VALIDAR DATOS DEL POKEMON
                
            if ($validador->fails()) {
                //MENSAJE DE ERROR
                $datos = array(
                    'status' => "error",
                    'codigo' => 404,
                    'mensaje' => 'No se guardo el historial',
                    'errores' => $validador->errors()
                );
            } else {
                //INICIO CREAR EL POKEMON
                    $pokemon = new historial();
                    $pokemon->nombre = $parametros_array['nombre'];

                    //GUARDAR pokemon
                    $pokemon->save();

                //FIN CREAR EL POKEMON

                //MENSAJE DE AGREGADO
                $datos = array(
                    'status' => "agregado",
                    'codigo' => 200,
                    'mensaje' => 'Se guardo el historial',
                    'usuario' => $pokemon
                );
            }
        } else {
            //MENSAJE DE ERROR
            $datos = array(
                'status' => "error",
                'codigo' => 500,
                'mensaje' => 'Los datos enviados no son correctos'
            );
        }

        //RETORNAR MENSAJE
        return response()->json($datos);  
    }
    //FIN METODO PARA GUARDAR EL HISTORIAL

    //INICIO METODO PARA TRAER EL HISTORIAL
    public function baseHistorial(){
        $historial = historial::all();
        $parametros_objeto = json_decode($historial);
        return response()->json($parametros_objeto);
    }
    //FIN METODO PARA TRAER EL HISTORIAL
}
