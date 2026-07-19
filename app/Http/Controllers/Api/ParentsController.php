<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The integration delta endpoint the tea-roster app syncs parent contacts from.
 *
 * Mirrors ChangesController's shape and semantics, but for households rather
 * than children:
 *  - `parents` — currently registered households (registration_status
 *    'completed') and their one or two guardians. The consumer upserts these.
 *  - `removed` — household ids that are no longer currently registered (e.g.
 *    a family who hasn't renewed this year). The consumer flags these
 *    inactive; it does NOT delete them, because the roster history attached to
 *    a family in tea-roster has to survive a family leaving.
 *
 * `last_changed_at` is the high-water mark — the newest updated_at across every
 * household, registered or not — so a departure also advances the consumer's
 * clock. Pass it back as `?since=` to fetch only what changed since; with no
 * `since`, it returns every household and the full removed set.
 *
 * ONLY the fields tea-roster needs to contact a parent are exposed: names,
 * email and phone per guardian. No children, no dates of birth, no emergency
 * contacts, no addresses, and none of the tokens on the model. Adding a field
 * here widens what a compromised consumer token can read, so keep the
 * allowlist in mapHousehold() tight and deliberate.
 */
class ParentsController extends Controller
{
    /** The columns read from the database — an explicit allowlist, never the whole model. */
    private const COLUMNS = [
        'id',
        'parent1_first_name', 'parent1_last_name', 'parent1_email', 'parent1_phone',
        'parent2_first_name', 'parent2_last_name', 'parent2_email', 'parent2_phone',
        'registration_status',
    ];

    public function index(Request $request): JsonResponse
    {
        $since = $request->query('since');

        $registered = fn () => ParentModel::query()
            ->where('registration_status', ParentModel::STATUS_COMPLETED);

        $departed = fn () => ParentModel::query()
            ->where('registration_status', '!=', ParentModel::STATUS_COMPLETED);

        // High-water mark across every household, so a family going unregistered
        // (a removal) advances the consumer's `since` just like an addition.
        $lastChangedAt = ParentModel::query()->max('updated_at');
        $count = $registered()->count();

        $parents = $registered()
            ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
            ->orderBy('id')
            ->get(self::COLUMNS)
            ->map(fn (ParentModel $household) => $this->mapHousehold($household));

        $removed = $departed()
            ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values();

        return response()->json([
            'last_changed_at' => $lastChangedAt ? (string) $lastChangedAt : null,
            'count' => $count,
            'parents' => $parents,
            'removed' => $removed,
        ]);
    }

    /**
     * One household as the consumer sees it: a stable id plus a list of
     * guardians. Guardian 2 is optional in the registration form, so a
     * household may legitimately have only one.
     *
     * @return array{registration_parent_id: string, guardians: array<int, array{first_name: string, last_name: string, email: string|null, phone: string|null}>}
     */
    private function mapHousehold(ParentModel $household): array
    {
        $guardians = [];

        foreach ([1, 2] as $n) {
            $firstName = trim((string) $household->{"parent{$n}_first_name"});
            $lastName = trim((string) $household->{"parent{$n}_last_name"});

            // A guardian with no name at all isn't a person, it's an empty
            // half of the form — skip rather than emit a blank record.
            if ($firstName === '' && $lastName === '') {
                continue;
            }

            $guardians[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $this->blankToNull($household->{"parent{$n}_email"}),
                'phone' => $this->blankToNull($household->{"parent{$n}_phone"}),
            ];
        }

        return [
            'registration_parent_id' => (string) $household->id,
            'guardians' => $guardians,
        ];
    }

    /** Normalise '' and whitespace-only values to null so consumers get one "missing" shape. */
    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
