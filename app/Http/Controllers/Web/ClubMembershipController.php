<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Services\ClubPermissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubMembershipController extends Controller
{
    private const MAX_OPEN_JOIN_REQUESTS = 3;

    private const BLOCK_SCORE_THRESHOLD = 0.88;

    private const WARN_SCORE_THRESHOLD = 0.65;

    public function __construct(private readonly ClubPermissionService $permissions)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $memberships = ClubMembership::query()
            ->where('user_id', $user->getKey())
            ->with(['club', 'roles'])
            ->orderBy('joined_at', 'desc')
            ->get();

        $openRequests = ClubJoinRequest::query()
            ->where('user_id', $user->getKey())
            ->whereIn('status', ['pending'])
            ->with('club')
            ->orderByDesc('id')
            ->get();

        $recentRequests = ClubJoinRequest::query()
            ->where('user_id', $user->getKey())
            ->whereNotIn('status', ['pending'])
            ->with('club')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $clubs = Club::query()
            ->orderBy('name')
            ->select(['id', 'name', 'slug'])
            ->get();

        return view('clubs.membership', [
            'memberships'    => $memberships,
            'openRequests'   => $openRequests,
            'recentRequests' => $recentRequests,
            'clubs'          => $clubs,
            'roleLabels'     => [
                ClubMembershipRole::ROLE_CLUB_MANAGER => __('Club-Manager'),
                ClubMembershipRole::ROLE_EVENT_MANAGER => __('Veranstaltungsmanager'),
                ClubMembershipRole::ROLE_TRAINER => __('Trainer'),
            ],
            'allRoles'       => ClubMembershipRole::ALL_ROLES,
        ]);
    }

    public function storeJoinRequest(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
        ]);

        $clubId = (int) $validated['club_id'];

        if ($user->isMemberOf($clubId)) {
            return back()->withErrors(['club_id' => __('Du bist bereits Mitglied in diesem Verein.')]);
        }

        $openCount = ClubJoinRequest::query()
            ->where('user_id', $user->getKey())
            ->where('status', 'pending')
            ->count();

        if ($openCount >= self::MAX_OPEN_JOIN_REQUESTS) {
            return back()->withErrors([
                'club_id' => __('Du hast bereits :count offene Anfragen. Bitte warte auf eine Antwort oder ziehe eine Anfrage zurück.', ['count' => self::MAX_OPEN_JOIN_REQUESTS]),
            ]);
        }

        $alreadyRequested = ClubJoinRequest::query()
            ->where('user_id', $user->getKey())
            ->where('club_id', $clubId)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyRequested) {
            return back()->withErrors(['club_id' => __('Du hast für diesen Verein bereits eine offene Anfrage.')]);
        }

        $club = Club::query()->findOrFail($clubId);

        ClubJoinRequest::query()->create([
            'club_id'              => $clubId,
            'user_id'              => $user->getKey(),
            'requested_club_name'  => (string) $club->getAttribute('name'),
            'requested_club_slug'  => (string) $club->getAttribute('slug'),
            'status'               => 'pending',
        ]);

        return back()->with('status', __('Anfrage an den Vereins-Manager gesendet.'));
    }

    public function cancelJoinRequest(Request $request, ClubJoinRequest $clubJoinRequest): RedirectResponse
    {
        if ((int) $clubJoinRequest->getAttribute('user_id') !== (int) $request->user()->getKey()) {
            abort(403);
        }

        if ($clubJoinRequest->getAttribute('status') !== 'pending') {
            return back()->withErrors(['request' => __('Diese Anfrage kann nicht mehr zurückgezogen werden.')]);
        }

        $clubJoinRequest->update(['status' => 'cancelled']);

        return back()->with('status', __('Anfrage zurückgezogen.'));
    }

    public function storeClub(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'club_name'       => ['required', 'string', 'min:2', 'max:255'],
            'confirm_new'     => ['nullable', 'boolean'],
        ]);

        $clubName = trim((string) $validated['club_name']);
        $clubSlug = Str::slug($clubName);

        if ($clubSlug === '') {
            return back()->withInput()->withErrors(['club_name' => __('Der Vereinsname ist ungültig.')]);
        }

        if (Club::query()->where('slug', $clubSlug)->exists()) {
            return back()->withInput()->withErrors([
                'club_name' => __('Ein Verein mit diesem Namen existiert bereits. Bitte beantrage den Beitritt.'),
            ]);
        }

        $likelyMatches = $this->findLikelyClubMatches($clubName, 3, self::WARN_SCORE_THRESHOLD);

        if (($likelyMatches[0]['score'] ?? 0.0) >= self::BLOCK_SCORE_THRESHOLD) {
            $suggestion = $likelyMatches[0]['club']->getAttribute('name');

            return back()->withInput()->withErrors([
                'club_name' => __('Ein sehr aehnlicher Verein wurde gefunden: ":name". Bitte beantrage den Beitritt zu diesem Verein statt einen neuen anzulegen.', ['name' => $suggestion]),
            ]);
        }

        if (count($likelyMatches) > 0 && ! (bool) ($validated['confirm_new'] ?? false)) {
            $suggestion = $likelyMatches[0]['club']->getAttribute('name');

            return back()->withInput()->with('club_duplicate_warning', [
                'name' => $suggestion,
                'input' => $clubName,
            ])->withErrors([
                'club_name' => __('Aehnlicher Verein gefunden: ":name". Wenn dein Verein wirklich neu ist, bestätige dies unten.', ['name' => $suggestion]),
            ]);
        }

        $club = Club::query()->create([
            'name'               => $clubName,
            'slug'               => $this->buildUniqueClubSlug($clubSlug),
            'created_by_user_id' => $user->getKey(),
        ]);

        $this->permissions->addMembership($user, (int) $club->getKey(), [
            ClubMembershipRole::ROLE_CLUB_MANAGER,
        ]);

        return redirect()->route('clubs.show', ['club' => $club->getAttribute('slug')])
            ->with('status', __('Verein ":name" angelegt. Du bist als Club-Manager eingetragen.', ['name' => $clubName]));
    }

    /**
     * @return array<int, array{club: Club, score: float}>
     */
    private function findLikelyClubMatches(string $clubName, int $limit = 5, float $minimumScore = 0.4): array
    {
        $needle = $this->normalizeClubName($clubName);

        if ($needle === '') {
            return [];
        }

        $clubs   = Club::query()->select(['id', 'name', 'slug'])->get();
        $matches = [];

        foreach ($clubs as $club) {
            $candidate = $this->normalizeClubName((string) $club->getAttribute('name'));

            if ($candidate === '') {
                continue;
            }

            $score = $this->calculateClubSimilarity($needle, $candidate);

            if ($score >= $minimumScore) {
                $matches[] = ['club' => $club, 'score' => $score];
            }
        }

        usort($matches, static fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    private function calculateClubSimilarity(string $needle, string $candidate): float
    {
        if ($needle === $candidate) {
            return 1.0;
        }

        similar_text($needle, $candidate, $similarityPercent);
        $similarityScore = max(0.0, min(1.0, $similarityPercent / 100));

        $maxLen        = max(strlen($needle), strlen($candidate));
        $distance      = levenshtein($needle, $candidate);
        $distanceScore = $maxLen > 0 ? max(0.0, 1 - ($distance / $maxLen)) : 0.0;
        $containsBonus = (str_contains($candidate, $needle) || str_contains($needle, $candidate)) ? 0.15 : 0.0;

        return min(1.0, (0.55 * $similarityScore) + (0.45 * $distanceScore) + $containsBonus);
    }

    private function normalizeClubName(string $clubName): string
    {
        return trim(
            Str::of($clubName)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString()
        );
    }

    private function buildUniqueClubSlug(string $baseSlug): string
    {
        $slug   = $baseSlug ?: 'club';
        $suffix = 1;

        while (Club::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }

        return $slug;
    }
}
