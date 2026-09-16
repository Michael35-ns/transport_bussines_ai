<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreDriverWorklogRequest;
use App\Models\Driver;
use App\Models\DriverWorklog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreDriverWorklogRequest::buildRules() is shared verbatim by
 * UpdateDriverWorklogRequest and by the Livewire component. hourly_rate_snapshot
 * and computed_pay are deliberately absent — both are always computed
 * server-side, never entered directly.
 */
class DriverWorklogRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'driver_id' => Driver::factory()->create()->id,
            'work_date' => '2026-09-15',
            'hours' => 8,
            'notes' => null,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $data = $this->validPayload();

        $validator = Validator::make($data, StoreDriverWorklogRequest::buildRules(null, $data['driver_id']));

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_more_than_24_hours(): void
    {
        $data = $this->validPayload();
        $data['hours'] = 25;

        $validator = Validator::make($data, StoreDriverWorklogRequest::buildRules(null, $data['driver_id']));

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_a_second_worklog_for_the_same_driver_and_day(): void
    {
        $data = $this->validPayload();
        DriverWorklog::factory()->create(['driver_id' => $data['driver_id'], 'work_date' => $data['work_date']]);

        $validator = Validator::make($data, StoreDriverWorklogRequest::buildRules(null, $data['driver_id']));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('work_date', $validator->errors()->toArray());
    }

    public function test_allows_the_same_day_for_a_different_driver(): void
    {
        $data = $this->validPayload();
        DriverWorklog::factory()->create(['driver_id' => Driver::factory()->create()->id, 'work_date' => $data['work_date']]);

        $validator = Validator::make($data, StoreDriverWorklogRequest::buildRules(null, $data['driver_id']));

        $this->assertFalse($validator->fails());
    }

    public function test_editing_the_same_worklog_does_not_trip_the_uniqueness_check(): void
    {
        $data = $this->validPayload();
        $worklog = DriverWorklog::factory()->create(['driver_id' => $data['driver_id'], 'work_date' => $data['work_date']]);

        $validator = Validator::make($data, StoreDriverWorklogRequest::buildRules($worklog, $data['driver_id']));

        $this->assertFalse($validator->fails());
    }
}
