<?php

/**
 * Dusk coverage report — what fraction of the app's UI surface the browser
 * tests actually exercise. Adapted from the same script in the pebl project.
 *
 * Computes two complementary percents:
 *
 *   1. Route coverage:    fraction of the app's GET routes a Dusk test visits.
 *   2. Selector coverage: fraction of `dusk="..."` markers in Blade that a
 *                         Dusk test references via an @selector.
 *
 * Both are surrogates for "what features are tested" — there's no line-coverage
 * analogue for Dusk in this stack. The route percent catches the coarse "this
 * whole screen has zero coverage" case; the selector percent (once you add
 * dusk="..." attributes to the controls you care about) tells you whether the
 * test actually interacted with them.
 *
 * Run via `make dusk-coverage`. No DB or browser needed — it parses the Dusk
 * test files, the Blade views, and `php artisan route:list`.
 */

declare(strict_types=1);

const VIEW_ROOT = __DIR__.'/../resources/views';
const DUSK_ROOT = __DIR__.'/../tests/Browser';
const PROJECT_ROOT = __DIR__.'/..';

main();

function main(): void
{
    [$visited, $selectorRefs] = scanDuskTests(DUSK_ROOT);
    [$selectorLiterals, $selectorPatterns] = scanBladeDuskAttrs(VIEW_ROOT);
    $routes = scanAppRoutes();

    // ---- Route coverage --------------------------------------------------
    $routeRegexes = array_map('routeUriToRegex', $routes);
    $routeHits = [];
    foreach ($routes as $i => $uri) {
        foreach ($visited as $v) {
            if (preg_match($routeRegexes[$i], $v) === 1) {
                $routeHits[$uri] = true;
                break;
            }
        }
    }
    $routeMissed = array_values(array_diff($routes, array_keys($routeHits)));
    $routePct = count($routes) === 0 ? 0 : round(100 * count($routeHits) / count($routes), 1);

    // ---- Selector coverage -----------------------------------------------
    $selectorHits = [];
    $selectorMissed = [];

    // Literals: exact-match against the @selector strings the tests use.
    foreach ($selectorLiterals as $literal) {
        if (in_array($literal, $selectorRefs, true)) {
            $selectorHits[] = $literal;
        } else {
            $selectorMissed[] = $literal;
        }
    }
    // Patterns (Blade-templated attrs like dusk="{{ $k }}-clear") match any
    // test selector that fits the pattern.
    foreach ($selectorPatterns as $pattern) {
        $matched = false;
        foreach ($selectorRefs as $ref) {
            if (preg_match($pattern['regex'], $ref) === 1) {
                $matched = true;
                break;
            }
        }
        if ($matched) {
            $selectorHits[] = $pattern['source'];
        } else {
            $selectorMissed[] = $pattern['source'];
        }
    }
    $total = count($selectorLiterals) + count($selectorPatterns);
    $selectorPct = $total === 0 ? 0 : round(100 * count($selectorHits) / $total, 1);

    // ---- Output ----------------------------------------------------------
    printf("\nDusk feature coverage\n");
    printf("───────────────────────────────────────\n");
    printf("  Routes visited     %3d / %-3d   (%5s%%)\n", count($routeHits), count($routes), formatPct($routePct));
    printf("  Dusk selectors     %3d / %-3d   (%5s%%)\n", count($selectorHits), $total, formatPct($selectorPct));
    printf("───────────────────────────────────────\n");
    printf("  Routes %%:    app GET pages with at least one Dusk ->visit().\n");
    printf("  Selectors %%: dusk=\"...\" attrs a test targets. None yet means\n");
    printf("               0/0 — add dusk=\"...\" to controls you want pinned,\n");
    printf("               then reference them in tests via the @ shorthand.\n\n");

    if ($routeMissed) {
        printf("Routes with no Dusk coverage (%d):\n", count($routeMissed));
        foreach ($routeMissed as $r) {
            printf("  - %s\n", $r);
        }
        echo "\n";
    } else {
        printf("Every app GET route is visited by a Dusk test.\n\n");
    }

    if ($selectorMissed) {
        printf("dusk=\"...\" attributes with no test reference (%d):\n", count($selectorMissed));
        foreach ($selectorMissed as $s) {
            printf("  - %s\n", $s);
        }
        echo "\n";
    }
}

/**
 * Returns [visitedUrls[], '@selectorRefs'[]] from every .php file under
 * tests/Browser/.
 */
function scanDuskTests(string $root): array
{
    $visited = [];
    $selectors = [];
    foreach (globAll($root, '*.php') as $file) {
        $src = (string) file_get_contents($file);
        if (preg_match_all("/->visit\(\s*['\"]([^'\"]+)['\"]/", $src, $m)) {
            $visited = array_merge($visited, $m[1]);
        }
        if (preg_match_all("/['\"](@[a-zA-Z0-9_\-]+)['\"]/", $src, $m)) {
            $selectors = array_merge($selectors, $m[1]);
        }
    }

    return [array_values(array_unique($visited)), array_values(array_unique($selectors))];
}

/**
 * Returns [literals[], patterns[]] of dusk="..." values across every Blade
 * file. Literals are bare strings; patterns are Blade-templated attributes
 * converted to regex (e.g. `dusk="{{ $k }}-clear"` → /^@.+-clear$/).
 */
function scanBladeDuskAttrs(string $root): array
{
    $literals = [];
    $patterns = [];
    foreach (globAll($root, '*.blade.php') as $file) {
        $src = (string) file_get_contents($file);
        if (! preg_match_all('/dusk="([^"]+)"/', $src, $m)) {
            continue;
        }
        foreach ($m[1] as $raw) {
            // Skip JS template-literal markers (`${...}`) that leak from
            // <script> blocks — they aren't real dusk attrs on the DOM.
            if (strpos($raw, '${') !== false) {
                continue;
            }
            if (strpos($raw, '{{') === false) {
                $literals[] = '@'.$raw;
            } else {
                $regexBody = preg_replace('/\{\{\s*[^}]+\s*\}\}/', '.+', $raw);
                $patterns[] = [
                    'source' => 'dusk="'.$raw.'"',
                    'regex' => '/^@'.preg_quote($regexBody, '/').'$/u',
                ];
                $patterns[count($patterns) - 1]['regex'] = str_replace('\\.\\+', '.+', $patterns[count($patterns) - 1]['regex']);
            }
        }
    }
    $literals = array_values(array_unique($literals));
    $byKey = [];
    foreach ($patterns as $p) {
        $byKey[$p['source']] = $p;
    }

    return [$literals, array_values($byKey)];
}

/**
 * GET routes handled by an App\Http\Controllers action. Framework routes
 * (health check `up`, storage, sanctum, etc.) are excluded from the
 * denominator — they aren't part of the app's UI surface.
 */
function scanAppRoutes(): array
{
    $json = shell_exec('cd '.escapeshellarg(realpath(PROJECT_ROOT)).' && php artisan route:list --json 2>/dev/null');
    if ($json === null || $json === '' || $json === false) {
        fprintf(STDERR, "warning: `php artisan route:list --json` produced no output; skipping route coverage\n");

        return [];
    }
    $rows = json_decode($json, true);
    if (! is_array($rows)) {
        fprintf(STDERR, "warning: could not parse route:list JSON; skipping route coverage\n");

        return [];
    }
    $uris = [];
    foreach ($rows as $row) {
        $methods = $row['method'] ?? '';
        $uri = $row['uri'] ?? '';
        $action = $row['action'] ?? '';
        if (strpos($methods, 'GET') === false) {
            continue;
        }
        if (strpos($action, 'App\\Http\\Controllers') !== 0) {
            continue;
        }
        // route:list strips the leading slash; restore it.
        $uris[] = '/'.ltrim($uri, '/');
    }

    return array_values(array_unique($uris));
}

function routeUriToRegex(string $uri): string
{
    // Convert /registration/update/{token} into /^\/registration\/update\/[^\/]+$/.
    $regex = preg_replace('/\{[^}]+\}/', '[^/]+', $uri);

    return '#^'.str_replace('#', '\\#', $regex).'$#';
}

function globAll(string $root, string $pattern): array
{
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (fnmatch($pattern, $f->getFilename())) {
            $out[] = $f->getPathname();
        }
    }

    return $out;
}

function formatPct(float $pct): string
{
    return number_format($pct, 1);
}
