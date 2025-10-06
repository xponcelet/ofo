<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                {{ isEdit ? 'Modifier une activité' : 'Nouvelle activité' }}
            </h2>

            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- 🏷️ Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Titre</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                            required
                        />
                    </div>

                    <!-- 📝 Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                        ></textarea>
                    </div>

                    <!-- 📍 Lieu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lieu</label>
                        <MapboxAutocomplete
                            v-model="form.location"
                            @update:coords="updateCoords"
                            class="mt-1"
                        />
                    </div>

                    <!-- 🔗 Lien externe -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lien externe</label>
                        <input
                            v-model="form.external_link"
                            type="url"
                            placeholder="https://..."
                            class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                        />
                    </div>

                    <!-- 📅 Date + heure -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input
                                v-model="form.date"
                                type="date"
                                class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                                required
                            />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Heure</label>
                            <input
                                v-model="form.start_at"
                                type="time"
                                class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                            />
                        </div>
                    </div>

                    <!-- 💰 Coût -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Coût</label>
                            <input
                                v-model="form.cost"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Devise</label>
                            <input
                                v-model="form.currency"
                                type="text"
                                placeholder="EUR"
                                class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                            />
                        </div>
                    </div>

                    <!-- 🗂️ Catégorie -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                        <input
                            v-model="form.category"
                            type="text"
                            placeholder="Ex: Visite, Restaurant..."
                            class="w-full mt-1 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                        />
                    </div>
                </div>

                <!-- 🔘 Boutons -->
                <div class="flex justify-end gap-3 mt-6">
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
                        {{ isEdit ? 'Enregistrer' : 'Créer' }}
                    </button>
                </div>
            </form>

            <!-- ❌ Bouton de fermeture -->
            <button
                @click="$emit('close')"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            >
                ✖
            </button>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, watch, onMounted } from "vue"
import MapboxAutocomplete from "@/Components/MapboxAutocomplete.vue"

const props = defineProps({
    show: { type: Boolean, default: false },
    stepId: { type: Number, required: false },
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
    start_at: "09:00", // 🕘 heure par défaut
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
            form.start_at = a.start_at ? a.start_at.slice(11, 16) : "09:00" // 🕘 garde 9h par défaut si vide
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
    // 👇 Transforme les données juste avant l’envoi
    form.transform((data) => {
        const start = data.start_at
            ? `${data.date} ${data.start_at}` // ex: "2025-10-06 09:00"
            : null

        return {
            ...data,
            start_at: start,
        }
    })

    if (isEdit.value) {
        form.put(route("activities.update", props.activity.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit("updated")
                emit("close")
            },
            onError: () => {
                // (optionnel) ouvrir un petit bloc d'erreurs
                console.warn('Validation errors:', form.errors)
            },
        })
    } else {
        form.post(route("steps.activities.store", props.stepId), {
            preserveScroll: true,
            onSuccess: () => {
                emit("created")
                emit("close")
                form.reset()
                form.start_at = "09:00" // remettre 9h par défaut
            },
            onError: () => {
                console.warn('Validation errors:', form.errors)
            },
        })
    }
}

</script>
