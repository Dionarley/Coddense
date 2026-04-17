<template>
    <div class="flex h-screen bg-slate-50">
        <aside class="w-80 bg-white border-r overflow-y-auto p-4 flex flex-col">
            <div class="mb-4">
                <Link href="/" class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1 mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </Link>
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold text-slate-900 truncate flex-1">{{ repository.name }}</h1>
                    <span :class="statusClass(repoStatus)" class="text-xs uppercase font-bold px-2 py-0.5 rounded">
                        {{ statusLabel(repoStatus) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 truncate">{{ repository.remote_url }}</p>
                <div v-if="repository.languages" class="flex gap-1 mt-2 flex-wrap">
                    <button v-for="lang in repository.languages" :key="lang"
                        @click="toggleLanguage(lang)"
                        class="text-xs px-2 py-0.5 rounded transition"
                        :class="activeLanguages.includes(lang) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        {{ lang }}
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Buscar entidades..." class="w-full pl-9 pr-3 mb-3 border-slate-200 rounded-lg text-sm p-2" />
                </div>
                <input v-model="searchEntity" type="text" placeholder="Filtrar arquivo..." class="w-full border-slate-200 rounded-lg text-sm p-2" />
            </div>

            <div class="flex gap-2 mb-4 flex-wrap">
                <button v-for="type in entityTypes" :key="type"
                    @click="toggleFilter(type)"
                    :class="[
                        'px-2 py-1 text-xs rounded transition',
                        activeFilters.includes(type) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                    ]">
                    {{ type }}s
                </button>
            </div>

            <div class="text-xs text-slate-400 mb-2 flex justify-between">
                <span>{{ filteredEntities.length }} entidades</span>
                <span v-if="vulnCount > 0" class="text-red-500">⚠️ {{ vulnCount }} vuln</span>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div v-for="(entities, type) in groupedEntities" :key="type" class="mb-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center justify-between">
                        {{ type }}s
                        <span class="bg-slate-100 text-slate-500 px-1.5 rounded">{{ entities.length }}</span>
                    </h3>
                    <ul class="space-y-1">
                        <li v-for="entity in entities" :key="entity.id"
                            class="text-sm p-2 rounded cursor-pointer hover:bg-blue-50 text-slate-700 transition"
                            :class="{ 'bg-blue-100': selectedEntity?.id === entity.id }"
                            @click="selectedEntity = entity">
                            <span class="flex items-center gap-2">
                                <span class="font-medium truncate">{{ entity.name }}</span>
                                <span v-if="entity.vulnerabilities?.length" class="shrink-0 inline-block w-2 h-2 rounded-full bg-red-500" title="Possui vulnerabilidades"></span>
                            </span>
                            <span class="block text-xs text-slate-400 truncate">{{ entity.file_path }}</span>
                        </li>
                    </ul>
                </div>
                <div v-if="Object.keys(groupedEntities).length === 0" class="text-center py-8 text-slate-400">
                    <p class="text-sm">Nenhuma entidade encontrada.</p>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-10 overflow-y-auto">
            <div v-if="selectedEntity" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 max-w-4xl">
                <div class="flex items-center gap-3 mb-1">
                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs font-mono">{{ selectedEntity.type }}</span>
                    <span v-if="selectedEntity.language" class="bg-purple-100 text-purple-600 px-2 py-1 rounded text-xs font-mono">
                        {{ selectedEntity.language }}
                    </span>
                    <span v-if="selectedEntity.vulnerabilities?.length" class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs font-bold">
                        ⚠️ {{ selectedEntity.vulnerabilities.length }} vuln
                    </span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight mb-2">{{ selectedEntity.name }}</h2>
                <p class="text-slate-500 font-mono text-sm mb-2">{{ selectedEntity.file_path }}</p>
                <p v-if="selectedEntity.namespace" class="text-slate-400 font-mono text-sm mb-6">namespace {{ selectedEntity.namespace }}</p>

                <div v-if="selectedEntity.details && Object.keys(selectedEntity.details).length > 0" class="border-t pt-6 space-y-6">
                    <div v-if="selectedEntity.details.extends" class="mb-4">
                        <h4 class="text-sm font-semibold text-slate-600 mb-1">Extends</h4>
                        <p class="font-mono text-sm text-slate-700">{{ selectedEntity.details.extends }}</p>
                    </div>

                    <div v-if="selectedEntity.details.implements?.length" class="mb-4">
                        <h4 class="text-sm font-semibold text-slate-600 mb-1">Implements</h4>
                        <p class="font-mono text-sm text-slate-700">{{ selectedEntity.details.implements.join(', ') }}</p>
                    </div>

                    <div v-if="selectedEntity.details.methods?.length" class="mb-4">
                        <h4 class="text-sm font-semibold text-slate-600 mb-2">Métodos ({{ selectedEntity.details.methods.length }})</h4>
                        <div class="space-y-2">
                            <div v-for="method in selectedEntity.details.methods" :key="method.name" class="bg-slate-50 p-3 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-1.5 py-0.5 rounded" :class="visibilityClass(method.visibility)">
                                        {{ method.visibility }}
                                    </span>
                                    <span class="font-mono font-medium">{{ method.name }}()</span>
                                    <span v-if="method.returnType" class="text-slate-400 text-sm">→ {{ method.returnType }}</span>
                                </div>
                                <div v-if="method.params?.length" class="mt-2 text-xs text-slate-500">
                                    params: {{ method.params.map(p => (p.type ? p.type + ' ' : '') + '$' + p.name).join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedEntity.details.properties?.length" class="mb-4">
                        <h4 class="text-sm font-semibold text-slate-600 mb-2">Propriedades ({{ selectedEntity.details.properties.length }})</h4>
                        <div class="space-y-1">
                            <div v-for="prop in selectedEntity.details.properties" :key="prop.name" class="bg-slate-50 p-2 rounded">
                                <span class="text-xs px-1.5 py-0.5 rounded mr-2" :class="visibilityClass(prop.visibility)">
                                    {{ prop.visibility }}
                                </span>
                                <span v-if="prop.type" class="text-slate-400 text-xs mr-2">{{ prop.type }}</span>
                                <span class="font-mono text-sm">${{ prop.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="selectedEntity.vulnerabilities?.length" class="border-t pt-6">
                    <h4 class="text-sm font-semibold text-red-600 mb-2">Vulnerabilidades ({{ selectedEntity.vulnerabilities.length }})</h4>
                    <div class="space-y-2">
                        <div v-for="(vuln, idx) in selectedEntity.vulnerabilities" :key="idx" 
                            class="p-3 rounded-lg border"
                            :class="{
                                'bg-red-50 border-red-200': vuln.severity === 'CRITICAL',
                                'bg-orange-50 border-orange-200': vuln.severity === 'HIGH',
                                'bg-yellow-50 border-yellow-200': vuln.severity === 'MEDIUM',
                                'bg-slate-50 border-slate-200': vuln.severity === 'LOW'
                            }">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs px-1.5 py-0.5 rounded font-bold"
                                    :class="{
                                        'bg-red-600 text-white': vuln.severity === 'CRITICAL',
                                        'bg-orange-500 text-white': vuln.severity === 'HIGH',
                                        'bg-yellow-400 text-slate-800': vuln.severity === 'MEDIUM',
                                        'bg-slate-400 text-white': vuln.severity === 'LOW'
                                    }">
                                    {{ vuln.severity }}
                                </span>
                                <span class="text-sm font-medium text-slate-700">{{ vuln.type }}</span>
                                <span v-if="vuln.cwe_id" class="text-xs text-slate-400">CWE-{{ vuln.cwe_id }}</span>
                            </div>
                            <p v-if="vuln.line" class="text-xs text-slate-500">Linha {{ vuln.line }}</p>
                            <code v-if="vuln.code" class="block mt-1 text-xs bg-white p-1 rounded border text-slate-600 overflow-x-auto whitespace-pre-wrap">{{ vuln.code }}</code>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="h-full flex items-center justify-center text-slate-400">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="italic">Selecione uma entidade no menu lateral</p>
                    <p class="text-sm mt-1">para ver os detalhes do mapeamento.</p>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    repository: Object,
});

const selectedEntity = ref(null);
const search = ref('');
const searchEntity = ref('');
const activeFilters = ref(['class', 'interface', 'trait', 'function']);
const activeLanguages = ref([]);

const repoStatus = computed(() => props.repository.status);
const entityTypes = ['class', 'interface', 'trait', 'function'];

const toggleFilter = (type) => {
    const idx = activeFilters.value.indexOf(type);
    if (idx > -1) {
        activeFilters.value.splice(idx, 1);
    } else {
        activeFilters.value.push(type);
    }
};

const toggleLanguage = (lang) => {
    const idx = activeLanguages.value.indexOf(lang);
    if (idx > -1) {
        activeLanguages.value.splice(idx, 1);
    } else {
        activeLanguages.value.push(lang);
    }
};

const filteredEntities = computed(() => {
    return props.repository.code_entities.filter(e => {
        const matchesType = activeFilters.value.includes(e.type);
        const matchesLang = activeLanguages.value.length === 0 || activeLanguages.value.includes(e.language);
        const matchesSearch = !search.value || e.name.toLowerCase().includes(search.value.toLowerCase());
        const matchesFile = !searchEntity.value || (e.file_path && e.file_path.toLowerCase().includes(searchEntity.value.toLowerCase()));
        return matchesType && matchesLang && matchesSearch && matchesFile;
    });
});

const vulnCount = computed(() => {
    return filteredEntities.value.filter(e => e.vulnerabilities?.length).length;
});

const groupedEntities = computed(() => {
    const groups = {};
    filteredEntities.value.forEach(e => {
        if (!groups[e.type]) groups[e.type] = [];
        groups[e.type].push(e);
    });
    return groups;
});

const visibilityClass = (visibility) => ({
    'bg-green-100 text-green-700': visibility === 'public',
    'bg-yellow-100 text-yellow-700': visibility === 'protected',
    'bg-red-100 text-red-700': visibility === 'private',
});

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
