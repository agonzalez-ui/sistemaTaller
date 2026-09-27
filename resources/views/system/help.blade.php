@extends('layouts.app')
@section('title', 'Ayuda del sistema')
@section('content-width', 'max-w-5xl')
@section('app-contents')
<div class="mt-6">
    <p class="text-slate-600">Guía rápida para trabajar en el taller. Abra cada sección para consultar los pasos.</p>
    <div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm leading-relaxed text-amber-900">Su rol determina las opciones disponibles. Si falta un botón o recibe un aviso de acceso, solicite al administrador que revise sus permisos.</div>
    <div class="mt-6 space-y-4">
        <details class="rounded-2xl border border-slate-200 p-4" open>
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Inicio, navegación y sesión</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Use el menú superior para cambiar de módulo. El logo y la opción Inicio lo llevan al panel principal.</p>
                <p>En tablets y celulares, las opciones se distribuyen en varias filas. Deslice hacia abajo para ver el resto del contenido.</p>
                <p>Al terminar, presione Cerrar sesión. Evite compartir contraseñas o dejar una sesión abierta en equipos compartidos.</p>
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Ir al inicio →</a>
            </div>
        </details>
        @can('module-access', ['customers','view'])
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Clientes</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Desde Clientes puede consultar el nombre completo, la identificación y los datos de contacto.</p>
                <p>Si tiene permiso, seleccione Nuevo cliente, complete el formulario y presione Guardar. Revise los mensajes cuando falte un dato o la identificación ya exista.</p>
                <p>Use Editar para corregir los datos. Antes de eliminar un cliente, revise si tiene motos u otros registros relacionados.</p>
                <a href="{{ route('customers.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Abrir clientes →</a>
            </div>
        </details>
        @endcan
        @can('module-access', ['reports','view'])
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Reportes y bitácoras</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>El centro de reportes contiene facturación, órdenes, inventario, ingresos y salidas, y movimientos de usuarios.</p>
                <p>Seleccione los filtros y presione Aplicar filtros. Cada reporte muestra sus totales y puede imprimirse con el resultado actual.</p>
                <p>Las bitácoras de seguridad permiten investigar sesiones y cambios por rango de fechas, usuario y tipo de movimiento.</p>
                <a href="{{ route('reports.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Abrir reportes →</a>
            </div>
        </details>
        @endcan
        @can('module-access', ['billing','view'])
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Facturas</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Una orden en estado Lista o Entregada puede convertirse en factura una sola vez. Use Generar factura desde el detalle de la orden o seleccione Nueva factura.</p>
                <p>El sistema copia los repuestos, la mano de obra y sus precios. Indique el descuento y el porcentaje de impuesto; los totales se calculan en el servidor.</p>
                <p>Después de emitirla, puede consultarla e imprimirla. Una factura anulada se conserva con su motivo y fecha para mantener la auditoría.</p>
                <a href="{{ route('invoices.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Abrir facturas →</a>
            </div>
        </details>
        @endcan
        @can('module-access', ['vehicles','view'])
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Motos y propietarios</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Registre primero al propietario en Clientes. Luego seleccione Nueva moto e indique placa, cliente, marca, modelo, año y color.</p>
                <p>La placa es única y se guarda en mayúsculas. Puede buscar por placa, marca, modelo o nombre del propietario, y filtrar por estado.</p>
                <p>Desactivar conserva la moto y su historial. Para reactivarla, entre en Editar, cambie el estado a Activa y guarde.</p>
                <a href="{{ route('vehicles.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Abrir motos →</a>
            </div>
        </details>
        @endcan
        @can('module-access', ['inventory','view'])
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Repuestos y existencias</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Registre el repuesto con un código único, nombre, precio de venta en colones y existencia mínima. Puede indicar marca y compatibilidad.</p>
                <p>El repuesto comienza con cero existencias. Abra Ver detalle y movimientos y registre una Entrada para agregar las unidades iniciales.</p>
                <ul class="list-inside list-disc space-y-2">
                    <li><strong>Entrada:</strong> unidades que se agregan por compra, devolución u otro motivo.</li>
                    <li><strong>Salida:</strong> unidades que se retiran. No puede superar las existencias disponibles.</li>
                    <li><strong>Ajuste por conteo:</strong> el administrador registra el total de unidades contadas físicamente; puede ser cero.</li>
                </ul>
                <p>Indique siempre el motivo o referencia. El historial registra responsable, fecha y saldos; puede filtrarlo por tipo y rango de fechas.</p>
                <p>Stock bajo significa que las existencias están en el mínimo o por debajo. Desactivar un repuesto conserva el saldo, pero impide nuevos movimientos hasta reactivarlo.</p>
                <a href="{{ route('spareparts.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Abrir repuestos →</a>
            </div>
        </details>
        @endcan
        @can('manage-security')
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Marcas, usuarios y permisos</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Desde Motos o Repuestos, abra Administrar marcas para registrar, editar, buscar o desactivar una marca. Para reactivarla, cambie su estado desde Editar.</p>
                <p>Una marca inactiva no puede seleccionarse en registros nuevos. Los registros existentes conservan la marca.</p>
                <p>Desde Usuarios puede crear cuentas, asignar roles y activar o desactivar el acceso. Al editar, deje la contraseña vacía para conservar la actual.</p>
                <p>Desde Roles configure los permisos de consulta, creación, edición y desactivación por módulo. El rol administrador está protegido y debe mantenerse activo.</p>
                <div class="flex flex-wrap gap-3"><a href="{{ route('users.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Usuarios →</a><a href="{{ route('roles.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Roles →</a></div>
            </div>
        </details>
        @endcan
        @can('module-access', ['orders','view'])
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Órdenes de trabajo</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p>Seleccione Nueva orden, el cliente y una de sus motos. Asigne el mecánico, describa el trabajo solicitado y registre el costo de mano de obra.</p>
                <p>Desde el detalle puede agregar repuestos. El sistema descuenta las existencias y conserva el precio utilizado en la orden. Al reducir o retirar una línea, devuelve la diferencia al inventario.</p>
                <p>Al cambiar el estado, indique el detalle para conservarlo en el historial. Una orden entregada queda protegida contra modificaciones.</p>
                <p>Cancelar una orden devuelve sus repuestos y conserva toda la información para consulta.</p>
                <a href="{{ route('orders.index') }}" class="inline-flex min-h-12 items-center font-semibold text-amber-700">Abrir órdenes →</a>
            </div>
        </details>
        @endcan
        <details class="rounded-2xl border border-slate-200 p-4">
            <summary class="flex min-h-12 cursor-pointer items-center text-lg font-bold text-slate-900">Mensajes y consultas frecuentes</summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-600">
                <p><strong>No se guarda el formulario:</strong> revise los avisos de validación y complete los campos obligatorios antes de guardar otra vez.</p>
                <p><strong>No aparece una marca:</strong> el administrador debe registrarla o reactivarla en el catálogo correspondiente.</p>
                <p><strong>No puedo registrar una salida:</strong> revise el stock y confirme que el repuesto esté activo.</p>
                <p><strong>Órdenes:</strong> registre el cliente y la moto, asigne un mecánico y agregue repuestos desde el detalle. El sistema descuenta existencias, conserva precios e historial y devuelve los repuestos al reducirlos, retirarlos o cancelar.</p>
                <p><strong>Facturas:</strong> la orden debe estar Lista o Entregada y no tener otra factura. Los precios quedan guardados tal como fueron emitidos.</p>
            </div>
        </details>
    </div>
</div>
@endsection
