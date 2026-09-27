<?php

namespace Database\Seeders;

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Role;
use App\Models\SparePart;
use App\Models\SparePartBrand;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SimrhSeeder::class);

        DB::transaction(function () {
            [$admin, $mechanic, $seller] = $this->users();
            $customers = $this->customers($admin);
            $vehicles = $this->vehicles($admin, $customers);
            $parts = $this->parts($admin);
            $this->ordersAndInvoices($admin, $mechanic, $seller, $customers, $vehicles, $parts);
            $this->securityLogs($admin, $mechanic, $seller);
        });
    }

    private function users(): array
    {
        $profiles = [
            ['Manuel Rodriguez', 'manuel@simrh.test', 'Administrador'],
            ['Julian Rodriguez', 'julian@simrh.test', 'Mecánico'],
            ['Jimena Rodriguez', 'jimena@simrh.test', 'Recepción y ventas'],
        ];

        return collect($profiles)->map(function (array $profile) {
            $role = Role::where('name', $profile[2])->firstOrFail();
            $user = User::where('name', $profile[0])->first() ?? User::where('email', $profile[1])->first();
            if (! $user) {
                $user = User::create(['name' => $profile[0], 'email' => $profile[1], 'password' => Hash::make('Demo12345!')]);
            }
            $user->forceFill(['role_id' => $role->id, 'active' => true, 'email_verified_at' => $user->email_verified_at ?? now()])->save();

            return $user;
        })->all();
    }

    private function customers(User $admin)
    {
        $names = [
            'Alejandro Vargas Mora', 'Andrea Solano Rojas', 'Bernardo Arias Castro', 'Camila Jimenez Soto',
            'Carlos Mendez Quesada', 'Daniela Chaves Leon', 'David Cordero Ruiz', 'Elena Salazar Vega',
            'Esteban Brenes Alfaro', 'Fernanda Mora Ureña', 'Gabriel Rojas Araya', 'Gabriela Castro Solis',
            'Hector Vargas Lopez', 'Irene Quesada Mora', 'Javier Solano Diaz', 'Jessica Araya Rojas',
            'Jorge Villalobos Soto', 'Jose Pablo Mena Ruiz', 'Karla Sanchez Vega', 'Laura Hernandez Mora',
            'Luis Fernando Castro', 'Marcela Rojas Arias', 'Marco Chaves Solano', 'Maria Fernanda Soto',
            'Mariana Vargas Leon', 'Mauricio Brenes Mora', 'Natalia Cordero Rojas', 'Oscar Mendez Araya',
            'Paola Jimenez Vega', 'Pablo Salazar Solis', 'Ricardo Quesada Ruiz', 'Rocio Mora Castro',
            'Sebastian Arias Soto', 'Sofia Villalobos Rojas', 'Valeria Chaves Mora', 'Victor Hernandez Vega',
        ];
        $locations = ['San José', 'Heredia', 'Alajuela', 'Cartago', 'Desamparados', 'Curridabat', 'Escazú', 'Goicoechea'];

        return collect($names)->map(function (string $name, int $index) use ($admin, $locations) {
            $number = (string) (900000001 + $index);
            $customer = Customer::firstOrCreate(['identification_number' => $number], [
                'name' => $name, 'email' => 'cliente'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'@simrh.test',
                'address' => ($index % 4 + 1).'00 metros '.($index % 2 ? 'norte' : 'este').' del centro de '.$locations[$index % count($locations)],
                'active' => $index % 17 !== 0, 'created_by' => $admin->id,
            ]);
            $customer->phones()->firstOrCreate(['phone' => (string) (70000000 + $index * 137)], ['type' => $index % 3 === 0 ? 'Trabajo' : 'Celular']);

            return $customer;
        });
    }

    private function vehicles(User $admin, $customers)
    {
        $models = [
            'Honda' => ['CB190R', 'XR150L', 'Navi', 'CB300R'], 'Yamaha' => ['FZ 2.0', 'XTZ 125', 'MT-03', 'NMAX'],
            'Suzuki' => ['Gixxer 150', 'DR150', 'GN125', 'Burgman'], 'Kawasaki' => ['Ninja 400', 'Z400', 'KLX230'],
            'Bajaj' => ['Pulsar NS200', 'Dominar 400', 'Boxer 150'], 'TVS' => ['Apache RTR 200', 'Raider 125'],
            'KTM' => ['Duke 200', 'Adventure 390'], 'Hero' => ['Hunk 160R', 'Xpulse 200'],
        ];
        $brands = VehicleBrand::whereIn('name', array_keys($models))->get()->keyBy('name');
        $type = VehicleType::where('name', 'Motocicleta')->firstOrFail();
        $colors = ['Negro', 'Rojo', 'Azul', 'Blanco', 'Gris', 'Naranja'];

        return collect(range(1, 48))->map(function (int $position) use ($admin, $customers, $models, $brands, $type, $colors) {
            $brandName = array_keys($models)[($position - 1) % count($models)];
            $brandModels = $models[$brandName];

            return Vehicle::firstOrCreate(['license_plate' => 'M'.str_pad((string) (900000 + $position), 6, '0', STR_PAD_LEFT)], [
                'customer_id' => $customers[($position - 1) % $customers->count()]->id,
                'vehicle_brand_id' => $brands[$brandName]->id, 'vehicle_type_id' => $type->id,
                'model' => $brandModels[($position - 1) % count($brandModels)], 'year' => 2015 + ($position % 11),
                'color' => $colors[$position % count($colors)], 'active' => $position % 19 !== 0, 'created_by' => $admin->id,
            ]);
        });
    }

    private function parts(User $admin)
    {
        $catalog = [
            ['Filtro de aceite', 4500], ['Filtro de aire', 7500], ['Bujía estándar', 3800], ['Bujía iridium', 9500],
            ['Pastillas de freno delanteras', 14500], ['Pastillas de freno traseras', 12500], ['Kit de arrastre', 48500],
            ['Cadena 428 reforzada', 28500], ['Piñón delantero', 9800], ['Catalina trasera', 18500],
            ['Aceite 10W-40 un litro', 6800], ['Líquido de frenos DOT 4', 5200], ['Refrigerante para moto', 6500],
            ['Bombillo H4', 4200], ['Direccional universal', 8500], ['Manigueta de freno', 11500],
            ['Manigueta de clutch', 10500], ['Cable de clutch', 8900], ['Cable de acelerador', 9200],
            ['Rodamiento de rueda', 7800], ['Retén de suspensión', 12500], ['Batería 12V 7Ah', 36500],
            ['Llanta delantera 90/90-17', 42500], ['Llanta trasera 130/70-17', 58500], ['Tubo interno 17 pulgadas', 9800],
            ['Espejo universal', 8500], ['Puño de manillar', 6500], ['Pedal de cambios', 14500],
            ['Disco de clutch', 32500], ['Empaque de tapa de válvulas', 7800],
        ];
        $brands = SparePartBrand::orderBy('id')->get();

        return collect($catalog)->map(function (array $item, int $index) use ($admin, $brands) {
            $part = SparePart::firstOrCreate(['code' => 'DEMO-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)], [
                'name' => $item[0], 'description' => 'Repuesto de demostración para motocicletas de uso frecuente.',
                'spare_part_brand_id' => $brands[$index % $brands->count()]->id, 'price' => $item[1],
                'stock_quantity' => 60, 'minimum_quantity' => 5 + ($index % 4), 'active' => $index !== 28, 'created_by' => $admin->id,
            ]);
            if (! InventoryMovement::where('request_token', 'demo-initial-'.$part->id)->exists()) {
                InventoryMovement::create(['spare_part_id' => $part->id, 'request_token' => 'demo-initial-'.$part->id, 'type' => 'IN', 'quantity' => 60, 'previous_balance' => 0, 'new_balance' => 60, 'user_id' => $admin->id, 'date' => now()->subDays(150), 'notes' => 'Inventario inicial de demostración.']);
            }

            return $part;
        });
    }

    private function ordersAndInvoices(User $admin, User $mechanic, User $seller, $customers, $vehicles, $parts): void
    {
        $statuses = OrderStatus::orderBy('sort_order')->get();
        $jobs = ['Cambio de aceite y revisión general', 'Diagnóstico de ruido en motor', 'Cambio de pastillas de freno', 'Ajuste de cadena y lubricación', 'Revisión del sistema eléctrico', 'Mantenimiento preventivo completo', 'Cambio de llanta y balanceo', 'Limpieza de carburador e inyección'];
        $balances = $parts->mapWithKeys(fn (SparePart $part) => [$part->id => (int) $part->fresh()->stock_quantity]);

        foreach (range(1, 60) as $position) {
            $number = 'OT-D-'.str_pad((string) $position, 4, '0', STR_PAD_LEFT);
            $vehicle = $vehicles[($position - 1) % $vehicles->count()];
            $customer = $customers->firstWhere('id', $vehicle->customer_id);
            $status = $statuses[($position - 1) % $statuses->count()];
            $received = Carbon::now()->subDays(120 - $position)->setTime(8 + ($position % 8), ($position * 7) % 60);
            $order = Order::firstOrCreate(['number' => $number], [
                'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'order_status_id' => $status->id,
                'mechanic_id' => $position % 8 === 0 ? null : $mechanic->id, 'description' => $jobs[$position % count($jobs)],
                'diagnosis' => $position % 4 === 0 ? null : 'Se revisó la motocicleta y se identificó desgaste normal de los componentes indicados.',
                'labor_cost' => 10000 + ($position % 6) * 3500, 'received_at' => $received,
                'delivered_at' => $status->is_final ? $received->copy()->addDays(2) : null, 'created_by' => $seller->id,
            ]);
            if ($order->wasRecentlyCreated) {
                OrderHistory::create(['order_id' => $order->id, 'order_status_id' => $status->id, 'user_id' => $mechanic->id, 'date' => $received, 'notes' => 'Estado de demostración: '.$status->name.'.']);
                foreach ([($position - 1) % $parts->count(), ($position + 7) % $parts->count()] as $partIndex) {
                    $part = $parts[$partIndex];
                    $quantity = 1 + ($position % 3);
                    OrderItem::create(['order_id' => $order->id, 'spare_part_id' => $part->id, 'quantity' => $quantity, 'unit_price' => $part->price, 'line_total' => $quantity * (float) $part->price]);
                    if ($status->name !== 'Cancelada') {
                        $previous = $balances[$part->id];
                        $balances[$part->id] = $previous - $quantity;
                        InventoryMovement::create(['spare_part_id' => $part->id, 'request_token' => (string) Str::uuid(), 'type' => 'OUT', 'quantity' => $quantity, 'previous_balance' => $previous, 'new_balance' => $balances[$part->id], 'order_id' => $order->id, 'user_id' => $mechanic->id, 'date' => $received->copy()->addHour(), 'notes' => 'Utilizado en la orden '.$order->number.'.']);
                    }
                }
            }
            if ($status->allows_invoicing && ! Invoice::where('order_id', $order->id)->exists()) {
                $this->invoice($order, $admin, $position);
            }
        }

        foreach ($parts as $index => $part) {
            $current = $balances[$part->id];
            if ($index < 7) {
                $target = $index;
                InventoryMovement::firstOrCreate(['request_token' => 'demo-adjust-'.$part->id], ['spare_part_id' => $part->id, 'type' => 'ADJUST', 'quantity' => $target, 'previous_balance' => $current, 'new_balance' => $target, 'user_id' => $admin->id, 'date' => now()->subDay(), 'notes' => 'Conteo físico de demostración para probar alertas de stock.']);
                $current = $target;
            }
            $part->update(['stock_quantity' => $current]);
        }
    }

    private function invoice(Order $order, User $admin, int $position): void
    {
        $order->load('items.sparePart');
        $subtotal = (float) $order->labor_cost + (float) $order->items->sum('line_total');
        $discount = $position % 5 === 0 ? 2500 : 0;
        $tax = round(($subtotal - $discount) * 0.13, 2);
        $cancelled = $position % 13 === 0;
        $invoice = Invoice::create(['number' => 'FAC-D-'.str_pad((string) $position, 4, '0', STR_PAD_LEFT), 'order_id' => $order->id, 'customer_id' => $order->customer_id, 'vehicle_id' => $order->vehicle_id, 'date' => $order->received_at->copy()->addDays(2), 'subtotal' => $subtotal, 'discount' => $discount, 'tax_rate' => 13, 'tax_amount' => $tax, 'total' => $subtotal - $discount + $tax, 'status' => $cancelled ? 'CANCELLED' : 'ISSUED', 'cancelled_at' => $cancelled ? $order->received_at->copy()->addDays(3) : null, 'cancellation_reason' => $cancelled ? 'Error de digitación en los datos del comprobante.' : null, 'created_by' => $admin->id]);
        foreach ($order->items as $item) {
            $invoice->items()->create(['line_type' => 'PART', 'spare_part_id' => $item->spare_part_id, 'description' => $item->sparePart->code.' · '.$item->sparePart->name, 'quantity' => $item->quantity, 'unit_price' => $item->unit_price, 'line_total' => $item->line_total]);
        }
        $invoice->items()->create(['line_type' => 'SERVICE', 'description' => 'Mano de obra de la orden '.$order->number, 'quantity' => 1, 'unit_price' => $order->labor_cost, 'line_total' => $order->labor_cost]);
    }

    private function securityLogs(User $admin, User $mechanic, User $seller): void
    {
        $users = [$admin, $mechanic, $seller];
        foreach (range(1, 45) as $position) {
            AccessLog::firstOrCreate(['ip_address' => '10.0.0.'.(20 + $position), 'logged_in_at' => Carbon::now()->subDays(46 - $position)->setTime(7 + ($position % 4), 15)], ['user_id' => $users[$position % 3]->id, 'logged_out_at' => Carbon::now()->subDays(46 - $position)->setTime(16 + ($position % 3), 30), 'logout_type' => $position % 9 === 0 ? 'TIMEOUT' : 'MANUAL']);
        }
        $actions = ['INSERT', 'UPDATE', 'DELETE', 'CANCEL'];
        $tables = ['customers', 'vehicles', 'spare_parts', 'orders', 'invoices'];
        foreach (range(1, 90) as $position) {
            ActivityLog::firstOrCreate(['record_id' => 'DEMO-'.$position, 'table_name' => $tables[$position % count($tables)]], ['user_id' => $users[$position % 3]->id, 'occurred_at' => Carbon::now()->subDays(91 - $position)->setTime(8 + ($position % 9), ($position * 11) % 60), 'action' => $actions[$position % count($actions)], 'details' => 'Movimiento demostrativo #'.$position.' para validar filtros y paginación.', 'ip_address' => '10.0.1.'.(20 + $position)]);
        }
    }
}
