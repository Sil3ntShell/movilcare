<?php

namespace Model;

//Modelo de Tabla Historial de Ventas
//Este detalla la venta
class HistorialVenta extends ActiveRecord {
    
    public static $tabla = 'historial_venta';
    public static $columnasDB = [
        'venta_id',
        'historial_fecha_venta',
        'historial_mes',
        'historial_anio',
        'historial_total_venta',
        'historial_tipo_venta',
        'cliente_id',
        'usuario_id',
        'historial_situacion'
    ];
    
    public static $idTabla = 'historial_id';
    
    public $historial_id;
    public $venta_id;
    public $historial_fecha_venta;
    public $historial_mes;
    public $historial_anio;
    public $historial_total_venta;
    public $historial_tipo_venta;
    public $cliente_id;
    public $usuario_id;
    public $historial_situacion;

    public function __construct($args = []){
        $this->historial_id = $args['historial_id'] ?? null;
        $this->venta_id = $args['venta_id'] ?? '';
        $this->historial_fecha_venta = $args['historial_fecha_venta'] ?? null;
        $this->historial_mes = $args['historial_mes'] ?? '';
        $this->historial_anio = $args['historial_anio'] ?? '';
        $this->historial_total_venta = $args['historial_total_venta'] ?? 0.00;
        $this->historial_tipo_venta = $args['historial_tipo_venta'] ?? '';
        $this->cliente_id = $args['cliente_id'] ?? '';
        $this->usuario_id = $args['usuario_id'] ?? '';
        $this->historial_situacion = $args['historial_situacion'] ?? 1;
    }
}