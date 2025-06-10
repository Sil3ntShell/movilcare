<?php

namespace Model;

// Modelo Tabla Venta
class DetalleVenta extends ActiveRecord {
    
    public static $tabla = 'detalle_venta';
    public static $columnasDB = [
        'venta_id',
        'inventario_id',
        'orden_id',
        'detalle_tipo_item',
        'detalle_descripcion',
        'detalle_cantidad',
        'detalle_precio_unitario',
        'detalle_subtotal',
        'detalle_situacion'
    ];
    
    public static $idTabla = 'detalle_id';
    
    public $detalle_id;
    public $venta_id;
    public $inventario_id;
    public $orden_id;
    public $detalle_tipo_item;
    public $detalle_descripcion;
    public $detalle_cantidad;
    public $detalle_precio_unitario;
    public $detalle_subtotal;
    public $detalle_situacion;

    public function __construct($args = []){
        $this->detalle_id = $args['detalle_id'] ?? null;
        $this->venta_id = $args['venta_id'] ?? '';
        $this->inventario_id = $args['inventario_id'] ?? null;
        $this->orden_id = $args['orden_id'] ?? null;
        $this->detalle_tipo_item = $args['detalle_tipo_item'] ?? '';
        $this->detalle_descripcion = $args['detalle_descripcion'] ?? '';
        $this->detalle_cantidad = $args['detalle_cantidad'] ?? 1;
        $this->detalle_precio_unitario = $args['detalle_precio_unitario'] ?? 0.00;
        $this->detalle_subtotal = $args['detalle_subtotal'] ?? 0.00;
        $this->detalle_situacion = $args['detalle_situacion'] ?? 1;
    }
}