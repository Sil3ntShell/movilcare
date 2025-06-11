<?php

namespace Model;

use PDO;
use Exception;

class ActiveRecord
{

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    protected static $idTabla = ['id'];
    protected static $id = 'id';

    // Alertas y Mensajes
    protected static $alertas = [];

    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database)
    {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje)
    {
        static::$alertas[$tipo][] = $mensaje;
    }
    // Validación
    public static function getAlertas()
    {
        return static::$alertas;
    }

    // Metodo que no teniamos
    public static function getDB()
    {
        return self::$db;
    }

    public function validar()
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Registros - CRUD
    public function guardar()
    {
        $resultado = '';

        // Obtener el primer ID de la tabla
        $idCampo = is_array(static::$idTabla) ? static::$idTabla[0] : static::$idTabla;

        if (!is_null($this->$idCampo)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    public static function all()
    {
        $query = "SELECT * FROM " . static::$tabla;
        $resultado = self::consultarSQL($query);

        // debuguear($resultado);
        return $resultado;
    }

    // Busca un registro por su id
    public static function find($id = [])
    {
        $query = "SELECT * FROM " . static::$tabla;

        // Siempre tratar como array
        $idsTabla = is_array(static::$idTabla) ? static::$idTabla : [static::$idTabla];

        if (count($idsTabla) > 1) {
            // ID compuesto
            $condiciones = [];
            foreach ($idsTabla as $campo) {
                if (isset($id[$campo])) {
                    $condiciones[] = "$campo = " . self::$db->quote($id[$campo]);
                }
            }
            if ($condiciones) {
                $query .= " WHERE " . join(' AND ', $condiciones);
            }
        } else {
            // ID simple
            $campo = $idsTabla[0];
            $valor = is_array($id) ? ($id[$campo] ?? $id[0] ?? null) : $id;
            if ($valor !== null) {
                $query .= " WHERE $campo = " . self::$db->quote($valor);
            }
        }

        // DEBUGGING: Ver la query
        error_log("Query find(): " . $query);

        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }


    // Obtener Registro
    public static function get($limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT " . $limite;
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Busqueda Where con Columna 
    public static function where($columna, $valor, $condicion = '=')
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE " . $columna . " " . $condicion . " '" . $valor . "'";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // SQL para Consultas Avanzadas.
    public static function SQL($consulta)
    {
        $query = $consulta;
        $resultado = self::$db->query($query);
        return $resultado;
    }

    // crea un nuevo registro
    public function crear()
    {
        // Obtener atributos sin sanitizar primero
        $atributos = $this->atributos();

        // Excluir campos vacíos o null
        $atributos = array_filter($atributos, function ($value) {
            return $value !== null && $value !== '';
        });

        $columnas = array_keys($atributos);
        $valores = array_values($atributos);
        // Crear placeholders para prepared statement
        $placeholders = str_repeat('?,', count($valores) - 1) . '?';

        $query = "INSERT INTO " . static::$tabla . " (" . join(', ', $columnas) . ") VALUES (" . $placeholders . ")";

        // DEBUGGING
        error_log("Query: " . $query);
        error_log("Valores: " . print_r($valores, true));

        try {
            $stmt = self::$db->prepare($query);
            $resultado = $stmt->execute($valores);

            return [
                'resultado' => $resultado,
                'id' => self::$db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Error en crear(): " . $e->getMessage());
            throw $e;
        }
    }

    public function actualizar()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key}={$value}";
        }

        $query = "UPDATE " . static::$tabla . " SET " . join(', ', $valores);

        // Manejar IDs como array siempre
        $idsTabla = is_array(static::$idTabla) ? static::$idTabla : [static::$idTabla];

        $condiciones = [];
        foreach ($idsTabla as $campo) {
            if (property_exists($this, $campo) && !is_null($this->$campo)) {
                $condiciones[] = "$campo = " . self::$db->quote($this->$campo);
            }
        }

        if ($condiciones) {
            $query .= " WHERE " . join(' AND ', $condiciones);
        }

        $resultado = self::$db->exec($query);
        return [
            'resultado' => $resultado,
        ];
    }

    // Eliminar un registro - Toma el ID de Active Record
    public function eliminar()
    { {
            $idsTabla = is_array(static::$idTabla) ? static::$idTabla : [static::$idTabla];

            $condiciones = [];
            foreach ($idsTabla as $campo) {
                if (property_exists($this, $campo) && !is_null($this->$campo)) {
                    $condiciones[] = "$campo = " . self::$db->quote($this->$campo);
                }
            }

            if (!$condiciones) {
                throw new Exception("No se puede eliminar: falta información del ID");
            }

            $query = "DELETE FROM " . static::$tabla . " WHERE " . join(' AND ', $condiciones);
            $resultado = self::$db->exec($query);
            return $resultado;
        }
    }

    public static function consultarSQL($query)
    {
        // Consultar la base de datos
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while ($registro = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $array[] = static::crearObjeto($registro);
        }

        // liberar la memoria
        $resultado->closeCursor();

        // retornar los resultados
        return $array;
    }

    public static function fetchArray($query)
    {
        try {
            $resultado = self::$db->query($query);

            if (!$resultado) {
                return [];
            }

            $respuesta = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $data = [];

            foreach ($respuesta as $value) {
                // Usar mb_convert_encoding en lugar de utf8_encode deprecated
                $data[] = array_change_key_case(array_map(function ($item) {
                    return mb_convert_encoding($item ?? '', 'UTF-8', 'auto');
                }, $value));
            }

            $resultado->closeCursor();
            return $data;
        } catch (Exception $e) {
            error_log("Error en fetchArray: " . $e->getMessage());
            return [];
        }
    }


    public static function fetchFirst($query)
    {
        try {
            $resultado = self::$db->query($query);

            if (!$resultado) {
                return null;
            }

            $respuesta = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $data = [];

            foreach ($respuesta as $value) {
                // Usar mb_convert_encoding en lugar de utf8_encode deprecated
                $data[] = array_change_key_case(array_map(function ($item) {
                    return mb_convert_encoding($item ?? '', 'UTF-8', 'auto');
                }, $value));
            }

            $resultado->closeCursor();
            return array_shift($data);
        } catch (Exception $e) {
            error_log("Error en fetchFirst: " . $e->getMessage());
            return null;
        }
    }

    protected static function crearObjeto($registro)
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            $key = strtolower($key);
            if (property_exists($objeto, $key)) {
                // Usar mb_convert_encoding en lugar de utf8_encode deprecated
                $objeto->$key = mb_convert_encoding($value ?? '', 'UTF-8', 'auto');
            }
        }

        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            $columna = strtolower($columna);

            // 🔧 CORRECCIÓN: Excluir el ID principal
            if ($columna === 'id') continue;

            // 🔧 CORRECCIÓN: Siempre tratar $idTabla como array
            $idsTabla = is_array(static::$idTabla) ? static::$idTabla : [static::$idTabla];
            $idsTablaLower = array_map('strtolower', $idsTabla);

            // Excluir todos los IDs de la tabla
            if (in_array($columna, $idsTablaLower)) continue;

            // Solo agregar si la propiedad existe en el objeto
            if (property_exists($this, $columna)) {
                $atributos[$columna] = $this->$columna;
            }
        }
        return $atributos;
    }

    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->quote($value);
        }
        return $sanitizado;
    }

    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    // METODOS NUEVOS PARA LA CLASE ACTIVE RECORD

    // Respuesta JSON estandarizada
    public static function respuestaJSON($codigo, $mensaje, $data = null, $httpCode = 200)
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=UTF-8');

        $respuesta = [
            'codigo' => $codigo,
            'mensaje' => $mensaje
        ];

        if ($data !== null) {
            $respuesta['data'] = $data;
        }

        echo json_encode($respuesta);
        exit;
    }

    // Buscar registros con condiciones y respuesta JSON automática
    public static function buscarConRespuesta($condiciones = "1=1", $orden = null)
    {
        try {
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . $condiciones;

            if ($orden) {
                $query .= " ORDER BY " . $orden;
            }

            $resultados = self::fetchArray($query);

            if ($resultados && count($resultados) > 0) {
                self::respuestaJSON(1, 'Registros encontrados', $resultados);
            } else {
                self::respuestaJSON(1, 'No hay registros', []);
            }
        } catch (Exception $e) {
            self::respuestaJSON(0, 'Error al buscar registros', null, 500);
        }
    }

    // Crear registro con validaciones y respuesta JSON automática
    public function crearConRespuesta($validaciones = [])
    {
        try {
            // Ejecutar validaciones personalizadas
            foreach ($validaciones as $validacion) {
                $resultado = $validacion($this);
                if ($resultado !== true) {
                    self::respuestaJSON(0, $resultado, null, 400);
                }
            }

            $resultado = $this->crear();

            if ($resultado['resultado']) {
                self::respuestaJSON(1, 'Registro creado exitosamente');
            } else {
                self::respuestaJSON(0, 'Error al crear el registro', null, 400);
            }
        } catch (Exception $e) {
            self::respuestaJSON(0, 'Error al crear: ' . $e->getMessage(), null, 500);
        }
    }

    // Actualizar registro con validaciones y respuesta JSON automática
    public function actualizarConRespuesta($validaciones = [])
    {
        try {
            // Ejecutar validaciones personalizadas
            foreach ($validaciones as $validacion) {
                $resultado = $validacion($this);
                if ($resultado !== true) {
                    self::respuestaJSON(0, $resultado, null, 400);
                }
            }

            $resultado = $this->actualizar();

            if ($resultado['resultado']) {
                self::respuestaJSON(1, 'Registro actualizado exitosamente');
            } else {
                self::respuestaJSON(0, 'Error al actualizar el registro', null, 400);
            }
        } catch (Exception $e) {
            self::respuestaJSON(0, 'Error al actualizar: ' . $e->getMessage(), null, 500);
        }
    }

    // Eliminar (cambiar situacion a 0) con respuesta JSON automática
    public static function eliminarLogicoConRespuesta($id, $campoId = 'id')
    {
        try {
            $query = "UPDATE " . static::$tabla . " SET situacion = 0 WHERE $campoId = " . self::$db->quote($id);
            $resultado = self::$db->exec($query);

            if ($resultado) {
                self::respuestaJSON(1, 'Registro eliminado exitosamente');
            } else {
                self::respuestaJSON(0, 'Error al eliminar el registro', null, 400);
            }
        } catch (Exception $e) {
            self::respuestaJSON(0, 'Error al eliminar: ' . $e->getMessage(), null, 500);
        }
    }

    // Validar campos requeridos
    public static function validarRequeridos($datos, $campos)
    {
        foreach ($campos as $campo) {
            if (empty($datos[$campo])) {
                return "El campo $campo es requerido";
            }
        }
        return true;
    }

    // Sanitizar datos de entrada
    public static function sanitizarDatos($datos)
    {
        $sanitizados = [];
        foreach ($datos as $key => $value) {
            if (is_string($value)) {
                $sanitizados[$key] = trim(htmlspecialchars($value));
            } else {
                $sanitizados[$key] = $value;
            }
        }
        return $sanitizados;
    }

    // Verificar si un valor ya existe en la BD
    public static function valorExiste($campo, $valor, $excluirId = null, $campoId = 'id')
    {
        $query = "SELECT COUNT(*) as total FROM " . static::$tabla . " WHERE $campo = " . self::$db->quote($valor);

        if ($excluirId) {
            $query .= " AND $campoId != " . self::$db->quote($excluirId);
        }

        $resultado = self::fetchArray($query);
        return $resultado && $resultado[0]['total'] > 0;
    }

    // Subir archivo de imagen
    public static function subirImagen($archivo, $carpeta = 'usuarios', $maxSize = 2097152, $nombrePersonalizado = null) // 2MB
    {
        try {
            // Validar que se subió un archivo
            if (!isset($archivo['tmp_name']) || empty($archivo['tmp_name'])) {
                return ['success' => false, 'mensaje' => 'No se seleccionó ningún archivo'];
            }

            // Validar errores de subida
            if ($archivo['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'mensaje' => 'Error al subir el archivo'];
            }

            // Validar tamaño
            if ($archivo['size'] > $maxSize) {
                return ['success' => false, 'mensaje' => 'El archivo es muy grande (máximo 2MB)'];
            }

            // Validar tipo de archivo
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $tipoArchivo = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);

            if (!in_array($tipoArchivo, $tiposPermitidos)) {
                return ['success' => false, 'mensaje' => 'Solo se permiten imágenes JPG, PNG, GIF, WEBP'];
            }

            // Crear carpetas si no existen
            $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . "/app03_dgcm/storage/imgs/$carpeta/";
            if (!file_exists($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            // Generar nombre único
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            // $nombreArchivo = uniqid('img_') . '_' . time() . '.' . $extension;
            // $rutaCompleta = $carpetaDestino . $nombreArchivo;

            if ($nombrePersonalizado) {
                // Usar nombre personalizado (DPI)
                $nombreArchivo = $nombrePersonalizado . '.' . $extension;
            } else {
                // Nombre único por defecto
                $nombreArchivo = uniqid('img_') . '_' . time() . '.' . $extension;
            }

            $rutaCompleta = $carpetaDestino . $nombreArchivo;

            // 🔧 VERIFICAR SI YA EXISTE Y ELIMINAR
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta); // Eliminar archivo existente
            }

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                $rutaRelativa = "/app03_dgcm/storage/imgs/$carpeta/$nombreArchivo";
                return [
                    'success' => true,
                    'ruta' => $rutaRelativa,
                    'nombre' => $nombreArchivo,
                    'mensaje' => 'Imagen subida exitosamente'
                ];
            } else {
                return ['success' => false, 'mensaje' => 'Error al guardar el archivo'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    // Eliminar imagen del servidor
    public static function eliminarImagen($rutaArchivo)
    {
        try {
            if (!empty($rutaArchivo) && file_exists($_SERVER['DOCUMENT_ROOT'] . $rutaArchivo)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $rutaArchivo);
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    // Obtener valor específico de la BD
    public static function obtenerValor($campo, $condicion, $valorCondicion)
    {
        try {
            $query = "SELECT $campo FROM " . static::$tabla . " WHERE $condicion = " . self::$db->quote($valorCondicion);
            $resultado = self::fetchFirst($query);
            return $resultado[$campo] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Buscar registros con relación a otra tabla
    public static function buscarConRelacionRespuesta($tablaRelacion, $llaveLocal, $llaveForanea, $camposRelacion = [], $condiciones = "1=1", $orden = null)
    {
        try {
            $query = "SELECT 
                    " . static::$tabla . ".*";

            // Agregar campos de la relación
            foreach ($camposRelacion as $alias => $campo) {
                $query .= ", " . $tablaRelacion . "." . $campo . " as " . $alias;
            }

            $query .= " FROM " . static::$tabla . "
                INNER JOIN " . $tablaRelacion . " 
                ON " . static::$tabla . "." . $llaveLocal . " = " . $tablaRelacion . "." . $llaveForanea . "
                WHERE " . $condiciones;

            if ($orden) {
                $query .= " ORDER BY " . $orden;
            }

            $resultados = self::fetchArray($query);

            if ($resultados && count($resultados) > 0) {
                self::respuestaJSON(1, 'Registros obtenidos exitosamente', $resultados);
            } else {
                self::respuestaJSON(1, 'No hay registros', []);
            }
        } catch (Exception $e) {
            self::respuestaJSON(0, 'Error al buscar registros: ' . $e->getMessage(), null, 500);
        }
    }
}
