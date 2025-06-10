<?php

namespace Model;

// Modelo Tabla Orden de Trabajo
class OrdenTrabajo extends ActiveRecord {
    
    public static $tabla = 'orden_trabajo';
    public static $columnasDB = [
        'recepcion_id',
        'empleado_id',
        'tipo_servicio_id',
        'orden_diagnostico',
        'orden_trabajo_realizado',
        'orden_repuestos_utilizados',
        'orden_costo_repuestos',
        'orden_costo_mano_obra',
        'orden_costo_total',
        'orden_estado',
        'orden_observaciones',
        'orden_situacion'
    ];
    
    public static $idTabla = 'orden_id';
    
    public $orden_id;
    public $recepcion_id;
    public $empleado_id;
    public $tipo_servicio_id;
    public $orden_fecha_asignacion;
    public $orden_fecha_inicio;
    public $orden_fecha_finalizacion;
    public $orden_diagnostico;
    public $orden_trabajo_realizado;
    public $orden_repuestos_utilizados;
    public $orden_costo_repuestos;
    public $orden_costo_mano_obra;
    public $orden_costo_total;
    public $orden_estado;
    public $orden_observaciones;
    public $orden_situacion;

    public function __construct($args = []){
        $this->orden_id = $args['orden_id'] ?? null;
        $this->recepcion_id = $args['recepcion_id'] ?? '';
        $this->empleado_id = $args['empleado_id'] ?? null;
        $this->tipo_servicio_id = $args['tipo_servicio_id'] ?? '';
        $this->orden_fecha_asignacion = $args['orden_fecha_asignacion'] ?? null;
        $this->orden_fecha_inicio = $args['orden_fecha_inicio'] ?? null;
        $this->orden_fecha_finalizacion = $args['orden_fecha_finalizacion'] ?? null;
        $this->orden_diagnostico = $args['orden_diagnostico'] ?? '';
        $this->orden_trabajo_realizado = $args['orden_trabajo_realizado'] ?? '';
        $this->orden_repuestos_utilizados = $args['orden_repuestos_utilizados'] ?? '';
        $this->orden_costo_repuestos = $args['orden_costo_repuestos'] ?? 0.00;
        $this->orden_costo_mano_obra = $args['orden_costo_mano_obra'] ?? 0.00;
        $this->orden_costo_total = $args['orden_costo_total'] ?? 0.00;
        $this->orden_estado = $args['orden_estado'] ?? '';
        $this->orden_observaciones = $args['orden_observaciones'] ?? '';
        $this->orden_situacion = $args['orden_situacion'] ?? 1;
    }
}
