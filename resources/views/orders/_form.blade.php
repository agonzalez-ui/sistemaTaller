@php
    $order = $order ?? null;
    $accessoryOptions = [
        'ignition_key' => 'Llave principal',
        'spare_key' => 'Llave adicional',
        'helmet' => 'Casco',
        'documents' => 'Documentos',
        'top_case' => 'Maletero / cajón',
        'tools' => 'Herramientas',
        'other' => 'Otro accesorio',
    ];
    $selectedAccessories = old('intake_accessories', $order?->intake_accessories ?? []);
    $mechanicEditing = auth()->user()->isMechanic() && $order;
@endphp
@if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800"><p class="font-semibold">Revise los datos.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if($mechanicEditing)
    <input type="hidden" name="customer_id" value="{{ $order->customer_id }}">
    <input type="hidden" name="vehicle_id" value="{{ $order->vehicle_id }}">
    <input type="hidden" name="mechanic_id" value="{{ $order->mechanic_id }}">
    <input type="hidden" name="description" value="{{ $order->description }}">
    <input type="hidden" name="odometer" value="{{ $order->odometer }}">
    <input type="hidden" name="fuel_level" value="{{ $order->fuel_level }}">
    <input type="hidden" name="intake_condition" value="{{ $order->intake_condition }}">
    @foreach($order->intake_accessories ?? [] as $accessory)<input type="hidden" name="intake_accessories[]" value="{{ $accessory }}">@endforeach
    <input type="hidden" name="labor_cost" value="{{ $order->labor_cost }}">
    <input type="hidden" name="received_at" value="{{ $order->received_at?->format('Y-m-d H:i:s') }}">
    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Orden asignada</p>
        <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $order->vehicle->license_plate }} · {{ $order->customer->name }}</h2>
        <p class="mt-2 text-sm text-slate-600">{{ $order->description }}</p>
        <p class="mt-3 text-xs text-slate-500">La recepción, el cliente, la moto, el costo y la asignación son datos administrativos protegidos.</p>
    </section>
    <div class="mt-5 grid gap-5 sm:grid-cols-2">
        <div><label for="order_status_id" class="block text-sm font-semibold">Estado del trabajo *</label><select id="order_status_id" name="order_status_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4">@foreach($statuses as $status)<option value="{{ $status->id }}" @selected(old('order_status_id',$order->order_status_id) == $status->id)>{{ $status->name }}</option>@endforeach</select>@error('order_status_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
        <div><label for="mechanic_name" class="block text-sm font-semibold">Mecánico asignado</label><div id="mechanic_name" class="mt-2 flex min-h-12 items-center rounded-xl border border-slate-200 bg-slate-100 px-4 font-semibold text-slate-700">{{ $order->mechanic?->name }}</div></div>
        <div class="sm:col-span-2"><label for="diagnosis" class="block text-sm font-semibold">Diagnóstico y trabajo realizado</label><textarea id="diagnosis" name="diagnosis" rows="5" maxlength="500" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('diagnosis',$order->diagnosis) }}</textarea>@error('diagnosis')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
        <div class="sm:col-span-2"><label for="status_notes" class="block text-sm font-semibold">Detalle del cambio de estado</label><input type="text" id="status_notes" name="status_notes" maxlength="250" value="{{ old('status_notes') }}" placeholder="Explique brevemente el avance realizado" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4"><p class="mt-1 text-xs text-slate-500">Es obligatorio cuando cambia el estado.</p>@error('status_notes')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    </div>
@else
<p class="text-sm text-slate-600">Seleccione primero al cliente; la lista de motos mostrará únicamente sus vehículos.</p>
<div class="grid gap-5 sm:grid-cols-2">
    <div><label for="customer_id" class="block text-sm font-semibold">Cliente *</label><select id="customer_id" name="customer_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4"><option value="">Seleccione un cliente</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id',$order?->customer_id) == $customer->id)>{{ $customer->name }} · {{ $customer->identification_number }}</option>@endforeach</select>@error('customer_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="vehicle_id" class="block text-sm font-semibold">Moto *</label><select id="vehicle_id" name="vehicle_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4"><option value="">Seleccione una moto</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}" data-customer="{{ $vehicle->customer_id }}" @selected(old('vehicle_id',$order?->vehicle_id) == $vehicle->id)>{{ $vehicle->license_plate }} · {{ $vehicle->brand->name }} {{ $vehicle->model }}</option>@endforeach</select><p class="mt-1 text-xs text-slate-500">Si no aparece, registre primero la moto del cliente.</p>@error('vehicle_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="mechanic_id" class="block text-sm font-semibold">Mecánico</label>@if(auth()->user()->isMechanic() && $order)<input type="hidden" name="mechanic_id" value="{{ $order->mechanic_id }}"><div class="mt-2 flex min-h-12 items-center rounded-xl border border-slate-200 bg-slate-100 px-4 font-semibold text-slate-700">{{ $order->mechanic?->name }}</div><p class="mt-1 text-xs text-slate-500">La asignación solo puede cambiarla recepción o administración.</p>@else<select id="mechanic_id" name="mechanic_id" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4"><option value="">Sin asignar</option>@foreach($mechanics as $mechanic)<option value="{{ $mechanic->id }}" @selected(old('mechanic_id',$order?->mechanic_id) == $mechanic->id)>{{ $mechanic->name }}</option>@endforeach</select>@endif @error('mechanic_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="order_status_id" class="block text-sm font-semibold">Estado *</label><select id="order_status_id" name="order_status_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4">@foreach($statuses as $status)<option value="{{ $status->id }}" @selected(old('order_status_id',$order?->order_status_id ?? $statuses->firstWhere('name','Recibido')?->id) == $status->id)>{{ $status->name }}</option>@endforeach</select>@error('order_status_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="received_at" class="block text-sm font-semibold">Fecha y hora de recepción *</label><input type="datetime-local" id="received_at" name="received_at" required value="{{ old('received_at',$order?->received_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4">@error('received_at')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="labor_cost" class="block text-sm font-semibold">Mano de obra (₡) *</label><input type="number" id="labor_cost" name="labor_cost" min="0" max="9999999999.99" step="0.01" inputmode="decimal" required value="{{ old('labor_cost',$order?->labor_cost ?? 0) }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4">@error('labor_cost')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div class="sm:col-span-2"><label for="description" class="block text-sm font-semibold">Trabajo solicitado / síntomas *</label><textarea id="description" name="description" rows="3" maxlength="500" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('description',$order?->description) }}</textarea>@error('description')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <fieldset class="sm:col-span-2 rounded-2xl border border-amber-200 bg-amber-50/60 p-5">
        <legend class="px-2 text-lg font-bold text-slate-900">Inspección de recepción</legend>
        <p class="text-sm text-slate-600">Documente cómo ingresa la moto. Esta información queda vinculada a la orden como evidencia.</p>
        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="odometer" class="block text-sm font-semibold">Kilometraje</label>
                <input type="number" id="odometer" name="odometer" min="0" max="9999999" inputmode="numeric" value="{{ old('odometer', $order?->odometer) }}" placeholder="Ej. 24500" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4">
                @error('odometer')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="fuel_level" class="block text-sm font-semibold">Nivel de combustible</label>
                <select id="fuel_level" name="fuel_level" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4">
                    <option value="">No indicado</option>
                    @foreach(['empty' => 'Vacío', 'quarter' => '1/4', 'half' => '1/2', 'three_quarters' => '3/4', 'full' => 'Lleno'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('fuel_level', $order?->fuel_level) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('fuel_level')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="intake_condition" class="block text-sm font-semibold">Estado y daños visibles</label>
                <textarea id="intake_condition" name="intake_condition" rows="4" maxlength="2000" placeholder="Ej. Rayón en el costado derecho, golpe pequeño en el tanque y manigueta izquierda quebrada." class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">{{ old('intake_condition', $order?->intake_condition) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Describa rayones, golpes, piezas faltantes, luces dañadas u otra condición previa.</p>
                @error('intake_condition')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <span class="block text-sm font-semibold">Accesorios entregados</span>
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($accessoryOptions as $value => $label)
                        <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                            <input type="checkbox" name="intake_accessories[]" value="{{ $value }}" @checked(in_array($value, $selectedAccessories, true)) class="h-5 w-5 rounded border-slate-300 text-amber-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('intake_accessories')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="intake_photos" class="block text-sm font-semibold">Fotografías de recepción</label>
                <input type="file" id="intake_photos" name="intake_photos[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:font-semibold file:text-white">
                <p class="mt-1 text-xs text-slate-500">Hasta 5 fotografías JPG, PNG o WebP; máximo 5 MB cada una. En celular puede usar la cámara desde el selector.</p>
                @if($order?->intakePhotos?->isNotEmpty())<p class="mt-2 text-sm font-medium text-slate-700">Esta orden ya conserva {{ $order->intakePhotos->count() }} fotografía(s). Las nuevas se agregarán a las existentes.</p>@endif
                @error('intake_photos')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                @error('intake_photos.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </fieldset>
    <div class="sm:col-span-2"><label for="diagnosis" class="block text-sm font-semibold">Diagnóstico y trabajo realizado</label><textarea id="diagnosis" name="diagnosis" rows="3" maxlength="500" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('diagnosis',$order?->diagnosis) }}</textarea>@error('diagnosis')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    @if($order)<div class="sm:col-span-2"><label for="status_notes" class="block text-sm font-semibold">Detalle del cambio de estado</label><input type="text" id="status_notes" name="status_notes" maxlength="250" value="{{ old('status_notes') }}" placeholder="Obligatorio cuando cambia el estado" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4">@error('status_notes')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>@endif
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{const customer=document.getElementById('customer_id'),vehicle=document.getElementById('vehicle_id'),options=[...vehicle.options];const filter=()=>{const selected=customer.value;options.forEach((option,index)=>{if(index===0)return;option.hidden=!selected||option.dataset.customer!==selected;option.disabled=option.hidden});if(vehicle.selectedOptions[0]?.disabled)vehicle.value=''};customer.addEventListener('change',filter);filter()});</script>
@endif
