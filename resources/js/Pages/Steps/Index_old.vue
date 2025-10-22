<script setup>
import RootLayout from "@/Layouts/RootLayout.vue"
import { Link, router, useForm } from "@inertiajs/vue3"
import MapboxAutocomplete from "@/Components/MapboxAutocomplete.vue"
import InputError from "@/Components/InputError.vue"
import { ref } from "vue"

defineOptions({ layout: RootLayout })

const props = defineProps({
    trip: Object,
    steps: Array,
    userDeparture: Object,
})

// ✅ Formulaire pour le point de départ utilisateur (trip_user)
const form = useForm({
    start_location: props.userDeparture?.start_location || "",
    latitude: props.userDeparture?.latitude || null,
    longitude: props.userDeparture?.longitude || null,
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function saveDeparture() {
    form.patch(route("trips.updateDeparture", props.trip.id), {
        preserveScroll: true,
    })
}

// ✅ Gestion drag & drop étapes
const localSteps = ref([...props.steps])
const dragSrcIndex = ref(null)
const draggingId = ref(null)
let nightsTimer = null

function handleDragStart(index, step) {
    dragSrcIndex.value = index
    draggingId.value = step.id
}

function handleDragOver(e) {
    e.preventDefault()
}

function handleDrop(index) {
    if (dragSrcIndex.value === null) return
    const moved = localSteps.value.splice(dragSrcIndex.value, 1)[0]
    localSteps.value.splice(index, 0, moved)
    dragSrcIndex.value = null
    draggingId.value = null
    updateOrder()
}

function updateOrder() {
    const orderedIds = localSteps.value.map((s) => s.id)
    router.put(route("steps.reorder", props.trip.id), { order: orderedIds }, { preserveScroll: true })
}

/** 🌙 Debounce + PATCH dédié */
function updateNights(step) {
    clearTimeout(nightsTimer)
    nightsTimer = setTimeout(() => {
        router.patch(route("steps.update.nights", step.id), { nights: step.nights }, {
            preserveScroll: true,
        })
    }, 400)
}
</script>

<template>
    <div class="space-y-8">
        <!-- 🧭 Mon point de départ -->
        <div class="bg-white rounded-xl shadow p-5 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                <span class="material-symbols-rounded text-gray-600">home_pin</span>
                Mon point de départ
            </h2>

            <MapboxAutocomplete
                v-model="form.start_location"
                @update:coords="updateCoords"
                placeholder="Ex : Bruxelles, Belgique"
            />
            <InputError :message="form.errors.start_location" class="mt-1" />

            <div class="flex justify-end mt-3">
                <button
                    type="button"
                    @click="saveDeparture"
                    class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span class="material-symbols-rounded text-base align-middle mr-1">save</span>
                    Enregistrer
                </button>
            </div>

            <p v-if="!form.start_location" class="text-sm text-gray-500 mt-2">
                Aucun point de départ défini pour l’instant.
            </p>
        </div>

        <!-- 🗺️ Liste des étapes -->
        <div v-if="localSteps.length" class="bg-white rounded-xl shadow divide-y">
            <transition-group name="fade-move" tag="div">
                <div
                    v-for="(step, i) in localSteps"
                    :key="step.id"
                    class="flex items-center justify-between px-5 py-4 transition-all duration-200 ease-in-out cursor-grab"
                    :class="{
                        'bg-emerald-50 scale-[1.01] shadow-md': draggingId === step.id,
                        'hover:bg-gray-50': draggingId !== step.id
                    }"
                    draggable="true"
                    @dragstart="handleDragStart(i, step)"
                    @dragover="handleDragOver"
                    @drop="handleDrop(i)"
                >
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-gray-400 select-none">drag_indicator</span>
                        <div>
                            <h2 class="font-medium">
                                {{ i + 1 }}. {{ step.title || step.location || 'Étape sans nom' }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                {{ step.start_date || '—' }} → {{ step.end_date || '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        <!-- 🌙 Nuits -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Nuits :</span>
                            <input
                                type="number"
                                min="0"
                                v-model.number="step.nights"
                                @input="updateNights(step)"
                                class="w-16 text-sm border-gray-300 rounded-md focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>

                        <div class="flex gap-3">
                            <Link :href="route('steps.edit', step.id)" class="text-emerald-600 hover:underline text-sm flex items-center gap-1">
                                <span class="material-symbols-rounded text-sm">edit</span> Modifier
                            </Link>
                            <Link
                                :href="route('steps.destroy', step.id)"
                                method="delete" as="button"
                                class="text-red-600 hover:underline text-sm flex items-center gap-1"
                                onclick="return confirm('Supprimer cette étape ?')"
                            >
                                <span class="material-symbols-rounded text-sm">delete</span> Supprimer
                            </Link>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>

        <p v-else class="text-gray-500 text-sm italic">
            Aucune étape enregistrée pour ce voyage.
        </p>
    </div>
</template>

<style scoped>
.fade-move-move,
.fade-move-enter-active,
.fade-move-leave-active {
    transition: all 0.25s ease;
}
.fade-move-enter-from,
.fade-move-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.material-symbols-rounded {
    font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24;
}
</style>
