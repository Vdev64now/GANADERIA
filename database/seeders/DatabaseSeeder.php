<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Farm;
use App\Models\Cattle;
use App\Models\Slaughter;
use App\Models\Slaughterhouse;
use App\Models\Customer;
use App\Models\CutType;
use App\Models\Deboning;
use App\Models\DeboningItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Default Admin User
        \App\Models\User::create([
            'name' => 'Administrador',
            'email' => 'admin@ganadoflow.com',
            'password' => \Illuminate\Support\Facades\Hash::make('759153654'),
        ]);

        // 1. Seed Settings
        Setting::setValue('min_yield_percentage', '53');
        Setting::setValue('max_waste_percentage', '8');

        // 2. Seed Farms
        $farm1 = Farm::create([
            'name' => 'Hacienda El Porvenir',
            'location' => 'Chontales, Nicaragua',
            'description' => 'Especializada en engorde de razas cárnicas.',
        ]);

        $farm2 = Farm::create([
            'name' => 'Finca La Esmeralda',
            'location' => 'Jinotega, Nicaragua',
            'description' => 'Finca de pastoreo de altura y crianza.',
        ]);

        // 3. Seed Slaughterhouses
        $sh1 = Slaughterhouse::create([
            'name' => 'Matadero Central S.A.',
            'location' => 'Km 10 Carretera Norte, Managua',
            'description' => 'Beneficio industrial con estándares de exportación.',
        ]);

        $sh2 = Slaughterhouse::create([
            'name' => 'Matadero Industrial del Norte',
            'location' => 'Matagalpa, Nicaragua',
            'description' => 'Servicios de matanza locales para ganaderos del norte.',
        ]);

        // 4. Seed Customers
        $customer1 = Customer::create([
            'first_name' => 'Julio',
            'last_name' => 'Ortega',
            'phone' => '+505 8888-8888',
            'has_butcher_shop' => true,
            'butcher_shop_name' => 'Carnicería Don Julio',
        ]);

        $customer2 = Customer::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '+505 7777-7777',
            'has_butcher_shop' => false,
        ]);

        // 5. Seed Cut Types
        $cuts = [
            ['name' => 'Lomo Fino', 'category' => 'Primera', 'description' => 'Corte sumamente tierno y de alta calidad.'],
            ['name' => 'Lomito', 'category' => 'Primera', 'description' => 'Filete selecto.'],
            ['name' => 'Entraña', 'category' => 'Primera', 'description' => 'Corte con gran sabor e ideal para asar.'],
            ['name' => 'Pulpa Negra', 'category' => 'Primera', 'description' => 'Corte magro de la pierna trasera.'],
            ['name' => 'Costilla', 'category' => 'Segunda', 'description' => 'Ideal para asados o caldos.'],
            ['name' => 'Osobuco', 'category' => 'Segunda', 'description' => 'Corte con hueso y tuétano para guisos.'],
            ['name' => 'Posta de Pierna', 'category' => 'Segunda', 'description' => 'Carne versátil para freír o guisar.'],
            ['name' => 'Falda', 'category' => 'Segunda', 'description' => 'Ideal para deshebrar.'],
            ['name' => 'Hueso Blanco', 'category' => 'Tercera/Desecho', 'description' => 'Hueso sin carne, usado para caldos.'],
            ['name' => 'Grasa/Mermas de Limpieza', 'category' => 'Tercera/Desecho', 'description' => 'Grasa y recortes descartados.'],
        ];

        $cutTypeModels = [];
        foreach ($cuts as $cut) {
            $cutTypeModels[$cut['name']] = CutType::create($cut);
        }

        // 6. Seed Cattle
        // Res 1: En Pie (Hacienda El Porvenir)
        Cattle::create([
            'farm_id' => $farm1->id,
            'ear_tag' => 'TE-001',
            'breed' => 'Brahman',
            'provider' => 'Ganadería Don Bosco',
            'purchase_date' => Carbon::parse('2026-05-10'),
            'live_weight' => 420.00,
            'purchase_price_total' => 950.00,
            'status' => 'en_pie',
        ]);

        // Res 2: Beneficiado Parcial (Left Side Deboned, Right Side Available)
        $cattle2 = Cattle::create([
            'farm_id' => $farm1->id,
            'ear_tag' => 'TE-002',
            'breed' => 'Angus',
            'provider' => 'Distribuidora El Toro',
            'purchase_date' => Carbon::parse('2026-05-12'),
            'live_weight' => 450.00,
            'purchase_price_total' => 1100.00,
            'status' => 'beneficiado_parcial',
        ]);

        $slaughter2 = Slaughter::create([
            'cattle_id' => $cattle2->id,
            'slaughterhouse_id' => $sh1->id,
            'slaughter_date' => Carbon::parse('2026-06-01'),
            'left_carcass_weight' => 132.00,
            'right_carcass_weight' => 135.00,
            'slaughter_cost' => 40.00,
            'left_carcass_status' => 'despostado',
            'right_carcass_status' => 'disponible',
        ]);

        // Deboning of cattle2 Left Side
        $deboning2 = Deboning::create([
            'slaughter_id' => $slaughter2->id,
            'side' => 'izquierdo',
            'deboning_date' => Carbon::parse('2026-06-02'),
            'input_weight' => 132.00,
            'total_cuts_weight' => 124.00,
            'waste_weight' => 8.00,
            'yield_percentage' => round((124.00 / 132.00) * 100, 2),
        ]);

        // Deboned Items for cattle2 Left Side
        $cuts2 = [
            'Lomo Fino' => 12.00,
            'Costilla' => 25.00,
            'Osobuco' => 15.00,
            'Pulpa Negra' => 35.00,
            'Hueso Blanco' => 20.00,
            'Grasa/Mermas de Limpieza' => 17.00,
        ];
        foreach ($cuts2 as $cutName => $w) {
            DeboningItem::create([
                'deboning_id' => $deboning2->id,
                'cut_type_id' => $cutTypeModels[$cutName]->id,
                'weight' => $w,
                'current_weight' => $w,
            ]);
        }

        // Res 3: Despostado Completo & Sold (Finca La Esmeralda)
        $cattle3 = Cattle::create([
            'farm_id' => $farm2->id,
            'ear_tag' => 'TE-003',
            'breed' => 'Hereford',
            'provider' => 'Ganadería Los Altos',
            'purchase_date' => Carbon::parse('2026-05-15'),
            'live_weight' => 480.00,
            'purchase_price_total' => 1200.00,
            'status' => 'despostado_completo',
        ]);

        $slaughter3 = Slaughter::create([
            'cattle_id' => $cattle3->id,
            'slaughterhouse_id' => $sh1->id,
            'slaughter_date' => Carbon::parse('2026-06-03'),
            'left_carcass_weight' => 145.00,
            'right_carcass_weight' => 147.00,
            'slaughter_cost' => 45.00,
            'left_carcass_status' => 'despostado',
            'right_carcass_status' => 'despostado',
        ]);

        // Deboning of cattle3 Both Sides
        $deboning3 = Deboning::create([
            'slaughter_id' => $slaughter3->id,
            'side' => 'ambos',
            'deboning_date' => Carbon::parse('2026-06-04'),
            'input_weight' => 292.00,
            'total_cuts_weight' => 277.00,
            'waste_weight' => 15.00,
            'yield_percentage' => round((277.00 / 292.00) * 100, 2),
        ]);

        // Deboned Items for cattle3
        $cuts3 = [
            'Lomo Fino' => ['initial' => 28.00, 'current' => 18.00],
            'Costilla' => ['initial' => 60.00, 'current' => 30.00],
            'Osobuco' => ['initial' => 32.00, 'current' => 32.00],
            'Pulpa Negra' => ['initial' => 85.00, 'current' => 85.00],
            'Hueso Blanco' => ['initial' => 40.00, 'current' => 40.00],
            'Grasa/Mermas de Limpieza' => ['initial' => 32.00, 'current' => 32.00],
        ];
        
        $deboningItems3 = [];
        foreach ($cuts3 as $cutName => $wInfo) {
            $deboningItems3[$cutName] = DeboningItem::create([
                'deboning_id' => $deboning3->id,
                'cut_type_id' => $cutTypeModels[$cutName]->id,
                'weight' => $wInfo['initial'],
                'current_weight' => $wInfo['current'],
            ]);
        }

        // 7. Seed Sales
        $sale = Sale::create([
            'customer_id' => $customer1->id,
            'sale_date' => Carbon::parse('2026-06-08'),
            'total_amount' => 285.00,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'type' => 'corte',
            'deboning_item_id' => $deboningItems3['Lomo Fino']->id,
            'weight' => 10.00,
            'price_per_kg' => 12.00,
            'subtotal' => 120.00,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'type' => 'corte',
            'deboning_item_id' => $deboningItems3['Costilla']->id,
            'weight' => 30.00,
            'price_per_kg' => 5.50,
            'subtotal' => 165.00,
        ]);
    }
}
