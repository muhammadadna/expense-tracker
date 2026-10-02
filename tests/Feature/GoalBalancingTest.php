<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Models\User;
use App\Services\GoalBalancingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalBalancingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Budi',
            'family_code' => 'BUDI-1234',
        ]);

        $this->user = User::factory()->create([
            'family_id' => $this->family->id,
        ]);
    }

    /**
     * Test 1: Dashboard Balancing & Goals dapat diakses.
     */
    public function test_user_can_view_goals_index_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('goals.index'));

        $response->assertStatus(200);
        $response->assertSee('Balancing & Goals', false);
        $response->assertSee('Create Pocket / Goal');
    }

    /**
     * Test 2: Membuat pos dengan Target (Mode Goal).
     */
    public function test_user_can_create_goal_with_target(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'name' => 'Beli Laptop MacBook',
            'has_target' => '1',
            'target_amount' => 20000000,
            'target_date' => now()->addMonths(6)->format('Y-m-d'),
            'initial_amount' => 5000000,
            'initial_note' => 'Tabungan awal dari bonus',
            'color' => '#13ec80',
            'icon' => 'laptop',
        ]);

        $response->assertRedirect(route('goals.index'));

        $this->assertDatabaseHas('goals', [
            'name' => 'Beli Laptop MacBook',
            'family_id' => $this->family->id,
            'has_target' => 1,
            'target_amount' => 20000000.00,
            'current_balance' => 5000000.00,
            'latest_note' => 'Tabungan awal dari bonus',
        ]);

        // Verifikasi mutasi awal otomatis tercatat
        $this->assertDatabaseHas('goal_transactions', [
            'type' => 'in',
            'amount' => 5000000.00,
            'balance_before' => 0.00,
            'balance_after' => 5000000.00,
        ]);
    }

    /**
     * Test 3: Membuat pos tanpa target (Mode Pencatatan Saja).
     */
    public function test_user_can_create_tracking_only_pocket(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'name' => 'Kas Kecil Harian',
            'has_target' => '0',
            'initial_amount' => 500000,
            'initial_note' => 'Uang saku minggu pertama',
            'color' => '#3b82f6',
            'icon' => 'account_balance_wallet',
        ]);

        $response->assertRedirect(route('goals.index'));

        $this->assertDatabaseHas('goals', [
            'name' => 'Kas Kecil Harian',
            'family_id' => $this->family->id,
            'has_target' => 0,
            'target_amount' => null,
            'current_balance' => 500000.00,
            'latest_note' => 'Uang saku minggu pertama',
        ]);
    }

    /**
     * Test 4: Mutasi Penambahan Saldo (+) berhasil mengupdate saldo & latest note.
     */
    public function test_user_can_add_balance_to_goal(): void
    {
        $goal = Goal::create([
            'user_id' => $this->user->id,
            'family_id' => $this->family->id,
            'name' => 'Dana Darurat',
            'has_target' => false,
            'current_balance' => 1000000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->post(route('goals.adjust', $goal->id), [
            'type' => 'in',
            'amount' => 500000,
            'notes' => 'Setoran gaji bulanan',
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success');

        $goal->refresh();
        $this->assertEquals(1500000.00, $goal->current_balance);
        $this->assertEquals('Setoran gaji bulanan', $goal->latest_note);

        $this->assertDatabaseHas('goal_transactions', [
            'goal_id' => $goal->id,
            'type' => 'in',
            'amount' => 500000.00,
            'balance_before' => 1000000.00,
            'balance_after' => 1500000.00,
            'notes' => 'Setoran gaji bulanan',
        ]);
    }

    /**
     * Test 5: Mutasi Pengurangan Saldo (-) dan aturan TIDAK ADA MINUS (clamp to 0).
     */
    public function test_user_can_deduct_balance_and_clamp_to_zero_without_negative(): void
    {
        $goal = Goal::create([
            'user_id' => $this->user->id,
            'family_id' => $this->family->id,
            'name' => 'Kas Operasional',
            'has_target' => false,
            'current_balance' => 200000,
            'status' => 'active',
        ]);

        // Skenario A: Penarikan normal (masih ada sisa saldo)
        $this->actingAs($this->user)->post(route('goals.adjust', $goal->id), [
            'type' => 'out',
            'amount' => 50000,
            'notes' => 'Beli bensin motor',
        ]);

        $goal->refresh();
        $this->assertEquals(150000.00, $goal->current_balance);
        $this->assertEquals('Beli bensin motor', $goal->latest_note);

        // Skenario B: Penarikan melebihi saldo (Saldo 150.000 ditarik 300.000)
        // Aturan user: "tidak ada minus, jika memang melebihi saldo kita akan catat 0 saja"
        $this->actingAs($this->user)->post(route('goals.adjust', $goal->id), [
            'type' => 'out',
            'amount' => 300000,
            'notes' => 'Pengeluaran darurat besar',
        ]);

        $goal->refresh();
        $this->assertEquals(0.00, $goal->current_balance, 'Saldo tidak boleh minus, harus menjadi 0 jika penarikan melebihi saldo');
        $this->assertEquals('Pengeluaran darurat besar', $goal->latest_note);

        $this->assertDatabaseHas('goal_transactions', [
            'goal_id' => $goal->id,
            'type' => 'out',
            'amount' => 300000.00,
            'balance_before' => 150000.00,
            'balance_after' => 0.00,
        ]);
    }

    /**
     * Test 6: Kalkulasi Monthly Balancing Summary.
     */
    public function test_monthly_balancing_summary_calculation(): void
    {
        $goal1 = Goal::create([
            'user_id' => $this->user->id,
            'family_id' => $this->family->id,
            'name' => 'Pos A',
            'has_target' => true,
            'target_amount' => 10000000,
            'current_balance' => 2000000,
            'status' => 'active',
        ]);

        $goal2 = Goal::create([
            'user_id' => $this->user->id,
            'family_id' => $this->family->id,
            'name' => 'Pos B',
            'has_target' => false,
            'current_balance' => 3000000,
            'status' => 'active',
        ]);

        // Mutasi masuk 1.000.000 di Pos A
        GoalTransaction::create([
            'goal_id' => $goal1->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'amount' => 1000000,
            'balance_before' => 1000000,
            'balance_after' => 2000000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // Mutasi keluar 400.000 di Pos B
        GoalTransaction::create([
            'goal_id' => $goal2->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'amount' => 400000,
            'balance_before' => 3400000,
            'balance_after' => 3000000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $service = app(GoalBalancingService::class);
        $summary = $service->getMonthlyBalancingSummary($this->family->id);

        $this->assertEquals(5000000.00, $summary['total_balance']);
        $this->assertEquals(1000000.00, $summary['total_in_month']);
        $this->assertEquals(400000.00, $summary['total_out_month']);
        $this->assertEquals(600000.00, $summary['net_balancing_month']);
        $this->assertEquals(2, $summary['active_goals_count']);
        $this->assertEquals(1, $summary['target_goals_count']);
        $this->assertEquals(1, $summary['tracking_goals_count']);
    }

    /**
     * Test 7: Isolasi Data Keluarga (Family Scoping).
     */
    public function test_family_data_isolation(): void
    {
        $otherFamily = Family::create([
            'name' => 'Keluarga Lain',
            'family_code' => 'LAIN-5678',
        ]);

        $otherUser = User::factory()->create([
            'family_id' => $otherFamily->id,
        ]);

        $otherGoal = Goal::create([
            'user_id' => $otherUser->id,
            'family_id' => $otherFamily->id,
            'name' => 'Tabungan Keluarga Lain',
            'has_target' => true,
            'target_amount' => 50000000,
            'current_balance' => 10000000,
            'status' => 'active',
        ]);

        // User Budi tidak boleh bisa melihat detail pos keluarga lain
        $response = $this->actingAs($this->user)->get(route('goals.show', $otherGoal->id));
        $response->assertStatus(404);

        // User Budi tidak boleh bisa memutasi pos keluarga lain
        $adjustResponse = $this->actingAs($this->user)->post(route('goals.adjust', $otherGoal->id), [
            'type' => 'in',
            'amount' => 100000,
        ]);
        $adjustResponse->assertStatus(404);
    }
}
