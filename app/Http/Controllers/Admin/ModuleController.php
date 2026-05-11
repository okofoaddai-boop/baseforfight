<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Modules\AiSettingsStore;
use App\Services\Modules\ModuleManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly AiSettingsStore $aiSettingsStore,
    ) {
    }

    public function index(): View
    {
        $aiSettings = $this->aiSettingsStore->read();
        $aiProviderPresets = $this->aiProviderPresets();

        return view('admin.modules.index', [
            'modules' => $this->moduleManager->all(),
            'aiSettings' => $aiSettings,
            'aiConfigured' => $this->aiSettingsStore->isConfigured(),
            'aiHasApiKey' => trim((string) ($aiSettings['api_key'] ?? '')) !== '',
            'aiProviderPresets' => $aiProviderPresets,
        ]);
    }

    public function toggle(string $module): RedirectResponse
    {
        if ($this->moduleManager->isActive($module)) {
            $this->moduleManager->deactivate($module);
            $status = 'Modul wurde deaktiviert.';
        } else {
            $this->moduleManager->activate($module);
            $status = 'Modul wurde aktiviert.';
        }

        return redirect()->route('admin.modules.index')->with('status', $status);
    }

    public function updateAiSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(array_keys($this->aiProviderPresets()))],
            'model' => ['required', 'string', 'max:190'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'base_url' => ['nullable', 'string', 'max:500'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'prompt_event_extraction' => ['required', 'string', 'min:10'],
            'prompt_pairing_suggestions' => ['required', 'string', 'min:10'],
        ]);

        $existing = $this->aiSettingsStore->read();
        $incomingApiKey = trim((string) ($validated['api_key'] ?? ''));
        $resolvedApiKey = $incomingApiKey !== ''
            ? $incomingApiKey
            : (string) ($existing['api_key'] ?? '');

        $this->aiSettingsStore->write([
            'provider' => trim((string) $validated['provider']),
            'model' => trim((string) $validated['model']),
            'api_key' => $resolvedApiKey,
            'base_url' => trim((string) ($validated['base_url'] ?? '')),
            'parameters' => [
                'temperature' => is_numeric($validated['temperature'] ?? null) ? (float) $validated['temperature'] : 0.2,
                'top_p' => is_numeric($validated['top_p'] ?? null) ? (float) $validated['top_p'] : 1.0,
                'max_tokens' => is_numeric($validated['max_tokens'] ?? null) ? (int) $validated['max_tokens'] : 4096,
            ],
            'prompts' => [
                'event_extraction' => trim((string) $validated['prompt_event_extraction']),
                'pairing_suggestions' => trim((string) $validated['prompt_pairing_suggestions']),
            ],
        ]);

        $status = $this->aiSettingsStore->isConfigured()
            ? 'KI-Modul wurde konfiguriert. Bei aktivem Modul sind die Veranstalter-Buttons sichtbar.'
            : 'KI-Modul gespeichert, aber noch nicht vollstaendig konfiguriert.';

        return redirect()->route('admin.modules.index')->with('status', $status);
    }

    private function aiProviderPresets(): array
    {
        return [
            'openai' => [
                'label' => 'OpenAI',
                'default_base_url' => 'https://api.openai.com/v1',
                'models' => ['gpt-4o-mini', 'gpt-4.1-mini', 'gpt-4o', 'gpt-4.1'],
            ],
            'anthropic' => [
                'label' => 'Anthropic',
                'default_base_url' => 'https://api.anthropic.com/v1',
                'models' => ['claude-3-5-haiku-latest', 'claude-3-5-sonnet-latest', 'claude-3-7-sonnet-latest'],
            ],
            'google' => [
                'label' => 'Google (Gemini)',
                'default_base_url' => '',
                'models' => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash'],
            ],
            'copilot' => [
                'label' => 'Copilot (OpenAI-kompatibel)',
                'default_base_url' => '',
                'models' => ['gpt-4o-mini', 'gpt-4o', 'gpt-4.1'],
            ],
        ];
    }
}
