<script setup>
import StepMapPreview from '@/Components/Step/StepMapPreview.vue'
import { ref, computed, onMounted } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({ trip: Object })

// 🇫🇷 Drapeau depuis code pays
function flagFromCode(code) {
    if (!code) return ''
    try {
        return String.fromCodePoint(...[...code.toUpperCase()].map(c => 0x1f1e6 + c.charCodeAt(0) - 65))
    } catch {
        return ''
    }
}

// Helpers
function plural(n, word) {
    return `${n} ${word}${n > 1 ? 's' : ''}`
}
function formatDistance(km) {
    if (!km) return null
    return km < 1 ? `${(km * 1000).toFixed(0)} m` : `${km.toFixed(1)} km`
}
function formatDateRange(start, end) {
    if (!start || !end) return ''
    const s = new Date(start)
    const e = new Date(end)
    const opts = { day: 'numeric', month: 'short' }
    const sTxt = s.toLocaleDateString('fr-FR', opts)
    const eTxt = e.toLocaleDateString('fr-FR', opts)
    return sTxt === eTxt ? sTxt : `${sTxt} → ${eTxt}`
}

// Notes
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
    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            step.note = { content: noteForm.content, id: step.note?.id ?? Date.now(), step_id: step.id }
            cancelEdit()
        },
    }
    step.note
        ? noteForm.put(route('step-notes.update', step.note.id), opts)
        : noteForm.post(route('step-notes.store', step.id), opts)
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

// Étapes triées
const steps = computed(() => [...props.trip.steps].sort((a, b) => (a.order ?? 0) - (b.order ?? 0)))

// Lazy loading des cartes
const visibleCards = ref(new Set())
function handleIntersection(entries) {
    entries.forEach(e => {
        if (e.isIntersecting) visibleCards.value.add(e.target.dataset.stepId)
    })
}
onMounted(() => {
    const obs = new IntersectionObserver(handleIntersection, { threshold: 0.3 })
    document.querySelectorAll('[data-step-id]').forEach(el => obs.observe(el))
})
</script>

<template>
    <div class="max-w-6xl mx-auto py-10 px-4 lg:px-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-10 flex items-center gap-2">
            🧭 Étapes du voyage
        </h2>

        <div class="relative">
            <!-- Ligne verticale statique -->
            <div class="absolute left-[1.25rem] top-0 bottom-0 w-[2px] bg-gray-200"></div>

            <!-- Étapes -->
            <div v-for="(step, i) in steps" :key="step.id" class="relative pb-16">
                <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-start" :data-step-id="step.id">
                    <!-- Numéro + dates -->
                    <div class="flex flex-col items-center w-[50px] shrink-0">
                        <div
                            class="flex items-center justify-center w-9 h-9 rounded-full font-semibold text-sm text-white shadow-md"
                            :class="[i === 0 ? 'bg-blue-600' : step.is_destination ? 'bg-pink-600' : 'bg-gray-400']"
                        >
                            {{ i + 1 }}
                        </div>
                        <div class="text-[11px] text-gray-600 text-center mt-2 leading-tight font-medium">
                            {{ formatDateRange(step.start_date, step.end_date) }}
                        </div>
                    </div>

                    <!-- Carte + contenu -->
                    <div
                        class="flex-1 bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-[2fr_1.2fr] gap-4 p-6 md:p-8">
                            <!-- Texte -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-start flex-wrap gap-3">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                            <span v-if="i === 0" class="material-symbols-rounded text-blue-600 text-lg">
                                                flight_takeoff
                                            </span>
                                            <span v-else-if="step.is_destination" class="material-symbols-rounded text-pink-600 text-lg">
                                                flag
                                            </span>
                                            {{ step.title || 'Étape sans titre' }}
                                        </h3>
                                        <p v-if="step.country" class="text-sm text-gray-600 flex items-center gap-2">
                                            <span>{{ flagFromCode(step.country_code) }}</span>
                                            {{ step.country }}
                                        </p>
                                    </div>

                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('steps.show', step.id)"
                                            class="text-pink-600 hover:text-pink-700 transition"
                                            title="Voir les activités"
                                        >
                                            <span class="material-symbols-rounded text-[20px]">travel_explore</span>
                                        </Link>

                                        <Link
                                            :href="route('steps.edit', step.id)"
                                            class="text-gray-500 hover:text-primary transition"
                                            title="Modifier l'étape"
                                        >
                                            <span class="material-symbols-rounded text-[20px]">edit</span>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Résumé -->
                                <div class="text-sm text-gray-700 flex flex-wrap gap-3 mt-2">
                                    <span v-if="step.nights">🌙 {{ plural(step.nights, 'nuit') }}</span>
                                    <span v-if="step.activities?.length">
                                        🎟️ {{ plural(step.activities.length, 'activité') }}
                                    </span>
                                </div>

                                <!-- Activités -->
                                <div v-if="step.activities?.length" class="mt-3">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-rounded text-sm text-pink-600">explore</span>
                                        Activités
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="a in step.activities.slice(0, 5)"
                                            :key="a.id"
                                            class="px-2 py-1 rounded-full text-xs font-medium bg-pink-50 text-pink-700 border border-pink-100"
                                        >
                                            {{ a.title }}
                                        </span>
                                        <span v-if="step.activities.length > 5" class="text-xs text-gray-400">
                                            +{{ step.activities.length - 5 }} autres…
                                        </span>
                                    </div>

                                    <!-- 🔗 Lien vers la page de gestion -->
                                    <div class="mt-3">
                                        <Link
                                            :href="route('steps.show', step.id)"
                                            class="inline-flex items-center gap-1 text-sm text-pink-600 hover:underline"
                                        >
                                            <span class="material-symbols-rounded text-sm">open_in_new</span>
                                            Voir les activités
                                        </Link>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mt-5 border border-dashed border-gray-300 rounded-xl p-4 bg-gray-50">
                                    <div class="flex items-start justify-between mb-2">
                                        <p class="font-medium text-gray-800 flex items-center gap-2">
                                            <span class="material-symbols-rounded text-base text-blue-600">note</span>
                                            Ma note personnelle
                                        </p>

                                        <div class="flex gap-2">
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

                                    <p
                                        v-if="editingStepId !== step.id"
                                        class="text-gray-700 whitespace-pre-line text-sm"
                                    >
                                        {{ step.note?.content || 'Aucune note pour cette étape.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Carte -->
                            <div v-if="step.latitude && step.longitude" class="h-[180px] rounded-xl overflow-hidden">
                                <transition name="fade-slide">
                                    <StepMapPreview
                                        v-if="visibleCards.has(String(step.id))"
                                        :latitude="step.latitude"
                                        :longitude="step.longitude"
                                        :zoom="8"
                                    />
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distance entre étapes -->
                <div
                    v-if="i < steps.length - 1 && step.distance_to_next"
                    class="absolute left-[75px] flex items-center gap-1 text-xs text-gray-500"
                    :style="{ top: 'calc(100% - 2rem)' }"
                >
                    <span class="material-symbols-rounded text-[16px] text-gray-400">alt_route</span>
                    {{ formatDistance(step.distance_to_next) }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.25s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
