<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Throwable;

class LegacyMigrationController extends Controller
{
    public function dryRun(): RedirectResponse
    {
        return $this->runCommand(true);
    }

    public function migrate(): RedirectResponse
    {
        return $this->runCommand(false);
    }

    private function runCommand(bool $dryRun): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('baseforfight:legacy-sync', [
                '--dry-run' => $dryRun,
                '--no-interaction' => true,
            ]);

            $output = trim(Artisan::output());
            $prefix = $dryRun ? 'Legacy-Migration Dry-Run' : 'Legacy-Migration Lauf';

            if ($exitCode === 0) {
                $message = $prefix . ' erfolgreich.';
                if ($output !== '') {
                    $message .= ' ' . $output;
                }

                return back()->with('status', $message);
            }

            $message = $prefix . ' fehlgeschlagen (Exit-Code ' . $exitCode . ').';
            if ($output !== '') {
                $message .= ' ' . $output;
            }

            return back()->withErrors(['legacy_sync' => $message]);
        } catch (CommandNotFoundException) {
            return back()->withErrors([
                'legacy_sync' => 'Der Command baseforfight:legacy-sync existiert noch nicht. Ich kann ihn als naechsten Schritt direkt anlegen.',
            ]);
        } catch (Throwable $exception) {
            return back()->withErrors([
                'legacy_sync' => 'Legacy-Migration konnte nicht gestartet werden: ' . $exception->getMessage(),
            ]);
        }
    }
}
