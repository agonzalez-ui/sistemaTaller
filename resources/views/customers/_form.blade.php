<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    {{-- Nombre --}}
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-slate-700">
            Nombre completo <span class="text-red-600">*</span>
        </label>

        <input id="name"
               type="text"
               name="name"
               value="{{ old('name', $customer->name ?? '') }}"
               maxlength="150"
               autocomplete="name"
               required
               class="mt-2 block w-full rounded-lg border border-slate-300
                      bg-white px-3 py-2.5 text-slate-900 shadow-sm
                      focus:border-amber-500 focus:outline-none
                      focus:ring-1 focus:ring-amber-500">

        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Identificación --}}
    <div>
        <label for="identification_number"
               class="block text-sm font-medium text-slate-700">
            Cédula o DIMEX <span class="text-red-600">*</span>
        </label>

        <input id="identification_number"
               type="text"
               name="identification_number"
               value="{{ old('identification_number', $customer->identification_number ?? '') }}"
               inputmode="numeric"
               minlength="9"
               maxlength="12"
               pattern="([1-9][0-9]{8}|[1-9][0-9]{10,11})"
               title="Ingrese una cédula de 9 dígitos o un DIMEX de 11 o 12 dígitos."
               aria-describedby="identification_help"
               required
               class="mt-2 block w-full rounded-lg border border-slate-300
                      bg-white px-3 py-2.5 text-slate-900 shadow-sm
                      focus:border-amber-500 focus:outline-none
                      focus:ring-1 focus:ring-amber-500">

        <p id="identification_help" class="mt-1 text-xs text-slate-500">
            Cédula: 9 dígitos. DIMEX: 11 o 12. Sin espacios ni guiones.
        </p>

        @error('identification_number')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Teléfono --}}
    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">
            Teléfono (opcional)
        </label>

        <input id="phone"
               type="tel"
               name="phone"
               value="{{ old('phone', isset($customer) ? $customer->phones->first()?->phone : '') }}"
               inputmode="numeric"
               minlength="8"
               maxlength="8"
               pattern="[0-9]{8}"
               title="Ingrese exactamente 8 dígitos sin espacios ni guiones."
               autocomplete="tel"
               aria-describedby="phone_help"
               class="mt-2 block w-full rounded-lg border border-slate-300
                      bg-white px-3 py-2.5 text-slate-900 shadow-sm
                      focus:border-amber-500 focus:outline-none
                      focus:ring-1 focus:ring-amber-500">

        <p id="phone_help" class="mt-1 text-xs text-slate-500">
            Número local de 8 dígitos, sin +506, espacios ni guiones.
        </p>

        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Correo --}}
    <div class="sm:col-span-2">
        <label for="email" class="block text-sm font-medium text-slate-700">
            Correo electrónico (opcional)
        </label>

        <input id="email"
               type="email"
               name="email"
               value="{{ old('email', $customer->email ?? '') }}"
               maxlength="150"
               autocomplete="email"
               class="mt-2 block w-full rounded-lg border border-slate-300
                      bg-white px-3 py-2.5 text-slate-900 shadow-sm
                      focus:border-amber-500 focus:outline-none
                      focus:ring-1 focus:ring-amber-500">

        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dirección --}}
    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-medium text-slate-700">
            Dirección (opcional)
        </label>

        <textarea id="address"
                  name="address"
                  rows="3"
                  maxlength="250"
                  autocomplete="street-address"
                  class="mt-2 block w-full rounded-lg border border-slate-300
                         bg-white px-3 py-2.5 text-slate-900 shadow-sm
                         focus:border-amber-500 focus:outline-none
                         focus:ring-1 focus:ring-amber-500">{{ old('address', $customer->address ?? '') }}</textarea>

        @error('address')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>