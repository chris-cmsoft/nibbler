<?php

namespace Tests\Feature;

use App\Jobs\ConsumePetCalories;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_consume_pet_calories_schedule_runs_on_one_server(): void
    {
        $event = collect(Schedule::events())
            ->firstWhere('description', ConsumePetCalories::class);

        $this->assertNotNull($event);
        $this->assertTrue($event->onOneServer);
        $this->assertSame(ConsumePetCalories::CONSUMPTION_INTERVAL_SECONDS, $event->repeatSeconds);
    }
}
