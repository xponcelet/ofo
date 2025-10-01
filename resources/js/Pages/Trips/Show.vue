<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import TripSteps from '@/Components/Step/TripSteps.vue'
import TripShowView from "@/Components/Trip/TripShowView.vue";
import TripChecklist from '@/Components/Trip/TripChecklist.vue'
import TripActivities from "@/Components/Trip/TripActivities.vue";

const props = defineProps({
    trip: Object,
    stepCount: Number,
    totalActivitiesCount: Number,
})

const currentTab = ref('resume')

// état pour ouvrir/fermer le menu
const menuOpen = ref(false)

function toggleMenu() {
    menuOpen.value = !menuOpen.value
}

function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-pink-600 border-pink-600 font-semibold'
        : 'text-gray-500 hover:text-gray-700'
}
</script>

<template>
    <div class=" max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- =======================
             Zone 2 : Hero voyage
        ======================= -->
        <section
            class="relative shadow-lg overflow-hidden bg-gradient-to-r from-pink-600 via-red-500 to-orange-400 text-white"
        >
            <div class="p-8 relative">
                <!-- Bouton menu 3 points -->
                <div class="absolute top-4 right-4">
                    <button
                        @click="toggleMenu"
                        class="p-2 rounded-full hover:bg-white/20 focus:outline-none"
                    >
                        ⋮
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-if="menuOpen"
                        class="absolute right-0 mt-2 w-40 bg-white text-gray-700 rounded-lg shadow-lg overflow-hidden z-50"
                    >
                        <Link
                            :href="route('trips.edit', trip.id)"
                            class="block px-4 py-2 hover:bg-gray-100"
                        >
                            ✏️ Éditer
                        </Link>
                        <Link
                            :href="route('trips.destroy', trip.id)"
                            method="delete"
                            as="button"
                            class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600"
                            onclick="return confirm('Supprimer ce voyage ?')"
                        >
                            🗑️ Supprimer
                        </Link>
                    </div>
                </div>

                <!-- Destination -->
                <h1 class="text-4xl font-extrabold tracking-tight mb-2">
                    {{ trip.title }}
                </h1>
                <p class="text-base opacity-90">
                    {{ trip.start_date }} → {{ trip.end_date }} •
                    {{ trip.steps.length }} étapes
                </p>

                <!-- Statistiques -->
                <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 gap-6">
                    <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                        <p class="text-3xl font-bold">{{ trip.days_count || 0 }}</p>
                        <p class="text-sm opacity-80">Jours</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                        <p class="text-3xl font-bold">{{ trip.steps.length }}</p>
                        <p class="text-sm opacity-80">Étapes</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                        <p class="text-3xl font-bold">100%</p>
                        <p class="text-sm opacity-80">Terminé</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-wrap gap-4">
                    <Link
                        :href="route('trips.steps.create', trip.id)"
                        class="px-5 py-2.5 bg-white text-pink-600 font-semibold rounded-lg shadow hover:bg-gray-100"
                    >
                        ➕ Ajouter une étape
                    </Link>
                    <button
                        type="button"
                        class="px-5 py-2.5 bg-pink-700 hover:bg-pink-800 text-white font-semibold rounded-lg shadow"
                    >
                        Partager le voyage
                    </button>
                </div>
            </div>
        </section>

        <!-- =======================
             Zone 4 : Onglets
        ======================= -->
        <section>
            <nav class="border-b border-gray-200 flex gap-6">
                <button
                    @click="currentTab = 'itineraire'"
                    class="pb-2 border-b-2 -mb-px text-sm transition-colors"
                    :class="tabClass('itineraire')"
                >
                    🗺️ Itinéraire
                </button>

                <button
                    @click="currentTab = 'infos'"
                    class="pb-2 border-b-2 -mb-px text-sm transition-colors"
                    :class="tabClass('infos')"
                >
                    ℹ️ Infos
                </button>

                <button
                    @click="currentTab = 'steps'"
                    class="pb-2 border-b-2 -mb-px text-sm transition-colors flex items-center"
                    :class="tabClass('steps')"
                >
                    📍 Étapes
                    <span
                        v-if="stepCount"
                        class="ml-1 text-xs text-gray-400"
                    >({{ stepCount }})</span
                    >
                </button>

                <button
                    @click="currentTab = 'activities'"
                    class="pb-2 border-b-2 -mb-px text-sm transition-colors flex items-center"
                    :class="tabClass('activities')"
                >
                    🧭 Activités
                    <span
                        v-if="totalActivitiesCount"
                        class="ml-1 text-xs text-gray-400"
                    >({{ totalActivitiesCount }})</span
                    >
                </button>
                <button
                    @click="currentTab = 'checklist'"
                    class="pb-2 border-b-2 -mb-px text-sm transition-colors flex items-center"
                    :class="tabClass('checklist')"
                >
                    🧾 Checklist
                    <span
                        v-if="trip.checklist_items && trip.checklist_items.length"
                        class="ml-1 text-xs text-gray-400"
                    >({{ trip.checklist_items.length }})</span>
                </button>


            </nav>
        </section>

        <!-- =======================
             Zone 3 : contenu des onglets
        ======================= -->
        <section v-if="currentTab === 'itineraire'">
            <TripShowView :steps="trip.steps" />
        </section>

        <section v-else-if="currentTab === 'infos'">
            <p class="text-gray-600">Infos générales du voyage…</p>
        </section>

        <section v-else-if="currentTab === 'steps'">
            <h2 class="text-xl font-semibold mb-6">Itinéraire détaillé</h2>
            <TripSteps :trip="trip" />
        </section>

        <section v-else-if="currentTab === 'activities'">
            <TripActivities :steps="trip.steps" />
        </section>

        <section v-else-if="currentTab === 'checklist'">
            <TripChecklist
                :trip="trip"
                :items="trip.checklist_items ?? []"
                width="max-w-3xl"
                :dense="true"
            />
        </section>

    </div>
</template>
