<?php

namespace Model;

// Modelo Tabla Marca
class Marca extends ActiveRecord {
    
    public static $tabla = 'marca';
    public static $columnasDB = [
        'marca_nombre',
        'marca_descripcion',
        'marca_situacion'
    ];
    
    public static $idTabla = 'marca_id';
    
    public $marca_id;
    public $marca_nombre;
    public $marca_descripcion;
    public $marca_fecha_creacion;
    public $marca_situacion;

    public function __construct($args = []){
        $this->marca_id = $args['marca_id'] ?? null;
        $this->marca_nombre = $args['marca_nombre'] ?? '';
        $this->marca_descripcion = $args['marca_descripcion'] ?? '';
        // $this->marca_fecha_creacion = $args['marca_fecha_creacion'] ?? null;
        $this->marca_situacion = $args['marca_situacion'] ?? 1;
    }
}