<script setup>
import Input from '@/Components/Input.vue'
import Textarea from '@/Components/Textarea.vue'
import Select from '@/Components/Select.vue'
import Checkbox from '@/Components/Checkbox.vue'
import Button from '@/Components/Button.vue'

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
    <form @submit.prevent="onSubmit" class="max-w-2xl mx-auto space-y-8">
        <!-- Titre -->
        <Input
            v-model="form.title"
            label="Titre du voyage"
            required
            placeholder="Ex. Road trip au Portugal"
            :error="form.errors.title"
        />

        <!-- Description -->
        <Textarea
            v-model="form.description"
            label="Description"
            placeholder="Racontez un peu ce que vous comptez faire..."
            :error="form.errors.description"
        />

        <!-- Image -->
        <Input
            v-model="form.image"
            label="Image (URL)"
            type="url"
            placeholder="https://exemple.com/photo.jpg"
            :error="form.errors.image"
        />

        <!-- Dates -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Input
                v-model="form.start_date"
                label="Date de début"
                type="date"
                :error="form.errors.start_date"
            />
            <Input
                v-model="form.end_date"
                label="Date de fin"
                type="date"
                :error="form.errors.end_date"
            />
        </div>

        <!-- Budget et devise -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Input
                v-model="form.budget"
                label="Budget"
                type="number"
                min="0"
                step="0.01"
                placeholder="Ex. 1200"
                :error="form.errors.budget"
            />

            <Select
                v-model="form.currency"
                label="Devise"
                :options="[
                    { value: 'EUR', label: 'Euro (€)' },
                    { value: 'USD', label: 'Dollar ($)' },
                ]"
                :error="form.errors.currency"
            />
        </div>

        <!-- Public -->
        <Checkbox
            v-model="form.is_public"
            label="Rendre ce voyage public"
            :error="form.errors.is_public"
        />

        <!-- Bouton -->
        <div class="flex justify-end pt-6">
            <Button
                type="submit"
                variant="primary"
                :disabled="form.processing"
                :loading="form.processing"
            >
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
