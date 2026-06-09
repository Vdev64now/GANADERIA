<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Farm;
use App\Models\Cattle;
use App\Models\Slaughter;
use App\Models\Slaughterhouse;
use App\Models\Customer;
use App\Models\CutType;
use App\Models\Deboning;
use App\Models\DeboningItem;
use App\Models\Setting;
use App\Models\Sale;

class GanadoFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_redirects_guest_users_to_login_screen()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_it_can_manage_full_cattle_workflow()
    {
        $user = \App\Models\User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        // 1. Seed Settings & Cut Types
        Setting::setValue('min_yield_percentage', '53');
        Setting::setValue('max_waste_percentage', '8');

        $lomo = CutType::create(['name' => 'Lomo Fino', 'category' => 'Primera']);
        $costilla = CutType::create(['name' => 'Costilla', 'category' => 'Segunda']);
        $osobuco = CutType::create(['name' => 'Osobuco', 'category' => 'Segunda']);
        $pulpa = CutType::create(['name' => 'Pulpa Negra', 'category' => 'Primera']);

        // Seed Slaughterhouse & Customer
        $sh = Slaughterhouse::create(['name' => 'Matadero Test S.A.']);
        $customer = Customer::create([
            'first_name' => 'Julio',
            'last_name' => 'Ortega',
            'has_butcher_shop' => true,
            'butcher_shop_name' => 'Carnicería Don Julio'
        ]);

        // 2. Create Farm & Cattle
        $farm = Farm::create(['name' => 'Hacienda Test', 'location' => 'Rivas']);
        
        $cattle = Cattle::create([
            'farm_id' => $farm->id,
            'ear_tag' => 'TEST-99',
            'breed' => 'Brahman',
            'live_weight' => 400.00,
            'purchase_price_total' => 800.00,
            'status' => 'en_pie',
        ]);

        $this->assertDatabaseHas('cattles', ['ear_tag' => 'TEST-99', 'status' => 'en_pie']);

        // 3. Register Slaughter (Left 118, Right 122 -> Total 240kg = 60% Yield)
        $slaughterData = [
            'cattle_id' => $cattle->id,
            'slaughterhouse_id' => $sh->id,
            'slaughter_date' => date('Y-m-d'),
            'left_carcass_weight' => 118.00,
            'right_carcass_weight' => 122.00,
            'slaughter_cost' => 50.00,
        ];

        $response = $this->post(route('slaughters.store'), $slaughterData);
        $response->assertRedirect(route('slaughters.index'));
        $response->assertSessionHas('success');

        // Check DB updates
        $this->assertDatabaseHas('slaughters', [
            'cattle_id' => $cattle->id,
            'left_carcass_weight' => 118.00,
            'right_carcass_weight' => 122.00,
            'left_carcass_status' => 'disponible',
            'right_carcass_status' => 'disponible',
        ]);

        $cattle->refresh();
        $this->assertEquals('beneficiado_completo', $cattle->status);

        // Check slaughter computed properties
        $slaughter = Slaughter::first();
        $this->assertEquals(240.00, $slaughter->total_carcass_weight);
        $this->assertEquals(60.00, $slaughter->yield_percentage);
        $this->assertEquals(160.00, $slaughter->waste_weight);

        // 4. Register Deboning of Left Side (118kg)
        // Obtained cuts: Lomo 20kg, Costilla 40kg, Osobuco 30kg, Pulpa 20kg = 110kg. Waste = 8kg (6.78%)
        $deboningData = [
            'slaughter_id' => $slaughter->id,
            'side' => 'izquierdo',
            'deboning_date' => date('Y-m-d'),
            'cuts' => [
                $lomo->id => 20.00,
                $costilla->id => 40.00,
                $osobuco->id => 30.00,
                $pulpa->id => 20.00,
            ],
        ];

        $response = $this->post(route('debonings.store'), $deboningData);
        $response->assertRedirect(route('debonings.index'));
        $response->assertSessionHas('success');

        // Verify slaughter side status
        $slaughter->refresh();
        $this->assertEquals('despostado', $slaughter->left_carcass_status);
        $this->assertEquals('disponible', $slaughter->right_carcass_status);

        // Verify cattle status becomes beneficiado_parcial
        $cattle->refresh();
        $this->assertEquals('beneficiado_parcial', $cattle->status);

        // Verify deboning and items in DB
        $deboning = Deboning::first();
        $this->assertEquals(118.00, $deboning->input_weight);
        $this->assertEquals(110.00, $deboning->total_cuts_weight);
        $this->assertEquals(8.00, $deboning->waste_weight);

        $this->assertDatabaseHas('deboning_items', [
            'deboning_id' => $deboning->id,
            'cut_type_id' => $lomo->id,
            'weight' => 20.00,
            'current_weight' => 20.00,
        ]);

        // 5. Register Sale of 10kg Lomo Fino @ $12/kg
        $debonedLomoItem = DeboningItem::where('cut_type_id', $lomo->id)->first();
        
        $saleData = [
            'customer_id' => $customer->id,
            'sale_date' => date('Y-m-d'),
            'sale_type' => 'corte',
            'deboning_item_id' => $debonedLomoItem->id,
            'weight' => 10.00,
            'price_per_kg' => 12.00,
        ];

        $response = $this->post(route('sales.store'), $saleData);
        $response->assertRedirect(route('sales.index'));
        $response->assertSessionHas('success');

        // Verify sale total and inventory reduction
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'total_amount' => 120.00,
        ]);

        $debonedLomoItem->refresh();
        $this->assertEquals(10.00, $debonedLomoItem->current_weight); // 20.00 - 10.00 = 10.00
    }
}
