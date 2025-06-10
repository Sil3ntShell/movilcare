<?php

namespace Model;

// Modelo Tabla Tipo de Servicio
class TipoServicio extends ActiveRecord {
    
    public static $tabla = 'tipo_servicio';
    public static $columnasDB = [
        'tipo_servicio_nombre',
        'tipo_servicio_descripcion',
        'tipo_servicio_precio_base',
        'tipo_servicio_tiempo_estimado',
        'tipo_servicio_situacion'
    ];
    
    public static $idTabla = 'tipo_servicio_id';
    
    public $tipo_servicio_id;
    public $tipo_servicio_nombre;
    public $tipo_servicio_descripcion;
    public $tipo_servicio_precio_base;
    public $tipo_servicio_tiempo_estimado;
    public $tipo_servicio_situacion;

    public function __construct($args = []){
        $this->tipo_servicio_id = $args['tipo_servicio_id'] ?? null;
        $this->tipo_servicio_nombre = $args['tipo_servicio_nombre'] ?? '';
        $this->tipo_servicio_descripcion = $args['tipo_servicio_descripcion'] ?? '';
        $this->tipo_servicio_precio_base = $args['tipo_servicio_precio_base'] ?? 0.00;
        $this->tipo_servicio_tiempo_estimado = $args['tipo_servicio_tiempo_estimado'] ?? 0;
        $this->tipo_servicio_situacion = $args['tipo_servicio_situacion'] ?? 1;
    }
}