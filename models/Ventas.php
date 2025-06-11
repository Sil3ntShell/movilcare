<?php

namespace Model;

class Venta extends ActiveRecord 
{
    public static $tabla = 'venta';
    public static $columnasDB = [
        'cliente_id',
        'usuario_id',
        'venta_subtotal',
        'venta_descuento',
        'venta_impuestos',
        'venta_total',
        'venta_forma_pago',
        'venta_estado',
        'venta_observaciones',
        'venta_situacion'
    ];

    public static $idTabla = 'venta_id';
    
    // Propiedades del modelo
    public $venta_id;
    public $cliente_id;
    public $usuario_id;
    public $venta_fecha;
    public $venta_subtotal;
    public $venta_descuento;
    public $venta_impuestos;
    public $venta_total;
    public $venta_forma_pago;
    public $venta_estado;
    public $venta_observaciones;
    public $venta_situacion;

    public function __construct($args = [])
    {
        $this->venta_id = $args['venta_id'] ?? null;
        $this->cliente_id = $args['cliente_id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->venta_fecha = $args['venta_fecha'] ?? null;
        $this->venta_subtotal = $args['venta_subtotal'] ?? 0;
        $this->venta_descuento = $args['venta_descuento'] ?? 0;
        $this->venta_impuestos = $args['venta_impuestos'] ?? 0;
        $this->venta_total = $args['venta_total'] ?? 0;
        $this->venta_forma_pago = $args['venta_forma_pago'] ?? 'EFECTIVO';
        $this->venta_estado = $args['venta_estado'] ?? 'PENDIENTE';
        $this->venta_observaciones = $args['venta_observaciones'] ?? '';
        $this->venta_situacion = $args['venta_situacion'] ?? 1;
    }

    // Calcular totales automáticamente
    public function calcularTotales()
    {
        $this->venta_total = $this->venta_subtotal - $this->venta_descuento + $this->venta_impuestos;
        return $this->venta_total;
    }

    // Obtener estados disponibles
    public static function getEstadosDisponibles()
    {
        return [
            'PENDIENTE' => 'Pendiente',
            'PROCESANDO' => 'Procesando',
            'COMPLETADA' => 'Completada',
            'CANCELADA' => 'Cancelada',
            'FACTURADA' => 'Facturada'
        ];
    }

    // Obtener formas de pago disponibles
    public static function getFormasPagoDisponibles()
    {
        return [
            'EFECTIVO' => 'Efectivo',
            'TARJETA_CREDITO' => 'Tarjeta de Crédito',
            'TARJETA_DEBITO' => 'Tarjeta de Débito',
            'TRANSFERENCIA' => 'Transferencia Bancaria',
            'CHEQUE' => 'Cheque',
            'CREDITO' => 'Crédito'
        ];
    }

    // Obtener detalle de la venta
    public function obtenerDetalle()
    {
        $sql = "
            SELECT 
                dv.*,
                CASE 
                    WHEN dv.inventario_id IS NOT NULL THEN i.inventario_descripcion
                    WHEN dv.orden_id IS NOT NULL THEN ot.orden_diagnostico
                    ELSE dv.detalle_descripcion
                END as item_descripcion,
                CASE 
                    WHEN dv.inventario_id IS NOT NULL THEN 'PRODUCTO'
                    WHEN dv.orden_id IS NOT NULL THEN 'SERVICIO'
                    ELSE 'OTRO'
                END as tipo_real
            FROM detalle_venta dv
            LEFT JOIN inventario i ON dv.inventario_id = i.inventario_id
            LEFT JOIN orden_trabajo ot ON dv.orden_id = ot.orden_id
            WHERE dv.venta_id = {$this->venta_id} AND dv.detalle_situacion = 1
            ORDER BY dv.detalle_id ASC
        ";
        return self::fetchArray($sql);
    }

    // Generar número de venta
    public function generarNumeroVenta()
    {
        $año = date('Y');
        $mes = date('m');
        
        // Buscar el último número de venta del mes
        $query = "SELECT COUNT(*) as count FROM venta WHERE YEAR(venta_fecha) = $año AND MONTH(venta_fecha) = $mes";
        $resultado = self::fetchArray($query);
        $numero = ($resultado[0]['count'] ?? 0) + 1;
        
        return sprintf("V-%s%s-%04d", $año, $mes, $numero);
    }

    // Eliminar venta (lógico)
    public static function EliminarVenta($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET venta_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}