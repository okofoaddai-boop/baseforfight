<?php

namespace App\Services\Modules;

use RuntimeException;

class BoxingSettingsStore
{
    // ----------------------------------------------------------------
    // Per-package file API (new canonical interface)
    // ----------------------------------------------------------------

    public function packagesDir(): string
    {
        return base_path('modules/boxing/settings/packages');
    }

    public function packageFilePath(string $key): string
    {
        $safe = preg_replace('/[^a-z0-9_-]+/i', '_', $key);
        return $this->packagesDir() . DIRECTORY_SEPARATOR . $safe . '.json';
    }

    public function readAllPackages(): array
    {
        $this->ensurePackagesDir();

        // Legacy migration: if old flat file exists, import and remove it
        $legacyFile = base_path('modules/boxing/settings/weight-classes-packages.json');
        if (is_file($legacyFile)) {
            $this->migrateLegacyFlatFile($legacyFile);
        }

        $packages = [];
        foreach (glob($this->packagesDir() . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file) {
            $key = basename($file, '.json');
            $data = $this->readJsonFile($file);
            if (is_array($data)) {
                $packages[$key] = $data;
            }
        }

        return $packages;
    }

    public function enrichPackagesForEventUi(array $packages): array
    {
        $result = [];

        foreach ($packages as $key => $package) {
            if (! is_array($package)) {
                continue;
            }

            $result[(string) $key] = $this->enrichPackageForEventUi($package);
        }

        return $result;
    }

    public function readPackage(string $key): array
    {
        $file = $this->packageFilePath($key);
        if (! is_file($file)) {
            return [];
        }
        return (array) $this->readJsonFile($file);
    }

    public function writePackage(string $key, array $data): void
    {
        $this->ensurePackagesDir();
        $this->writeJsonFile($this->packageFilePath($key), $data);
    }

    public function deletePackage(string $key): void
    {
        $file = $this->packageFilePath($key);
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function readActivePackage(): string
    {
        $file = $this->activePackageFilePath();
        if (! is_file($file)) {
            return '';
        }
        $data = $this->readJsonFile($file);
        return trim((string) ($data['active'] ?? ''));
    }

    public function writeActivePackage(string $key): void
    {
        $this->ensurePackagesDir();
        $this->writeJsonFile($this->activePackageFilePath(), ['active' => $key]);
    }

    // ----------------------------------------------------------------
    // Legacy section API — kept for any remaining callers
    // ----------------------------------------------------------------

    public function read(string $section): array
    {
        $file = $this->legacyFilePath($section);
        if (! is_file($file)) {
            return [];
        }
        return (array) $this->readJsonFile($file);
    }

    public function write(string $section, array $data): void
    {
        $file = $this->legacyFilePath($section);
        $dir  = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->writeJsonFile($file, $data);
    }

    // ----------------------------------------------------------------
    // Internals
    // ----------------------------------------------------------------

    private function activePackageFilePath(): string
    {
        return base_path('modules/boxing/settings/active_package.json');
    }

    private function ensurePackagesDir(): void
    {
        $dir = $this->packagesDir();
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    private function readJsonFile(string $file): array
    {
        $json = file_get_contents($file);
        if ($json === false || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException("Ungueltige JSON-Datei: {$file}");
        }
        return $decoded;
    }

    private function writeJsonFile(string $file, array $data): void
    {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($encoded)) {
            throw new RuntimeException("Konnte JSON nicht serialisieren: {$file}");
        }
        file_put_contents($file, $encoded . PHP_EOL);
    }

    private function legacyFilePath(string $section): string
    {
        return match ($section) {
            'age-classes'         => base_path('modules/boxing/settings/age-classes.json'),
            'performance-classes' => base_path('modules/boxing/settings/performance-classes.json'),
            'weight-classes'      => base_path('modules/boxing/settings/weight-classes-packages.json'),
            default               => throw new RuntimeException("Unbekannter Boxing-Settings-Bereich: {$section}"),
        };
    }

    private function migrateLegacyFlatFile(string $legacyFile): void
    {
        $data    = $this->readJsonFile($legacyFile);
        $pkgs    = (array) ($data['packages'] ?? []);
        $active  = (string) ($data['active_package'] ?? '');

        foreach ($pkgs as $key => $pkg) {
            $target = $this->packageFilePath((string) $key);
            if (! is_file($target)) {
                $this->writePackage((string) $key, (array) $pkg);
            }
        }

        if ($active !== '' && ! is_file($this->activePackageFilePath())) {
            $this->writeActivePackage($active);
        }

        // Rename old file so migration runs only once
        rename($legacyFile, $legacyFile . '.migrated');
    }

    private function enrichPackageForEventUi(array $package): array
    {
        $ageClasses = (array) ($package['age_classes'] ?? []);
        if ($ageClasses === []) {
            $package['_ui'] = ['age_groups' => []];
            return $package;
        }

        $sorted = collect($ageClasses)
            ->map(function (array $ageData, string $ageCode): array {
                return [
                    'code' => $ageCode,
                    'name' => trim((string) ($ageData['name'] ?? $ageCode)),
                    'sex' => trim((string) ($ageData['sex'] ?? '')),
                    'alter' => is_numeric($ageData['alter'] ?? null) ? (int) $ageData['alter'] : null,
                ];
            })
            ->sortBy(fn (array $row) => $row['alter'] ?? 999)
            ->values()
            ->all();

        $numericAges = collect($sorted)
            ->pluck('alter')
            ->filter(fn ($age) => is_int($age))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $lowerBound = 0;
        $rangeByAge = [];
        foreach ($numericAges as $upperBound) {
            $rangeByAge[$upperBound] = 'bis ' . $upperBound . ' Jahre';
            $lowerBound = $upperBound + 1;
        }

        $ageRanges = [];
        foreach ($sorted as $row) {
            $ageRanges[$row['code']] = $row['alter'] !== null
                ? (string) ($rangeByAge[$row['alter']] ?? ('bis ' . $row['alter'] . ' Jahre'))
                : ('ab ' . $lowerBound . ' Jahre');
        }

        $groups = [];
        foreach ($sorted as $row) {
            $range = (string) ($ageRanges[$row['code']] ?? '');

            $groupKey = 'code-' . $row['code'] . '|' . $range;
            if (($row['sex'] === 'm' || $row['sex'] === 'w') && $row['alter'] !== null) {
                $groupKey = 'age-' . $row['alter'] . '|' . $range;
            }

            if (! array_key_exists($groupKey, $groups)) {
                $groups[$groupKey] = [
                    'key' => $groupKey,
                    'base_name' => $row['name'],
                    'range' => $range,
                    'items' => [],
                ];
            }

            $groups[$groupKey]['items'][] = [
                'code' => $row['code'],
                'sex' => $row['sex'],
                'name' => $row['name'],
            ];
        }

        foreach ($groups as &$group) {
            $items = (array) ($group['items'] ?? []);
            $maleName = collect($items)
                ->first(fn (array $item) => (string) ($item['sex'] ?? '') === 'm')['name'] ?? null;
            $femaleName = collect($items)
                ->first(fn (array $item) => (string) ($item['sex'] ?? '') === 'w')['name'] ?? null;
            $fallbackName = collect($items)->first()['name'] ?? 'Altersklasse';

            $group['base_name'] = (string) ($maleName ?? $femaleName ?? $fallbackName);
        }
        unset($group);

        $package['_ui'] = [
            'age_groups' => array_values($groups),
            'age_ranges' => $ageRanges,
        ];

        return $package;
    }
}
