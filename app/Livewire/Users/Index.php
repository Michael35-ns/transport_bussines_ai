<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Models\DriverWorklog;
use App\Models\FuelRecord;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Payment;
use App\Models\TollRecord;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TruckExpense;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Usuarios')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public ?string $password = null;

    public ?string $password_confirmation = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search !== '', function ($query): void {
                $term = "%{$this->search}%";
                $query->where('name', 'like', $term)->orWhere('email', 'like', $term);
            })
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function roleOptions(): array
    {
        return collect(UserRole::cases())->mapWithKeys(fn (UserRole $case) => [$case->value => $case->label()])->all();
    }

    public function create(): void
    {
        $this->authorize('create', User::class);

        $this->resetForm();
        Flux::modal('user-form')->show();
    }

    public function edit(User $user): void
    {
        $this->authorize('update', $user);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->password = null;
        $this->password_confirmation = null;

        Flux::modal('user-form')->show();
    }

    public function save(): void
    {
        $user = $this->editingId ? User::findOrFail($this->editingId) : null;

        $this->authorize($user ? 'update' : 'create', $user ?? User::class);

        $validated = $this->validate(StoreUserRequest::buildRules(
            ignoring: $user,
            passwordRequired: $user === null,
        ));

        if ($user) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];
            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }
            $user->save();
        } else {
            $user = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
            ]);
            // Created internally by an admin — skip the email-verification loop.
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        unset($this->users);
        Flux::modal('user-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $user->wasRecentlyCreated ? __('Usuario creado.') : __('Usuario actualizado.'));
    }

    public function delete(User $user): void
    {
        $this->authorize('delete', $user);

        if ($this->hasRelatedRecords($user)) {
            Flux::toast(
                variant: 'danger',
                text: __('No se puede eliminar: este usuario tiene registros asociados (viajes, gastos, etc.). Cambia su rol o contraseña en vez de eliminarlo.'),
            );

            return;
        }

        $user->delete();

        unset($this->users);
        Flux::toast(variant: 'success', text: __('Usuario eliminado.'));
    }

    /**
     * Every table with a `created_by` column restricted to `users.id`
     * (`restrictOnDelete()`) — checked up front so deleting a user who has
     * ever created a record fails with a clear message instead of a raw DB
     * constraint error. Kept in sync by hand, matching this project's
     * convention of explicit schema knowledge over reflection (see e.g.
     * CostTypeSeeder's hand-written category list).
     */
    private function hasRelatedRecords(User $user): bool
    {
        return Trip::query()->where('created_by', $user->id)->exists()
            || FuelRecord::query()->where('created_by', $user->id)->exists()
            || TollRecord::query()->where('created_by', $user->id)->exists()
            || TripExpense::query()->where('created_by', $user->id)->exists()
            || Maintenance::query()->where('created_by', $user->id)->exists()
            || TruckExpense::query()->where('created_by', $user->id)->exists()
            || DriverWorklog::query()->where('created_by', $user->id)->exists()
            || Invoice::query()->where('created_by', $user->id)->exists()
            || Payment::query()->where('created_by', $user->id)->exists();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'password_confirmation']);
        $this->role = UserRole::Admin->value;
        $this->resetErrorBag();
    }
}
