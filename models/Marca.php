<?php

namespace Model;

class Marca extends ActiveRecord
{
    // Nombre de la tabla en la BD
    public static $tabla = 'marca';

    // Columnas que se van a mapear a la BD
    public static $columnasDB = [
        'marca_nombre',
        'marca_descripcion',
        'marca_situacion'
    ];

    public static $idTabla = 'marca_id';

    // Propiedades
    public $marca_id;
    public $marca_nombre;
    public $marca_descripcion;
    public $marca_fecha_creacion;
    public $marca_situacion;

    public function __construct($args = [])
    {
        $this->marca_id = $args['marca_id'] ?? null;
        $this->marca_nombre = $args['marca_nombre'] ?? '';
        $this->marca_descripcion = $args['marca_descripcion'] ?? '';
        $this->marca_situacion = $args['marca_situacion'] ?? 1;
    }

    public static function verificarMarcaExistente($nombre, $excluirId = null)
    {
        $nombre = self::sanitizarCadena($nombre);

        $condNombre = "marca_nombre = '$nombre' AND marca_situacion = 1";

        if ($excluirId) {
            $condNombre .= " AND marca_id != " . intval($excluirId);
        }

        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM marca WHERE $condNombre) AS nombre_existe
        ";

        $resultado = self::fetchArray($sql);
        return $resultado[0] ?? ['nombre_existe' => 0];
    }

    public static function EliminarMarca($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET marca_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener todas las marcas activas para selects
    public static function obtenerMarcasActivas()
    {
        $sql = "SELECT marca_id, marca_nombre FROM marca WHERE marca_situacion = 1 ORDER BY marca_nombre ASC";
        return self::fetchArray($sql);
    }

    //Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}