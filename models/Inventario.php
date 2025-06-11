<?php

namespace Model;

class Inventario extends ActiveRecord
{
    // Nombre de la tabla en la BD
    public static $tabla = 'inventario';

    // Columnas que se van a mapear a la BD
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

    // Propiedades
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

    public function __construct($args = [])
    {
        $this->inventario_id = $args['inventario_id'] ?? null;
        $this->modelo_id = $args['modelo_id'] ?? '';
        $this->inventario_numero_serie = $args['inventario_numero_serie'] ?? '';
        $this->inventario_imei = $args['inventario_imei'] ?? '';
        $this->inventario_estado = $args['inventario_estado'] ?? '';
        $this->inventario_precio_compra = $args['inventario_precio_compra'] ?? 0;
        $this->inventario_precio_venta = $args['inventario_precio_venta'] ?? 0;
        $this->inventario_stock_disponible = $args['inventario_stock_disponible'] ?? 1;
        $this->inventario_ubicacion = $args['inventario_ubicacion'] ?? '';
        $this->inventario_observaciones = $args['inventario_observaciones'] ?? '';
        $this->inventario_situacion = $args['inventario_situacion'] ?? 1;
    }

    public static function verificarInventarioExistente($numero_serie, $imei, $excluirId = null)
    {
        $numero_serie = self::sanitizarCadena($numero_serie);
        $imei = self::sanitizarCadena($imei);

        $condSerie = "inventario_numero_serie = '$numero_serie' AND inventario_situacion = 1";
        $condImei = "inventario_imei = '$imei' AND inventario_situacion = 1";

        if ($excluirId) {
            $condSerie .= " AND inventario_id != " . intval($excluirId);
            $condImei .= " AND inventario_id != " . intval($excluirId);
        }

        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM inventario WHERE $condSerie) AS serie_existe,
                (SELECT COUNT(*) FROM inventario WHERE $condImei) AS imei_existe
        ";

        $resultado = self::fetchArray($sql);
        return $resultado[0] ?? ['serie_existe' => 0, 'imei_existe' => 0];
    }

    public static function EliminarInventario($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET inventario_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener inventario disponible para ventas
    public static function obtenerInventarioDisponible()
    {
        $sql = "
            SELECT 
                i.inventario_id,
                i.inventario_numero_serie,
                i.inventario_imei,
                i.inventario_precio_venta,
                i.inventario_stock_disponible,
                m.modelo_nombre,
                ma.marca_nombre
            FROM inventario i
            INNER JOIN modelo m ON i.modelo_id = m.modelo_id
            INNER JOIN marca ma ON m.marca_id = ma.marca_id
            WHERE i.inventario_situacion = 1 
            AND i.inventario_stock_disponible > 0
            AND i.inventario_estado = 'Disponible'
            ORDER BY ma.marca_nombre ASC, m.modelo_nombre ASC
        ";
        return self::fetchArray($sql);
    }

    // Obtener estadísticas del inventario
    public static function obtenerEstadisticasInventario()
    {
        $sql = "
            SELECT 
                COUNT(*) as total_productos,
                SUM(inventario_stock_disponible) as stock_total,
                SUM(CASE WHEN inventario_estado = 'Disponible' THEN inventario_stock_disponible ELSE 0 END) as stock_disponible,
                SUM(CASE WHEN inventario_estado = 'Vendido' THEN 1 ELSE 0 END) as productos_vendidos,
                SUM(CASE WHEN inventario_estado = 'Dañado' THEN 1 ELSE 0 END) as productos_dañados,
                AVG(inventario_precio_venta) as precio_promedio_venta
            FROM inventario 
            WHERE inventario_situacion = 1
        ";
        $resultado = self::fetchArray($sql);
        return $resultado[0] ?? [];
    }

    // Buscar productos por criterios
    public static function buscarProductos($criterio = '', $valor = '')
    {
        $where = "i.inventario_situacion = 1";
        
        if ($criterio && $valor) {
            $valor = self::sanitizarCadena($valor);
            switch ($criterio) {
                case 'marca':
                    $where .= " AND ma.marca_nombre LIKE '%$valor%'";
                    break;
                case 'modelo':
                    $where .= " AND m.modelo_nombre LIKE '%$valor%'";
                    break;
                case 'imei':
                    $where .= " AND i.inventario_imei LIKE '%$valor%'";
                    break;
                case 'serie':
                    $where .= " AND i.inventario_numero_serie LIKE '%$valor%'";
                    break;
                case 'estado':
                    $where .= " AND i.inventario_estado = '$valor'";
                    break;
            }
        }

        $sql = "
            SELECT 
                i.*,
                m.modelo_nombre,
                ma.marca_nombre
            FROM inventario i
            INNER JOIN modelo m ON i.modelo_id = m.modelo_id
            INNER JOIN marca ma ON m.marca_id = ma.marca_id
            WHERE $where
            ORDER BY i.inventario_fecha_ingreso DESC
        ";
        
        return self::fetchArray($sql);
    }

    //Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}