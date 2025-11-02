<script setup>
import { ref, watch, computed, nextTick } from "vue"
import { useForm, router } from "@inertiajs/vue3"
import PoiExplorer from "@/Components/PoiExplorer.vue"
import MapboxPoiAutocomplete from "@/Components/MapboxPoiAutocomplete.vue"

const props = defineProps({
    show: Boolean,
    stepId: Number,
    step: Object,
    activity: Object,
    selectedDate: String,
})

const emit = defineEmits(["close", "created", "updated"])

/* ---------- helpers d’état ---------- */
const initialForm = () => ({
    title: "",
    category: "autre",
    location: "",
    link: "",
    description: "",
    date: "",
    time: "",
    latitude: null,
    longitude: null,
})

const form = useForm(initialForm())
const searchQuery = ref("")          // <- champ de recherche Mapbox
const selectedPOI = ref(null)

const categories = [
    { key: "restaurant", label: "Restaurant", icon: "restaurant" },
    { key: "hotel", label: "Hôtel", icon: "hotel" },
    { key: "bar", label: "Bar", icon: "local_bar" },
    { key: "museum", label: "Musée", icon: "museum" },
    { key: "park", label: "Parc", icon: "park" },
    { key: "attraction", label: "Attraction", icon: "star" },
    { key: "autre", label: "Autre", icon: "location_on" },
]

const minDate = computed(() => props.step?.start_date ?? "")
const maxDate = computed(() => props.step?.end_date ?? "")

/* ---------- reset total du modal ---------- */
function hardReset() {
    form.reset()
    form.clearErrors()
    Object.assign(form, initialForm())
    searchQuery.value = ""       // vide l’auto-complete
    selectedPOI.value = null
}

/* ---------- ouverture / fermeture ---------- */
watch(
    () => props.show,
    async (isOpen, wasOpen) => {
        if (isOpen) {
            // on part toujours d’un état vierge
            hardReset()
            // valeurs par défaut d’ouverture
            form.category = "autre"
            form.time = "09:00"
            form.date = props.selectedDate || props.step?.start_date || ""

            // mode édition : on remplit seulement ici
            if (props.activity?.id) {
                Object.assign(form, {
                    title: props.activity.title || "",
                    category: props.activity.category || "autre",
                    location: props.activity.location || "",
                    link: props.activity.external_link || "",
                    description: props.activity.description || "",
                    date: props.activity.start_at?.slice(0, 10) || props.step?.start_date || "",
                    time: props.activity.start_at?.slice(11, 16) || "09:00",
                    latitude: props.activity.latitude ?? null,
                    longitude: props.activity.longitude ?? null,
                })
                searchQuery.value = form.location || form.title || ""
            }
            await nextTick()
        } else if (wasOpen && !isOpen) {
            // en sortie, on efface pour la prochaine ouverture
            hardReset()
        }
    }
)

/* ---------- sélection depuis POI Explorer ---------- */
function onSelectPOI(poi) {
    form.title = poi.name
    form.location = poi.address || poi.name
    form.latitude = poi.lat
    form.longitude = poi.lon
    if (poi.category) form.category = poi.category
    searchQuery.value = poi.name
}

/* ---------- sélection depuis l’autocomplete Mapbox ---------- */
function onSelectPlace(place) {
    form.title = place.name || form.title
    form.location = place.address || place.name || ""
    form.latitude = place.latitude ?? null
    form.longitude = place.longitude ?? null
    if (place.category) form.category = place.category
}

/* ---------- envoi ---------- */
function toDateTime(date, time) {
    if (!date) return null
    const hhmm = time && time.length ? time : "09:00"
    return `${date} ${hhmm}:00`
}

function closeAndReset() {
    hardReset()
    emit("close")
}

function submit() {
    const payload = {
        title: form.title || "",
        description: form.description || null,
        location: form.location || null,
        external_link: form.link || null,
        category: form.category || null,
        date: form.date || null,
        start_at: toDateTime(form.date, form.time),
        latitude: form.latitude ?? null,
        longitude: form.longitude ?? null,
    }

    if (props.activity?.id) {
        form.put(route("activities.update", props.activity.id), {
            data: payload,
            preserveScroll: true,
            onSuccess: () => {
                emit("updated")
                closeAndReset()             // <-- reset garanti
                router.reload({ only: ["activities"] })
            },
        })
    } else {
        form.post(route("steps.activities.store", props.stepId), {
            data: payload,
            preserveScroll: true,
            onSuccess: () => {
                emit("created")
                closeAndReset()             // <-- reset garanti
                router.reload({ only: ["activities"] })
            },
        })
    }
}
</script>

<template>
    <transition name="fade">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-6xl rounded-2xl shadow-xl overflow-hidden flex flex-col h-[90vh]">
                <!-- Header -->
                <div class="flex justify-between items-center border-b border-gray-100 p-5">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-pink-600">edit_calendar</span>
                        {{ props.activity?.id ? "Modifier l’activité" : "Nouvelle activité" }}
                    </h2>
                    <button @click="closeAndReset" class="text-gray-400 hover:text-gray-600 transition">
                        <span class="material-symbols-rounded text-xl">close</span>
                    </button>
                </div>

                <!-- Corps -->
                <div class="flex-1 grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] overflow-hidden">
                    <!-- Formulaire -->
                    <div class="p-6 space-y-5 overflow-y-auto">
                        <!-- Recherche lieu (Mapbox) -->
                        <div>
                            <MapboxPoiAutocomplete
                                v-model="searchQuery"
                                :proximity="{ latitude: props.step?.latitude, longitude: props.step?.longitude }"
                                placeholder="Rechercher un lieu (ex: Tour Eiffel, café...)"
                                @select-place="onSelectPlace"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                            <input v-model="form.title" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="Nom de l’activité" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="cat in categories" :key="cat.key" type="button"
                                    @click="form.category = cat.key"
                                    class="flex items-center justify-center gap-1 px-3 py-2 rounded-lg border transition"
                                    :class="form.category === cat.key ? 'border-pink-500 bg-pink-50 text-pink-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <span class="material-symbols-rounded text-[18px]">{{ cat.icon }}</span>
                                    <span class="text-sm">{{ cat.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                            <input v-model="form.location" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="Lieu, adresse..." />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lien externe</label>
                            <input v-model="form.link" type="url" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="https://..." />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input v-model="form.date" type="date" :min="minDate" :max="maxDate" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heure (optionnelle)</label>
                                <input v-model="form.time" type="time" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500 resize-none" placeholder="Détails facultatifs sur l’activité..."></textarea>
                        </div>
                    </div>

                    <!-- Explorateur de POI -->
                    <div class="relative bg-gray-50 p-4 overflow-hidden">
                        <PoiExplorer
                            v-if="props.step?.latitude && props.step?.longitude"
                            :latitude="props.step.latitude"
                            :longitude="props.step.longitude"
                            @select-poi="onSelectPOI"
                        />
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-100 bg-gray-50 p-4 flex justify-end gap-2">
                    <button @click="closeAndReset" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Annuler</button>
                    <button
                        @click="submit"
                        class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg transition disabled:bg-gray-300"
                        :disabled="form.processing || !form.title">
                        {{ props.activity?.id ? "Enregistrer" : "Créer" }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.material-symbols-rounded {
    font-variation-settings: "FILL" 1, "wght" 400, "GRAD" 0, "opsz" 24;
}
</style>
