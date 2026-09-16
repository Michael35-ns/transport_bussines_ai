<?php

namespace Tests\Feature\Livewire\TollRecords;

use App\Livewire\TollRecords\Index;
use App\Models\TollRecord;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('toll-records.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_record_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123']);
        TollRecord::factory()->create(['truck_id' => $truck->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('SJB-123');
    }

    public function test_an_admin_can_create_a_toll_record(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('amount', 1500)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('toll_records', ['truck_id' => $truck->id, 'amount' => '1500.00']);
    }

    public function test_creating_a_toll_record_requires_an_amount(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('amount', null)
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    public function test_a_viewer_cannot_create_a_toll_record(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('amount', 1500)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('toll_records', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_a_toll_record(): void
    {
        $record = TollRecord::factory()->create(['amount' => 1000]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $record)
            ->assertSet('amount', 1000.0)
            ->set('amount', 2000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('2000.00', $record->fresh()->amount);
    }

    public function test_an_admin_can_delete_a_toll_record(): void
    {
        $record = TollRecord::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $record);

        $this->assertModelMissing($record);
    }

    public function test_a_viewer_cannot_delete_a_toll_record(): void
    {
        $record = TollRecord::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $record)
            ->assertForbidden();

        $this->assertModelExists($record);
    }
}
