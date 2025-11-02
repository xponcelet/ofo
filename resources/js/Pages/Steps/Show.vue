<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import ActivityModal from '@/Components/ActivityModal.vue'
import RootLayout from '@/Layouts/RootLayout.vue'
import StepActivitiesMap from '@/Components/Step/StepActivitiesMap.vue'

defineOptions({ layout: RootLayout })

const props = defineProps({
    step: Object,
    trip: Object,
    activities: Array,
})

/* ---------------------
   MODAL
--------------------- */
const showModal = ref(false)
const selectedActivity = ref(null)
const selectedDate = ref(null)

function openModal(activity = null, date = null) {
    selectedActivity.value = activity
    selectedDate.value = date
    showModal.value = true
}
function closeModal() {
    showModal.value = false
    selectedActivity.value = null
    selectedDate.value = null
}

/* ---------------------
   JOURS ENTRE START/END
--------------------- */
function generateDays(start, end) {
    if (!start || !end) return []
    const days = []
    const cur = new Date(start)
    const endDate = new Date(end)
    while (cur <= endDate) {
        days.push(cur.toISOString().slice(0, 10))
        cur.setDate(cur.getDate() + 1)
    }
    return days
}
const days = computed(() => generateDays(props.step.start_date, props.step.end_date))

/* ---------------------
   FORMAT DATES / HEURES
--------------------- */
function normalizeDateTime(dt) {
    if (!dt) return null
    return new Date(dt.replace(' ', 'T'))
}

function formatHour(dt) {
    if (!dt) return null
    const d = normalizeDateTime(dt)
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

function formatDayTitle(dateStr, index) {
    const options = { weekday: 'short', day: 'numeric', month: 'short' }
    return `Jour ${index + 1} – ${new Date(dateStr).toLocaleDateString('fr-FR', options)}`
}

/* ---------------------
   FILTRAGE ACTIVITÉS PAR JOUR
--------------------- */
function activitiesForDay(day) {
    const list = props.activities.filter(a => a.start_at?.slice(0, 10) === day)
    return list.sort((a, b) => {
        const getMinutes = (x) => {
            if (!x.start_at) return null
            const d = normalizeDateTime(x.start_at)
            return d.getHours() * 60 + d.getMinutes()
        }
        const aM = getMinutes(a)
        const bM = getMinutes(b)
        if (aM === null && bM === null) return 0
        if (aM === null) return -1
        if (bM === null) return 1
        return aM - bM
    })
}

/* ---------------------
   STYLE CATÉGORIE
--------------------- */
function categoryStyle(cat) {
    const map = {
        restaurant: { color: 'text-red-600', bg: 'bg-red-50', icon: 'restaurant' },
        hotel: { color: 'text-blue-600', bg: 'bg-blue-50', icon: 'hotel' },
        bar: { color: 'text-amber-600', bg: 'bg-amber-50', icon: 'local_bar' },
        museum: { color: 'text-purple-600', bg: 'bg-purple-50', icon: 'museum' },
        park: { color: 'text-green-600', bg: 'bg-green-50', icon: 'park' },
        attraction: { color: 'text-pink-600', bg: 'bg-pink-50', icon: 'star' },
        autre: { color: 'text-gray-600', bg: 'bg-gray-50', icon: 'location_on' },
    }
    return map[cat?.toLowerCase()] || map.autre
}
</script>

<template>
    <Head :title="step.title || 'Étape'" />

    <div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-[1fr_320px] gap-10">
        <!-- COLONNE PRINCIPALE -->
        <div class="space-y-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                        🧭 {{ step.title || 'Étape sans titre' }}
                    </h1>
                    <p class="text-gray-500 mt-1">
                        Voyage :
                        <span class="font-medium text-gray-700">{{ trip.title }}</span>
                    </p>
                </div>
            </div>

            <!-- Informations -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <span class="material-symbols-rounded text-pink-500">info</span>
                    Informations sur l’étape
                </h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>
                        <span class="material-symbols-rounded text-sm text-pink-400 align-middle">location_on</span>
                        {{ step.location || 'Lieu non défini' }}
                    </li>
                    <li v-if="step.distance_to_next">
                        <span class="material-symbols-rounded text-sm text-pink-400 align-middle">straighten</span>
                        {{ step.distance_to_next }} km jusqu’à la suivante
                    </li>
                </ul>
                <a
                    v-if="step.latitude && step.longitude"
                    :href="`https://www.google.com/maps/dir/?api=1&destination=${step.latitude},${step.longitude}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 text-sm text-pink-600 hover:underline font-medium mt-3"
                >
                    <span class="material-symbols-rounded text-base">assistant_direction</span>
                    Depuis ma position
                </a>
            </div>

            <!-- Activités -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-pink-500">event</span>
                    Activités de l’étape
                </h2>

                <div v-if="!days.length" class="text-sm text-gray-500 italic">
                    Aucune date définie pour cette étape.
                </div>

                <div v-else class="relative">
                    <div class="absolute left-8 top-0 bottom-0 w-[2px] bg-gray-200"></div>

                    <div v-for="(day, i) in days" :key="day" class="relative pl-14 pb-10">
                        <div
                            class="absolute left-[5px] top-1 w-5 h-5 rounded-full bg-pink-600 flex items-center justify-center text-white text-xs font-semibold shadow"
                        >
                            {{ i + 1 }}
                        </div>

                        <h3 class="font-semibold text-gray-900 mb-5">
                            {{ formatDayTitle(day, i) }}
                        </h3>

                        <!-- Activités -->
                        <div v-if="activitiesForDay(day).length" class="space-y-4">
                            <div
                                v-for="a in activitiesForDay(day)"
                                :key="a.id"
                                class="group relative ml-2 pl-4 border-l-2 border-pink-200 hover:border-pink-400 transition"
                            >
                                <div
                                    class="absolute -left-[11px] top-[10px] w-4 h-4 bg-pink-500 rounded-full border-2 border-white shadow"
                                ></div>

                                <div
                                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 ml-6 hover:shadow-md transition"
                                >
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="material-symbols-rounded text-[20px]"
                                                :class="categoryStyle(a.category).color"
                                            >
                                                {{ categoryStyle(a.category).icon }}
                                            </span>
                                            <p class="font-medium text-gray-900 flex items-center gap-1">
                                                {{ a.title }}
                                                <a
                                                    v-if="a.external_link"
                                                    :href="a.external_link"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    :class="['flex items-center', categoryStyle(a.category).color]"
                                                    title="Ouvrir le lien externe"
                                                >
                                                    <span class="material-symbols-rounded text-[18px] align-middle ml-1">open_in_new</span>
                                                </a>
                                            </p>
                                        </div>
                                        <button
                                            @click="openModal(a, day)"
                                            class="text-gray-400 hover:text-pink-600 transition"
                                            title="Modifier l’activité"
                                        >
                                            <span class="material-symbols-rounded text-[20px]">edit</span>
                                        </button>
                                    </div>

                                    <!-- Heures + catégorie -->
                                    <div class="mt-2 flex flex-wrap items-center gap-3">
                                        <span
                                            v-if="formatHour(a.start_at)"
                                            class="text-xs font-medium text-pink-700 bg-pink-50 px-2 py-0.5 rounded-lg"
                                        >
                                            🕓 {{ formatHour(a.start_at) }}
                                        </span>
                                        <span
                                            v-if="a.category"
                                            :class="['text-xs font-medium px-2 py-0.5 rounded-lg', categoryStyle(a.category).bg, categoryStyle(a.category).color]"
                                        >
                                            {{ a.category }}
                                        </span>
                                    </div>

                                    <!-- 📍 Liens Google Maps -->
                                    <div class="flex flex-wrap gap-4 mt-3">
                                        <a
                                            :href="a.latitude && a.longitude
                                                ? `https://www.google.com/maps/search/?api=1&query=${a.latitude},${a.longitude}`
                                                : `https://www.google.com/maps/search/${encodeURIComponent(a.location || a.title)}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-sm text-pink-600 hover:underline flex items-center gap-1 font-medium"
                                        >
                                            <span class="material-symbols-rounded text-base">map</span>
                                            Voir sur Google Maps
                                        </a>

                                        <a
                                            v-if="a.latitude && a.longitude"
                                            :href="`https://www.google.com/maps/dir/?api=1&destination=${a.latitude},${a.longitude}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-sm text-pink-600 hover:underline flex items-center gap-1 font-medium"
                                        >
                                            <span class="material-symbols-rounded text-base">assistant_direction</span>
                                            M’y rendre
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton ajouter -->
                        <div class="mt-5 ml-6">
                            <button
                                @click="openModal(null, day)"
                                class="flex items-center gap-1 text-sm text-gray-500 hover:text-pink-600 transition"
                            >
                                <span class="material-symbols-rounded text-[18px]">add_circle</span>
                                Ajouter une activité
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE -->
        <div class="flex flex-col gap-6">
            <a
                :href="route('trips.show', trip.id)"
                class="self-end inline-flex items-center gap-1 text-sm text-gray-600 hover:text-pink-600 transition"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                Retour au voyage
            </a>

            <StepActivitiesMap
                :step="step"
                :activities="activities"
                height="320px"
            />
        </div>
    </div>

    <!-- Modal activités -->
    <ActivityModal
        :show="showModal"
        :step-id="step.id"
        :activity="selectedActivity"
        :step="step"
        :selected-date="selectedDate"
        @close="closeModal"
        @created="router.reload({ only: ['activities'] })"
        @updated="router.reload({ only: ['activities'] })"
    />
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings: "FILL" 1, "wght" 400, "GRAD" 0, "opsz" 24;
}
</style>
