<script setup>
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    form: Object,
    onSubmit: Function,
    submitLabel: {
        type: String,
        default: 'Valider',
    },
})
</script>


<template>
    <form @submit.prevent="onSubmit" class="max-w-2xl mx-auto space-y-6">
        <!-- Titre -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Titre *</label>
            <input
                v-model="form.title"
                type="text"
                class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            />
            <InputError :message="form.errors.title" />
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            />
            <InputError :message="form.errors.description" />
        </div>

        <!-- Image -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Image (URL)</label>
            <input
                v-model="form.image"
                type="text"
                class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            />
            <InputError :message="form.errors.image" />
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Date de début</label>
                <input
                    v-model="form.start_date"
                    type="date"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                <InputError :message="form.errors.start_date" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                <input
                    v-model="form.end_date"
                    type="date"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                <InputError :message="form.errors.end_date" />
            </div>
        </div>

        <!-- Budget et devise -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Budget</label>
                <input
                    v-model="form.budget"
                    type="number"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                <InputError :message="form.errors.budget" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Devise</label>
                <select
                    v-model="form.currency"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                >
                    <option value="EUR">EUR</option>
                    <option value="USD">USD</option>
                </select>
                <InputError :message="form.errors.currency" />
            </div>
        </div>

        <!-- Public -->
        <div class="flex items-center gap-2">
            <input v-model="form.is_public" type="checkbox" />
            <label class="text-sm text-gray-600">Rendre ce voyage public</label>
        </div>
        <InputError :message="form.errors.is_public" />

        <!-- Bouton -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700"
                :disabled="form.processing"
            >
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>


