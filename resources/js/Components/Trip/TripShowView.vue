<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import TripMap from '@/Components/Trip/TripMap.vue'

const props = defineProps({
    steps: { type: Array, default: () => [] },
    initialActiveId: [Number, String],
})

const emit = defineEmits(['update:activeStep', 'go-to-activities'])

const activeId = ref(props.initialActiveId ?? (props.steps.length ? props.steps[0].id : null))
const activeStep = computed(() => props.steps.find((s) => s.id === activeId.value) || null)

const previousStep = computed(() => {
    if (!activeStep.value) return null
    const idx = props.steps.findIndex(s => s.id === activeStep.value.id)
    return idx > 0 ? props.steps[idx - 1] : null
})

const fullItineraryUrl = computed(() => {
    const coords = props.steps
        .filter(s => s.latitude && s.longitude)
        .map(s => `${s.latitude},${s.longitude}`)
    if (coords.length < 2) return null
    return `https://www.google.com/maps/dir/${coords.join('/')}`
})

watch(activeStep, (s) => emit('update:activeStep', s))

function formatDates(step) {
    if (!step?.start_date || !step?.end_date) return ''
    const start = new Date(step.start_date).toLocaleDateString('fr-FR')
    const end = new Date(step.end_date).toLocaleDateString('fr-FR')
    return `du ${start} au ${end}`
}

function getFlagEmoji(code) {
    if (!code) return ''
    return code.toUpperCase().replace(/./g, char =>
        String.fromCodePoint(127397 + char.charCodeAt())
    )
}
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-[340px_1fr] gap-8">
        <!-- ===========================
             Liste des étapes
        ============================ -->
        <aside class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-pink-50/50">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-1 uppercase">
                        <span class="material-symbols-rounded text-pink-600 text-base">flag</span>
                        Étapes du voyage
                    </h2>
                    <span class="text-xs text-gray-500">{{ steps.length }} au total</span>
                </div>

                <nav class="max-h-[calc(100vh-300px)] overflow-y-auto p-2 custom-scrollbar">
                    <button
                        v-for="s in steps"
                        :key="s.id"
                        @click="activeId = s.id"
                        class="w-full text-left rounded-lg px-4 py-3 mb-1 flex items-center gap-3 border transition"
                        :class="s.id === activeId
                ? 'bg-pink-50 border-pink-200 shadow-sm'
                : 'hover:bg-gray-50 border-transparent'"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium"
                            :class="s.id === activeId
                  ? 'bg-pink-100 text-pink-700'
                  : 'bg-gray-100 text-gray-600'"
                        >
                            {{ s.order ?? '?' }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900 flex items-center gap-1">
                                {{ s.title || s.location }}
                                <span v-if="s.country_code" class="ml-1">{{ getFlagEmoji(s.country_code) }}</span>
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ s.start_date }} → {{ s.end_date }}
                            </p>
                        </div>
                    </button>
                </nav>
            </div>

            <div v-if="fullItineraryUrl" class="hidden lg:block text-center">
                <a
                    :href="fullItineraryUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                 text-pink-700 border border-pink-200 hover:bg-pink-50 transition"
                >
                    <span class="material-symbols-rounded text-base">travel_explore</span>
                    Voir l’itinéraire complet
                </a>
            </div>
        </aside>

        <!-- ===========================
             Carte + détails
        ============================ -->
        <section class="space-y-8 pb-16">
            <!-- 🗺️ Carte -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm h-[320px] lg:h-[400px]">
                <TripMap
                    :steps="steps"
                    :active-id="activeId"
                    @update:activeId="activeId = $event"
                />
            </div>

            <!-- 📍 Détails de l’étape active -->
            <div v-if="activeStep" class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 animate-fade-in">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-1 mb-1">
                    <span class="material-symbols-rounded text-pink-600 text-lg">location_on</span>
                    {{ activeStep.title || activeStep.location }}
                    <span v-if="activeStep.country_code" class="ml-1">{{ getFlagEmoji(activeStep.country_code) }}</span>
                </h3>

                <p class="text-sm text-gray-500 mb-3">{{ formatDates(activeStep) }}</p>
                <p v-if="activeStep.description" class="text-gray-700 mb-4">{{ activeStep.description }}</p>

                <div v-if="activeStep.distance_to_next || activeStep.duration_to_next"
                     class="flex flex-wrap gap-3 text-sm text-gray-700 mb-4">
          <span v-if="activeStep.distance_to_next" class="flex items-center gap-1">
            <span class="material-symbols-rounded text-gray-400 text-base">straighten</span>
            {{ (activeStep.distance_to_next / 1000).toFixed(1) }} km
          </span>
                    <span v-if="activeStep.duration_to_next" class="flex items-center gap-1">
            <span class="material-symbols-rounded text-gray-400 text-base">schedule</span>
            {{ Math.round(activeStep.duration_to_next / 3600) }} h
          </span>
                </div>

                <!-- 🌍 Liens Google Maps -->
                <div class="flex flex-wrap gap-3 mb-4">
                    <a
                        v-if="previousStep && activeStep.latitude && activeStep.longitude"
                        :href="`https://www.google.com/maps/dir/?api=1&origin=${previousStep.latitude},${previousStep.longitude}&destination=${activeStep.latitude},${activeStep.longitude}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm text-pink-600 hover:underline flex items-center gap-1 font-medium"
                    >
                        <span class="material-symbols-rounded text-base">route</span>
                        Depuis l’étape précédente
                    </a>
                    <a
                        v-if="activeStep.latitude && activeStep.longitude"
                        :href="`https://www.google.com/maps/dir/?api=1&destination=${activeStep.latitude},${activeStep.longitude}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm text-pink-600 hover:underline flex items-center gap-1 font-medium"
                    >
                        <span class="material-symbols-rounded text-base">assistant_direction</span>
                        Depuis ma position
                    </a>
                </div>

                <!-- 🎟️ Activités -->
                <div v-if="activeStep.activities?.length" class="border-t pt-4 mt-4">
                    <p class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-1">
                        <span class="material-symbols-rounded text-pink-500 text-base">confirmation_number</span>
                        {{ activeStep.activities.length }} activité<span v-if="activeStep.activities.length>1">s</span>
                    </p>

                    <button
                        @click="async () => { emit('go-to-activities'); await nextTick(); window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                        class="text-sm font-semibold text-pink-600 hover:text-pink-800 flex items-center gap-1 transition"
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
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.material-symbols-rounded {
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
