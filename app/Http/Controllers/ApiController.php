<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRepositoryJob;
use App\Models\CodeEntity;
use App\Models\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function repositories(): JsonResponse
    {
        return response()->json(Repository::latest()->get());
    }

    public function storeRepository(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'remote_url' => 'required|string|max:500',
        ]);

        $repo = Repository::create($validated);
        ProcessRepositoryJob::dispatch($repo->id);

        return response()->json([
            'message' => 'Coddense começou a mapear!',
            'repository' => $repo,
        ], 201);
    }

    public function showRepository(Repository $repository): JsonResponse
    {
        return response()->json($repository->load('codeEntities'));
    }

    public function destroyRepository(Repository $repository): JsonResponse
    {
        $repository->delete();

        return response()->json(['message' => 'Repositório removido.']);
    }

    public function entities(Repository $repository, Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 50), 200);
        $page = (int) $request->input('page', 1);
        $type = $request->input('type');
        $language = $request->input('language');

        $query = $repository->codeEntities()
            ->select('id', 'type', 'name', 'namespace', 'file_path', 'language', 'details', 'vulnerabilities')
            ->orderBy('type')
            ->orderBy('name');

        if ($type) {
            $query->where('type', $type);
        }

        if ($language) {
            $query->where('language', $language);
        }

        $entities = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($entities);
    }

    public function vulnerabilities(Repository $repository, Request $request): JsonResponse
    {
        $severity = $request->input('severity');

        $query = $repository->codeEntities()
            ->whereNotNull('vulnerabilities')
            ->where('vulnerabilities', '!=', '[]')
            ->select('id', 'name', 'file_path', 'language', 'vulnerabilities');

        if ($severity) {
            $query->whereJsonContains('vulnerabilities', ['severity' => $severity]);
        }

        $vulnerabilities = $query->get();

        $grouped = $vulnerabilities->flatMap(function ($entity) {
            return collect($entity->vulnerabilities)->map(function ($vuln) use ($entity) {
                return [
                    'entity_id' => $entity->id,
                    'entity_name' => $entity->name,
                    'file_path' => $entity->file_path,
                    'language' => $entity->language,
                    'type' => $vuln['type'] ?? null,
                    'severity' => $vuln['severity'] ?? null,
                    'cwe_id' => $vuln['cwe_id'] ?? null,
                    'line' => $vuln['line'] ?? null,
                    'code' => $vuln['code'] ?? null,
                ];
            });
        });

        $sorted = $grouped->sortBy(function ($item) {
            $order = ['CRITICAL' => 0, 'HIGH' => 1, 'MEDIUM' => 2, 'LOW' => 3];

            return $order[$item['severity']] ?? 4;
        })->values();

        return response()->json($sorted);
    }

    public function stats(): JsonResponse
    {
        $repos = Repository::count();
        $entities = CodeEntity::count();

        $byLanguage = CodeEntity::selectRaw('language, count(*) as count')
            ->whereNotNull('language')
            ->groupBy('language')
            ->pluck('count', 'language');

        $byType = CodeEntity::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $vulnCount = CodeEntity::whereNotNull('vulnerabilities')
            ->where('vulnerabilities', '!=', '[]')
            ->count();

        return response()->json([
            'total_repositories' => $repos,
            'total_entities' => $entities,
            'entities_by_language' => $byLanguage,
            'entities_by_type' => $byType,
            'repositories_with_vulnerabilities' => $vulnCount,
        ]);
    }

    public function reprocess(Repository $repository): JsonResponse
    {
        $repository->codeEntities()->delete();
        $repository->update(['status' => 'pending']);
        ProcessRepositoryJob::dispatch($repository->id);

        return response()->json([
            'message' => 'Repositório será reprocessado!',
            'repository' => $repository,
        ]);
    }
}
