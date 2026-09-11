<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attachable_type' => Invoice::class,
            'attachable_id' => Invoice::factory(),
            'disk' => 'local',
            'path' => 'attachments/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime' => 'application/pdf',
            'uploaded_by' => User::factory(),
        ];
    }
}
