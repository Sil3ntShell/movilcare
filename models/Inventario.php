<?php

namespace Model;

// Modelo Tabla Inventario
class Inventario extends ActiveRecord {
    
    public static $tabla = 'inventario';
    public static $columnasDB = [
        'modelo_id',
        'inventario_numero_serie',
        'inventario_imei',
        'inventario_estado',
        'inventario_precio_compra',
        'inventario_precio_venta',
        'inventario_stock_disponible',
        'inventario_ubicacion',
        'inventario_observaciones',
        'inventario_situacion'
    ];
    
    public static $idTabla = 'inventario_id';
    
    public $inventario_id;
    public $modelo_id;
    public $inventario_numero_serie;
    public $inventario_imei;
    public $inventario_estado;
    public $inventario_precio_compra;
    public $inventario_precio_venta;
    public $inventario_stock_disponible;
    public $inventario_ubicacion;
    public $inventario_fecha_ingreso;
    public $inventario_fecha_actualizacion;
    public $inventario_observaciones;
    public $inventario_situacion;

    public function __construct($args = []){
        $this->inventario_id = $args['inventario_id'] ?? null;
        $this->modelo_id = $args['modelo_id'] ?? '';
        $this->inventario_numero_serie = $args['inventario_numero_serie'] ?? '';
        $this->inventario_imei = $args['inventario_imei'] ?? '';
        $this->inventario_estado = $args['inventario_estado'] ?? '';
        $this->inventario_precio_compra = $args['inventario_precio_compra'] ?? 0.00;
        $this->inventario_precio_venta = $args['inventario_precio_venta'] ?? 0.00;
        $this->inventario_stock_disponible = $args['inventario_stock_disponible'] ?? 1;
        $this->inventario_ubicacion = $args['inventario_ubicacion'] ?? '';
        // $this->inventario_fecha_ingreso = $args['inventario_fecha_ingreso'] ?? null;
        // $this->inventario_fecha_actualizacion = $args['inventario_fecha_actualizacion'] ?? null;
        $this->inventario_observaciones = $args['inventario_observaciones'] ?? '';
        $this->inventario_situacion = $args['inventario_situacion'] ?? 1;
    }
}
