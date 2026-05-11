<?php

namespace App\Services\Modules;

class AiSettingsStore
{
    public function filePath(): string
    {
        return base_path('modules/ai/settings/config.json');
    }

    public function read(): array
    {
        $file = $this->filePath();
        if (! is_file($file)) {
            return $this->defaults();
        }

        $raw = file_get_contents($file);
        if (! is_string($raw) || trim($raw) === '') {
            return $this->defaults();
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $this->defaults();
        }

        return array_replace_recursive($this->defaults(), $decoded);
    }

    public function write(array $settings): void
    {
        $file = $this->filePath();
        $dir = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $payload = array_replace_recursive($this->defaults(), $settings);
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($encoded)) {
            return;
        }

        file_put_contents($file, $encoded . PHP_EOL);
    }

    public function isConfigured(): bool
    {
        $settings = $this->read();

        return $this->requiredFieldFilled((string) ($settings['provider'] ?? ''))
            && $this->requiredFieldFilled((string) ($settings['model'] ?? ''))
            && $this->requiredFieldFilled((string) ($settings['api_key'] ?? ''))
            && $this->requiredFieldFilled((string) ($settings['prompts']['event_extraction'] ?? ''))
            && $this->requiredFieldFilled((string) ($settings['prompts']['pairing_suggestions'] ?? ''));
    }

    private function defaults(): array
    {
        return [
            'provider' => '',
            'model' => '',
            'api_key' => '',
            'base_url' => '',
            'parameters' => [
                'temperature' => 0.2,
                'top_p' => 1.0,
                'max_tokens' => 4096,
            ],
            'prompts' => [
                'event_extraction' => '',
                'pairing_suggestions' => '',
            ],
        ];
    }

    private function requiredFieldFilled(string $value): bool
    {
        return trim($value) !== '';
    }
}
