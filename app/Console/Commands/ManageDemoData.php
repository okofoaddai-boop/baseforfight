<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ManageDemoData extends Command
{
    protected $signature = 'baseforfight:demo-data {action : enable|disable|refresh}';

    protected $description = 'Aktiviert oder deaktiviert markierte Demo-Daten fuer BaseForFight.';

    public function handle(): int
    {
        $action = strtolower((string) $this->argument('action'));

        return match ($action) {
            'enable' => $this->enableDemoData(),
            'disable' => $this->disableDemoData(),
            'refresh' => $this->refreshDemoData(),
            default => $this->invalidAction($action),
        };
    }

    private function enableDemoData(): int
    {
        $this->call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);

        $this->info('Demo-Daten wurden aktiviert beziehungsweise aktualisiert.');

        return self::SUCCESS;
    }

    private function disableDemoData(): int
    {
        DB::transaction(function (): void {
            Registration::query()
                ->whereIn('event_id', Event::withTrashed()->where('is_demo', true)->select('id'))
                ->orWhereIn('fighter_id', Fighter::withTrashed()->where('is_demo', true)->select('id'))
                ->delete();

            ClubJoinRequest::query()->where('is_demo', true)->delete();

            Event::withTrashed()->where('is_demo', true)->get()->each(function (Event $event): void {
                $event->forceDelete();
            });

            Fighter::withTrashed()->where('is_demo', true)->get()->each(function (Fighter $fighter): void {
                $fighter->forceDelete();
            });

            Club::withTrashed()->where('is_demo', true)->get()->each(function (Club $club): void {
                $club->forceDelete();
            });

            User::query()->where('is_demo', true)->delete();
        });

        $this->info('Alle markierten Demo-Daten wurden entfernt.');

        return self::SUCCESS;
    }

    private function refreshDemoData(): int
    {
        $disableExitCode = $this->disableDemoData();

        if ($disableExitCode !== self::SUCCESS) {
            return $disableExitCode;
        }

        return $this->enableDemoData();
    }

    private function invalidAction(string $action): int
    {
        $this->error('Unbekannte Aktion: ' . $action . '. Erlaubt sind enable, disable oder refresh.');

        return self::FAILURE;
    }
}