<?php

namespace Model;

// Modelo Tabla Usuario
class Usuario extends ActiveRecord {
    
    public static $tabla = 'usuario';
    public static $columnasDB = [
        'usuario_nom1',
        'usuario_nom2', 
        'usuario_ape1',
        'usuario_ape2',
        'usuario_tel',
        'usuario_direc',
        'usuario_dpi',
        'usuario_correo',
        'usuario_contra',
        'usuario_token',
        'usuario_fotografia',
        'usuario_situacion',
        'rol_id'
    ];

    public static $idTabla = 'usuario_id';
    
    public $usuario_id;
    public $usuario_nom1;
    public $usuario_nom2;
    public $usuario_ape1;
    public $usuario_ape2;
    public $usuario_tel;
    public $usuario_direc;
    public $usuario_dpi;
    public $usuario_correo;
    public $usuario_contra;
    public $usuario_token;
    public $usuario_fecha_creacion;
    public $usuario_fecha_contra;
    public $usuario_fotografia;
    public $usuario_situacion;
    public $rol_id;
    public $usuario_ultimo_acceso;

    public function __construct($args = []){
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->usuario_nom1 = $args['usuario_nom1'] ?? '';
        $this->usuario_nom2 = $args['usuario_nom2'] ?? '';
        $this->usuario_ape1 = $args['usuario_ape1'] ?? '';
        $this->usuario_ape2 = $args['usuario_ape2'] ?? '';
        $this->usuario_tel = $args['usuario_tel'] ?? '';
        $this->usuario_direc = $args['usuario_direc'] ?? '';
        $this->usuario_dpi = $args['usuario_dpi'] ?? '';
        $this->usuario_correo = $args['usuario_correo'] ?? '';
        $this->usuario_contra = $args['usuario_contra'] ?? '';
        $this->usuario_token = $args['usuario_token'] ?? '';
        // $this->usuario_fecha_creacion = $args['usuario_fecha_creacion'] ?? null;
        // $this->usuario_fecha_contra = $args['usuario_fecha_contra'] ?? null;
        $this->usuario_fotografia = $args['usuario_fotografia'] ?? '';
        $this->usuario_situacion = $args['usuario_situacion'] ?? 1;
        $this->rol_id = $args['rol_id'] ?? '';
        $this->usuario_ultimo_acceso = $args['usuario_ultimo_acceso'] ?? null;
    }
}