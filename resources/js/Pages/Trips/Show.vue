<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

import TripShowView from '@/Components/Trip/TripShowView.vue'
import TripChecklist from '@/Components/Trip/TripChecklist.vue'
import TripActivities from '@/Components/Trip/TripActivities.vue'
import ShowShareModal from '@/Components/Trip/ShowShareModal.vue'
import TripSteps from "@/Components/Step/TripSteps.vue";

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
const showShareModal = ref(false)

function toggleMenu() {
    menuOpen.value = !menuOpen.value
}

function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-primary border-b-2 border-primary font-semibold'
        : 'text-on-surface-variant hover:text-primary border-b-2 border-transparent'
}

function getFlagEmoji(code) {
    if (!code) return ''
    return code
        .toUpperCase()
        .replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt()))
}

const form = useForm({
    title: props.trip.title || '',
    description: props.trip.description || '',
    budget: props.trip.budget || '',
    image: props.trip.image || '',
    is_public: props.trip.is_public ?? true,
})

function submit() {
    form.put(route('trips.update', props.trip.id), {
        onSuccess: () => (showEditModal.value = false),
    })
}
</script>

<template>
    <div class="min-h-screen bg-white text-on-surface">
        <!-- =======================
             HEADER clair et sobre
        ======================= -->
        <section class="border-b border-outline">
            <div class="max-w-screen-2xl mx-auto px-4 md:px-6 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <!-- Titre -->
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl font-bold text-primary-dark flex items-center gap-2">
                        <span>{{ trip.title }}</span>
                        <span v-if="trip.destination_country_code" class="text-2xl leading-none">
                            {{ getFlagEmoji(trip.destination_country_code) }}
                        </span>
                    </h1>
                    <p class="text-sm text-on-surface-variant mt-1">
                        {{ trip.start_date }} → {{ trip.end_date }} • {{ trip.steps.length }} étapes
                    </p>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-white rounded-lg border border-outline px-3 py-2 shadow-sm">
                        <p class="text-lg font-semibold text-primary-dark">{{ trip.days_count || 0 }}</p>
                        <p class="text-xs text-on-surface-variant">Jours</p>
                    </div>
                    <div class="bg-white rounded-lg border border-outline px-3 py-2 shadow-sm">
                        <p class="text-lg font-semibold text-primary-dark">{{ trip.steps.length }}</p>
                        <p class="text-xs text-on-surface-variant">Étapes</p>
                    </div>
                    <div class="bg-white rounded-lg border border-outline px-3 py-2 shadow-sm">
                        <p class="text-lg">🌍</p>
                        <p class="text-xs text-on-surface-variant">Public</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3 justify-end">
                    <Link
                        :href="route('trips.steps.create', trip.id)"
                        class="px-4 py-2 bg-accent text-white font-semibold rounded-lg shadow hover:bg-primary transition"
                    >
                        ➕ Étape
                    </Link>

                    <button
                        type="button"
                        @click="showShareModal = true"
                        class="px-4 py-2 bg-primary text-white font-semibold rounded-lg shadow hover:bg-primary-dark transition"
                    >
                        🔗 Partager
                    </button>

                    <div class="relative">
                        <button
                            @click="toggleMenu"
                            class="p-2 rounded-full hover:bg-primary/10 transition"
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
             Onglets
        ======================= -->
        <section class="bg-white border-b border-outline">
            <nav class="max-w-screen-2xl mx-auto px-4 flex gap-6 overflow-x-auto scrollbar-hide">
                <button
                    @click="currentTab = 'steps'"
                    class="py-3 text-sm transition-colors flex items-center"
                    :class="tabClass('steps')"
                >
                    🧳 Étapes & Activités
                </button>
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
                        class="ml-1 text-xs text-on-surface-variant"
                    >
                        ({{ totalActivitiesCount }})
                    </span>
                </button>

                <button
                    @click="currentTab = 'checklist'"
                    class="py-3 text-sm transition-colors flex items-center"
                    :class="tabClass('checklist')"
                >
                    🧾 Checklist
                    <span
                        v-if="trip.checklist_items && trip.checklist_items.length"
                        class="ml-1 text-xs text-on-surface-variant"
                    >
                        ({{ trip.checklist_items.length }})
                    </span>
                </button>


            </nav>
        </section>

        <!-- =======================
             Contenu des onglets
        ======================= -->
        <section class="max-w-screen-2xl mx-auto px-4 py-6">
            <!-- D’abord le v-if principal -->
            <div v-if="currentTab === 'steps'">
                <TripSteps :trip="trip" :steps="trip.steps ?? []" />
            </div>

            <!-- Puis les autres -->
            <div v-else-if="currentTab === 'itineraire'">
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
                <div
                    class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative"
                >
                    <h2 class="text-xl font-semibold mb-4 text-primary-dark">
                        Modifier le voyage
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Titre</label>
                            <input v-model="form.title" type="text" class="input w-full" />
                            <div v-if="form.errors.title" class="text-sm text-red-500">
                                {{ form.errors.title }}
                            </div>
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
                                class="text-sm font-medium text-primary hover:underline"
                            >
                                ⚙️ Gérer les étapes
                            </Link>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="showEditModal = false"
                                    class="btn-secondary"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="submit"
                                    class="btn-primary"
                                    :disabled="form.processing"
                                >
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <ShowShareModal
            :show="showShareModal"
            :trip="trip"
            @close="showShareModal = false"
        />
    </div>
</template>

<style scoped>
.input {
    @apply rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none;
}
.btn-primary {
    @apply px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition;
}
.btn-secondary {
    @apply px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
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
