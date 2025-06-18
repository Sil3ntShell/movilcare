<?php

namespace Model;

class Cliente extends ActiveRecord 
{
    public static $tabla = 'cliente';

    public static $columnasDB = [
        'cliente_nom1',
        'cliente_nom2',
        'cliente_ape1',
        'cliente_ape2',
        'cliente_dpi',
        'cliente_nit',
        'cliente_correo',
        'cliente_tel',
        'cliente_direc',
        'cliente_fecha_nacimiento',
        'cliente_observaciones',
        'cliente_situacion'
    ];

    public static $idTabla = 'cliente_id';

    // Propiedades
    public $cliente_id;
    public $cliente_nom1;
    public $cliente_nom2;
    public $cliente_ape1;
    public $cliente_ape2;
    public $cliente_dpi;
    public $cliente_nit;
    public $cliente_correo;
    public $cliente_tel;
    public $cliente_direc;
    public $cliente_fecha_nacimiento;
    public $cliente_fecha_registro;
    public $cliente_observaciones;
    public $cliente_situacion;

    public function __construct($args = [])
    {
        $this->cliente_id = $args['cliente_id'] ?? null;
        $this->cliente_nom1 = $args['cliente_nom1'] ?? '';
        $this->cliente_nom2 = $args['cliente_nom2'] ?? '';
        $this->cliente_ape1 = $args['cliente_ape1'] ?? '';
        $this->cliente_ape2 = $args['cliente_ape2'] ?? '';
        $this->cliente_dpi = $args['cliente_dpi'] ?? '';
        $this->cliente_nit = $args['cliente_nit'] ?? '';
        $this->cliente_correo = $args['cliente_correo'] ?? '';
        $this->cliente_tel = $args['cliente_tel'] ?? '';
        $this->cliente_direc = $args['cliente_direc'] ?? '';
        $this->cliente_fecha_nacimiento = $args['cliente_fecha_nacimiento'] ?? null;
        $this->cliente_fecha_registro = $args['cliente_fecha_registro'] ?? date('Y-m-d');
        $this->cliente_observaciones = $args['cliente_observaciones'] ?? '';
        $this->cliente_situacion = $args['cliente_situacion'] ?? 1;
    }

    public static function verificarDpiNitExistente($dpi, $nit, $excluirId = null)
    {
        $dpi = self::sanitizarCadena($dpi);
        $nit = self::sanitizarCadena($nit);

        $condDpi = "cliente_dpi = '$dpi' AND cliente_situacion = 1";
        $condNit = "cliente_nit = '$nit' AND cliente_situacion = 1";

        if ($excluirId) {
            $condDpi .= " AND cliente_id != " . intval($excluirId);
            $condNit .= " AND cliente_id != " . intval($excluirId);
        }

        $sqlDpi = "SELECT COUNT(*) as count FROM cliente WHERE $condDpi";
        $sqlNit = "SELECT COUNT(*) as count FROM cliente WHERE $condNit";

        $resDpi = self::fetchArray($sqlDpi);
        $resNit = self::fetchArray($sqlNit);

        return [
            'dpi_existe' => ($resDpi[0]['count'] ?? 0) > 0,
            'nit_existe' => ($resNit[0]['count'] ?? 0) > 0
        ];
    }

    public static function eliminarCliente($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET cliente_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    public static function obtenerClientesActivos()
    {
        $sql = "SELECT cliente_id, (cliente_nom1 || ' ' || cliente_ape1) AS nombre_completo 
                FROM cliente 
                WHERE cliente_situacion = 1 
                ORDER BY cliente_nom1 ASC";
        return self::fetchArray($sql);
    }

    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}
