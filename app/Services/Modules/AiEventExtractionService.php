<?php

namespace App\Services\Modules;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiEventExtractionService
{
    public function __construct(private readonly AiSettingsStore $aiSettingsStore)
    {
    }

    public function extractEventData(string $pdfText): array
    {
        $settings = $this->aiSettingsStore->read();
        $apiKey = trim((string) ($settings['api_key'] ?? ''));
        $model = trim((string) ($settings['model'] ?? ''));
        $provider = strtolower(trim((string) ($settings['provider'] ?? 'openai')));
        $prompt = trim((string) ($settings['prompts']['event_extraction'] ?? ''));

        if ($apiKey === '' || $model === '' || $prompt === '') {
            throw new RuntimeException('KI-Modul ist nicht vollständig konfiguriert.');
        }

        $temperature = is_numeric($settings['parameters']['temperature'] ?? null) ? (float) $settings['parameters']['temperature'] : 0.2;
        $topP = is_numeric($settings['parameters']['top_p'] ?? null) ? (float) $settings['parameters']['top_p'] : 1.0;
        $maxTokens = is_numeric($settings['parameters']['max_tokens'] ?? null) ? (int) $settings['parameters']['max_tokens'] : 4096;

        if ($provider === 'copilot' && trim((string) ($settings['base_url'] ?? '')) === '') {
            throw new RuntimeException('Für Copilot muss eine OpenAI-kompatible Base URL hinterlegt sein.');
        }

        $response = match ($provider) {
            'openai' => $this->sendOpenAiCompatibleRequest(
                apiKey: $apiKey,
                baseUrl: (string) ($settings['base_url'] ?? ''),
                model: $model,
                prompt: $prompt,
                pdfText: $pdfText,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
            ),
            'copilot' => $this->sendOpenAiCompatibleRequest(
                apiKey: $apiKey,
                baseUrl: (string) ($settings['base_url'] ?? ''),
                model: $model,
                prompt: $prompt,
                pdfText: $pdfText,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
            ),
            'anthropic' => $this->sendAnthropicRequest(
                apiKey: $apiKey,
                baseUrl: (string) ($settings['base_url'] ?? ''),
                model: $model,
                prompt: $prompt,
                pdfText: $pdfText,
                temperature: $temperature,
                maxTokens: $maxTokens,
            ),
            'google' => $this->sendGoogleRequest(
                apiKey: $apiKey,
                baseUrl: (string) ($settings['base_url'] ?? ''),
                model: $model,
                prompt: $prompt,
                pdfText: $pdfText,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
            ),
            default => throw new RuntimeException('Unbekannter KI-Provider: ' . $provider),
        };

        if (! $response->successful()) {
            $snippet = substr((string) $response->body(), 0, 800);
            $hint = '';
            if (str_contains(strtolower($snippet), 'invalid_api_key')) {
                $hint = ' Prüfe, ob Provider und API-Key zusammenpassen.';
            }
            if ($provider === 'google' && $response->status() === 404) {
                $availableModels = $this->listGoogleGenerateContentModels(
                    apiKey: $apiKey,
                    baseUrl: (string) ($settings['base_url'] ?? ''),
                );

                if ($availableModels !== []) {
                    $hint .= ' Verfuegbare Modelle (generateContent): ' . implode(', ', array_slice($availableModels, 0, 8)) . '.';
                } else {
                    $hint .= ' Modell scheint fuer den verwendeten Endpoint nicht verfuegbar zu sein; pruefe Modellname und API-Version (v1/v1beta).';
                }
            }

            throw new RuntimeException('KI-Request für Provider "' . $provider . '" fehlgeschlagen: ' . $snippet . $hint);
        }

        $content = $this->extractContentByProvider($provider, (array) $response->json());
        if (trim($content) === '') {
            throw new RuntimeException('KI-Antwort enthält keinen auswertbaren Inhalt.');
        }

        $decoded = $this->decodeJsonContent($content);

        return $this->normalizeResult($decoded);
    }

    private function resolveEndpoint(string $baseUrl): string
    {
        $trimmed = trim($baseUrl);
        if ($trimmed === '') {
            return 'https://api.openai.com/v1/chat/completions';
        }

        if (str_ends_with($trimmed, '/chat/completions')) {
            return $trimmed;
        }

        return rtrim($trimmed, '/') . '/chat/completions';
    }

    private function sendOpenAiCompatibleRequest(
        string $apiKey,
        string $baseUrl,
        string $model,
        string $prompt,
        string $pdfText,
        float $temperature,
        float $topP,
        int $maxTokens,
    ) {
        $endpoint = $this->resolveEndpoint($baseUrl);

        return Http::timeout(120)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($endpoint, [
                'model' => $model,
                'temperature' => $temperature,
                'top_p' => $topP,
                'max_tokens' => $maxTokens,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "PDF_TEXT:\n" . $pdfText],
                ],
            ]);
    }

    private function sendAnthropicRequest(
        string $apiKey,
        string $baseUrl,
        string $model,
        string $prompt,
        string $pdfText,
        float $temperature,
        int $maxTokens,
    ) {
        $endpoint = trim($baseUrl) !== ''
            ? rtrim(trim($baseUrl), '/') . '/messages'
            : 'https://api.anthropic.com/v1/messages';

        return Http::timeout(120)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->acceptJson()
            ->post($endpoint, [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'system' => $prompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => "PDF_TEXT:\n" . $pdfText],
                        ],
                    ],
                ],
            ]);
    }

    private function sendGoogleRequest(
        string $apiKey,
        string $baseUrl,
        string $model,
        string $prompt,
        string $pdfText,
        float $temperature,
        float $topP,
        int $maxTokens,
    ) {
        $normalizedModel = $this->normalizeGoogleModelName($model);
        $profile = $this->googleRequestProfile($normalizedModel, $baseUrl);
        $lastResponse = null;

        foreach ($profile['api_bases'] as $base) {
            $endpoint = $base . '/models/' . $normalizedModel . ':generateContent?key=' . urlencode($apiKey);

            foreach ($profile['payload_modes'] as $payloadMode) {
                $payload = $this->buildGooglePayload(
                    payloadMode: $payloadMode,
                    prompt: $prompt,
                    pdfText: $pdfText,
                    temperature: $temperature,
                    topP: $topP,
                    maxTokens: $maxTokens,
                );

                $response = Http::timeout(120)
                    ->acceptJson()
                    ->post($endpoint, $payload);

                if ($response->successful()) {
                    return $response;
                }

                $lastResponse = $response;

                if (! $this->shouldRetryGooglePayload($response)) {
                    break;
                }
            }

            if (! $this->shouldRetryGoogleBase($lastResponse)) {
                break;
            }
        }

        return $lastResponse;
    }

    private function buildGooglePayload(
        string $payloadMode,
        string $prompt,
        string $pdfText,
        float $temperature,
        float $topP,
        int $maxTokens,
    ): array {
        $inlineText = $prompt . "\n\nPDF_TEXT:\n" . $pdfText;

        return match ($payloadMode) {
            'system_snake' => [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "PDF_TEXT:\n" . $pdfText],
                        ],
                    ],
                ],
                'generation_config' => [
                    'temperature' => $temperature,
                    'top_p' => $topP,
                    'max_output_tokens' => $maxTokens,
                ],
            ],
            'inline_snake' => [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $inlineText],
                        ],
                    ],
                ],
                'generation_config' => [
                    'temperature' => $temperature,
                    'top_p' => $topP,
                    'max_output_tokens' => $maxTokens,
                ],
            ],
            'inline_camel' => [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $inlineText],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'topP' => $topP,
                    'maxOutputTokens' => $maxTokens,
                ],
            ],
            default => [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $inlineText],
                        ],
                    ],
                ],
            ],
        };
    }

    private function normalizeGoogleModelName(string $model): string
    {
        $name = trim($model);
        if (str_starts_with($name, 'models/')) {
            return substr($name, 7);
        }

        return $name;
    }

    private function googleRequestProfile(string $model, string $baseUrl): array
    {
        if (trim($baseUrl) !== '') {
            return [
                'api_bases' => [rtrim(trim($baseUrl), '/')],
                'payload_modes' => $this->googlePayloadModesForModel($model),
            ];
        }

        $normalizedModel = strtolower(trim($model));

        $apiBases = [
            'https://generativelanguage.googleapis.com/v1',
            'https://generativelanguage.googleapis.com/v1beta',
        ];

        if ($normalizedModel !== '') {
            if (str_contains($normalizedModel, '1.5') || str_contains($normalizedModel, 'preview') || str_contains($normalizedModel, 'experimental')) {
                $apiBases = [
                    'https://generativelanguage.googleapis.com/v1beta',
                    'https://generativelanguage.googleapis.com/v1',
                ];
            }

            if (str_contains($normalizedModel, '2.0') || str_contains($normalizedModel, '2.5')) {
                $apiBases = [
                    'https://generativelanguage.googleapis.com/v1',
                    'https://generativelanguage.googleapis.com/v1beta',
                ];
            }
        }

        return [
            'api_bases' => $apiBases,
            'payload_modes' => $this->googlePayloadModesForModel($normalizedModel),
        ];
    }

    private function googlePayloadModesForModel(string $model): array
    {
        $normalizedModel = strtolower(trim($model));

        if ($normalizedModel === '') {
            return ['inline_snake', 'system_snake', 'inline_camel'];
        }

        if (str_contains($normalizedModel, '1.5')) {
            return ['inline_snake', 'system_snake', 'inline_camel'];
        }

        if (str_contains($normalizedModel, '2.0') || str_contains($normalizedModel, '2.5')) {
            return ['inline_snake', 'system_snake', 'inline_camel'];
        }

        if (str_contains($normalizedModel, 'preview') || str_contains($normalizedModel, 'experimental')) {
            return ['inline_snake', 'inline_camel', 'system_snake'];
        }

        return ['inline_snake', 'system_snake', 'inline_camel'];
    }

    private function shouldRetryGooglePayload($response): bool
    {
        if ($response === null) {
            return false;
        }

        $body = strtolower((string) $response->body());

        return $response->status() === 400
            && (
                str_contains($body, 'unknown name')
                || str_contains($body, 'cannot find field')
                || str_contains($body, 'invalid json payload')
            );
    }

    private function shouldRetryGoogleBase($response): bool
    {
        if ($response === null) {
            return false;
        }

        $body = strtolower((string) $response->body());

        return $response->status() === 404
            || str_contains($body, 'not supported for generatecontent')
            || str_contains($body, 'model is not found');
    }

    private function listGoogleGenerateContentModels(string $apiKey, string $baseUrl): array
    {
        foreach ($this->googleRequestProfile('', $baseUrl)['api_bases'] as $base) {
            try {
                $response = Http::timeout(30)
                    ->acceptJson()
                    ->get($base . '/models?key=' . urlencode($apiKey));

                if (! $response->successful()) {
                    continue;
                }

                $models = collect((array) data_get((array) $response->json(), 'models', []))
                    ->filter(function ($model): bool {
                        $methods = (array) data_get($model, 'supportedGenerationMethods', []);
                        return in_array('generateContent', $methods, true);
                    })
                    ->map(function ($model): string {
                        $name = (string) data_get($model, 'name', '');
                        return str_starts_with($name, 'models/') ? substr($name, 7) : $name;
                    })
                    ->filter(fn (string $name): bool => $name !== '')
                    ->unique()
                    ->values()
                    ->all();

                if ($models !== []) {
                    return $models;
                }
            } catch (\Throwable) {
                // Ignore lookup errors; caller will still receive the original API error.
            }
        }

        return [];
    }

    private function extractContentByProvider(string $provider, array $responseJson): string
    {
        return match ($provider) {
            'openai', 'copilot' => (string) data_get($responseJson, 'choices.0.message.content', ''),
            'anthropic' => (string) collect((array) data_get($responseJson, 'content', []))
                ->where('type', 'text')
                ->pluck('text')
                ->implode("\n"),
            'google' => (string) collect((array) data_get($responseJson, 'candidates.0.content.parts', []))
                ->pluck('text')
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->implode("\n"),
            default => '',
        };
    }

    private function decodeJsonContent(string $content): array
    {
        $trimmed = trim($content);
        $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
        $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');
        if ($start === false || $end === false || $end <= $start) {
            throw new RuntimeException('KI-Antwort konnte nicht als JSON gelesen werden.');
        }

        $candidate = substr($trimmed, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('KI-Antwort enthält ungültiges JSON.');
        }

        return $decoded;
    }

    private function normalizeResult(array $payload): array
    {
        return [
            'title' => $this->nullableString($payload['title'] ?? null),
            'description' => $this->nullableString($payload['description'] ?? null),
            'starts_at' => $this->nullableString($payload['starts_at'] ?? null),
            'ends_at' => $this->nullableString($payload['ends_at'] ?? null),
            'registration_deadline' => $this->nullableString($payload['registration_deadline'] ?? null),
            'venue_name' => $this->nullableString($payload['venue_name'] ?? null),
            'location' => $this->nullableString($payload['location'] ?? null),
            'address_line1' => $this->nullableString($payload['address_line1'] ?? null),
            'address_line2' => $this->nullableString($payload['address_line2'] ?? null),
            'postal_code' => $this->nullableString($payload['postal_code'] ?? null),
            'city' => $this->nullableString($payload['city'] ?? null),
            'country' => $this->nullableString($payload['country'] ?? null),
            'entry_fee_cents' => is_numeric($payload['entry_fee_cents'] ?? null) ? (int) $payload['entry_fee_cents'] : null,
            'currency' => $this->nullableString($payload['currency'] ?? null),
            'max_registrations' => is_numeric($payload['max_registrations'] ?? null) ? (int) $payload['max_registrations'] : null,
            'allow_waitlist' => is_bool($payload['allow_waitlist'] ?? null) ? (bool) $payload['allow_waitlist'] : null,
            'status' => $this->nullableString($payload['status'] ?? null),
            'sport_module' => $this->nullableString($payload['sport_module'] ?? null),
            'boxing_package_key' => $this->nullableString($payload['boxing_package_key'] ?? null),
            'boxing_age_classes' => $this->stringArray($payload['boxing_age_classes'] ?? []),
            'boxing_performance_classes' => $this->stringArray($payload['boxing_performance_classes'] ?? []),
            'boxing_sexes' => $this->stringArray($payload['boxing_sexes'] ?? []),
            'info_documents' => $this->stringArray($payload['info_documents'] ?? []),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $result = trim((string) $value);
        return $result !== '' ? $result : null;
    }

    private function stringArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item): string {
            return is_scalar($item) ? trim((string) $item) : '';
        }, $value), fn (string $item): bool => $item !== ''));
    }
}
