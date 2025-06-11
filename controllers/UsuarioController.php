<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Usuario;

/**
 * UsuarioController
 * 
 * Controlador para el manejo completo de usuarios del sistema
 * Maneja las operaciones CRUD y APIs para la gestión de usuarios
 * 
 * @author Tu Nombre
 * @version 1.0
 */
class UsuarioController extends ActiveRecord
{
    /**
     * Renderiza la página principal de usuarios
     * 
     * @param Router $router Instancia del router para renderizar vistas
     * @return void
     */
    public static function renderizarPagina(Router $router)
    {
        // Renderizar la vista de usuarios con datos vacíos
        $router->render('usuario/index', []);
    }

    /**
     * API para buscar y listar todos los usuarios activos
     * 
     * Endpoint: GET /usuario/buscarAPI
     * Retorna lista de usuarios en formato JSON
     * 
     * @return void (echo JSON response)
     */
    public static function buscarAPI()
    {
        // Configurar headers para API
        getHeadersApi();
        
        try {
            // Log del inicio de búsqueda
            error_log("=== BÚSQUEDA DE USUARIOS INICIADA ===");
            error_log("Timestamp: " . date('Y-m-d H:i:s'));
            
            // Query SQL para obtener usuarios activos con toda la información necesaria
            $query = "SELECT 
                        usuario_id,
                        usuario_nom1,
                        usuario_nom2,
                        usuario_ape1,
                        usuario_ape2,
                        usuario_tel,
                        usuario_direc,
                        usuario_dpi,
                        usuario_correo,
                        usuario_fotografia,
                        usuario_fecha_creacion,
                        usuario_fecha_contra,
                        usuario_situacion,
                        rol_id,
                        usuario_ultimo_acceso
                      FROM usuario 
                      WHERE usuario_situacion = 1
                      ORDER BY usuario_fecha_creacion DESC, usuario_id DESC";
                      
            // Ejecutar query usando el modelo Usuario
            $usuarios = Usuario::fetchArray($query);
            
            // Log de resultado exitoso
            error_log("✅ Usuarios encontrados: " . count($usuarios));
            
            // Procesar datos adicionales si es necesario
            foreach ($usuarios as &$usuario) {
                // Formatear fechas si existen
                if ($usuario['usuario_fecha_creacion']) {
                    $usuario['fecha_formateada'] = date('d/m/Y H:i', strtotime($usuario['usuario_fecha_creacion']));
                }
                
                // Limpiar datos sensibles para la respuesta (no enviar contraseñas, tokens, etc.)
                unset($usuario['usuario_contra']);
                unset($usuario['usuario_token']);
            }
            
            // Respuesta exitosa
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios encontrados exitosamente',
                'detalle' => count($usuarios) . ' usuarios encontrados',
                'data' => $usuarios,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            // Log del error
            error_log("❌ Error en buscarAPI: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Respuesta de error
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al buscar los usuarios',
                'detalle' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * API para guardar un nuevo usuario
     * 
     * Endpoint: POST /usuario/guardarAPI
     * Recibe datos del formulario via FormData y crea un nuevo usuario
     * 
     * @return void (echo JSON response)
     */
    public static function guardarAPI()
    {
        // Configurar headers para API
        getHeadersApi();

        // ===========================================
        // LOGS DETALLADOS PARA DEBUG
        // ===========================================
        error_log("=== PROCESO DE REGISTRO DE USUARIO INICIADO ===");
        error_log("Timestamp: " . date('Y-m-d H:i:s'));
        error_log("IP Cliente: " . ($_SERVER['REMOTE_ADDR'] ?? 'No disponible'));
        error_log("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'No disponible'));
        error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No definido'));
        
        // Debug de datos POST recibidos (sin mostrar contraseñas)
        $postDebug = $_POST;
        if (isset($postDebug['usuario_contra'])) $postDebug['usuario_contra'] = '[PROTEGIDO]';
        if (isset($postDebug['confirmar_contra'])) $postDebug['confirmar_contra'] = '[PROTEGIDO]';
        error_log("POST recibido: " . json_encode($postDebug));
        
        // Debug de archivos recibidos
        error_log("FILES recibido: " . json_encode($_FILES));
        error_log("================================================");

        // ===========================================
        // VALIDACIONES DE CAMPOS OBLIGATORIOS
        // ===========================================
        
        // Validar primer nombre
        if (empty($_POST['usuario_nom1'])) {
            error_log("❌ VALIDACIÓN FALLIDA: usuario_nom1 vacío");
            self::respuestaError(400, 'El primer nombre es obligatorio');
            return;
        }

        // Validar primer apellido
        if (empty($_POST['usuario_ape1'])) {
            error_log("❌ VALIDACIÓN FALLIDA: usuario_ape1 vacío");
            self::respuestaError(400, 'El primer apellido es obligatorio');
            return;
        }

        // Validar teléfono (exactamente 8 dígitos)
        if (empty($_POST['usuario_tel']) || strlen($_POST['usuario_tel']) != 8 || !ctype_digit($_POST['usuario_tel'])) {
            error_log("❌ VALIDACIÓN FALLIDA: teléfono inválido - " . ($_POST['usuario_tel'] ?? 'vacío'));
            self::respuestaError(400, 'El teléfono debe tener exactamente 8 dígitos numéricos');
            return;
        }

        // Validar DPI (exactamente 13 dígitos)
        if (empty($_POST['usuario_dpi']) || strlen($_POST['usuario_dpi']) != 13 || !ctype_digit($_POST['usuario_dpi'])) {
            error_log("❌ VALIDACIÓN FALLIDA: DPI inválido - " . ($_POST['usuario_dpi'] ?? 'vacío'));
            self::respuestaError(400, 'El DPI debe tener exactamente 13 dígitos numéricos');
            return;
        }

        // Validar dirección
        if (empty($_POST['usuario_direc'])) {
            error_log("❌ VALIDACIÓN FALLIDA: dirección vacía");
            self::respuestaError(400, 'La dirección es obligatoria');
            return;
        }

        // Validar correo electrónico
        if (empty($_POST['usuario_correo']) || !filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            error_log("❌ VALIDACIÓN FALLIDA: correo inválido - " . ($_POST['usuario_correo'] ?? 'vacío'));
            self::respuestaError(400, 'El correo electrónico es obligatorio y debe ser válido');
            return;
        }

        // Validar contraseña longitud mínima
        if (empty($_POST['usuario_contra']) || strlen($_POST['usuario_contra']) < 10) {
            error_log("❌ VALIDACIÓN FALLIDA: contraseña muy corta");
            self::respuestaError(400, 'La contraseña debe tener al menos 10 caracteres');
            return;
        }

        // ===========================================
        // VALIDACIONES DE COMPLEJIDAD DE CONTRASEÑA
        // ===========================================
        
        // Validar mayúscula
        if (!preg_match('/[A-Z]/', $_POST['usuario_contra'])) {
            error_log("❌ VALIDACIÓN FALLIDA: contraseña sin mayúsculas");
            self::respuestaError(400, 'La contraseña debe contener al menos una letra mayúscula');
            return;
        }

        // Validar minúscula
        if (!preg_match('/[a-z]/', $_POST['usuario_contra'])) {
            error_log("❌ VALIDACIÓN FALLIDA: contraseña sin minúsculas");
            self::respuestaError(400, 'La contraseña debe contener al menos una letra minúscula');
            return;
        }

        // Validar número
        if (!preg_match('/[0-9]/', $_POST['usuario_contra'])) {
            error_log("❌ VALIDACIÓN FALLIDA: contraseña sin números");
            self::respuestaError(400, 'La contraseña debe contener al menos un número');
            return;
        }

        // Validar carácter especial
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $_POST['usuario_contra'])) {
            error_log("❌ VALIDACIÓN FALLIDA: contraseña sin caracteres especiales");
            self::respuestaError(400, 'La contraseña debe contener al menos un carácter especial');
            return;
        }

        // Validar confirmación de contraseña
        if ($_POST['usuario_contra'] !== $_POST['confirmar_contra']) {
            error_log("❌ VALIDACIÓN FALLIDA: contraseñas no coinciden");
            self::respuestaError(400, 'Las contraseñas no coinciden');
            return;
        }

        error_log("✅ Todas las validaciones básicas pasaron correctamente");

        try {
            // ===========================================
            // VERIFICAR DUPLICADOS EN BASE DE DATOS
            // ===========================================
            error_log("🔍 Verificando si ya existe usuario con este correo o DPI...");
            
            $usuarioExistente = Usuario::verificarUsuarioExistente($_POST['usuario_correo'], $_POST['usuario_dpi']);
            
            if ($usuarioExistente['correo_existe']) {
                error_log("❌ USUARIO DUPLICADO: Correo ya existe - " . $_POST['usuario_correo']);
                self::respuestaError(400, 'Ya existe un usuario registrado con este correo electrónico');
                return;
            }

            if ($usuarioExistente['dpi_existe']) {
                error_log("❌ USUARIO DUPLICADO: DPI ya existe - " . $_POST['usuario_dpi']);
                self::respuestaError(400, 'Ya existe un usuario registrado con este DPI');
                return;
            }

            error_log("✅ No hay duplicados - correo y DPI disponibles");

            // ===========================================
            // PROCESAMIENTO DE FOTOGRAFÍA
            // ===========================================
            error_log("📷 Iniciando procesamiento de fotografía...");
            
            $nombreFotografia = '';
            try {
                $nombreFotografia = self::procesarFotografia();
                error_log("✅ Fotografía procesada correctamente: " . ($nombreFotografia ?: 'Sin fotografía'));
            } catch (Exception $e) {
                error_log("❌ Error procesando fotografía: " . $e->getMessage());
                self::respuestaError(400, $e->getMessage());
                return;
            }

            // ===========================================
            // SANITIZACIÓN Y PREPARACIÓN DE DATOS
            // ===========================================
            error_log("🧹 Sanitizando y preparando datos...");
            
            // Sanitizar y formatear nombres (Primera letra mayúscula)
            $usuario_nom1 = ucwords(strtolower(trim(htmlspecialchars($_POST['usuario_nom1']))));
            $usuario_nom2 = !empty($_POST['usuario_nom2']) ? ucwords(strtolower(trim(htmlspecialchars($_POST['usuario_nom2'])))) : '';
            $usuario_ape1 = ucwords(strtolower(trim(htmlspecialchars($_POST['usuario_ape1']))));
            $usuario_ape2 = !empty($_POST['usuario_ape2']) ? ucwords(strtolower(trim(htmlspecialchars($_POST['usuario_ape2'])))) : '';
            
            // Sanitizar campos numéricos
            $usuario_tel = filter_var($_POST['usuario_tel'], FILTER_SANITIZE_NUMBER_INT);
            $usuario_dpi = filter_var($_POST['usuario_dpi'], FILTER_SANITIZE_NUMBER_INT);
            
            // Sanitizar otros campos
            $usuario_direc = trim(htmlspecialchars($_POST['usuario_direc']));
            $usuario_correo = filter_var($_POST['usuario_correo'], FILTER_SANITIZE_EMAIL);

            // Encriptar contraseña de forma segura
            $usuario_contra_hash = password_hash($_POST['usuario_contra'], PASSWORD_DEFAULT);
            
            // Generar token único para el usuario
            $usuario_token = bin2hex(random_bytes(32));

            error_log("✅ Datos sanitizados correctamente");
            
            // Log de datos finales (sin mostrar información sensible)
            error_log("📝 Datos finales preparados:");
            error_log("- Nombre: $usuario_nom1 $usuario_nom2");
            error_log("- Apellidos: $usuario_ape1 $usuario_ape2");
            error_log("- DPI: $usuario_dpi");
            error_log("- Teléfono: $usuario_tel");
            error_log("- Correo: $usuario_correo");
            error_log("- Dirección: $usuario_direc");
            error_log("- Fotografía: " . ($nombreFotografia ?: 'Sin foto'));

            // ===========================================
            // CREACIÓN DEL OBJETO USUARIO
            // ===========================================
            error_log("👤 Creando objeto Usuario...");
            
            $usuario = new Usuario([
                'usuario_nom1' => $usuario_nom1,
                'usuario_nom2' => $usuario_nom2,
                'usuario_ape1' => $usuario_ape1,
                'usuario_ape2' => $usuario_ape2,
                'usuario_tel' => $usuario_tel,
                'usuario_direc' => $usuario_direc,
                'usuario_dpi' => $usuario_dpi,
                'usuario_correo' => $usuario_correo,
                'usuario_contra' => $usuario_contra_hash,
                'usuario_token' => $usuario_token,
                'usuario_fotografia' => $nombreFotografia,
                'usuario_situacion' => 1, // Usuario activo por defecto
                'rol_id' => 1 // Rol de usuario normal por defecto
            ]);

            // ===========================================
            // GUARDAR EN BASE DE DATOS
            // ===========================================
            error_log("💾 Guardando usuario en base de datos...");
            
            $resultado = $usuario->crear();

            // Verificar resultado de la operación
            if ($resultado && $resultado['resultado']) {
                // ✅ REGISTRO EXITOSO
                error_log("✅ Usuario registrado exitosamente con ID: " . $resultado['id']);
                
                // Log de auditoría del registro exitoso
                error_log("📊 AUDITORÍA - Usuario registrado:");
                error_log("- ID: " . $resultado['id']);
                error_log("- Nombre completo: $usuario_nom1 $usuario_nom2 $usuario_ape1 $usuario_ape2");
                error_log("- Correo: $usuario_correo");
                error_log("- DPI: $usuario_dpi");
                error_log("- IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'No disponible'));
                error_log("- Timestamp: " . date('Y-m-d H:i:s'));
                
                // Respuesta exitosa al cliente
                http_response_code(201);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario registrado correctamente',
                    'datos' => [
                        'usuario_id' => $resultado['id'],
                        'usuario_token' => $usuario_token,
                        'nombre_completo' => trim("$usuario_nom1 $usuario_nom2 $usuario_ape1 $usuario_ape2")
                    ],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
            } else {
                // ❌ ERROR AL GUARDAR
                error_log("❌ Error al guardar usuario en base de datos");
                
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar el usuario en la base de datos',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }

        } catch (Exception $e) {
            // ❌ ERROR GENERAL DEL PROCESO
            error_log("💥 ERROR CRÍTICO en guardarAPI: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        error_log("=== PROCESO DE REGISTRO FINALIZADO ===");
    }

    /**
     * Procesa y valida la fotografía subida por el usuario
     * 
     * @return string Nombre del archivo guardado, o cadena vacía si no hay archivo
     * @throws Exception Si hay errores en la validación o procesamiento
     */
     private static function procesarFotografia()
    {
        // Si no hay archivo subido, retornar cadena vacía (es opcional)
        if (!isset($_FILES['usuario_fotografia']) || $_FILES['usuario_fotografia']['error'] === UPLOAD_ERR_NO_FILE) {
            error_log("📷 No se subió fotografía - continuando sin imagen");
            return '';
        }

        $archivo = $_FILES['usuario_fotografia'];
        error_log("📷 Procesando fotografía: " . $archivo['name']);

        // ===========================================
        // VALIDACIONES DEL ARCHIVO
        // ===========================================
        
        // Validar que no hay errores de subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
            ];
            
            $mensajeError = $errores[$archivo['error']] ?? 'Error desconocido al subir el archivo: ' . $archivo['error'];
            error_log("❌ Error de subida: $mensajeError");
            throw new Exception($mensajeError);
        }

        // Validar tamaño máximo (2MB)
        $tamañoMaximo = 2 * 1024 * 1024; // 2MB en bytes
        if ($archivo['size'] > $tamañoMaximo) {
            error_log("❌ Archivo muy grande: " . round($archivo['size'] / 1024 / 1024, 2) . "MB");
            throw new Exception('El archivo es muy grande. Tamaño máximo permitido: 2MB');
        }

        // Validar tipo MIME del archivo usando finfo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipoMime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($tipoMime, $tiposPermitidos)) {
            error_log("❌ Tipo MIME no permitido: $tipoMime");
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, JPEG, PNG');
        }

        // Validar extensión del archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($extension, $extensionesPermitidas)) {
            error_log("❌ Extensión no permitida: $extension");
            throw new Exception('Extensión de archivo no permitida. Solo se permiten: jpg, jpeg, png');
        }

        // ===========================================
        // PREPARACIÓN DEL DIRECTORIO - CORREGIDO
        // ===========================================
        
        // 🔧 CORRECCIÓN: Usar ruta consistente con tu proyecto
        $directorioDestino = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/';
        
        // Crear directorio si no existe
        if (!is_dir($directorioDestino)) {
            if (!mkdir($directorioDestino, 0755, true)) {
                error_log("❌ No se pudo crear directorio: $directorioDestino");
                throw new Exception('No se pudo crear el directorio de fotografías');
            }
            error_log("📁 Directorio creado: $directorioDestino");
        }

        // Verificar permisos de escritura
        if (!is_writable($directorioDestino)) {
            error_log("❌ Directorio sin permisos de escritura: $directorioDestino");
            throw new Exception('El directorio de fotografías no tiene permisos de escritura');
        }

        // ===========================================
        // GENERACIÓN DE NOMBRE ÚNICO
        // ===========================================
        
        // Generar nombre único para evitar conflictos
        $timestamp = time();
        $uniqid = uniqid();
        $nombreArchivo = "usuario_{$timestamp}_{$uniqid}.{$extension}";
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        error_log("📁 Archivo destino: $nombreArchivo");
        error_log("📁 Ruta completa: $rutaCompleta");

        // ===========================================
        // GUARDAR ARCHIVO
        // ===========================================
        
        // Mover archivo del directorio temporal al destino final
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            error_log("❌ Error al mover archivo a: $rutaCompleta");
            throw new Exception('Error al guardar el archivo en el servidor');
        }

        // Verificar que el archivo se guardó correctamente
        if (!file_exists($rutaCompleta)) {
            error_log("❌ Archivo no existe después de mover: $rutaCompleta");
            throw new Exception('Error: el archivo no se guardó correctamente');
        }

        // Establecer permisos seguros para el archivo
        chmod($rutaCompleta, 0644);

        error_log("✅ Fotografía guardada exitosamente: $nombreArchivo");
        error_log("📊 Tamaño del archivo: " . round(filesize($rutaCompleta) / 1024, 2) . " KB");

        // Retornar solo el nombre del archivo (no la ruta completa)
        return $nombreArchivo;
    }

    /**
     * Función auxiliar para enviar respuestas de error estandarizadas
     * 
     * @param int $httpCode Código HTTP de error
     * @param string $mensaje Mensaje de error para el usuario
     * @param string $detalle Detalle adicional del error (opcional)
     * @return void
     */
    private static function respuestaError($httpCode, $mensaje, $detalle = '')
    {
        http_response_code($httpCode);
        echo json_encode([
            'codigo' => 0,
            'mensaje' => $mensaje,
            'detalle' => $detalle,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // ===========================================
    // MÉTODOS ADICIONALES PARA FUTURAS FUNCIONALIDADES
    // ===========================================

   /**
     * API para obtener un usuario específico por ID
     */
    public static function obtenerPorIdAPI()
    {
        getHeadersApi();
        
        $usuario_id = $_GET['id'] ?? $_POST['usuario_id'] ?? null;
        
        if (!$usuario_id || !is_numeric($usuario_id)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
            return;
        }
        
        try {
            $query = "SELECT * FROM usuario WHERE usuario_id = ? AND usuario_situacion = 1";
            $usuario = Usuario::fetchFirst($query, [$usuario_id]);
            
            if ($usuario) {
                // Limpiar datos sensibles
                unset($usuario['usuario_contra'], $usuario['usuario_token']);
                
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario encontrado',
                    'data' => $usuario
                ]);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * API para actualizar un usuario existente
     */
    public static function actualizarAPI()
    {
        getHeadersApi();
        
        $usuario_id = $_POST['usuario_id'] ?? null;
        
        if (!$usuario_id || !is_numeric($usuario_id)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
            return;
        }
        
        try {
            $usuario = Usuario::obtenerUsuarioActivo($usuario_id);
            if (!$usuario) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
                return;
            }
            
            // Validaciones básicas
            if (empty($_POST['usuario_nom1'])) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer nombre es obligatorio']);
                return;
            }
            
            if (empty($_POST['usuario_ape1'])) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer apellido es obligatorio']);
                return;
            }
            
            if (!empty($_POST['usuario_tel']) && (strlen($_POST['usuario_tel']) != 8 || !ctype_digit($_POST['usuario_tel']))) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
                return;
            }
            
            if (!empty($_POST['usuario_correo']) && !filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
                return;
            }
            
            // Verificar correo duplicado
            if (!empty($_POST['usuario_correo'])) {
                $correoExiste = Usuario::fetchFirst(
                    "SELECT usuario_id FROM usuario WHERE usuario_correo = ? AND usuario_id != ? AND usuario_situacion = 1",
                    [$_POST['usuario_correo'], $usuario_id]
                );
                
                if ($correoExiste) {
                    echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está en uso']);
                    return;
                }
            }
            
            // Actualizar campos
            $campos = ['usuario_nom1', 'usuario_nom2', 'usuario_ape1', 'usuario_ape2', 'usuario_tel', 'usuario_correo', 'usuario_direc'];
            
            foreach ($campos as $campo) {
                if (isset($_POST[$campo])) {
                    if (in_array($campo, ['usuario_nom1', 'usuario_nom2', 'usuario_ape1', 'usuario_ape2'])) {
                        $usuario->$campo = ucwords(strtolower(trim($_POST[$campo])));
                    } else {
                        $usuario->$campo = trim($_POST[$campo]);
                    }
                }
            }
            
            $resultado = $usuario->guardar();
            
            if ($resultado && $resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario actualizado correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al actualizar usuario']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * API para eliminar (desactivar) un usuario
     */
    public static function eliminarAPI()
    {
        getHeadersApi();
        
        // Leer datos JSON o POST
        $input = json_decode(file_get_contents('php://input'), true);
        $usuario_id = $input['usuario_id'] ?? $_POST['usuario_id'] ?? null;
        
        if (!$usuario_id || !is_numeric($usuario_id)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
            return;
        }
        
        try {
            $usuario = Usuario::obtenerPorId($usuario_id);
            if (!$usuario || $usuario->usuario_situacion != 1) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado o ya inactivo']);
                return;
            }
            
            $usuario->usuario_situacion = 0;
            $resultado = $usuario->guardar();
            
            if ($resultado && $resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario eliminado correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar usuario']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
}