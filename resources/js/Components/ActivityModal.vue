<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, watch } from "vue"
import MapboxAutocomplete from "@/Components/MapboxAutocomplete.vue"
import PoiExplorer from "@/Components/PoiExplorer.vue"

const props = defineProps({
    show: { type: Boolean, default: false },
    stepId: { type: Number, required: false },
    step: { type: Object, required: false },
    dayDate: { type: String, required: false },
    activity: { type: Object, default: null },
})

const emit = defineEmits(["close", "created", "updated"])

const isEdit = computed(() => !!props.activity)

const form = useForm({
    title: "",
    description: "",
    location: "",
    external_link: "",
    date: props.dayDate || "",
    start_at: "09:00",
    cost: "",
    currency: "EUR",
    category: "",
    step_id: props.stepId,
    latitude: null,
    longitude: null,
})

// 📍 coordonnées depuis Mapbox
function updateCoords(coords) {
    form.latitude = coords?.lat || null
    form.longitude = coords?.lng || null
}

// 🔍 Préremplir à partir d’un POI sélectionné
function prefillFromPoi(poi) {
    const lat = poi.lat || poi.center?.lat || poi.geometry?.lat
    const lon = poi.lon || poi.center?.lon || poi.geometry?.lon
    form.title = poi.name || "Lieu sans nom"
    form.location = poi.name || ""
    form.latitude = lat
    form.longitude = lon
    form.description = poi.tags?.description || ""
    form.category = poi.category || poi.tags?.amenity || poi.tags?.tourism || "Découverte"
}

// 🧩 Remplit si édition
watch(
    () => props.activity,
    (a) => {
        if (a) {
            form.title = a.title || ""
            form.description = a.description || ""
            form.location = a.location || ""
            form.external_link = a.external_link || ""
            form.date = a.date || props.dayDate || ""
            form.start_at = a.start_at ? a.start_at.slice(11, 16) : "09:00"
            form.cost = a.cost || ""
            form.currency = a.currency || "EUR"
            form.category = a.category || ""
            form.step_id = a.step_id || props.stepId
            form.latitude = a.latitude || null
            form.longitude = a.longitude || null
        }
    },
    { immediate: true }
)

// 💾 Soumission
function submit() {
    form.transform((data) => ({
        ...data,
        start_at: data.start_at ? `${data.date} ${data.start_at}` : null,
    }))

    if (isEdit.value) {
        form.put(route("activities.update", props.activity.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit("updated")
                emit("close")
            },
        })
    } else {
        form.post(route("steps.activities.store", props.stepId), {
            preserveScroll: true,
            onSuccess: () => {
                emit("created")
                emit("close")
                form.reset()
                form.start_at = "09:00"
            },
        })
    }
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
    >
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-8 relative overflow-y-auto max-h-[90vh]"
        >
            <!-- 🏷️ En-tête -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">
                    {{ isEdit ? "Modifier une activité" : "Nouvelle activité" }}
                </h2>
                <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                >
                    ✕
                </button>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 🏷️ Titre -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Titre</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                        required
                    />
                </div>

                <!-- 📝 Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                    ></textarea>
                </div>

                <!-- 📍 Lieu -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Lieu</label>
                    <MapboxAutocomplete
                        v-model="form.location"
                        @update:coords="updateCoords"
                        class="mt-1"
                    />
                </div>

                <!-- 🔍 Explorer les lieux -->
                <div class="md:col-span-2">
                    <details v-if="!isEdit" class="border rounded-lg bg-gray-50">
                        <summary
                            class="cursor-pointer px-4 py-2 font-medium text-gray-700 select-none flex items-center gap-1"
                        >
                            <span class="material-symbols-rounded text-base text-gray-500">travel_explore</span>
                            Explorer les lieux à proximité
                        </summary>
                        <div class="p-4">
                            <PoiExplorer
                                v-if="step?.latitude && step?.longitude"
                                :latitude="step.latitude"
                                :longitude="step.longitude"
                                @select-poi="prefillFromPoi"
                            />
                            <p v-else class="text-sm text-gray-500 italic">
                                Localisation indisponible pour cette étape.
                            </p>
                        </div>
                    </details>
                </div>

                <!-- 🔗 Lien externe -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Lien externe</label>
                    <input
                        v-model="form.external_link"
                        type="url"
                        placeholder="https://..."
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                    />
                </div>

                <!-- 📅 Date + Heure -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input
                        v-model="form.date"
                        type="date"
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Heure</label>
                    <input
                        v-model="form.start_at"
                        type="time"
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                    />
                </div>

                <!-- 💰 Coût + Devise -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Coût</label>
                    <input
                        v-model="form.cost"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Devise</label>
                    <input
                        v-model="form.currency"
                        type="text"
                        placeholder="EUR"
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                    />
                </div>

                <!-- 🗂️ Catégorie -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                    <input
                        v-model="form.category"
                        type="text"
                        placeholder="Ex: Visite, Restaurant..."
                        class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                    />
                </div>

                <!-- 🔘 Boutons -->
                <div class="md:col-span-2 flex justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        class="px-5 py-2 rounded-lg bg-pink-600 text-white hover:bg-pink-700 transition"
                        :disabled="form.processing"
                    >
                        {{ isEdit ? "Enregistrer" : "Créer" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
