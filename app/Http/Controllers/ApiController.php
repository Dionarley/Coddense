<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRepositoryJob;
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

    public function entities(Repository $repository): JsonResponse
    {
        $entities = $repository->codeEntities()
            ->select('id', 'type', 'name', 'namespace', 'file_path', 'details')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json($entities);
    }
}
