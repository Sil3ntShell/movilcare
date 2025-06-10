<?php

namespace Model;

// Modelo Tabla Modelo
class Modelo extends ActiveRecord {
    
    public static $tabla = 'modelo';
    public static $columnasDB = [
        'marca_id',
        'modelo_nombre',
        'modelo_descripcion',
        'modelo_situacion'
    ];
    
    public static $idTabla = 'modelo_id';
    
    public $modelo_id;
    public $marca_id;
    public $modelo_nombre;
    public $modelo_descripcion;
    public $modelo_fecha_creacion;
    public $modelo_situacion;

    public function __construct($args = []){
        $this->modelo_id = $args['modelo_id'] ?? null;
        $this->marca_id = $args['marca_id'] ?? '';
        $this->modelo_nombre = $args['modelo_nombre'] ?? '';
        $this->modelo_descripcion = $args['modelo_descripcion'] ?? '';
        // $this->modelo_fecha_creacion = $args['modelo_fecha_creacion'] ?? null;
        $this->modelo_situacion = $args['modelo_situacion'] ?? 1;
    }
}