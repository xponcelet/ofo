<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import TripMap from '@/Components/Trip/TripMap.vue'

const props = defineProps({
    steps: { type: Array, default: () => [] },
    initialActiveId: [Number, String],
})

const emit = defineEmits(['update:activeStep', 'go-to-activities'])

const activeId = ref(
    props.initialActiveId ?? (props.steps.length ? props.steps[0].id : null)
)

const activeStep = computed(() =>
    props.steps.find((s) => s.id === activeId.value) || null
)

// 🔁 Étape précédente
const previousStep = computed(() => {
    if (!activeStep.value) return null
    const idx = props.steps.findIndex(s => s.id === activeStep.value.id)
    return idx > 0 ? props.steps[idx - 1] : null
})

// 🗺️ Lien Google Maps complet (toutes les étapes)
const fullItineraryUrl = computed(() => {
    const coords = props.steps
        .filter(s => s.latitude && s.longitude)
        .map(s => `${s.latitude},${s.longitude}`)

    if (coords.length < 2) return null
    return `https://www.google.com/maps/dir/${coords.join('/')}`
})

// 🟢 Informe le parent quand l’étape change
watch(activeStep, (s) => emit('update:activeStep', s))

/** 🗓️ Format dates */
function formatDates(step) {
    if (!step?.start_date || !step?.end_date) return ''
    const start = new Date(step.start_date).toLocaleDateString('fr-FR')
    const end = new Date(step.end_date).toLocaleDateString('fr-FR')
    return `du ${start} au ${end}`
}

/** 🇫🇷 Convertit le code pays (FR, IT, ES...) en emoji drapeau */
function getFlagEmoji(code) {
    if (!code) return ''
    return code
        .toUpperCase()
        .replace(/./g, char =>
            String.fromCodePoint(127397 + char.charCodeAt())
        )
}
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_2fr] gap-6 relative">
        <!-- ===========================
             Liste des étapes
        ============================ -->
        <aside class="lg:h-[calc(100vh-220px)]">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm h-full flex flex-col">
                <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-sm font-semibold tracking-wide text-gray-700 uppercase flex items-center gap-1">
                        <span class="material-symbols-rounded text-base text-gray-600">flag</span>
                        Étapes
                    </h2>
                    <span class="text-xs text-gray-500">{{ steps.length }} total</span>
                </div>

                <nav class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                    <button
                        v-for="s in steps"
                        :key="s.id"
                        @click="activeId = s.id"
                        class="w-full text-left rounded-xl px-4 py-3 transition flex items-center gap-3 border border-transparent"
                        :class="s.id === activeId
                            ? 'bg-emerald-50 border-emerald-200 ring-1 ring-emerald-300'
                            : 'hover:bg-gray-50 border-gray-100'">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border text-sm font-medium"
                            :class="s.id === activeId
                                ? 'border-emerald-400 text-emerald-700 bg-white shadow-sm'
                                : 'border-gray-300 text-gray-600 bg-gray-50'">
                            {{ s.order ?? '?' }}
                        </div>

                        <div class="min-w-0">
                            <!-- 🏷️ Titre -->
                            <p class="truncate text-sm font-semibold text-gray-900 flex items-center gap-1">
                                {{ s.title || s.location }}
                                <span v-if="s.country_code" class="ml-1">{{ getFlagEmoji(s.country_code) }}</span>
                            </p>

                            <!-- Dates -->
                            <p class="truncate text-xs text-gray-500">
                                {{ s.start_date }} → {{ s.end_date }}
                            </p>

                            <!-- Description -->
                            <p v-if="s.description" class="truncate text-xs text-gray-400 italic mt-0.5">
                                {{ s.description }}
                            </p>
                        </div>
                    </button>
                </nav>
            </div>
        </aside>

        <!-- ===========================
             Carte + Détails étape
        ============================ -->
        <section class="lg:h-[calc(100vh-220px)] flex flex-col gap-4">
            <div class="rounded-2xl border border-gray-200 overflow-hidden shadow-sm flex-1 relative">
                <TripMap
                    :steps="steps"
                    :active-id="activeId"
                    @update:activeId="activeId = $event"
                />

                <!-- 🗺️ Bouton flottant mobile -->
                <a
                    v-if="fullItineraryUrl"
                    :href="fullItineraryUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-50
                           bg-emerald-600 text-white px-6 py-3 rounded-full shadow-lg
                           font-medium text-sm flex items-center gap-2 hover:bg-emerald-700 transition"
                >
                    <span class="material-symbols-rounded text-base">travel_explore</span>
                    Voir l’itinéraire complet
                </a>
            </div>

            <!-- 🖥️ Bouton centré (desktop uniquement) -->
            <div v-if="fullItineraryUrl" class="hidden lg:flex justify-center">
                <a
                    :href="fullItineraryUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                           text-emerald-700 border border-emerald-200 hover:bg-emerald-50 transition"
                >
                    <span class="material-symbols-rounded text-base">travel_explore</span>
                    Voir l’itinéraire complet sur Google Maps
                </a>
            </div>

            <div
                v-if="activeStep"
                class="bg-white rounded-xl shadow border border-gray-200 p-6 animate-fade-in"
            >
                <h3 class="text-lg font-semibold text-gray-900 mb-1 flex items-center gap-1">
                    <span class="material-symbols-rounded text-lg text-emerald-600">location_on</span>
                    {{ activeStep.title || activeStep.location }}
                    <span v-if="activeStep.country_code" class="ml-1">
                        {{ getFlagEmoji(activeStep.country_code) }}
                    </span>
                </h3>

                <p class="text-sm text-gray-500 mb-3">
                    {{ formatDates(activeStep) }}
                </p>

                <p v-if="activeStep.description" class="text-gray-700 mb-4">
                    {{ activeStep.description }}
                </p>

                <div v-if="activeStep.distance_to_next || activeStep.duration_to_next"
                     class="flex flex-wrap gap-3 text-sm text-gray-700 mb-3">
                    <span v-if="activeStep.distance_to_next" class="flex items-center gap-1">
                        <span class="material-symbols-rounded text-gray-500 text-base">straighten</span>
                        {{ (activeStep.distance_to_next / 1000).toFixed(1) }} km
                    </span>
                    <span v-if="activeStep.duration_to_next" class="flex items-center gap-1">
                        <span class="material-symbols-rounded text-gray-500 text-base">schedule</span>
                        {{ Math.round(activeStep.duration_to_next / 3600) }} h
                    </span>
                </div>

                <!-- 🌍 Liens Google Maps -->
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <a
                        v-if="previousStep && activeStep.latitude && activeStep.longitude"
                        :href="`https://www.google.com/maps/dir/?api=1&origin=${previousStep.latitude},${previousStep.longitude}&destination=${activeStep.latitude},${activeStep.longitude}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:underline"
                    >
                        <span class="material-symbols-rounded text-base">route</span>
                        Depuis l’étape précédente
                    </a>
                    <a
                        v-if="activeStep.latitude && activeStep.longitude"
                        :href="`https://www.google.com/maps/dir/?api=1&destination=${activeStep.latitude},${activeStep.longitude}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:underline"
                    >
                        <span class="material-symbols-rounded text-base">assistant_direction</span>
                        Depuis ma position
                    </a>
                </div>

                <div v-if="activeStep.activities?.length" class="border-t pt-4 mt-4">
                    <p class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-1">
                        <span class="material-symbols-rounded text-base text-gray-600">confirmation_number</span>
                        {{ activeStep.activities.length }} activité<span v-if="activeStep.activities.length>1">s</span>
                    </p>

                    <button
                        @click="async () => {
                            emit('go-to-activities')
                            await nextTick()
                            window.scrollTo({ top: 0, behavior: 'smooth' })
                        }"
                        class="text-sm font-semibold text-emerald-700 hover:text-emerald-900 transition flex items-center gap-1"
                    >
                        <span class="material-symbols-rounded text-sm">arrow_forward</span>
                        Voir toutes les activités
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

.animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.material-symbols-rounded {
    font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24;
}
</style>
