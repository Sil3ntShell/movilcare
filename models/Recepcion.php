<?php

namespace Model;

class Recepcion extends ActiveRecord 
{
    public static $tabla = 'recepcion';
    public static $columnasDB = [
        'cliente_id',
        'empleado_id',
        'recepcion_tipo_celular',
        'recepcion_marca',
        'recepcion_modelo',
        'recepcion_imei',
        'recepcion_numero_serie',
        'recepcion_motivo_ingreso',
        'recepcion_estado_dispositivo',
        'recepcion_accesorios',
        'recepcion_observaciones_cliente',
        'recepcion_costo_estimado',
        'recepcion_tiempo_estimado',
        'recepcion_estado',
        'recepcion_situacion'
    ];

    public static $idTabla = 'recepcion_id';
    
    // Propiedades del modelo
    public $recepcion_id;
    public $cliente_id;
    public $empleado_id;
    public $recepcion_fecha;
    public $recepcion_tipo_celular;
    public $recepcion_marca;
    public $recepcion_modelo;
    public $recepcion_imei;
    public $recepcion_numero_serie;
    public $recepcion_motivo_ingreso;
    public $recepcion_estado_dispositivo;
    public $recepcion_accesorios;
    public $recepcion_observaciones_cliente;
    public $recepcion_costo_estimado;
    public $recepcion_tiempo_estimado;
    public $recepcion_estado;
    public $recepcion_situacion;

    public function __construct($args = [])
    {
        $this->recepcion_id = $args['recepcion_id'] ?? null;
        $this->cliente_id = $args['cliente_id'] ?? null;
        $this->empleado_id = $args['empleado_id'] ?? null;
        $this->recepcion_fecha = $args['recepcion_fecha'] ?? null;
        $this->recepcion_tipo_celular = $args['recepcion_tipo_celular'] ?? '';
        $this->recepcion_marca = $args['recepcion_marca'] ?? '';
        $this->recepcion_modelo = $args['recepcion_modelo'] ?? '';
        $this->recepcion_imei = $args['recepcion_imei'] ?? '';
        $this->recepcion_numero_serie = $args['recepcion_numero_serie'] ?? '';
        $this->recepcion_motivo_ingreso = $args['recepcion_motivo_ingreso'] ?? '';
        $this->recepcion_estado_dispositivo = $args['recepcion_estado_dispositivo'] ?? '';
        $this->recepcion_accesorios = $args['recepcion_accesorios'] ?? '';
        $this->recepcion_observaciones_cliente = $args['recepcion_observaciones_cliente'] ?? '';
        $this->recepcion_costo_estimado = $args['recepcion_costo_estimado'] ?? 0;
        $this->recepcion_tiempo_estimado = $args['recepcion_tiempo_estimado'] ?? 1;
        $this->recepcion_estado = $args['recepcion_estado'] ?? 'RECIBIDO';
        $this->recepcion_situacion = $args['recepcion_situacion'] ?? 1;
    }

    // Eliminar recepción (lógico)
    public static function EliminarRecepcion($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET recepcion_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener estados disponibles
    public static function getEstadosDisponibles()
    {
        return [
            'RECIBIDO' => 'Recibido',
            'EN_DIAGNOSTICO' => 'En Diagnóstico',
            'ESPERANDO_REPUESTOS' => 'Esperando Repuestos',
            'EN_REPARACION' => 'En Reparación',
            'REPARADO' => 'Reparado',
            'ENTREGADO' => 'Entregado',
            'CANCELADO' => 'Cancelado'
        ];
    }

    // Obtener marcas comunes de celulares
    public static function getMarcasComunes()
    {
        return [
            'Samsung',
            'Apple',
            'Huawei',
            'Xiaomi',
            'LG',
            'Motorola',
            'Nokia',
            'Sony',
            'OnePlus',
            'Oppo',
            'Vivo',
            'Realme',
            'Honor',
            'Google',
            'Otros'
        ];
    }

    // Obtener tipos de celular
    public static function getTiposCelular()
    {
        return [
            'Smartphone',
            'Teléfono Básico',
            'iPhone',
            'Tablet Android',
            'iPad',
            'Smartwatch',
            'Otros'
        ];
    }

    // Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}