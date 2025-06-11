<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 1200px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Órdenes de Trabajo</h3>

  <form name="FormularioOrdenTrabajo" id="FormularioOrdenTrabajo" method="POST">
    <input type="hidden" id="orden_id" name="orden_id">

    <!-- RECEPCIÓN Y SERVICIO -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="recepcion_id" class="form-label">Recepción <span class="text-danger">*</span></label>
        <select class="form-select" id="recepcion_id" name="recepcion_id" required>
          <option value="">Seleccione una recepción</option>
        </select>
        <div class="form-text">Dispositivo que se va a reparar</div>
      </div>
      <div class="col-md-6">
        <label for="tipo_servicio_id" class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
        <select class="form-select" id="tipo_servicio_id" name="tipo_servicio_id" required>
          <option value="">Seleccione un tipo de servicio</option>
        </select>
        <div class="form-text">Tipo de reparación a realizar</div>
      </div>
    </div>

    <!-- EMPLEADO ASIGNADO -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="empleado_id" class="form-label">Empleado Asignado</label>
        <select class="form-select" id="empleado_id" name="empleado_id">
          <option value="">Sin asignar</option>
        </select>
        <div class="form-text">Técnico responsable de la reparación</div>
      </div>
    </div>

    <!-- DIAGNÓSTICO -->
    <div class="card mb-3">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Diagnóstico</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="orden_diagnostico" class="form-label">Diagnóstico <span class="text-danger">*</span></label>
          <textarea class="form-control" id="orden_diagnostico" name="orden_diagnostico" rows="3" required 
                    placeholder="Describa el diagnóstico del problema encontrado..."></textarea>
        </div>

        <div class="mb-3">
          <label for="orden_trabajo_realizado" class="form-label">Trabajo Realizado</label>
          <textarea class="form-control" id="orden_trabajo_realizado" name="orden_trabajo_realizado" rows="3" 
                    placeholder="Describa el trabajo realizado o por realizar..."></textarea>
        </div>

        <div class="mb-3">
          <label for="orden_repuestos_utilizados" class="form-label">Repuestos Utilizados</label>
          <textarea class="form-control" id="orden_repuestos_utilizados" name="orden_repuestos_utilizados" rows="2" 
                    placeholder="Liste los repuestos utilizados..."></textarea>
        </div>
      </div>
    </div>

    <!-- COSTOS -->
    <div class="card mb-3">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Costos</h5>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label for="orden_costo_repuestos" class="form-label">Costo Repuestos</label>
            <div class="input-group">
              <span class="input-group-text">Q.</span>
              <input type="number" class="form-control" id="orden_costo_repuestos" name="orden_costo_repuestos" 
                     step="0.01" min="0" value="0" placeholder="0.00">
            </div>
          </div>
          <div class="col-md-4">
            <label for="orden_costo_mano_obra" class="form-label">Costo Mano de Obra</label>
            <div class="input-group">
              <span class="input-group-text">Q.</span>
              <input type="number" class="form-control" id="orden_costo_mano_obra" name="orden_costo_mano_obra" 
                     step="0.01" min="0" value="0" placeholder="0.00">
            </div>
          </div>
          <div class="col-md-4">
            <label for="orden_costo_total" class="form-label">Costo Total</label>
            <div class="input-group">
              <span class="input-group-text">Q.</span>
              <input type="number" class="form-control" id="orden_costo_total" name="orden_costo_total" 
                     step="0.01" min="0" value="0" placeholder="0.00" readonly>
            </div>
            <div class="form-text">Se calcula automáticamente</div>
          </div>
        </div>
      </div>
    </div>

    <!-- FECHAS Y ESTADO (solo para editar) -->
    <div class="card mb-3" id="fechas-section" style="display: none;">
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-calendar me-2"></i>Fechas y Estado</h5>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label for="orden_fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="datetime-local" class="form-control" id="orden_fecha_inicio" name="orden_fecha_inicio">
            <div class="form-text">Opcional - Se puede asignar después</div>
          </div>
          <div class="col-md-4">
            <label for="orden_fecha_finalizacion" class="form-label">Fecha de Finalización</label>
            <input type="datetime-local" class="form-control" id="orden_fecha_finalizacion" name="orden_fecha_finalizacion">
            <div class="form-text">Se llena cuando se complete el trabajo</div>
          </div>
          <div class="col-md-4" id="estado-section">
            <label for="orden_estado" class="form-label">Estado de la Orden</label>
            <select class="form-select" id="orden_estado" name="orden_estado">
              <option value="ASIGNADA">Asignada</option>
              <option value="EN_PROGRESO">En Progreso</option>
              <option value="EN_ESPERA_REPUESTOS">En Espera de Repuestos</option>
              <option value="PAUSADA">Pausada</option>
              <option value="COMPLETADA">Completada</option>
              <option value="CANCELADA">Cancelada</option>
              <option value="ENTREGADA">Entregada</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- OBSERVACIONES -->
    <div class="mb-3">
      <label for="orden_observaciones" class="form-label">Observaciones</label>
      <textarea class="form-control" id="orden_observaciones" name="orden_observaciones" rows="2" 
                placeholder="Observaciones adicionales..."></textarea>
    </div>

    <!-- BOTONES -->
    <div class="row justify-content-center mt-4 g-2">
      <div class="col-auto">
        <button type="submit" class="btn btn-success px-4" id="BtnGuardar">
          <i class="bi bi-check-circle me-1"></i> Registrar Orden
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

<!-- Tabla de órdenes de trabajo -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-wrench me-2"></i> Órdenes de Trabajo
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TablaOrdenesTrabajo"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Información adicional -->
<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="alert alert-info" role="alert">
        <h6 class="alert-heading">
          <i class="bi bi-info-circle me-2"></i>Información sobre Órdenes de Trabajo
        </h6>
        <p class="mb-0">
          Las órdenes de trabajo se crean a partir de las recepciones de dispositivos. Cada orden debe tener un diagnóstico
          y puede ser asignada a un empleado específico. Los costos se calculan automáticamente sumando repuestos y mano de obra.
          Los estados permiten hacer seguimiento del progreso de la reparación.
        </p>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/ordentrabajo/index.js') ?>"></script>