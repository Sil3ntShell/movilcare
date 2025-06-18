<?php

namespace Model;

class Usuario extends ActiveRecord 
{
    public static $tabla = 'usuario';

    public static $columnasDB = [
        'usuario_nom1',
        'usuario_nom2',
        'usuario_ape1',
        'usuario_ape2',
        'usuario_tel',
        'usuario_direc',
        'usuario_dpi',
        'usuario_correo',
        'usuario_contra',
        'usuario_token',
        'usuario_fotografia',
        'usuario_situacion'
    ];

    public static $idTabla = 'usuario_id';

    // Propiedades
    public $usuario_id;
    public $usuario_nom1;
    public $usuario_nom2;
    public $usuario_ape1;
    public $usuario_ape2;
    public $usuario_tel;
    public $usuario_direc;
    public $usuario_dpi;
    public $usuario_correo;
    public $usuario_contra;
    public $usuario_token;
    public $usuario_fotografia;
    public $usuario_situacion;
    public $usuario_fecha_creacion;
    public $usuario_fecha_contra;
    public $usuario_ultimo_acceso;

    public function __construct($args = [])
    {
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->usuario_nom1 = $args['usuario_nom1'] ?? '';
        $this->usuario_nom2 = $args['usuario_nom2'] ?? '';
        $this->usuario_ape1 = $args['usuario_ape1'] ?? '';
        $this->usuario_ape2 = $args['usuario_ape2'] ?? '';
        $this->usuario_tel = $args['usuario_tel'] ?? '';
        $this->usuario_direc = $args['usuario_direc'] ?? '';
        $this->usuario_dpi = $args['usuario_dpi'] ?? '';
        $this->usuario_correo = $args['usuario_correo'] ?? '';
        $this->usuario_contra = $args['usuario_contra'] ?? '';
        $this->usuario_token = $args['usuario_token'] ?? '';
        $this->usuario_fotografia = $args['usuario_fotografia'] ?? '';
        $this->usuario_situacion = $args['usuario_situacion'] ?? 1;
        $this->usuario_fecha_creacion = $args['usuario_fecha_creacion'] ?? null;
        $this->usuario_fecha_contra = $args['usuario_fecha_contra'] ?? null;
        $this->usuario_ultimo_acceso = $args['usuario_ultimo_acceso'] ?? null;
    }

    // Verificar si existe un correo o DPI ya registrado
    public static function verificarCorreoODpiExistente($correo, $dpi, $excluirId = null)
    {
        $correo = self::sanitizarCadena($correo);
        $dpi = self::sanitizarCadena($dpi);

        $condCorreo = "usuario_correo = '$correo' AND usuario_situacion = 1";
        $condDpi = "usuario_dpi = '$dpi' AND usuario_situacion = 1";

        if ($excluirId) {
            $condCorreo .= " AND usuario_id != " . intval($excluirId);
            $condDpi .= " AND usuario_id != " . intval($excluirId);
        }

        // DEBUG
        error_log("SQL correo: SELECT COUNT(*) FROM usuario WHERE $condCorreo");
        error_log("SQL DPI: SELECT COUNT(*) FROM usuario WHERE $condDpi");

        $sqlCorreo = "SELECT COUNT(*) as count FROM usuario WHERE $condCorreo";
        $sqlDpi = "SELECT COUNT(*) as count FROM usuario WHERE $condDpi";

        $resCorreo = self::fetchArray($sqlCorreo);
        $resDpi = self::fetchArray($sqlDpi);

        return [
            'correo_existe' => ($resCorreo[0]['count'] ?? 0) > 0,
            'dpi_existe' => ($resDpi[0]['count'] ?? 0) > 0
        ];
    }

    // Eliminar usuario (baja lógica)
    public static function EliminarUsuario($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET usuario_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener usuarios activos para selects
    public static function obtenerUsuariosActivos()
    {
        $sql = "SELECT usuario_id, (usuario_nom1 || ' ' || usuario_ape1) AS nombre_completo 
                FROM usuario 
                WHERE usuario_situacion = 1 
                ORDER BY usuario_nom1 ASC";
        return self::fetchArray($sql);
    }

    // Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }

    // Generar token único para usuario
    public static function generarToken() {
        return bin2hex(random_bytes(32));
    }

}

