<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;

class ProrataCalculationService
{
    /**
     * Calculate prorated invoice items for a newly created subscription.
     * Returns an array of line items to be inserted into invoice_items, or null if no proration needed.
     */
    public function calculateNewSubscription(Subscription $sub): ?array
    {
        if (! \App\Models\SystemSetting::get('billing.proration_enabled', true)) {
            return null;
        }

        $startDate = Carbon::parse($sub->installed_at)->startOfDay();

        // If it starts on the generate day, it's a full month, no proration needed
        $generateDay = (int) \App\Models\SystemSetting::get('billing.auto_generate_day', 1);
        if ($startDate->day === $generateDay) {
            return null;
        }

        // Calculate end date: the day before the NEXT generate day
        $endDate = $startDate->copy();
        if ($startDate->day > $generateDay) {
            $endDate->addMonth();
        }
        $endDate->day($generateDay)->subDay()->endOfDay();

        $activeDays = $startDate->diffInDays($endDate) + 1;
        $daysInMonth = $startDate->daysInMonth;

        // For multi-month packages, calculate based on the full period
        // Usually proration is for the first month only
        $monthlyBasePrice = $sub->base_price / max(1, $sub->billing_period_months);
        $dailyRate = $monthlyBasePrice / $daysInMonth;

        $proratedAmount = round($dailyRate * $activeDays, 2);

        $description = sprintf(
            'Prorata Langganan %s (%s - %s, %d hari)',
            $sub->package->name,
            $startDate->format('d M Y'),
            $endDate->format('d M Y'),
            $activeDays
        );

        return [
            [
                'description' => $description,
                'amount' => $proratedAmount,
                'qty' => 1,
                'total' => $proratedAmount,
                'subscription_id' => $sub->id,
                'is_prorated' => true,
                'proration_start_date' => $startDate->format('Y-m-d'),
                'proration_end_date' => $endDate->format('Y-m-d'),
                'proration_days' => $activeDays,
            ],
        ];
    }

    /**
     * Calculate prorated invoice items for a subscription that is upgraded or downgraded.
     */
    public function calculateUpgradeDowngrade(Subscription $sub, float $oldBasePrice, Carbon $changeDate, int $oldBillingPeriodMonths = 1): ?array
    {
        if (! \App\Models\SystemSetting::get('billing.proration_enabled', true)) {
            return null;
        }

        $startDate = $changeDate->copy()->startOfDay();
        $generateDay = (int) \App\Models\SystemSetting::get('billing.auto_generate_day', 1);

        // Find the END of the current billing cycle
        $endDate = $startDate->copy();
        if ($startDate->day >= $generateDay) {
            $endDate->addMonth();
        }
        $endDate->day($generateDay)->subDay()->endOfDay();

        $remainingDays = $startDate->diffInDays($endDate) + 1;
        $daysInMonth = $startDate->daysInMonth;

        $monthlyOldPrice = $oldBasePrice / max(1, $oldBillingPeriodMonths);
        $monthlyNewPrice = $sub->base_price / max(1, $sub->billing_period_months);

        $oldDailyRate = $monthlyOldPrice / $daysInMonth;
        $newDailyRate = $monthlyNewPrice / $daysInMonth;

        $creditAmount = round($oldDailyRate * $remainingDays, 2);
        $chargeAmount = round($newDailyRate * $remainingDays, 2);

        $items = [];

        // Credit for the old package
        if ($creditAmount > 0) {
            $items[] = [
                'description' => sprintf(
                    'Kredit Sisa Langganan Paket Lama (%s - %s, %d hari)',
                    $startDate->format('d M Y'),
                    $endDate->format('d M Y'),
                    $remainingDays
                ),
                'amount' => -$creditAmount,
                'qty' => 1,
                'total' => -$creditAmount,
                'subscription_id' => $sub->id,
                'is_prorated' => true,
                'proration_start_date' => $startDate->format('Y-m-d'),
                'proration_end_date' => $endDate->format('Y-m-d'),
                'proration_days' => $remainingDays,
            ];
        }

        // Charge for the new package
        if ($chargeAmount > 0) {
            $items[] = [
                'description' => sprintf(
                    'Prorata Langganan %s (%s - %s, %d hari)',
                    $sub->package->name,
                    $startDate->format('d M Y'),
                    $endDate->format('d M Y'),
                    $remainingDays
                ),
                'amount' => $chargeAmount,
                'qty' => 1,
                'total' => $chargeAmount,
                'subscription_id' => $sub->id,
                'is_prorated' => true,
                'proration_start_date' => $startDate->format('Y-m-d'),
                'proration_end_date' => $endDate->format('Y-m-d'),
                'proration_days' => $remainingDays,
            ];
        }

        return $items;
    }

    /**
     * Calculate prorated credit when a subscription is suspended or terminated mid-cycle.
     */
    public function calculateSuspendTerminate(Subscription $sub, Carbon $terminationDate): ?array
    {
        if (! \App\Models\SystemSetting::get('billing.proration_enabled', true)) {
            return null;
        }

        $startDate = $terminationDate->copy()->startOfDay();
        $generateDay = (int) \App\Models\SystemSetting::get('billing.auto_generate_day', 1);

        $endDate = $startDate->copy();
        if ($startDate->day >= $generateDay) {
            $endDate->addMonth();
        }
        $endDate->day($generateDay)->subDay()->endOfDay();

        $remainingDays = $startDate->diffInDays($endDate) + 1;
        $daysInMonth = $startDate->daysInMonth;

        $monthlyBasePrice = $sub->base_price / max(1, $sub->billing_period_months);
        $dailyRate = $monthlyBasePrice / $daysInMonth;

        $creditAmount = round($dailyRate * $remainingDays, 2);

        if ($creditAmount <= 0) {
            return null;
        }

        $statusLabel = $sub->status === 'suspended' ? 'Suspend' : 'Terminasi';

        return [
            [
                'description' => sprintf(
                    'Kredit %s Langganan %s (%s - %s, %d hari)',
                    $statusLabel,
                    $sub->package->name,
                    $startDate->format('d M Y'),
                    $endDate->format('d M Y'),
                    $remainingDays
                ),
                'amount' => -$creditAmount,
                'qty' => 1,
                'total' => -$creditAmount,
                'subscription_id' => $sub->id,
                'is_prorated' => true,
                'proration_start_date' => $startDate->format('Y-m-d'),
                'proration_end_date' => $endDate->format('Y-m-d'),
                'proration_days' => $remainingDays,
            ],
        ];
    }
}
