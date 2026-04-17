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
                <h1 class="text-lg font-bold text-slate-900">{{ repository.name }}</h1>
                <p class="text-xs text-slate-500 truncate">{{ repository.remote_url }}</p>
                <div v-if="repository.languages" class="flex gap-1 mt-2">
                    <span v-for="lang in repository.languages" :key="lang" class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                        {{ lang }}
                    </span>
                </div>
            </div>

            <input v-model="search" type="text" placeholder="Filtrar classes/funções..." class="w-full mb-4 border-slate-200 rounded-lg text-sm p-2" />

            <div class="flex gap-2 mb-4">
                <button v-for="type in entityTypes" :key="type"
                    @click="toggleFilter(type)"
                    :class="[
                        'px-2 py-1 text-xs rounded',
                        activeFilters.includes(type) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'
                    ]">
                    {{ type }}s
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div v-for="(entities, type) in groupedEntities" :key="type" class="mb-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">{{ type }}s ({{ entities.length }})</h3>
                    <ul class="space-y-1">
                        <li v-for="entity in entities" :key="entity.id"
                            class="text-sm p-2 rounded cursor-pointer hover:bg-blue-50 text-slate-700"
                            :class="{ 'bg-blue-100': selectedEntity?.id === entity.id }"
                            @click="selectedEntity = entity">
                            <span class="flex items-center gap-2">
                                <span class="font-medium">{{ entity.name }}</span>
                                <span v-if="entity.vulnerabilities?.length" class="inline-block w-2 h-2 rounded-full bg-red-500" title="Possui vulnerabilidades"></span>
                            </span>
                            <span class="block text-xs text-slate-400 truncate">{{ entity.file_path }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-10 overflow-y-auto">
            <div v-if="selectedEntity" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 max-w-4xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs font-mono">{{ selectedEntity.type }}</span>
                    <span v-if="selectedEntity.language" class="bg-purple-100 text-purple-600 px-2 py-1 rounded text-xs font-mono">
                        {{ selectedEntity.language }}
                    </span>
                    <h2 class="text-2xl font-bold tracking-tight">{{ selectedEntity.name }}</h2>
                </div>
                <p class="text-slate-500 font-mono text-sm mb-6">{{ selectedEntity.file_path }}</p>
                <p v-if="selectedEntity.namespace" class="text-slate-400 font-mono text-sm mb-6">namespace {{ selectedEntity.namespace }}</p>

                <div v-if="selectedEntity.details" class="border-t pt-6 space-y-6">
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
                                <code v-if="vuln.code" class="block mt-1 text-xs bg-white p-1 rounded border text-slate-600 overflow-x-auto">{{ vuln.code }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="h-full flex items-center justify-center text-slate-400">
                <div class="text-center">
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
const activeFilters = ref(['class', 'interface', 'trait', 'function']);

const entityTypes = ['class', 'interface', 'trait', 'function'];

const toggleFilter = (type) => {
    const idx = activeFilters.value.indexOf(type);
    if (idx > -1) {
        activeFilters.value.splice(idx, 1);
    } else {
        activeFilters.value.push(type);
    }
};

const filteredEntities = computed(() => {
    return props.repository.code_entities.filter(e =>
        activeFilters.value.includes(e.type) &&
        e.name.toLowerCase().includes(search.value.toLowerCase())
    );
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
</script>
