<script setup>
import { ref, computed } from 'vue'  // ⬅️ ajoute computed
import TripShowView from '@/Components/Trip/TripShowView.vue'
import TripChecklist from '@/Components/Trip/TripChecklist.vue'
import TripActivities from '@/Components/Trip/TripActivities.vue'

const props = defineProps({
    trip: Object,
    days: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    totalActivitiesCount: Number,
})

const currentTab = ref('itineraire')

function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-pink-600 border-b-2 border-pink-600 font-semibold'
        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'
}

function getFlagEmoji(code) {
    if (!code) return ''
    return code.toUpperCase().replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt()))
}

</script>


<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Hero du voyage -->
        <section
            class="relative left-1/2 right-1/2 -mx-[50vw] w-screen
                   bg-gradient-to-r from-pink-600 via-red-500 to-orange-400 text-white
                   shadow-md overflow-hidden"
        >
            <div class="max-w-screen-2xl mx-auto px-6 md:px-10 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-1 flex items-center gap-2">
                        <span>{{ trip.title }}</span>
                        <span v-if="trip.destination_country_code" class="text-2xl leading-none">
                            {{ getFlagEmoji(trip.destination_country_code) }}
                        </span>
                    </h1>
                    <p class="text-sm sm:text-base opacity-90">{{ trip.steps.length }} étapes</p>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-white/10 backdrop-blur rounded-lg px-3 py-2">
                        <p class="text-xl font-bold">{{ trip.days_count || 0 }}</p>
                        <p class="text-xs opacity-80">Jours</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-lg px-3 py-2">
                        <p class="text-xl font-bold">{{ trip.steps.length }}</p>
                        <p class="text-xs opacity-80">Étapes</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-lg px-3 py-2">
                        <p class="text-xl font-bold">🌍</p>
                        <p class="text-xs opacity-80">Public</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Onglets -->
        <section class="bg-white border-b border-gray-200">
            <nav class="max-w-screen-2xl mx-auto px-6 flex gap-6 overflow-x-auto scrollbar-hide">
                <button @click="currentTab = 'itineraire'" class="py-3 text-sm transition-colors" :class="tabClass('itineraire')">
                    🗺️ Itinéraire
                </button>

                <button @click="currentTab = 'activities'" class="py-3 text-sm transition-colors flex items-center" :class="tabClass('activities')">
                    🧭 Activités
                    <span v-if="totalActivitiesCount" class="ml-1 text-xs text-gray-400">
                        ({{ totalActivitiesCount }})
                    </span>
                </button>
            </nav>
        </section>

        <!-- Contenu des onglets -->
        <section class="max-w-screen-2xl mx-auto px-6 py-8">
            <div v-if="currentTab === 'itineraire'">
                <TripShowView :steps="trip.steps" />
            </div>

            <div v-else-if="currentTab === 'activities'">
                <!-- ✅ days bien passé ici -->
                <TripActivities :days="days" :activities="activities" />
            </div>
        </section>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
