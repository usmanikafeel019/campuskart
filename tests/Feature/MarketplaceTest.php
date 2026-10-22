<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Material;
use App\Models\Report;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['title' => 'Original DBMS Notes', 'description' => 'Original handwritten notes for the entire semester.', 'category_id' => Category::firstOrCreate(['name' => 'Notes'])->id, 'subject_id' => Subject::firstOrCreate(['name' => 'DBMS'])->id, 'semester' => 4, 'price' => '100.00', 'condition' => 'good', 'permission' => 1];
    }

    private function material(?User $u = null, array $extra = []): Material
    {
        $d = $this->data();
        unset($d['permission']);

        return Material::create(array_merge($d, ['user_id' => ($u ?? User::factory()->create())->id], $extra));
    }

    public function test_registration_cannot_escalate_role_and_password_is_hashed(): void
    {
        $this->post('/register', ['name' => 'Student', 'email' => 'student@test.com', 'password' => 'Password123', 'password_confirmation' => 'Password123', 'role' => 'admin'])->assertRedirect('/my-listings');
        $u = User::first();
        $this->assertSame('student', $u->role);
        $this->assertTrue(Hash::check('Password123', $u->password));
        $this->assertAuthenticatedAs($u);
    }

    public function test_login_logout_and_protected_pages(): void
    {
        $u = User::factory()->create(['password' => 'Password123']);
        $this->get('/my-listings')->assertRedirect('/login');
        $this->post('/login', ['email' => $u->email, 'password' => 'Password123'])->assertRedirect();
        $this->get('/my-listings')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_listing_upload_update_and_delete_cleanup(): void
    {
        Storage::fake('public');
        $u = User::factory()->create();
        $this->actingAs($u)->post('/materials', $this->data() + ['image' => UploadedFile::fake()->image('cover.jpg')])->assertRedirect();
        $m = Material::first();
        $old = $m->image_path;
        Storage::disk('public')->assertExists($old);
        $this->get('/materials/'.$m->id)->assertOk()->assertSee('Original DBMS Notes');
        $this->put('/materials/'.$m->id, array_merge($this->data(), ['title' => 'Updated notes', 'image' => UploadedFile::fake()->image('new.png')]))->assertRedirect();
        Storage::disk('public')->assertMissing($old);
        $new = $m->fresh()->image_path;
        $this->delete('/materials/'.$m->id)->assertRedirect();
        Storage::disk('public')->assertMissing($new);
        $this->assertDatabaseCount('materials', 0);
    }

    public function test_search_filters_and_sold_visibility(): void
    {
        $m = $this->material();
        $this->material(null, ['title' => 'Sold item', 'status' => 'sold']);
        $this->get('/materials?q=DBMS&semester=4&category_id='.$m->category_id.'&min=90&max=110&condition=good')->assertOk()->assertSee($m->title)->assertDontSee('Sold item');
        $this->get('/materials?min=200')->assertOk()->assertSee('No matches found');
    }

    public function test_enquiry_response_and_private_access(): void
    {
        $owner = User::factory()->create();
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $m = $this->material($owner);
        $this->actingAs($buyer)->post('/materials/'.$m->id.'/enquiries', ['message' => 'Can we meet at the library?'])->assertRedirect();
        $e = Enquiry::first();
        $this->get('/enquiries')->assertSee('Can we meet at the library?');
        $this->actingAs($owner)->get('/enquiries/received')->assertSee('Can we meet at the library?');
        $this->patch('/enquiries/'.$e->id, ['status' => 'responded', 'response' => 'Yes, at noon.'])->assertRedirect();
        $this->actingAs($buyer)->get('/enquiries')->assertSee('Yes, at noon.');
        $this->actingAs($stranger)->get('/enquiries')->assertDontSee('Can we meet at the library?');
        $this->patch('/enquiries/'.$e->id, ['status' => 'closed'])->assertForbidden();
    }

    public function test_sold_material_rejects_enquiries(): void
    {
        $seller = User::factory()->create();
        $m = $this->material($seller);
        $this->actingAs($seller)->patch('/materials/'.$m->id.'/sold')->assertRedirect();
        $this->assertDatabaseHas('materials', ['id' => $m->id, 'status' => 'sold']);
        $this->actingAs(User::factory()->create())->post('/materials/'.$m->id.'/enquiries', ['message' => 'I would like this book.'])->assertStatus(422);
    }

    public function test_ownership_admin_and_self_enquiry_protection(): void
    {
        $m = $this->material();
        $other = User::factory()->create();
        $this->actingAs($other)->get('/materials/'.$m->id.'/edit')->assertForbidden();
        $this->put('/materials/'.$m->id, $this->data())->assertForbidden();
        $this->delete('/materials/'.$m->id)->assertForbidden();
        $this->get('/admin')->assertForbidden();
        $this->actingAs($m->user)->post('/materials/'.$m->id.'/enquiries', ['message' => 'My own enquiry'])->assertForbidden();
    }

    public function test_validation_rejects_negative_price_unsafe_and_large_uploads(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post('/materials', [])->assertSessionHasErrors(['title', 'price']);
        $this->post('/materials', array_merge($this->data(), ['price' => -1]))->assertSessionHasErrors('price');
        $this->post('/materials', $this->data() + ['image' => UploadedFile::fake()->create('payload.php', 10)])->assertSessionHasErrors('image');
        $this->post('/materials', $this->data() + ['image' => UploadedFile::fake()->image('large.jpg')->size(3000)])->assertSessionHasErrors('image');
        $this->assertDatabaseCount('materials', 0);
    }

    public function test_admin_taxonomy_reports_and_suspension(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $m = $this->material();
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->post('/materials/'.$m->id.'/reports', ['reason' => 'This listing appears to be an unauthorized copy.'])->assertRedirect();
        $this->post('/materials/'.$m->id.'/reports', ['reason' => 'This listing appears to be an unauthorized copy.']);
        $this->assertDatabaseCount('reports', 1);
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/materials')->assertOk();
        $this->get('/admin/reports')->assertOk();
        $this->post('/admin/options/categories', ['name' => 'Workbooks'])->assertRedirect();
        $c = Category::where('name', 'Workbooks')->first();
        $this->put('/admin/options/categories/'.$c->id, ['name' => 'Lab Books'])->assertRedirect();
        $this->delete('/admin/options/categories/'.$m->category_id)->assertSessionHasErrors('name');
        $this->patch('/admin/reports/'.Report::first()->id, ['status' => 'resolved'])->assertRedirect();
        $this->patch('/admin/users/'.$m->user_id)->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $m->user_id, 'is_active' => false]);
        $this->delete('/materials/'.$m->id)->assertRedirect('/admin/materials');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_public_and_student_pages_render(): void
    {
        foreach (['/', '/about', '/contact', '/materials', '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }$this->actingAs(User::factory()->create());
        foreach (['/profile', '/my-listings', '/enquiries', '/enquiries/received', '/materials/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_suspended_accounts_are_logged_out(): void
    {
        $u = User::factory()->create();
        $u->forceFill(['is_active' => false])->save();
        $this->actingAs($u)->get('/my-listings')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_profile_needs_current_password_and_role_is_ignored(): void
    {
        $u = User::factory()->create(['password' => 'Password123']);
        $this->actingAs($u)->put('/profile', ['name' => 'Updated', 'email' => $u->email, 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->put('/profile', ['name' => 'Updated', 'email' => $u->email, 'current_password' => 'Password123', 'role' => 'admin'])->assertRedirect();
        $this->assertSame('student',$u->fresh()->role);
    }
}
