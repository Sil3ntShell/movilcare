<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 1200px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Recepción de Dispositivos</h3>

  <form name="FormularioRecepcion" id="FormularioRecepcion" method="POST">
    <input type="hidden" id="recepcion_id" name="recepcion_id">

    <!-- CLIENTE Y EMPLEADO -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
        <select class="form-select" id="cliente_id" name="cliente_id" required>
          <option value="">Seleccione un cliente</option>
        </select>
        <div class="form-text">El cliente que trae el dispositivo</div>
      </div>
      <div class="col-md-6">
        <label for="empleado_id" class="form-label">Empleado que Recibe <span class="text-danger">*</span></label>
        <select class="form-select" id="empleado_id" name="empleado_id" required>
          <option value="">Seleccione un empleado</option>
        </select>
        <div class="form-text">Empleado responsable de la recepción</div>
      </div>
    </div>

    <!-- INFORMACIÓN DEL DISPOSITIVO -->
    <div class="card mb-3">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-phone me-2"></i>Información del Dispositivo</h5>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label for="recepcion_tipo_celular" class="form-label">Tipo de Dispositivo <span class="text-danger">*</span></label>
            <select class="form-select" id="recepcion_tipo_celular" name="recepcion_tipo_celular" required>
              <option value="">Seleccione tipo</option>
              <option value="Smartphone">Smartphone</option>
              <option value="iPhone">iPhone</option>
              <option value="Teléfono Básico">Teléfono Básico</option>
              <option value="Tablet Android">Tablet Android</option>
              <option value="iPad">iPad</option>
              <option value="Smartwatch">Smartwatch</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="recepcion_marca" class="form-label">Marca <span class="text-danger">*</span></label>
            <select class="form-select" id="recepcion_marca" name="recepcion_marca" required>
              <option value="">Seleccione marca</option>
              <option value="Samsung">Samsung</option>
              <option value="Apple">Apple</option>
              <option value="Huawei">Huawei</option>
              <option value="Xiaomi">Xiaomi</option>
              <option value="LG">LG</option>
              <option value="Motorola">Motorola</option>
              <option value="Nokia">Nokia</option>
              <option value="Sony">Sony</option>
              <option value="OnePlus">OnePlus</option>
              <option value="Oppo">Oppo</option>
              <option value="Vivo">Vivo</option>
              <option value="Realme">Realme</option>
              <option value="Honor">Honor</option>
              <option value="Google">Google</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="recepcion_modelo" class="form-label">Modelo</label>
            <input type="text" class="form-control" id="recepcion_modelo" name="recepcion_modelo" maxlength="100" placeholder="Ej. Galaxy S21, iPhone 13 Pro">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="recepcion_imei" class="form-label">IMEI</label>
            <input type="text" class="form-control" id="recepcion_imei" name="recepcion_imei" maxlength="20" placeholder="Código IMEI del dispositivo">
          </div>
          <div class="col-md-6">
            <label for="recepcion_numero_serie" class="form-label">Número de Serie</label>
            <input type="text" class="form-control" id="recepcion_numero_serie" name="recepcion_numero_serie" maxlength="100" placeholder="Número de serie del dispositivo">
          </div>
        </div>
      </div>
    </div>

    <!-- PROBLEMA Y ESTADO -->
    <div class="card mb-3">
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Problema y Estado del Dispositivo</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="recepcion_motivo_ingreso" class="form-label">Motivo de Ingreso / Problema <span class="text-danger">*</span></label>
          <textarea class="form-control" id="recepcion_motivo_ingreso" name="recepcion_motivo_ingreso" rows="3" required placeholder="Describa detalladamente el problema o motivo por el cual ingresa el dispositivo..."></textarea>
        </div>

        <div class="mb-3">
          <label for="recepcion_estado_dispositivo" class="form-label">Estado Físico del Dispositivo</label>
          <textarea class="form-control" id="recepcion_estado_dispositivo" name="recepcion_estado_dispositivo" rows="2" placeholder="Describa golpes, rayones, roturas, estado de la pantalla, etc."></textarea>
        </div>

        <div class="mb-3">
          <label for="recepcion_accesorios" class="form-label">Accesorios Incluidos</label>
          <textarea class="form-control" id="recepcion_accesorios" name="recepcion_accesorios" rows="2" placeholder="Cargador, cable, funda, protector de pantalla, etc."></textarea>
        </div>

        <div class="mb-3">
          <label for="recepcion_observaciones_cliente" class="form-label">Observaciones del Cliente</label>
          <textarea class="form-control" id="recepcion_observaciones_cliente" name="recepcion_observaciones_cliente" rows="2" placeholder="Comentarios adicionales del cliente..."></textarea>
        </div>
      </div>
    </div>

    <!-- ESTIMACIONES -->
    <div class="card mb-3">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Estimaciones</h5>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="recepcion_costo_estimado" class="form-label">Costo Estimado</label>
            <div class="input-group">
              <span class="input-group-text">Q.</span>
              <input type="number" class="form-control" id="recepcion_costo_estimado" name="recepcion_costo_estimado" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="form-text">Costo aproximado de la reparación (opcional)</div>
          </div>
          <div class="col-md-6">
            <label for="recepcion_tiempo_estimado" class="form-label">Tiempo Estimado</label>
            <div class="input-group">
              <input type="number" class="form-control" id="recepcion_tiempo_estimado" name="recepcion_tiempo_estimado" min="1" value="1" placeholder="1">
              <span class="input-group-text">días</span>
            </div>
            <div class="form-text">Tiempo estimado para completar la reparación</div>
          </div>
        </div>

        <!-- ESTADO DE RECEPCIÓN (solo para editar) -->
        <div class="row g-3 mb-3" id="estado-section" style="display: none;">
          <div class="col-md-12">
            <label for="recepcion_estado" class="form-label">Estado de la Recepción</label>
            <select class="form-select" id="recepcion_estado" name="recepcion_estado">
              <option value="RECIBIDO">Recibido</option>
              <option value="EN_DIAGNOSTICO">En Diagnóstico</option>
              <option value="ESPERANDO_REPUESTOS">Esperando Repuestos</option>
              <option value="EN_REPARACION">En Reparación</option>
              <option value="REPARADO">Reparado</option>
              <option value="ENTREGADO">Entregado</option>
              <option value="CANCELADO">Cancelado</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- BOTONES -->
    <div class="row justify-content-center mt-4 g-2">
      <div class="col-auto">
        <button type="submit" class="btn btn-success px-4" id="BtnGuardar">
          <i class="bi bi-check-circle me-1"></i> Registrar Recepción
        </button>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-warning d-none px-4" id="BtnModificar">
          <i class="bi bi-pencil-square me-1"></i> Actualizar
        </button>
      </div>
      <div class="col-auto">
        <button type="reset" class="btn btn-secondary px-4" id="BtnLimpiar">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Tabla de recepciones -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-inbox me-2"></i> Recepciones de Dispositivos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TablaRecepciones"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/recepcion/index.js') ?>"></script>