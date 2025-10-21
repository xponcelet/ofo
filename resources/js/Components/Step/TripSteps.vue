<script setup>
import TripMap from '@/Components/Trip/TripMap.vue'
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    trip: Object,
})

// 🇫🇷 Convertit un code pays (FR → 🇫🇷)
function flagFromCode(code) {
    if (!code) return ''
    const cc = code.toUpperCase()
    const base = 0x1f1e6
    return String.fromCodePoint(...[...cc].map(c => base + c.charCodeAt(0) - 65))
}

// === Notes ===
const editingStepId = ref(null)
const noteForm = useForm({ content: '' })

function openEditor(step) {
    editingStepId.value = step.id
    noteForm.content = step.note?.content ?? ''
}

function cancelEdit() {
    editingStepId.value = null
    noteForm.reset()
}

function saveOrUpdate(step) {
    if (!noteForm.content.trim()) return

    // ✅ Si la note existe → update
    if (step.note) {
        noteForm.put(route('step-notes.update', step.note.id), {
            preserveScroll: true,
            onSuccess: () => {
                step.note.content = noteForm.content
                cancelEdit()
            },
        })
    }
    // ✅ Sinon → création
    else {
        noteForm.post(route('step-notes.store', step.id), {
            preserveScroll: true,
            onSuccess: () => {
                step.note = { content: noteForm.content, id: Date.now(), step_id: step.id }
                cancelEdit()
            },
        })
    }
}

function deleteNote(step) {
    if (!step.note || !confirm('Supprimer cette note ?')) return
    router.delete(route('step-notes.destroy', step.note.id), {
        preserveScroll: true,
        onSuccess: () => {
            step.note = null
            cancelEdit()
        },
    })
}
</script>

<template>
    <div class="max-w-7xl mx-auto py-10 px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-10">
            <!-- =======================
                 📍 Liste des étapes
            ======================== -->
            <section class="space-y-8">
                <div
                    v-for="(step, i) in trip.steps"
                    :key="step.id"
                    class="bg-white rounded-2xl shadow-sm border border-outline-variant overflow-hidden transition hover:shadow-md"
                >
                    <!-- Image -->
                    <div v-if="step.photo_url" class="h-52 w-full overflow-hidden">
                        <img
                            :src="step.photo_url"
                            :alt="step.title"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Contenu -->
                    <div class="p-6 space-y-4">
                        <!-- En-tête -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm shadow"
                                >
                                    {{ step.order || i + 1 }}
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 leading-tight">
                                    {{ step.title || 'Étape sans titre' }}
                                </h3>
                            </div>
                            <div class="text-right text-xs text-on-surface-variant">
                                <p>{{ step.start_date || '—' }} → {{ step.end_date || '—' }}</p>
                                <p v-if="step.nights" class="text-gray-500">
                                    <span class="material-symbols-rounded text-sm align-middle">nights_stay</span>
                                    {{ step.nights }} nuit<span v-if="step.nights > 1">s</span>
                                </p>
                            </div>
                        </div>

                        <!-- Lieu -->
                        <p class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="material-symbols-rounded text-base text-gray-500">location_on</span>
                            {{ step.city || step.location || 'Localisation inconnue' }}
                            <span v-if="step.country_code" class="text-lg">
                                {{ flagFromCode(step.country_code) }}
                            </span>
                        </p>

                        <!-- Activités -->
                        <div v-if="step.activities?.length" class="pt-2">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                <span class="material-symbols-rounded text-sm">explore</span>
                                Activités
                                <span class="text-xs text-gray-400">({{ step.activities.length }})</span>
                            </h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <li
                                    v-for="a in [...step.activities].sort((a,b) => new Date(a.start_at) - new Date(b.start_at))"
                                    :key="a.id"
                                    class="bg-gray-50 border border-gray-200 rounded-xl p-3 hover:bg-gray-100 transition"
                                >
                                    <div class="flex justify-between items-start">
                                        <div class="font-medium text-gray-800 truncate">{{ a.title }}</div>
                                        <span v-if="a.start_at" class="text-xs text-gray-500 whitespace-nowrap">
                                            {{ new Date(a.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                        </span>
                                    </div>
                                    <p v-if="a.description" class="text-xs text-gray-600 line-clamp-2 mt-1">
                                        {{ a.description }}
                                    </p>
                                </li>
                            </ul>
                        </div>

                        <!-- Notes personnelles -->
                        <div class="mt-4 border border-dashed border-gray-300 rounded-xl p-4 bg-gray-50">
                            <div class="flex items-start justify-between mb-2">
                                <p class="font-medium text-gray-800 flex items-center gap-2">
                                    <span class="material-symbols-rounded text-base text-blue-600">note</span>
                                    Ma note personnelle
                                </p>

                                <div class="flex gap-2">
                                    <!-- Ajouter / Modifier -->
                                    <button
                                        v-if="!editingStepId || editingStepId !== step.id"
                                        @click="openEditor(step)"
                                        class="flex items-center gap-1 px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 transition"
                                    >
                                        <span class="material-symbols-rounded text-sm">
                                            {{ step.note ? 'edit' : 'add' }}
                                        </span>
                                        {{ step.note ? 'Modifier' : 'Ajouter' }}
                                    </button>

                                    <!-- Supprimer -->
                                    <button
                                        v-if="step.note"
                                        @click="deleteNote(step)"
                                        class="flex items-center gap-1 px-3 py-1 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition"
                                    >
                                        <span class="material-symbols-rounded text-sm">delete</span>
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            <!-- ✅ Mode édition -->
                            <transition name="fade-slide">
                                <div v-if="editingStepId === step.id" class="space-y-2">
                                    <textarea
                                        v-model="noteForm.content"
                                        rows="3"
                                        class="w-full rounded-lg border border-gray-300 p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        placeholder="Ajoutez votre note ici..."
                                    ></textarea>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="cancelEdit"
                                            class="flex items-center gap-1 px-3 py-1 text-sm bg-gray-200 rounded-lg hover:bg-gray-300"
                                        >
                                            <span class="material-symbols-rounded text-sm">close</span>
                                            Annuler
                                        </button>
                                        <button
                                            @click="saveOrUpdate(step)"
                                            class="flex items-center gap-1 px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                        >
                                            <span class="material-symbols-rounded text-sm">save</span>
                                            Enregistrer
                                        </button>
                                    </div>
                                </div>
                            </transition>

                            <!-- 📝 Affichage -->
                            <p
                                v-if="editingStepId !== step.id"
                                class="text-gray-700 whitespace-pre-line text-sm"
                            >
                                {{ step.note?.content || 'Aucune note pour cette étape.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =======================
                 🗺️ Carte
            ======================== -->
            <aside
                class="sticky top-24 h-[75vh] rounded-2xl overflow-hidden border border-outline-variant shadow-md bg-white"
            >
                <TripMap :steps="trip.steps" />
            </aside>
        </div>
    </div>
</template>

<style scoped>
@media (max-width: 1024px) {
    aside { position: relative; height: 400px; }
}

/* ✨ Animation fluide */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.25s ease;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(-5px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
