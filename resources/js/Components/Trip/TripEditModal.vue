<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
    trip: { type: Object, required: true },
    show: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const form = useForm({
    title: props.trip.title || '',
    description: props.trip.description || '',
    budget: props.trip.budget || '',
    image: props.trip.image || '',
    is_public: props.trip.is_public ?? true,
})

watch(() => props.trip, (t) => {
    form.title = t.title
    form.description = t.description
    form.budget = t.budget
    form.image = t.image
    form.is_public = t.is_public
})

function submit() {
    form.put(route('trips.update', props.trip.id), {
        onSuccess: () => emit('close'),
    })
}
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="emit('close')">
            <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-lg rounded-2xl bg-surface shadow-lg p-6">
                    <DialogTitle class="text-lg font-semibold mb-4">
                        Modifier le voyage
                    </DialogTitle>

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
                                <button type="button" @click="emit('close')" class="btn-secondary">
                                    Annuler
                                </button>
                                <button type="submit" class="btn-primary" :disabled="form.processing">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
.input {
    @apply rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none;
}
.btn-primary {
    @apply px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition;
}
.btn-secondary {
    @apply px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition;
}
</style>
