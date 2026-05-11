<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Modules\BoxingSettingsStore;
use App\Services\Modules\ModuleManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BoxSettingsController extends Controller
{
    private const ALLOWED_SECTIONS = ['age-classes', 'weight-classes', 'performance-classes', 'pass-keywords'];

    private const GLOBAL_PACKAGE_ACTIONS = ['create-package', 'toggle-package', 'delete-package'];

    public function __construct(
        private readonly BoxingSettingsStore $settingsStore,
        private readonly ModuleManager $moduleManager
    ) {
    }

    public function index(Request $request, string $section = 'age-classes'): View
    {
        if (! $this->moduleManager->isActive('boxing')) {
            abort(404);
        }

        $section = in_array($section, self::ALLOWED_SECTIONS, true) ? $section : 'age-classes';
        [$packages, $activePackage] = $this->readPackagesState();

        $selectedPackage = trim((string) $request->query('package', ''));
        if ($selectedPackage === '' || ! array_key_exists($selectedPackage, $packages)) {
            $selectedPackage = $activePackage;
        }
        if ($selectedPackage === '' && count($packages) > 0) {
            $selectedPackage = (string) array_key_first($packages);
        }

        $selectedData = $selectedPackage !== '' && array_key_exists($selectedPackage, $packages)
            ? (array) $packages[$selectedPackage]
            : [];

        return view('admin.boxing.settings', [
            'section' => $section,
            'packages' => $packages,
            'activePackage' => $activePackage,
            'selectedPackage' => $selectedPackage,
            'selectedData' => $selectedData,
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        if (! $this->moduleManager->isActive('boxing')) {
            abort(404);
        }

        if (! in_array($section, self::ALLOWED_SECTIONS, true)) {
            abort(404);
        }

        $action = (string) $request->input('_action', '');
        if (in_array($action, self::GLOBAL_PACKAGE_ACTIONS, true)) {
            $this->handleGlobalPackageAction($request, $action);
        } elseif ($section === 'age-classes') {
            $this->updateAgeClasses($request);
        } elseif ($section === 'performance-classes') {
            $this->updatePerformanceClasses($request);
        } elseif ($section === 'weight-classes') {
            $this->updateWeightClasses($request);
        } elseif ($section === 'pass-keywords') {
            $this->updatePassKeywords($request);
        }

        $redirectPackage = trim((string) $request->input('package_key', ''));
        if ($redirectPackage === '') {
            $redirectPackage = strtolower(trim((string) $request->input('new_package_key', '')));
        }

        $routeParams = ['section' => $section];
        if ($redirectPackage !== '') {
            $routeParams['package'] = $redirectPackage;
        }

        return redirect()
            ->route('admin.boxing.settings.index', $routeParams)
            ->with('status', 'Box-Settings gespeichert.');
    }

    private function updateAgeClasses(Request $request): void
    {
        $request->validate([
            'package_key' => ['required', 'string'],
            'class_code'  => ['array'],
            'class_name'  => ['array'],
            'class_sex'   => ['array'],
            'class_alter' => ['array'],
            'rounds_A'    => ['array'],
            'rounds_B'    => ['array'],
            'rounds_C'    => ['array'],
            'time_A'      => ['array'],
            'time_B'      => ['array'],
            'time_C'      => ['array'],
            'break_A'     => ['array'],
            'break_B'     => ['array'],
            'break_C'     => ['array'],
        ]);

        [$packages, $activePackage] = $this->readPackagesState();
        $packageKey = trim((string) $request->input('package_key'));
        if (! array_key_exists($packageKey, $packages)) {
            abort(404);
        }

        $package            = (array) $packages[$packageKey];
        $existingAgeClasses = (array) ($package['age_classes'] ?? []);

        $codes   = (array) $request->input('class_code', []);
        $names   = (array) $request->input('class_name', []);
        $sexes   = (array) $request->input('class_sex', []);
        $alters  = (array) $request->input('class_alter', []);
        $roundsA = (array) $request->input('rounds_A', []);
        $roundsB = (array) $request->input('rounds_B', []);
        $roundsC = (array) $request->input('rounds_C', []);
        $timeA   = (array) $request->input('time_A', []);
        $timeB   = (array) $request->input('time_B', []);
        $timeC   = (array) $request->input('time_C', []);
        $breakA  = (array) $request->input('break_A', []);
        $breakB  = (array) $request->input('break_B', []);
        $breakC  = (array) $request->input('break_C', []);

        $toInt = static function (mixed $val): ?int {
            $val = trim((string) $val);
            return $val !== '' && is_numeric($val) ? (int) $val : null;
        };

        $newAgeClasses = [];
        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            $code = trim((string) ($codes[$index] ?? ''));
            if ($name === '' || $code === '') {
                continue;
            }

            $sex   = trim((string) ($sexes[$index] ?? ''));
            $sex   = in_array($sex, ['m', 'w'], true) ? $sex : 'm';
            $alter = $toInt($alters[$index] ?? '');

            $newAgeClasses[$code] = [
                'name'    => $name,
                'alter'   => $alter,
                'sex'     => $sex,
                'time'    => ['A' => $toInt($timeA[$index] ?? ''),   'B' => $toInt($timeB[$index] ?? ''),   'C' => $toInt($timeC[$index] ?? '')],
                'break'   => ['A' => $toInt($breakA[$index] ?? ''),  'B' => $toInt($breakB[$index] ?? ''),  'C' => $toInt($breakC[$index] ?? '')],
                'rounds'  => ['A' => $toInt($roundsA[$index] ?? ''), 'B' => $toInt($roundsB[$index] ?? ''), 'C' => $toInt($roundsC[$index] ?? '')],
                'gewicht' => (array) ($existingAgeClasses[$code]['gewicht'] ?? []),
            ];
        }

        $package['age_classes'] = $newAgeClasses;
        $this->settingsStore->writePackage($packageKey, $package);
    }

    private function updatePerformanceClasses(Request $request): void
    {
        $request->validate([
            'package_key' => ['required', 'string'],
            'class_key' => ['array'],
            'class_name' => ['array'],
            'wins_min' => ['array'],
            'wins_max' => ['array'],
        ]);

        [$packages, $activePackage] = $this->readPackagesState();
        $packageKey = trim((string) $request->input('package_key'));
        if (! array_key_exists($packageKey, $packages)) {
            abort(404);
        }

        $keys = (array) $request->input('class_key', []);
        $names = (array) $request->input('class_name', []);
        $winsMin = (array) $request->input('wins_min', []);
        $winsMax = (array) $request->input('wins_max', []);

        $classes = [];
        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $min = trim((string) ($winsMin[$index] ?? ''));
            $max = trim((string) ($winsMax[$index] ?? ''));

            $classes[] = [
                'key' => trim((string) ($keys[$index] ?? '')),
                'name' => $name,
                'wins_min' => $min === '' ? null : (int) $min,
                'wins_max' => $max === '' ? null : (int) $max,
            ];
        }

        $package = (array) $packages[$packageKey];
        $package['performance_classes'] = $classes;
        $this->settingsStore->writePackage($packageKey, $package);
    }

    private function updateWeightClasses(Request $request): void
    {
        $request->validate([
            'package_key'  => ['required', 'string'],
            'age_code'     => ['array'],
            'weight_kg'    => ['array'],
            'weight_name'  => ['array'],
            'weight_short' => ['array'],
            'weight_note'  => ['array'],
        ]);

        [$packages, $activePackage] = $this->readPackagesState();
        $packageKey = trim((string) $request->input('package_key'));
        if (! array_key_exists($packageKey, $packages)) {
            abort(404);
        }

        $package    = (array) $packages[$packageKey];
        $ageClasses = (array) ($package['age_classes'] ?? []);

        // clear all gewicht — will be fully rebuilt from form
        foreach ($ageClasses as $code => $ageData) {
            $ageClasses[$code]['gewicht'] = [];
        }

        $ageCodes     = (array) $request->input('age_code', []);
        $weightKgs    = (array) $request->input('weight_kg', []);
        $weightNames  = (array) $request->input('weight_name', []);
        $weightShorts = (array) $request->input('weight_short', []);
        $weightNotes  = (array) $request->input('weight_note', []);

        foreach ($weightNames as $index => $weightName) {
            $weightName = trim((string) $weightName);
            $ageCode    = trim((string) ($ageCodes[$index] ?? ''));
            $kg         = trim((string) ($weightKgs[$index] ?? ''));

            if ($weightName === '' || $ageCode === '' || $kg === '') {
                continue;
            }

            if (! array_key_exists($ageCode, $ageClasses)) {
                continue;
            }

            $ageClasses[$ageCode]['gewicht'][$kg] = [
                'name'  => $weightName,
                'short' => trim((string) ($weightShorts[$index] ?? '')),
                'note'  => trim((string) ($weightNotes[$index] ?? '')),
            ];
        }

        $package['age_classes'] = $ageClasses;
        $this->settingsStore->writePackage($packageKey, $package);
    }

    private function updatePassKeywords(Request $request): void
    {
        $request->validate([
            'package_key' => ['required', 'string'],
            'pass_keyword' => ['nullable', 'array'],
            'pass_keyword.*' => ['nullable', 'string', 'max:120'],
        ]);

        [$packages, $activePackage] = $this->readPackagesState();
        $packageKey = trim((string) $request->input('package_key'));
        if (! array_key_exists($packageKey, $packages)) {
            abort(404);
        }

        $keywords = array_values(array_unique(array_filter(
            (array) $request->input('pass_keyword', []),
            fn ($keyword) => is_string($keyword) && trim($keyword) !== ''
        )));

        $package = (array) $packages[$packageKey];
        $package['pass_keywords'] = $keywords;
        $this->settingsStore->writePackage($packageKey, $package);
    }

    private function handleGlobalPackageAction(Request $request, string $action): void
    {
        [$packages, $activePackage] = $this->readPackagesState();

        if ($action === 'create-package') {
            $request->validate([
                'new_package_key' => ['required', 'string', 'max:80'],
                'new_package_name' => ['required', 'string', 'max:255'],
                'new_package_source' => ['nullable', 'string', 'max:255'],
            ]);

            $key = strtolower(trim((string) $request->input('new_package_key')));
            if (array_key_exists($key, $packages)) {
                abort(422, 'Paket-Key existiert bereits.');
            }

            $newPackage = [
                'name' => trim((string) $request->input('new_package_name')),
                'source' => trim((string) $request->input('new_package_source')),
                'performance_classes' => [],
                'age_classes' => [],
            ];
            $this->settingsStore->writePackage($key, $newPackage);

            if ($activePackage === '') {
                $activePackage = $key;
                $this->settingsStore->writeActivePackage($activePackage);
            }

            return;
        }

        if ($action === 'toggle-package') {
            $packageKey = trim((string) $request->input('package_key'));
            if (! array_key_exists($packageKey, $packages)) {
                abort(404);
            }

            $activePackage = $activePackage === $packageKey ? '' : $packageKey;
            $this->settingsStore->writeActivePackage($activePackage);

            return;
        }

        if ($action === 'delete-package') {
            $packageKey = trim((string) $request->input('package_key'));
            $this->settingsStore->deletePackage($packageKey);
            if ($activePackage === $packageKey) {
                $this->settingsStore->writeActivePackage('');
            }

            return;
        }
    }

    private function readPackagesState(): array
    {
        $packages     = $this->settingsStore->readAllPackages();
        $activePackage = $this->settingsStore->readActivePackage();

        if ($activePackage !== '' && ! array_key_exists($activePackage, $packages)) {
            $activePackage = '';
        }

        if ($activePackage === '' && count($packages) > 0) {
            $activePackage = (string) array_key_first($packages);
        }

        return [$packages, $activePackage];
    }

    private function writePackagesState(array $packages, string $activePackage): void
    {
        foreach ($packages as $key => $data) {
            $this->settingsStore->writePackage((string) $key, (array) $data);
        }
        $this->settingsStore->writeActivePackage($activePackage);
    }

    private function migrateLegacyDataToPackageModel(array $legacyWeightData): array
    {
        $legacyPackages = (array) ($legacyWeightData['packages'] ?? []);
        $legacyAges = (array) $this->settingsStore->read('age-classes');
        $legacyPerformance = (array) $this->settingsStore->read('performance-classes');

        $ageClasses = collect((array) ($legacyAges['classes'] ?? []))
            ->map(fn (array $age): array => [
                'key' => trim((string) ($age['key'] ?? '')),
                'name' => trim((string) ($age['name'] ?? '')),
                'age_min' => isset($age['age_min']) ? (is_numeric($age['age_min']) ? (int) $age['age_min'] : null) : null,
                'age_max' => isset($age['age_max']) ? (is_numeric($age['age_max']) ? (int) $age['age_max'] : null) : null,
                'weight_classes' => [],
            ])
            ->filter(fn (array $age): bool => $age['key'] !== '' && $age['name'] !== '')
            ->values()
            ->all();

        $performanceClasses = collect((array) ($legacyPerformance['classes'] ?? []))
            ->map(function (array $class): array {
                $rule = trim((string) ($class['rule'] ?? ''));
                [$winsMin, $winsMax] = $this->extractWinsRange($rule);

                return [
                    'key' => trim((string) ($class['key'] ?? '')),
                    'name' => trim((string) ($class['name'] ?? '')),
                    'wins_min' => $winsMin,
                    'wins_max' => $winsMax,
                ];
            })
            ->filter(fn (array $class): bool => $class['name'] !== '')
            ->values()
            ->all();

        $packages = [];
        foreach ($legacyPackages as $key => $legacyPackage) {
            $key = trim((string) $key);
            if ($key === '' || ! is_array($legacyPackage)) {
                continue;
            }

            $packageAges = $ageClasses;
            $groups = (array) ($legacyPackage['groups'] ?? []);
            foreach ($groups as $group) {
                $groupKey = trim((string) ($group['key'] ?? ''));
                $groupName = trim((string) ($group['name'] ?? ''));
                if ($groupKey === '' || $groupName === '') {
                    continue;
                }

                $weights = collect((array) ($group['classes'] ?? []))
                    ->map(fn (array $class): array => [
                        'name' => trim((string) ($class['name'] ?? '')),
                        'range' => trim((string) ($class['range'] ?? '')),
                    ])
                    ->filter(fn (array $class): bool => $class['name'] !== '')
                    ->values()
                    ->all();

                $existingIndex = collect($packageAges)->search(fn (array $age): bool => $age['key'] === $groupKey);
                if ($existingIndex !== false) {
                    $packageAges[$existingIndex]['weight_classes'] = $weights;
                    continue;
                }

                $packageAges[] = [
                    'key' => $groupKey,
                    'name' => $groupName,
                    'age_min' => null,
                    'age_max' => null,
                    'weight_classes' => $weights,
                ];
            }

            $packages[$key] = [
                'name' => trim((string) ($legacyPackage['name'] ?? $key)),
                'source' => trim((string) ($legacyPackage['source'] ?? '')),
                'performance_classes' => $performanceClasses,
                'age_classes' => array_values($packageAges),
            ];
        }

        $activePackage = (string) ($legacyWeightData['active_package'] ?? '');
        if ($activePackage === '' && count($packages) > 0) {
            $activePackage = (string) array_key_first($packages);
        }

        return [$packages, $activePackage];
    }

    private function extractWinsRange(string $rule): array
    {
        $rule = strtolower($rule);

        if (preg_match('/(\d+)\s*bis\s*(\d+)/', $rule, $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        if (preg_match('/weniger\s+als\s*(\d+)/', $rule, $matches) === 1) {
            return [null, max(0, (int) $matches[1] - 1)];
        }

        if (preg_match('/mehr\s+als\s*(\d+)/', $rule, $matches) === 1) {
            return [(int) $matches[1] + 1, null];
        }

        if (preg_match('/ab\s*(\d+)/', $rule, $matches) === 1) {
            return [(int) $matches[1], null];
        }

        return [null, null];
    }
}
