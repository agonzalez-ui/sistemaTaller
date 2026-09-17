<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $customers = Customer::with('phones')
            ->orderBy('name')
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',

            'identification_number' => [
                'required',
                'string',
                'regex:/\A(?:[1-9][0-9]{8}|[1-9][0-9]{10,11})\z/',
                'unique:customers,identification_number',
            ],

            'phone' => [
                'nullable',
                'string',
                'regex:/\A[0-9]{8}\z/',
            ],

            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:250',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 150 caracteres.',

            'identification_number.required' => 'La cédula o DIMEX es obligatorio.',
            'identification_number.regex' => 'Ingrese una cédula de 9 dígitos o un DIMEX de 11 o 12 dígitos, sin espacios ni guiones.',
            'identification_number.unique' => 'Ya existe un cliente con esa identificación.',

            'phone.regex' => 'El teléfono debe contener exactamente 8 dígitos, sin espacios ni guiones.',

            'email.email' => 'El correo no tiene un formato válido.',
            'email.max' => 'El correo no puede superar los 150 caracteres.',
            'address.max' => 'La dirección no puede superar los 250 caracteres.',
        ]);

        DB::transaction(function () use ($request, $data) {
            $customer = Customer::create([
                'name' => $data['name'],
                'identification_number' => $data['identification_number'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            if (! empty($data['phone'])) {
                $customer->phones()->create([
                    'phone' => $data['phone'],
                ]);
            }
        });

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $customer->load('phones');

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'identification_number' => [
                'required',
                'string',
                'regex:/\A(?:[1-9][0-9]{8}|[1-9][0-9]{10,11})\z/',
                Rule::unique('customers', 'identification_number')->ignore($customer),
            ],
            'phone' => ['nullable', 'string', 'regex:/\A[0-9]{8}\z/'],
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:250',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'identification_number.required' => 'La cédula o DIMEX es obligatorio.',
            'identification_number.regex' => 'Ingrese una cédula de 9 dígitos o un DIMEX de 11 o 12 dígitos, sin espacios ni guiones.',
            'identification_number.unique' => 'Ya existe un cliente con esa identificación.',
            'phone.regex' => 'El teléfono debe contener exactamente 8 dígitos, sin espacios ni guiones.',
            'email.email' => 'El correo no tiene un formato válido.',
        ]);

        DB::transaction(function () use ($data, $customer) {
            $phoneNumber = $data['phone'] ?? null;
            unset($data['phone']);
            $customer->update($data);

            $phone = $customer->phones()->orderBy('id')->first();

            if ($phoneNumber !== null && $phoneNumber !== '') {
                if ($phone) {
                    $phone->update(['phone' => $phoneNumber]);
                } else {
                    $customer->phones()->create(['phone' => $phoneNumber]);
                }
            } elseif ($phone) {
                $phone->delete();
            }
        });

        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
