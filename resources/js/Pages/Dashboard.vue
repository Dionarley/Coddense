<template>
    <div class="p-8 max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Coddense <span class="text-blue-500">.</span></h1>
            <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Mapear Novo Repo
            </button>
        </header>

        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="repositories.length === 0" class="text-center py-16 text-slate-500">
            <p class="text-lg">Nenhum repositório mapeado ainda.</p>
            <p class="text-sm mt-2">Clique em "Mapear Novo Repo" para começar.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="repo in repositories" :key="repo.id" class="border p-6 rounded-xl bg-white shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-slate-900">{{ repo.name }}</h2>
                        <p class="text-sm text-slate-500 truncate max-w-[200px]">{{ repo.remote_url }}</p>
                        <span v-if="isLocalPath(repo.remote_url)" class="text-xs text-blue-600">📁 Local</span>
                    </div>
                    <button @click="deleteRepo(repo.id)" class="text-slate-400 hover:text-red-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <span :class="statusClass(repo.status)" class="text-xs uppercase font-bold px-2 py-1 rounded">
                        {{ statusLabel(repo.status) }}
                    </span>
                    <Link v-if="repo.status === 'completed'" :href="`/repositories/${repo.id}`" class="text-blue-600 font-medium hover:text-blue-700">
                        Ver Mapa →
                    </Link>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
                <h2 class="text-xl font-bold mb-4">Mapear Novo Repositório</h2>
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
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    repositories: Array,
    flash: Object,
});

const showModal = ref(false);
const form = reactive({
    name: '',
    remote_url: '',
    source_type: 'git',
});

const isLocalPath = (url) => {
    return !url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('git@');
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
    if (confirm('Tem certeza que deseja remover este repositório?')) {
        router.delete(`/repositories/${id}`);
    }
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