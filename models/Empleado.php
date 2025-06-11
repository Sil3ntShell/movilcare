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
     * Verificar si existe empleado con correo o DPI
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
            return $resultado[0] ?? ['correo_existe' => 0, 'dpi_existe' => 0];

        } catch (\Exception $e) {
            return ['correo_existe' => false, 'dpi_existe' => false];
        }
    }

    /**
     * Buscar el primer registro que coincida con la consulta
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
            return null;
        }
    }

    /**
     * Obtener empleado por ID
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
            return null;
        }
    }

    /**
     * Sanear cadena de entrada
     */
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}