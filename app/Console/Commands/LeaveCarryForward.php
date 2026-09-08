<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Console\Command;

class LeaveCarryForward extends Command
{
    protected $signature = 'leave:carry-forward {--year= : Target year}';

    protected $description = 'Carry forward unused leave balances to the next year';

    public function handle(): void
    {
        $year = $this->option('year') ?: now()->year;
        $nextYear = $year + 1;

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $processed = 0;

        foreach ($leaveTypes as $leaveType) {
            $balances = LeaveBalance::where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->where('remaining_days', '>', 0)
                ->with('user')
                ->get();

            foreach ($balances as $balance) {
                $carryForward = min($balance->remaining_days, $leaveType->carry_forward_limit ?? 5);

                if ($carryForward <= 0) {
                    continue;
                }

                LeaveBalance::updateOrCreate(
                    [
                        'user_id' => $balance->user_id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $nextYear,
                    ],
                    [
                        'allocated_days' => $leaveType->annual_allocation + $carryForward,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->annual_allocation + $carryForward,
                    ]
                );

                $processed++;
            }
        }

        $this->info("Carry-forward completed: {$processed} balance records processed for year {$nextYear}.");
    }
}
