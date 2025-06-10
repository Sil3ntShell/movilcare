<?php

namespace Model;

// Modelo Tabla Empleado
class Empleado extends ActiveRecord {
    
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

    public function __construct($args = []){
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
        // $this->empleado_fecha_contratacion = $args['empleado_fecha_contratacion'] ?? null;
        $this->empleado_salario = $args['empleado_salario'] ?? 0.00;
        $this->empleado_situacion = $args['empleado_situacion'] ?? 1;
    }
}
