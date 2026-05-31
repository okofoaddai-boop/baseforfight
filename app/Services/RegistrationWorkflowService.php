<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationWorkflowService
{
    public function hasDeadlinePassed(Event $event): bool
    {
        return $event->registration_deadline !== null && now()->greaterThan($event->registration_deadline);
    }

    public function lockBillingForEvent(Event $event): void
    {
        if (! $this->hasDeadlinePassed($event) || $event->billing_locked_at !== null) {
            return;
        }

        DB::transaction(function () use ($event): void {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            if ($lockedEvent->billing_locked_at !== null || ! $this->hasDeadlinePassed($lockedEvent)) {
                return;
            }

            Registration::query()
                ->where('event_id', $lockedEvent->getKey())
                ->where('status', Registration::STATUS_ACTIVE)
                ->whereNull('billable_at')
                ->update([
                    'billable_at' => $lockedEvent->registration_deadline ?? now(),
                    'billable_reason' => Registration::BILLABLE_REASON_DEADLINE_ACTIVE,
                    'status_changed_at' => now(),
                    'updated_at' => now(),
                ]);

            $lockedEvent->forceFill([
                'billing_locked_at' => now(),
            ])->save();

            $event->setAttribute('billing_locked_at', $lockedEvent->billing_locked_at);
        });
    }

    public function activeRegistrationCount(Event $event): int
    {
        return (int) Registration::query()
            ->where('event_id', $event->getKey())
            ->where('status', Registration::STATUS_ACTIVE)
            ->count();
    }

    public function determineInitialStatus(Event $event, int $activeRegistrationCount): ?string
    {
        $limitReached = $this->limitReached($event, $activeRegistrationCount);

        if ($limitReached && ! $event->allow_waitlist) {
            return null;
        }

        if ($this->hasDeadlinePassed($event)) {
            return Registration::STATUS_WAITING;
        }

        if ($event->registration_approval_mode === 'manual') {
            return Registration::STATUS_WAITING;
        }

        if ($limitReached) {
            return Registration::STATUS_WAITING;
        }

        return Registration::STATUS_ACTIVE;
    }

    public function transitionStatus(
        Registration $registration,
        string $targetStatus,
        ?User $actor = null,
        ?string $reason = null,
        array $meta = [],
    ): Registration {
        return DB::transaction(function () use ($registration, $targetStatus, $actor, $reason, $meta): Registration {
            /** @var Registration $lockedRegistration */
            $lockedRegistration = Registration::query()
                ->with('event')
                ->lockForUpdate()
                ->findOrFail($registration->getKey());

            $event = $lockedRegistration->event;
            if ($event) {
                $this->lockBillingForEvent($event);
                $event->refresh();
            }

            $fromStatus = (string) $lockedRegistration->status;
            if ($fromStatus === $targetStatus) {
                return $lockedRegistration;
            }

            $attributes = [
                'status' => $targetStatus,
                'status_changed_at' => now(),
                'withdrawn_at' => $targetStatus === Registration::STATUS_WITHDRAWN ? now() : null,
            ];

            if ($event && $targetStatus === Registration::STATUS_ACTIVE && $this->hasDeadlinePassed($event) && $lockedRegistration->billable_at === null) {
                $attributes['billable_at'] = now();
                $attributes['billable_reason'] = Registration::BILLABLE_REASON_POST_DEADLINE_APPROVAL;
            }

            $lockedRegistration->fill($attributes);
            $lockedRegistration->save();

            RegistrationStatusHistory::query()->create([
                'registration_id' => $lockedRegistration->getKey(),
                'from_status' => $fromStatus,
                'to_status' => $targetStatus,
                'changed_by_user_id' => $actor?->getKey(),
                'reason' => $reason,
                'meta' => $meta,
            ]);

            $registration->fill($lockedRegistration->getAttributes());

            return $lockedRegistration;
        });
    }

    public function markCreated(Registration $registration, ?User $actor = null, ?string $reason = null, array $meta = []): void
    {
        RegistrationStatusHistory::query()->create([
            'registration_id' => $registration->getKey(),
            'from_status' => null,
            'to_status' => (string) $registration->status,
            'changed_by_user_id' => $actor?->getKey(),
            'reason' => $reason,
            'meta' => $meta,
        ]);
    }

    private function limitReached(Event $event, int $activeRegistrationCount): bool
    {
        return is_numeric($event->max_registrations) && $activeRegistrationCount >= (int) $event->max_registrations;
    }
}