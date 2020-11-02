<?php

namespace App\helpers;

use Firebase\JWT\JWT;
use Iluminate\Support\Facades\DB;
use App\User;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class JwtAuth {

    public $key;

    public function __construct() {
        $this->key = 'clave_super_duper_requetecontra_ulta_dificil_alpha_bravo_omega-007';
    }

    public function login($email, $contrasena, $getToken = null) {

        //INICIO BUSCAR SI EXISTE USUARIO
        $usuario = User::where([
                    'email' => $email,
                    'contrasena' => $contrasena
                ])->first();
        //FIN BUSCAR SI EXISTE USUARIO
        
        //INICIO COMPROBACION 
        $login = false;
        $data = array(
                'status' => null,
                'nombre' => null
            );
        
        if (is_object($usuario)) {
            $login = true;
        }
        //FIN COMPROBACION
        //
        //INICIO GENERAR TOKEN
        if ($login == true) {
            $token = array(
                'sub' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'iat' => time(),
                'exp' => time() + (60 * 60),
                'status' => null
            );
             
            //INICIO TOKEN DECODIFICADO Y CODIFICADO
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
            //FIN TOKEN DECODIFICADO Y CODIFICADO
        } else {
            $data = array(
                'status' => 'error',
                'mensaje' => 'El Login fallo ya que los datos son incorrectos.',
                'nombre' => null
            );
        }
        return $data;
        //FIN GENERAR TOKEN
    }

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

}
