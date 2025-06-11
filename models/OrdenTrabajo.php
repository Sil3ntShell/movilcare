<?php

namespace Model;

class OrdenTrabajo extends ActiveRecord 
{
    public static $tabla = 'orden_trabajo';
    public static $columnasDB = [
        'recepcion_id',
        'empleado_id',
        'tipo_servicio_id',
        'orden_fecha_inicio',
        'orden_fecha_finalizacion',
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
    
    // Propiedades del modelo
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

    public function __construct($args = [])
    {
        $this->orden_id = $args['orden_id'] ?? null;
        $this->recepcion_id = $args['recepcion_id'] ?? null;
        $this->empleado_id = $args['empleado_id'] ?? null;
        $this->tipo_servicio_id = $args['tipo_servicio_id'] ?? null;
        $this->orden_fecha_asignacion = $args['orden_fecha_asignacion'] ?? null;
        $this->orden_fecha_inicio = $args['orden_fecha_inicio'] ?? null;
        $this->orden_fecha_finalizacion = $args['orden_fecha_finalizacion'] ?? null;
        $this->orden_diagnostico = $args['orden_diagnostico'] ?? '';
        $this->orden_trabajo_realizado = $args['orden_trabajo_realizado'] ?? '';
        $this->orden_repuestos_utilizados = $args['orden_repuestos_utilizados'] ?? '';
        $this->orden_costo_repuestos = $args['orden_costo_repuestos'] ?? 0;
        $this->orden_costo_mano_obra = $args['orden_costo_mano_obra'] ?? 0;
        $this->orden_costo_total = $args['orden_costo_total'] ?? 0;
        $this->orden_estado = $args['orden_estado'] ?? 'ASIGNADA';
        $this->orden_observaciones = $args['orden_observaciones'] ?? '';
        $this->orden_situacion = $args['orden_situacion'] ?? 1;
    }

    // Calcular costo total automáticamente
    public function calcularCostoTotal()
    {
        $this->orden_costo_total = $this->orden_costo_repuestos + $this->orden_costo_mano_obra;
        return $this->orden_costo_total;
    }

    // Eliminar orden de trabajo (lógico)
    public static function EliminarOrdenTrabajo($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET orden_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener estados disponibles para órdenes de trabajo
    public static function getEstadosDisponibles()
    {
        return [
            'ASIGNADA' => 'Asignada',
            'EN_PROGRESO' => 'En Progreso',
            'EN_ESPERA_REPUESTOS' => 'En Espera de Repuestos',
            'PAUSADA' => 'Pausada',
            'COMPLETADA' => 'Completada',
            'CANCELADA' => 'Cancelada',
            'ENTREGADA' => 'Entregada'
        ];
    }

    // Obtener órdenes por estado
    public static function obtenerPorEstado($estado)
    {
        $sql = "
            SELECT 
                ot.*,
                r.recepcion_tipo_celular,
                r.recepcion_marca,
                r.recepcion_modelo,
                (c.cliente_nom1 || ' ' || c.cliente_ape1) as cliente_nombre,
                (e.empleado_nom1 || ' ' || e.empleado_ape1) as empleado_nombre,
                ts.tipo_servicio_nombre
            FROM orden_trabajo ot
            LEFT JOIN recepcion r ON ot.recepcion_id = r.recepcion_id
            LEFT JOIN cliente c ON r.cliente_id = c.cliente_id
            LEFT JOIN empleado e ON ot.empleado_id = e.empleado_id
            LEFT JOIN tipo_servicio ts ON ot.tipo_servicio_id = ts.tipo_servicio_id
            WHERE ot.orden_estado = '$estado' AND ot.orden_situacion = 1
            ORDER BY ot.orden_fecha_asignacion DESC
        ";
        return self::fetchArray($sql);
    }

    // Generar número de orden
    public function generarNumeroOrden()
    {
        $año = date('Y');
        $mes = date('m');
        
        // Buscar el último número de orden del mes
        $query = "SELECT COUNT(*) as count FROM orden_trabajo WHERE YEAR(orden_fecha_asignacion) = $año AND MONTH(orden_fecha_asignacion) = $mes";
        $resultado = self::fetchArray($query);
        $numero = ($resultado[0]['count'] ?? 0) + 1;
        
        return sprintf("OT-%s%s-%04d", $año, $mes, $numero);
    }

    // Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}