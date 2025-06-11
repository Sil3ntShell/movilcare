<?php

namespace Model;

class Modelo extends ActiveRecord
{
    // Nombre de la tabla en la BD
    public static $tabla = 'modelo';

    // Columnas que se van a mapear a la BD
    public static $columnasDB = [
        'marca_id',
        'modelo_nombre',
        'modelo_descripcion',
        'modelo_situacion'
    ];

    public static $idTabla = 'modelo_id';

    // Propiedades
    public $modelo_id;
    public $marca_id;
    public $modelo_nombre;
    public $modelo_descripcion;
    public $modelo_fecha_creacion;
    public $modelo_situacion;

    public function __construct($args = [])
    {
        $this->modelo_id = $args['modelo_id'] ?? null;
        $this->marca_id = $args['marca_id'] ?? '';
        $this->modelo_nombre = $args['modelo_nombre'] ?? '';
        $this->modelo_descripcion = $args['modelo_descripcion'] ?? '';
        $this->modelo_situacion = $args['modelo_situacion'] ?? 1;
    }

    public static function verificarModeloExistente($marca_id, $nombre, $excluirId = null)
    {
        $marca_id = intval($marca_id);
        $nombre = self::sanitizarCadena($nombre);

        $condNombre = "marca_id = $marca_id AND modelo_nombre = '$nombre' AND modelo_situacion = 1";

        if ($excluirId) {
            $condNombre .= " AND modelo_id != " . intval($excluirId);
        }

        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM modelo WHERE $condNombre) AS nombre_existe
        ";

        $resultado = self::fetchArray($sql);
        return $resultado[0] ?? ['nombre_existe' => 0];
    }

    public static function EliminarModelo($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET modelo_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener todos los modelos activos para selects
    public static function obtenerModelosActivos()
    {
        $sql = "
            SELECT 
                m.modelo_id, 
                m.modelo_nombre, 
                m.marca_id,
                ma.marca_nombre
            FROM modelo m
            INNER JOIN marca ma ON m.marca_id = ma.marca_id
            WHERE m.modelo_situacion = 1 AND ma.marca_situacion = 1
            ORDER BY ma.marca_nombre ASC, m.modelo_nombre ASC
        ";
        return self::fetchArray($sql);
    }

    // Obtener modelos por marca
    public static function obtenerModelosPorMarca($marca_id)
    {
        $marca_id = intval($marca_id);
        $sql = "
            SELECT modelo_id, modelo_nombre 
            FROM modelo 
            WHERE marca_id = $marca_id AND modelo_situacion = 1 
            ORDER BY modelo_nombre ASC
        ";
        return self::fetchArray($sql);
    }

    //Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}