<script setup>
import RootLayout from "@/Layouts/RootLayout.vue"
import { Head, router } from "@inertiajs/vue3"
import { computed } from "vue"

defineOptions({ layout: RootLayout })

const props = defineProps({
    trip: Object,
    steps: Array,
})

function updateNights(step, newNights) {
    router.patch(route("steps.update", step.id), { nights: newNights }, { preserveScroll: true })
}

function addStepAfter(step) {
    const suggestedStart = step.end_date || step.start_date || ""
    const query = new URLSearchParams({
        after: step.id,
        suggested_start: suggestedStart,
    }).toString()
    router.visit(`${route("steps.create", { trip: props.trip.id })}?${query}`)
}

function addDeparture() {
    // création d’un point de départ
    router.visit(route("steps.create", { trip: props.trip.id, at_start: true }), { method: "get" })
}

const sortedSteps = computed(() => [...props.steps].sort((a, b) => (a.order ?? 0) - (b.order ?? 0)))

// 👉 vrai départ seulement si la première étape n’est PAS une destination
const hasDeparture = computed(() => sortedSteps.value.length > 0 && !sortedSteps.value[0].is_destination)
</script>

<template>
    <Head title="Étapes du voyage" />

    <div class="max-w-4xl mx-auto px-4 py-10 space-y-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Itinéraire du voyage</h1>
                <p class="text-sm text-gray-500">Gérez le départ, les étapes et la destination dans l’ordre chronologique.</p>
            </div>
            <button
                @click="router.visit(route('trips.show', props.trip.id))"
                class="text-pink-600 hover:text-pink-700 text-sm font-medium flex items-center gap-1"
            >
                <span class="material-symbols-rounded text-[18px]">arrow_back</span>
                Retour
            </button>
        </div>

        <div class="relative border-l-2 border-gray-200 ml-4">
            <!-- 🅰️ Cas 1 : aucune étape → proposer d’ajouter le départ -->
            <div v-if="!sortedSteps.length" class="pl-8 pb-10">
                <button
                    @click="addDeparture"
                    class="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition"
                >
                    <span class="material-symbols-rounded">add_circle</span>
                    Ajouter le départ
                </button>
            </div>

            <!-- 🅱️ Cas 2 : il y a des étapes mais pas de départ (la première est une destination) -->
            <template v-else-if="!hasDeparture">
                <div class="pl-8 mb-4">
                    <button
                        @click="addDeparture"
                        class="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition"
                    >
                        <span class="material-symbols-rounded">add_circle</span>
                        Ajouter le départ
                    </button>
                </div>

                <!-- Timeline normale (destination & autres étapes existantes) -->
                <div v-for="(step, i) in sortedSteps" :key="step.id" class="relative pl-8 pb-10">
                    <div
                        class="absolute -left-[9px] top-2 w-4 h-4 rounded-full border-2"
                        :class="step.is_destination ? 'bg-pink-600 border-pink-600' : 'bg-white border-gray-400'"
                    ></div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-gray-900 font-semibold">
                                    {{ step.is_destination ? 'Destination finale' : (step.title || `Étape ${i + 1}`) }}
                                </h3>
                                <span v-if="step.is_destination" class="material-symbols-rounded text-pink-600 text-sm">flag</span>
                            </div>
                            <div class="text-sm text-gray-600 flex items-center gap-1">
                                <span class="material-symbols-rounded text-base">location_on</span>
                                {{ step.location || 'Non défini' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm flex-wrap">
                            <div class="flex items-center gap-1 text-gray-500">
                                <span class="material-symbols-rounded text-base">calendar_month</span>
                                {{ step.start_date ?? '—' }}
                            </div>

                            <div class="flex items-center gap-1">
                                <span class="material-symbols-rounded text-base text-gray-400">hotel</span>
                                <input
                                    type="number"
                                    min="0"
                                    class="w-16 text-center border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500"
                                    :value="step.nights"
                                    @change="updateNights(step, $event.target.value)"
                                />
                                <span class="text-gray-500">nuit<span v-if="step.nights > 1">s</span></span>
                            </div>

                            <button
                                @click="router.visit(route('steps.edit', { step: step.id }))"
                                class="text-pink-600 hover:text-pink-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                Modifier
                            </button>
                        </div>
                    </div>

                    <!-- Ajouter une étape ici (toujours visible) -->
                    <div class="pl-8 mt-3">
                        <button
                            @click="addStepAfter(step)"
                            class="flex items-center gap-2 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition text-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">add</span>
                            Ajouter une étape ici
                        </button>
                    </div>
                </div>
            </template>

            <!-- 🅲 Cas 3 : un départ existe (première étape non-destination) -->
            <template v-else>
                <!-- Carte Départ -->
                <div class="relative pl-8 pb-10">
                    <div class="absolute -left-[9px] top-2 w-4 h-4 bg-pink-600 border-2 border-pink-600 rounded-full"></div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-rounded text-pink-600 text-lg">flight_takeoff</span>
                                <h3 class="text-gray-900 font-semibold">Départ</h3>
                            </div>
                            <p class="text-gray-600 text-sm">{{ sortedSteps[0].location || 'Lieu non défini' }}</p>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <div class="flex items-center gap-1 text-gray-500">
                                <span class="material-symbols-rounded text-base">calendar_month</span>
                                {{ sortedSteps[0].start_date ?? 'Non défini' }}
                            </div>
                            <button
                                @click="router.visit(route('steps.edit', { step: sortedSteps[0].id }))"
                                class="text-pink-600 hover:text-pink-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                Modifier
                            </button>
                        </div>
                    </div>

                    <!-- Ajouter une étape ici (après le départ) -->
                    <div class="pl-8 mt-3">
                        <button
                            @click="addStepAfter(sortedSteps[0])"
                            class="flex items-center gap-2 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition text-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">add</span>
                            Ajouter une étape ici
                        </button>
                    </div>
                </div>

                <!-- Étapes suivantes (intermédiaires + destination) -->
                <div
                    v-for="(step, i) in sortedSteps.slice(1)"
                    :key="step.id"
                    class="relative pl-8 pb-10"
                >
                    <div
                        class="absolute -left-[9px] top-2 w-4 h-4 rounded-full border-2"
                        :class="step.is_destination ? 'bg-pink-600 border-pink-600' : 'bg-white border-gray-400'"
                    ></div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-rounded text-gray-500 text-lg">location_on</span>
                                <h3 class="text-gray-900 font-semibold">
                                    {{ step.is_destination ? 'Destination finale' : (step.title || `Étape ${i + 1}`) }}
                                </h3>
                            </div>
                            <p class="text-gray-600 text-sm">{{ step.location }}</p>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <div class="flex items-center gap-1 text-gray-500">
                                <span class="material-symbols-rounded text-base">calendar_month</span>
                                {{ step.start_date ?? '—' }}
                            </div>

                            <div class="flex items-center gap-1">
                                <span class="material-symbols-rounded text-base text-gray-400">hotel</span>
                                <input
                                    type="number"
                                    min="0"
                                    class="w-16 text-center border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500"
                                    :value="step.nights"
                                    @change="updateNights(step, $event.target.value)"
                                />
                                <span class="text-gray-500">nuit<span v-if="step.nights > 1">s</span></span>
                            </div>

                            <button
                                @click="router.visit(route('steps.edit', { step: step.id }))"
                                class="text-pink-600 hover:text-pink-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                Modifier
                            </button>
                        </div>
                    </div>

                    <!-- Ajouter une étape ici -->
                    <div class="pl-8 mt-3">
                        <button
                            @click="addStepAfter(step)"
                            class="flex items-center gap-2 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition text-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">add</span>
                            Ajouter une étape ici
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
