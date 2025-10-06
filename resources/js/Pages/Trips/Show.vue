<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import TripShowView from "@/Components/Trip/TripShowView.vue"
import TripChecklist from '@/Components/Trip/TripChecklist.vue'
import TripActivities from "@/Components/Trip/TripActivities.vue"

const props = defineProps({
    trip: Object,
    stepCount: Number,
    totalActivitiesCount: Number,
    activities: { type: Array, default: () => [] },
    days: { type: Array, default: () => [] },
})

const currentTab = ref('itineraire')
const menuOpen = ref(false)
const showEditModal = ref(false)

function toggleMenu() {
    menuOpen.value = !menuOpen.value
}

function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-pink-600 border-b-2 border-pink-600 font-semibold'
        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'
}

function getFlagEmoji(code) {
    if (!code) return ''
    return code
        .toUpperCase()
        .replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt()))
}

/** --- Formulaire édition voyage --- **/
const form = useForm({
    title: props.trip.title || '',
    description: props.trip.description || '',
    budget: props.trip.budget || '',
    image: props.trip.image || '',
    is_public: props.trip.is_public ?? true,
})

function submit() {
    form.put(route('trips.update', props.trip.id), {
        onSuccess: () => {
            showEditModal.value = false
        }
    })
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- =======================
             HERO compact + drapeau
        ======================= -->
        <section
            class="relative left-1/2 right-1/2 -mx-[50vw] w-screen
                   bg-gradient-to-r from-pink-600 via-red-500 to-orange-400 text-white
                   shadow-md overflow-visible"
        >
            <div class="max-w-screen-2xl mx-auto px-6 md:px-10 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-1 flex items-center gap-2">
                        <span>{{ trip.title }}</span>
                        <span
                            v-if="trip.destination_country_code"
                            class="text-2xl leading-none"
                        >
                            {{ getFlagEmoji(trip.destination_country_code) }}
                        </span>
                    </h1>
                    <p class="text-sm sm:text-base opacity-90">
                        {{ trip.start_date }} → {{ trip.end_date }} • {{ trip.steps.length }} étapes
                    </p>
                </div>

                <!-- Statistiques -->
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
                        <p class="text-xl font-bold">100%</p>
                        <p class="text-xs opacity-80">Terminé</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3 justify-end">
                    <Link
                        :href="route('trips.steps.create', trip.id)"
                        class="px-4 py-2 bg-white text-pink-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition"
                    >
                        ➕ Étape
                    </Link>

                    <button
                        type="button"
                        class="px-4 py-2 bg-pink-700 hover:bg-pink-800 text-white font-semibold rounded-lg shadow transition"
                    >
                        Partager
                    </button>

                    <!-- Menu 3 points -->
                    <div class="relative">
                        <button
                            @click="toggleMenu"
                            class="p-2 rounded-full hover:bg-white/20 transition"
                        >
                            ⋮
                        </button>

                        <div
                            v-if="menuOpen"
                            class="absolute right-0 mt-2 w-44 bg-white text-gray-700 rounded-lg shadow-lg overflow-hidden z-50"
                        >
                            <button
                                @click="showEditModal = true; menuOpen = false"
                                class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                            >
                                ✏️ Modifier le voyage
                            </button>
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
                </div>
            </div>
        </section>

        <!-- =======================
             Onglets principaux
        ======================= -->
        <section class="bg-white border-b border-gray-200">
            <nav class="max-w-screen-2xl mx-auto px-6 flex gap-6 overflow-x-auto scrollbar-hide">
                <button
                    @click="currentTab = 'itineraire'"
                    class="py-3 text-sm transition-colors"
                    :class="tabClass('itineraire')"
                >
                    🗺️ Itinéraire
                </button>


                <button
                    @click="currentTab = 'activities'"
                    class="py-3 text-sm transition-colors flex items-center"
                    :class="tabClass('activities')"
                >
                    🧭 Activités
                    <span
                        v-if="totalActivitiesCount"
                        class="ml-1 text-xs text-gray-400"
                    >({{ totalActivitiesCount }})</span>
                </button>

                <button
                    @click="currentTab = 'checklist'"
                    class="py-3 text-sm transition-colors flex items-center"
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
             Contenu des onglets
        ======================= -->
        <section class="max-w-screen-2xl mx-auto px-6 py-8">
            <div v-if="currentTab === 'itineraire'">
                <TripShowView
                    :steps="trip.steps"
                    @go-to-activities="currentTab = 'activities'"
                />
            </div>

            <div v-else-if="currentTab === 'activities'">
                <TripActivities :days="trip.days" :activities="activities" />
            </div>

            <div v-else-if="currentTab === 'checklist'">
                <TripChecklist
                    :trip="trip"
                    :items="trip.checklist_items ?? []"
                    width="max-w-3xl"
                    :dense="true"
                />
            </div>
        </section>

        <!-- =======================
             MODAL ÉDITION
        ======================= -->
        <transition name="fade">
            <div
                v-if="showEditModal"
                class="fixed inset-0 flex items-center justify-center bg-black/40 z-50"
            >
                <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                    <h2 class="text-xl font-semibold mb-4">Modifier le voyage</h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Titre</label>
                            <input v-model="form.title" type="text" class="input w-full" />
                            <div v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" rows="3" class="input w-full"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Budget</label>
                            <input v-model="form.budget" type="number" class="input w-full" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Image (URL)</label>
                            <input v-model="form.image" type="text" class="input w-full" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_public" id="is_public" />
                            <label for="is_public">Voyage public</label>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <Link
                                :href="route('trips.steps.index', trip.id)"
                                class="text-sm font-medium text-pink-600 hover:underline"
                            >
                                ⚙️ Gérer les étapes
                            </Link>
                            <div class="flex gap-3">
                                <button type="button" @click="showEditModal = false" class="btn-secondary">
                                    Annuler
                                </button>
                                <button type="submit" class="btn-primary" :disabled="form.processing">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.input {
    @apply rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500 focus:outline-none;
}
.btn-primary {
    @apply px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition;
}
.btn-secondary {
    @apply px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
