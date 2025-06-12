<?php

namespace Model;

class Empleado extends ActiveRecord 
{
    public static $tabla = 'empleado';
    public static $columnasDB = [
        'usuario_id',
        'empleado_nom1',
        'empleado_nom2',
        'empleado_ape1',
        'empleado_ape2',
        'empleado_dpi',
        'empleado_tel',
        'empleado_correo',
        'empleado_especialidad',
        'empleado_salario',
        'empleado_situacion'
    ];

    public static $idTabla = 'empleado_id';
    
    // Propiedades del modelo
    public $empleado_id;
    public $usuario_id;
    public $empleado_nom1;
    public $empleado_nom2;
    public $empleado_ape1;
    public $empleado_ape2;
    public $empleado_dpi;
    public $empleado_tel;
    public $empleado_correo;
    public $empleado_especialidad;
    public $empleado_fecha_contratacion;
    public $empleado_salario;
    public $empleado_situacion;

    public function __construct($args = [])
    {
        $this->empleado_id = $args['empleado_id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->empleado_nom1 = $args['empleado_nom1'] ?? '';
        $this->empleado_nom2 = $args['empleado_nom2'] ?? '';
        $this->empleado_ape1 = $args['empleado_ape1'] ?? '';
        $this->empleado_ape2 = $args['empleado_ape2'] ?? '';
        $this->empleado_dpi = $args['empleado_dpi'] ?? '';
        $this->empleado_tel = $args['empleado_tel'] ?? '';
        $this->empleado_correo = $args['empleado_correo'] ?? '';
        $this->empleado_especialidad = $args['empleado_especialidad'] ?? '';
        $this->empleado_salario = $args['empleado_salario'] ?? 0;
        $this->empleado_situacion = $args['empleado_situacion'] ?? 1;
    }

    /**
     * Validaciones antes de guardar
     */
    public function validar()
    {
        $errores = [];

        if (!$this->empleado_nom1) {
            $errores[] = 'El primer nombre es obligatorio';
        }

        if (!$this->empleado_ape1) {
            $errores[] = 'El primer apellido es obligatorio';
        }

        if (!$this->empleado_tel) {
            $errores[] = 'El teléfono es obligatorio';
        }

        if (!$this->empleado_dpi) {
            $errores[] = 'El DPI es obligatorio';
        }

        if (!$this->empleado_correo) {
            $errores[] = 'El correo es obligatorio';
        }

        if (!$this->empleado_especialidad) {
            $errores[] = 'La especialidad es obligatoria';
        }

        if (!$this->empleado_salario || $this->empleado_salario <= 0) {
            $errores[] = 'El salario debe ser mayor a 0';
        }

        return $errores;
    }

    /**
     * Verificar si existe empleado con correo o DPI - CORREGIDO
     */
    public static function verificarEmpleadoExistente($correo, $dpi, $excluirId = null)
    {
        try {
            $correo = self::sanitizarCadena($correo);
            $dpi = self::sanitizarCadena($dpi);

            $condCorreo = "empleado_correo = '$correo' AND empleado_situacion = 1";
            $condDpi = "empleado_dpi = '$dpi' AND empleado_situacion = 1";

            if ($excluirId) {
                $condCorreo .= " AND empleado_id != " . intval($excluirId);
                $condDpi .= " AND empleado_id != " . intval($excluirId);
            }

            $sql = "
                SELECT 
                    (SELECT COUNT(*) FROM empleado WHERE $condCorreo) AS correo_existe,
                    (SELECT COUNT(*) FROM empleado WHERE $condDpi) AS dpi_existe
            ";

            $resultado = self::fetchArray($sql);
            
            // ASEGURAR que siempre retorne la estructura correcta
            if (!empty($resultado) && isset($resultado[0])) {
                return [
                    'correo_existe' => intval($resultado[0]['correo_existe']),
                    'dpi_existe' => intval($resultado[0]['dpi_existe'])
                ];
            } else {
                return ['correo_existe' => 0, 'dpi_existe' => 0];
            }

        } catch (\Exception $e) {
            error_log("Error en verificarEmpleadoExistente: " . $e->getMessage());
            // Fallback seguro en caso de error
            return ['correo_existe' => 0, 'dpi_existe' => 0];
        }
    }

    /**
     * Obtener empleados con información de usuario - NUEVO
     */
    public static function obtenerEmpleadosConUsuario()
    {
        $sql = "SELECT 
                    e.empleado_id,
                    e.usuario_id,
                    e.empleado_nom1,
                    e.empleado_nom2,
                    e.empleado_ape1,
                    e.empleado_ape2,
                    e.empleado_dpi,
                    e.empleado_tel,
                    e.empleado_correo,
                    e.empleado_especialidad,
                    e.empleado_fecha_contratacion,
                    e.empleado_salario,
                    e.empleado_situacion,
                    u.usuario_fotografia,
                    u.rol_id,
                    r.rol_nombre
                FROM empleado e
                LEFT JOIN usuario u ON e.usuario_id = u.usuario_id
                LEFT JOIN rol r ON u.rol_id = r.rol_id
                WHERE e.empleado_situacion = 1
                ORDER BY e.empleado_fecha_contratacion DESC, e.empleado_id DESC";
        
        return self::fetchArray($sql);
    }

    /**
     * Obtener empleado por ID - MEJORADO
     */
    public static function obtenerPorId($id)
    {
        try {
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . static::$idTabla . " = ?";
            $resultado = self::fetchFirst($query, [$id]);
            
            if ($resultado) {
                return new static($resultado);
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener empleado activo por ID
     */
    public static function obtenerEmpleadoActivo($id)
    {
        try {
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . static::$idTabla . " = ? AND empleado_situacion = 1";
            $resultado = self::fetchFirst($query, [$id]);
            
            if ($resultado) {
                return new static($resultado);
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Error en obtenerEmpleadoActivo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar el primer registro que coincida con la consulta - MEJORADO
     */
    public static function fetchFirst($query, $params = [])
    {
        try {
            $db = self::$db;
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result ?: null;
            
        } catch (\Exception $e) {
            error_log("Error en fetchFirst: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener empleados activos para selects
     */
    public static function obtenerEmpleadosActivos()
    {
        $sql = "SELECT 
                empleado_id,
                CONCAT(empleado_nom1, ' ', empleado_ape1) AS empleado_nombre_completo
                FROM empleado
                WHERE empleado_situacion = 1
                ORDER BY empleado_nom1 ASC";
        
        return self::fetchArray($sql);
    }

    /**
     * Eliminar empleado (cambiar situación) - NUEVO
     */
    public static function EliminarEmpleado($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET empleado_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    /**
     * Sanear cadena de entrada
     */
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}