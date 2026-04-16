<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRepositoryJob;
use App\Models\Repository;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function index()
    {
        $repositories = Repository::latest()->get();

        return inertia('Dashboard', ['repositories' => $repositories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'remote_url' => 'required|string|max:500',
        ]);

        $url = $validated['remote_url'];
        $isLocalPath = ! preg_match('/^(http|https|git@)/', $url);

        if ($isLocalPath && ! is_dir($url)) {
            return redirect()->back()
                ->withErrors(['remote_url' => 'O diretório local não existe: '.$url])
                ->withInput();
        }

        $repo = Repository::create($validated);
        ProcessRepositoryJob::dispatch($repo->id);

        return redirect()->back()->with('success', 'Coddense começou a mapear o repositório!');
    }

    public function show(Repository $repository)
    {
        $repository->load('codeEntities');

        return inertia('Repository/Show', ['repository' => $repository]);
    }

    public function destroy(Repository $repository)
    {
        $repository->delete();

        return redirect()->back()->with('success', 'Repositório removido.');
    }
}
