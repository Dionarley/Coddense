<template>
    <div class="p-8 max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Coddense <span class="text-blue-500">.</span></h1>
            <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Mapear Novo Repo
            </button>
        </header>

        <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl border shadow-sm hover:shadow-md transition cursor-pointer" @click="activeTab = 'repos'">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Repositórios</p>
                <p class="text-2xl font-bold text-slate-900">{{ stats.total_repositories }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border shadow-sm hover:shadow-md transition cursor-pointer" @click="activeTab = 'entities'">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Entidades</p>
                <p class="text-2xl font-bold text-slate-900">{{ stats.total_entities }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border shadow-sm hover:shadow-md transition cursor-pointer" @click="activeTab = 'languages'">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Linguagens</p>
                <p class="text-2xl font-bold text-slate-900">{{ Object.keys(stats.entities_by_language || {}).length }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border-2 shadow-sm cursor-pointer" 
                :class="(stats.vuln_count > 0 ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50') + ' hover:shadow-md transition'"
                @click="activeTab = 'vulnerabilities'">
                <p class="text-xs uppercase tracking-wide" :class="stats.vuln_count > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ stats.vuln_count > 0 ? '⚠️' : '✓' }} Vulnerabilidades
                </p>
                <p class="text-2xl font-bold" :class="stats.vuln_count > 0 ? 'text-red-700' : 'text-green-700'">{{ stats.vuln_count }}</p>
            </div>
        </div>

        <div class="flex gap-2 mb-6">
            <button v-for="tab in tabs" :key="tab.key"
                @click="activeTab = tab.key"
                class="px-3 py-1.5 text-sm rounded-lg transition"
                :class="activeTab === tab.key ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                {{ tab.label }}
            </button>
        </div>

        <div v-if="activeTab !== 'repos'" class="mb-6">
            <div v-if="activeTab === 'languages' && Object.keys(stats?.entities_by_language || {}).length > 0" class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Entidades por Linguagem</h3>
                <div class="space-y-3">
                    <div v-for="(count, lang) in stats.entities_by_language" :key="lang" class="flex items-center gap-3">
                        <span class="w-20 text-sm font-medium text-slate-600">{{ lang }}</span>
                        <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                :style="{ width: (count / stats.total_entities * 100) + '%' }"
                                :class="langColor(lang)">
                            </div>
                        </div>
                        <span class="text-sm text-slate-500 w-16 text-right">{{ count }}</span>
                    </div>
                </div>
            </div>
            <div v-else-if="activeTab === 'vulnerabilities'" class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-lg font-semibold mb-4 text-red-600">Vulnerabilidades Encontradas</h3>
                <div v-if="stats.vuln_count === 0" class="text-center py-8 text-green-600">
                    <svg class="w-12 h-12 mx-auto mb-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium">Nenhuma vulnerabilidade encontrada!</p>
                    <p class="text-sm text-slate-500 mt-1">Seus códigos estão seguros.</p>
                </div>
                <p v-else class="text-slate-500">Veja os detalhes no mapa de cada repositório.</p>
            </div>
            <div v-else-if="activeTab === 'entities'" class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Resumo das Entidades</h3>
                <p class="text-slate-500">Total: <strong>{{ stats.total_entities }}</strong> entidades mapeadas.</p>
                <p class="text-sm text-slate-400 mt-2">Clique em um repositório para ver os detalhes.</p>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-700">Repositórios</h2>
            <select v-model="sortBy" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5">
                <option value="latest">Mais recentes</option>
                <option value="name">Nome A-Z</option>
                <option value="status">Status</option>
            </select>
        </div>

        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="sortedRepositories.length === 0" class="text-center py-16 text-slate-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <p class="text-lg font-medium">Nenhum repositório mapeado ainda.</p>
            <p class="text-sm mt-2">Clique em "Mapear Novo Repo" para começar.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="repo in sortedRepositories" :key="repo.id" class="border p-6 rounded-xl bg-white shadow-sm hover:shadow-md transition group">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1 min-w-0">
                        <h2 class="font-semibold text-lg text-slate-900 truncate">{{ repo.name }}</h2>
                        <p class="text-sm text-slate-500 truncate max-w-[200px]">{{ repo.remote_url }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span v-if="isLocalPath(repo.remote_url)" class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-600">Local</span>
                            <span v-for="lang in repo.languages" :key="lang" class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-500">{{ lang }}</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button @click="toggleMenu(repo.id)" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                            </svg>
                        </button>
                        <div v-if="openMenu === repo.id" class="absolute right-0 top-8 bg-white border rounded-lg shadow-lg py-1 z-10 min-w-[140px]">
                            <button @click="reprocessRepo(repo.id)" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reprocessar
                            </button>
                            <button @click="deleteRepo(repo.id)" class="w-full px-4 py-2 text-left text-sm hover:bg-red-50 text-red-600 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span :class="statusClass(repo.status)" class="text-xs uppercase font-bold px-2 py-1 rounded">
                        {{ statusLabel(repo.status) }}
                    </span>
                    <Link v-if="repo.status === 'completed'" :href="`/repositories/${repo.id}`" class="text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1">
                        Ver Mapa
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showModal = false">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Mapear Novo Repositório</h2>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitRepo">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nome</label>
                        <input v-model="form.name" type="text" required class="w-full border-slate-200 rounded-lg text-sm" placeholder="Meu Projeto">
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex gap-4 mb-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" v-model="form.source_type" value="git" class="text-blue-600">
                                Repositório Git
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" v-model="form.source_type" value="local" class="text-blue-600">
                                Pasta Local
                            </label>
                        </div>
                        
                        <input 
                            v-model="form.remote_url" 
                            type="text" 
                            required 
                            class="w-full border-slate-200 rounded-lg text-sm"
                            :placeholder="form.source_type === 'git' ? 'https://github.com/user/repo.git' : '/home/user/projeto/src'"
                        >
                        <p v-if="form.source_type === 'local'" class="text-xs text-slate-500 mt-1">
                            Digite o caminho absoluto da pasta com arquivos PHP
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Mapear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    repositories: Array,
    flash: Object,
    stats: Object,
});

const showModal = ref(false);
const sortBy = ref('latest');
const openMenu = ref(null);
const activeTab = ref('repos');

const tabs = [
    { key: 'repos', label: 'Repositórios' },
    { key: 'languages', label: 'Linguagens' },
    { key: 'entities', label: 'Entidades' },
    { key: 'vulnerabilities', label: 'Vulnerabilidades' },
];

const form = reactive({
    name: '',
    remote_url: '',
    source_type: 'git',
});

const sortedRepositories = computed(() => {
    const repos = [...props.repositories];
    if (sortBy.value === 'latest') return repos;
    if (sortBy.value === 'name') return repos.sort((a, b) => a.name.localeCompare(b.name));
    if (sortBy.value === 'status') return repos.sort((a, b) => a.status.localeCompare(b.status));
    return repos;
});

const isLocalPath = (url) => {
    return !url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('git@');
};

const langColor = (lang) => {
    const colors = {
        'PHP': 'bg-purple-500',
        'JavaScript': 'bg-yellow-400',
        'TypeScript': 'bg-blue-500',
        'Python': 'bg-green-500',
        'Ruby': 'bg-red-500',
        'Rust': 'bg-orange-500',
        'Java': 'bg-orange-400',
        'C/C++': 'bg-blue-600',
    };
    return colors[lang] || 'bg-slate-400';
};

const toggleMenu = (id) => {
    openMenu.value = openMenu.value === id ? null : id;
};

const submitRepo = () => {
    router.post('/repositories', form, {
        onSuccess: () => {
            showModal.value = false;
            form.name = '';
            form.remote_url = '';
            form.source_type = 'git';
        },
    });
};

const deleteRepo = (id) => {
    openMenu.value = null;
    if (confirm('Tem certeza que deseja remover este repositório?')) {
        router.delete(`/repositories/${id}`);
    }
};

const reprocessRepo = (id) => {
    openMenu.value = null;
    router.post(`/api/repositories/${id}/reprocess`);
};

const statusClass = (status) => ({
    'bg-yellow-100 text-yellow-700': status === 'processing',
    'bg-green-100 text-green-700': status === 'completed',
    'bg-red-100 text-red-700': status === 'failed',
    'bg-gray-100 text-gray-700': status === 'pending',
});

const statusLabel = (status) => ({
    'pending': 'Pendente',
    'processing': 'Processando',
    'completed': 'Concluído',
    'failed': 'Falhou',
}[status] || status);
</script>