<?php

namespace Model;

// Modelo Tabla Rol
class Rol extends ActiveRecord {
    
    public static $tabla = 'rol';
    public static $columnasDB = [
        'rol_nombre',
        'rol_descripcion',
        'rol_situacion'
    ];
    
    public static $idTabla = 'rol_id';
    
    public $rol_id;
    public $rol_nombre;
    public $rol_descripcion;
    public $rol_fecha_creacion;
    public $rol_situacion;

    public function __construct($args = []){
        $this->rol_id = $args['rol_id'] ?? null;
        $this->rol_nombre = $args['rol_nombre'] ?? '';
        $this->rol_descripcion = $args['rol_descripcion'] ?? '';
        // $this->rol_fecha_creacion = $args['rol_fecha_creacion'] ?? null;
        $this->rol_situacion = $args['rol_situacion'] ?? 1;
    }
}