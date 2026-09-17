@php($vehicle = $vehicle ?? null)
@if ($errors->any())
<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800"><p class="font-semibold">Revise los datos del formulario.</p><ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<p class="text-sm text-slate-600">Vincule la moto con su propietario. Los campos con * son obligatorios.</p>
<div class="grid gap-5 sm:grid-cols-2">
@foreach (['license_plate'=>['Placa *','text',15], 'model'=>['Modelo *','text',100], 'year'=>['Año *','number',4], 'color'=>['Color','text',40]] as $field=>$settings)
<div><label for="{{ $field }}" class="block text-sm font-semibold text-slate-700">{{ $settings[0] }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $settings[1] }}" value="{{ old($field, $vehicle?->{$field} ?? '') }}" @if($field !== 'color') required @endif @if($field === 'year') min="1900" max="{{ now()->year + 1 }}" @else maxlength="{{ $settings[2] }}" @endif @if($field === 'license_plate') placeholder="Ej.: M123456" autocapitalize="characters" @endif class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-amber-500 focus:ring-amber-500" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
@error($field)<p id="{{ $field }}-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
@endforeach
<div class="sm:col-span-2"><label for="customer_id" class="block text-sm font-semibold text-slate-700">Propietario *</label><select id="customer_id" name="customer_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"><option value="">Seleccione un cliente</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', $vehicle?->customer_id ?? '') == $customer->id)>{{ $customer->name }} · {{ $customer->identification_number }}{{ $customer->active ? '' : ' (inactivo: seleccione otro cliente)' }}</option>@endforeach</select>
@error('customer_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
@can('module-access', ['customers','create'])<a href="{{ route('customers.create') }}" class="mt-2 inline-flex min-h-12 items-center text-sm font-semibold text-amber-700">¿Falta el propietario? Registrar cliente</a>@endcan
@if($customers->isEmpty())<p class="mt-2 text-sm text-amber-800">Primero debe registrar un cliente activo.</p>@endif
</div>
<div><label for="vehicle_brand_id" class="block text-sm font-semibold text-slate-700">Marca *</label><select id="vehicle_brand_id" name="vehicle_brand_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"><option value="">Seleccione una marca</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('vehicle_brand_id', $vehicle?->vehicle_brand_id ?? '') == $brand->id)>{{ $brand->name }}{{ $brand->active ? '' : ' (inactiva: se conserva en esta moto)' }}</option>@endforeach</select>@error('vehicle_brand_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
<div><label for="active" class="block text-sm font-semibold text-slate-700">Estado *</label><select id="active" name="active" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"><option value="1" @selected((string)old('active', $vehicle?->active ?? 1) === '1')>Activa</option><option value="0" @selected((string)old('active', $vehicle?->active ?? 1) === '0')>Inactiva</option></select><p class="mt-2 text-xs text-slate-500">Desactivar conserva los datos y el historial de la moto.</p></div>
</div>

@can('manage-security')<a href="{{ route('vehicle-brands.index') }}" class="inline-flex min-h-12 items-center text-sm font-semibold text-amber-700">¿Falta una marca? Administrar marcas de motos</a>@endcan
