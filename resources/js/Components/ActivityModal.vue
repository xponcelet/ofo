<template>
    <!-- Overlay -->
    <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <!-- Modal -->
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
            <h2 class="text-xl font-bold mb-4">Ajouter une activité</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Titre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Titre *</label>
                    <input v-model="form.title" type="text" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    <span v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</span>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="form.description" rows="2"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>

                <!-- Localisation via Mapbox -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Localisation</label>
                    <MapboxAutocomplete v-model="form.location" placeholder="Chercher un lieu..." />
                    <span v-if="form.errors.location" class="text-red-600 text-sm">{{ form.errors.location }}</span>
                </div>

                <!-- Date fixe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="text" :value="formatDate(props.dayDate)" disabled
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" />
                </div>

                <!-- Heures -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Heure de début</label>
                        <input v-model="startTime" type="time"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Heure de fin</label>
                        <input v-model="endTime" type="time"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>


                <!-- Lien externe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lien externe</label>
                    <input v-model="form.external_link" type="url" placeholder="https://exemple.com"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>

                <!-- Coût + devise -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Coût</label>
                        <input v-model="form.cost" type="number" step="0.01"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Devise</label>
                        <input v-model="form.currency" type="text" maxlength="3" placeholder="EUR"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase" />
                    </div>
                </div>

                <!-- Catégorie -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                    <input v-model="form.category" type="text"
                           placeholder="Culture, Gastronomie, Nature"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>

                <!-- Boutons -->
                <div class="flex justify-between items-center pt-4">
                    <button type="button" @click="close"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Annuler
                    </button>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'

const props = defineProps({
    stepId: { type: Number, required: true },
    dayDate: { type: String, required: true }, // ex: "2025-10-17"
    show: { type: Boolean, default: false }
})
const emit = defineEmits(['close', 'created'])

const form = useForm({
    title: '',
    description: '',
    location: '',
    start_at: '', // ✅ les vrais champs de la DB
    end_at: '',
    external_link: '',
    cost: '',
    currency: 'EUR',
    category: ''
})

// Pour l’UI, on gère juste des heures
const startTime = ref('09:00')
const endTime   = ref('18:00')

function submit() {
    // ⚡ On combine date + heure pour remplir les champs DB
    form.start_at = `${props.dayDate} ${startTime.value}:00`
    form.end_at   = `${props.dayDate} ${endTime.value}:00`

    form.post(route('steps.activities.store', props.stepId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            startTime.value = '09:00'
            endTime.value = '18:00'
            emit('created')
            close()
        }
    })
}

function close() {
    emit('close')
}

function formatDate(dateStr) {
    if (!dateStr) return ''
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}
</script>
