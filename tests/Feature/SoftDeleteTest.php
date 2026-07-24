<?php

namespace Tests\Feature;

use App\Models\CompanyFile;
use App\Models\ProductItem;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_soft_delete_preserves_database_row()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        // Standard Eloquent query excludes soft deleted user
        $this->assertNull(User::find($userId));

        // Record still exists in database table with deleted_at timestamp
        $row = DB::table('users')->where('id', $userId)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->deleted_at);
        $this->assertEquals($user->name, $row->name);

        // Eloquent withTrashed finds the record
        $this->assertNotNull(User::withTrashed()->find($userId));
    }

    public function test_quote_soft_delete_preserves_database_row()
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $user->id]);
        $quoteId = $quote->id;

        $quote->delete();

        $this->assertNull(Quote::find($quoteId));
        $row = DB::table('quotes')->where('id', $quoteId)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->deleted_at);
    }

    public function test_supplier_soft_delete_preserves_database_row()
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789',
        ]);
        $id = $supplier->id;

        $supplier->delete();

        $this->assertNull(Supplier::find($id));
        $row = DB::table('suppliers')->where('id', $id)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->deleted_at);
    }
}
