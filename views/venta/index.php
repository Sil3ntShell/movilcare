<!-- Contenedor principal del formulario -->
<div class="container-fluid mt-4 p-4">
  <h2 class="mb-4 text-center text-primary fw-bold">
    <i class="bi bi-cart3 me-2"></i>Sistema de Punto de Venta
  </h2>

  <form name="FormularioVenta" id="FormularioVenta" method="POST">
    <input type="hidden" id="venta_id" name="venta_id">
    <input type="hidden" id="empleado_id" name="empleado_id" value="<?= $_SESSION['empleado_id'] ?? '' ?>">
    <input type="hidden" id="venta_subtotal" name="venta_subtotal" value="0">
    <input type="hidden" id="venta_descuento" name="venta_descuento" value="0">
    <input type="hidden" id="venta_impuestos" name="venta_impuestos" value="0">
    <input type="hidden" id="venta_total" name="venta_total" value="0">

    <div class="row">
      <!-- COLUMNA IZQUIERDA: FORMULARIO -->
      <div class="col-lg-8">

        <!-- INFORMACIÓN DE LA VENTA -->
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información de la Venta</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                  <option value="">Seleccione un cliente</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="venta_fecha" class="form-label">Fecha de Venta</label>
                <input type="date" class="form-control" id="venta_fecha" name="venta_fecha" value="<?= date('Y-m-d') ?>" readonly>
              </div>
              <div class="col-md-3">
                <label for="venta_forma_pago" class="form-label">Forma de Pago</label>
                <select class="form-select" id="venta_forma_pago" name="venta_forma_pago">
                  <option value="EFECTIVO">Efectivo</option>
                  <option value="TARJETA_CREDITO">Tarjeta de Crédito</option>
                  <option value="TARJETA_DEBITO">Tarjeta de Débito</option>
                  <option value="TRANSFERENCIA">Transferencia</option>
                  <option value="CREDITO">Crédito</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- AGREGAR PRODUCTOS -->
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-box me-2"></i>Agregar Productos del Inventario</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label for="producto_id" class="form-label">Producto</label>
                <select class="form-select" id="producto_id">
                  <option value="">Seleccione un producto</option>
                </select>
              </div>
              <div class="col-md-2">
                <label for="cantidad_producto" class="form-label">Cantidad</label>
                <input type="number" class="form-control text-center" id="cantidad_producto" value="1" min="1">
              </div>
              <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-success w-100" id="btn-agregar-producto">
                  <i class="bi bi-plus-circle"></i> Agregar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- AGREGAR SERVICIOS -->
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-wrench me-2"></i>Agregar Servicios Completados</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-10">
                <label for="servicio_id" class="form-label">Servicio/Reparación Completada</label>
                <select class="form-select" id="servicio_id">
                  <option value="">Seleccione un servicio</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-info w-100" id="btn-agregar-servicio">
                  <i class="bi bi-plus-circle"></i> Agregar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- DETALLE DE VENTA -->
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Detalle de Venta</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="TablaDetalle">
                <thead class="table-dark">
                  <tr>
                    <th>Descripción</th>
                    <th width="120" class="text-center">Cantidad</th>
                    <th width="120" class="text-end">Precio Unit.</th>
                    <th width="120" class="text-end">Subtotal</th>
                    <th width="80" class="text-center">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="bi bi-basket me-2"></i>No hay productos agregados
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- COLUMNA DERECHA: RESUMEN Y TOTALES -->
      <div class="col-lg-4">

        <!-- RESUMEN DE TOTALES -->
        <div class="card shadow-lg sticky-top" style="top: 20px;">
          <div class="card-header bg-gradient text-white text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <h4 class="mb-0"><i class="bi bi-calculator me-2"></i>Resumen de Venta</h4>
          </div>
          <div class="card-body">

            <!-- Totales -->
            <div class="mb-4">
              <!-- Subtotal / Descuento / Impuestos -->
              <!-- ... (igual que ya tienes) ... -->

              <!-- Estado de la venta -->
              <div class="mb-3">
                <label for="venta_estado" class="form-label fw-bold">Estado de la Venta</label>
                <select class="form-select" id="venta_estado" name="venta_estado">
                  <option value="PENDIENTE">Pendiente</option>
                  <option value="PROCESANDO">Procesando</option>
                  <option value="COMPLETADA" selected>Completada</option>
                  <option value="FACTURADA">Facturada</option>
                </select>
              </div>

              <!-- Observaciones -->
              <div class="mb-4">
                <label for="venta_observaciones" class="form-label fw-bold">Observaciones</label>
                <textarea class="form-control" id="venta_observaciones" name="venta_observaciones" rows="2" placeholder="Comentarios sobre la venta..."></textarea>
              </div>

              <!-- Botones de Acción -->
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg fw-bold" id="BtnGuardar">
                  <i class="bi bi-check-circle me-2"></i>PROCESAR VENTA
                </button>
                <button type="button" class="btn btn-outline-secondary" id="BtnLimpiar">
                  <i class="bi bi-arrow-clockwise me-2"></i>Nueva Venta
                </button>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </form>
</div>

<!-- HISTORIAL DE VENTAS -->
<div class="container-fluid mt-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-table me-2"></i> Historial de Ventas
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100" id="TablaVentas">
              <thead class="table-dark">
                <tr>
                  <th>No. Venta</th>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Empleado</th>
                  <th>Subtotal</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Forma Pago</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Ver Detalle de Venta -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Detalle de Venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenido-detalle">
        <!-- Se llenará dinámicamente -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Script del módulo -->
<script src="<?= asset('build/js/venta/index.js') ?>"></script>
