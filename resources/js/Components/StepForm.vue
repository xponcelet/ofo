<template>
    <form @submit.prevent="props.onSubmit" class="space-y-8">
        <!-- Section infos générales -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h3>

            <!-- Titre -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Titre (optionnel)</label>
                <input
                    v-model="props.form.title"
                    type="text"
                    placeholder="Ex: Camp de base"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                />
                <InputError :message="props.form.errors?.title" />
            </div>

            <!-- Lieu -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lieu principal *</label>
                <MapboxAutocomplete
                    :key="mapboxInstanceKey"
                    v-model="props.form.location"
                    @update:coords="updateCoords"
                    @update:country="updateCountry"
                />
                <InputError :message="props.form.errors?.location" />
            </div>

            <!-- Carte -->
            <StepMapPreview
                class="mt-4 rounded-lg border border-gray-200"
                :latitude="props.form.latitude"
                :longitude="props.form.longitude"
            />
        </div>

        <!-- Section logistique -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Logistique</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Date début -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input
                        v-model="props.form.start_date"
                        type="date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                    />
                    <InputError :message="props.form.errors?.start_date" />
                </div>

                <!-- Nuits -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre de nuits</label>
                    <input
                        v-model.number="props.form.nights"
                        type="number"
                        min="0"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                    />
                    <InputError :message="props.form.errors?.nights" />
                </div>

                <!-- Date fin (calculée) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                    <input
                        :value="computedEndDate"
                        type="date"
                        readonly
                        class="mt-1 block w-full bg-gray-100 rounded-lg border-gray-300 shadow-sm text-gray-600"
                    />
                </div>
            </div>

            <!-- Transport -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Moyen de transport</label>
                <select
                    v-model="props.form.transport_mode"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                >
                    <option value="car">🚗 Voiture</option>
                    <option value="plane">🛫 Avion</option>
                    <option value="train">🚆 Train</option>
                    <option value="bus">🚌 Bus</option>
                    <option value="bike">🚲 Vélo</option>
                    <option value="walk">🚶 Marche</option>
                    <option value="boat">⛵ Bateau</option>
                </select>
                <InputError :message="props.form.errors?.transport_mode" />
            </div>
        </div>

        <!-- Section description -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Description</h3>
            <textarea
                v-model="props.form.description"
                rows="4"
                placeholder="Ajoutez quelques notes ou une description..."
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
            />
            <InputError :message="props.form.errors?.description" />
        </div>

        <!-- Bouton -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-pink-600 text-white font-semibold rounded-lg shadow hover:bg-pink-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="props.form.processing"
            >
                {{ props.submitLabel }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    submitLabel: { type: String, default: 'Valider' },
})

const page = usePage()
const routeStepId =
    page?.props?.ziggy?.routeParameters?.id ??
    page?.props?.routeParams?.id ??
    null

const mapboxInstanceKey = computed(() => {
    return `step-${props.form?.id ?? routeStepId ?? 'new'}`
})

// Date de fin = start_date + nights
const computedEndDate = computed(() => {
    if (!props.form.start_date || !props.form.nights) return ''
    const start = new Date(props.form.start_date)
    const end = new Date(start)
    end.setDate(start.getDate() + props.form.nights)
    return end.toISOString().split('T')[0] // format YYYY-MM-DD
})

onMounted(() => {
    if (props.form.location === undefined) props.form.location = ''
    if (props.form.latitude === undefined) props.form.latitude = null
    if (props.form.longitude === undefined) props.form.longitude = null
    if (props.form.country === undefined) props.form.country = ''
    if (props.form.transport_mode === undefined) props.form.transport_mode = 'car'
    if (props.form.nights === undefined) props.form.nights = 0
})

// Maj coordonnées
function updateCoords({ latitude, longitude }) {
    props.form.latitude = latitude
    props.form.longitude = longitude
}

// Maj pays depuis MapboxAutocomplete
function updateCountry(country) {
    props.form.country = country
}
</script>
