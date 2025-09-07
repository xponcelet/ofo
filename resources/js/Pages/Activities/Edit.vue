<template>
    <div class="max-w-3xl mx-auto py-10 px-4 space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Modifier l’activité</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trip.title }} · Étape #{{ step.order }} — {{ step.location || step.title || 'Lieu non précisé' }}
                </p>
            </div>
            <div class="flex gap-2">
                <Link :href="backToTripUrl" class="px-3 py-2 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm">← Voyage</Link>
                <Link :href="route('steps.edit', activity.step_id) + '?tab=activities'" class="px-3 py-2 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm">Étape</Link>
            </div>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow p-6 ring-1 ring-gray-100 space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Titre *</label>
                    <input v-model="form.title" type="text" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    <InputError :message="form.errors.title" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Catégorie</label>
                    <input v-model="form.category" type="text" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="visit, food, transport..." />
                    <InputError :message="form.errors.category" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Étape</label>
                    <select v-model="form.step_id" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500">
                        <option v-for="s in steps" :key="s.id" :value="s.id">Étape #{{ s.order }} — {{ s.location }}</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Seules les étapes du même voyage sont proposées.</p>
                    <InputError :message="form.errors.step_id" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Lien externe</label>
                    <input v-model="form.external_link" type="url" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="https://..." />
                    <InputError :message="form.errors.external_link" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Début</label>
                    <input v-model="form.start_at" type="datetime-local" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    <InputError :message="form.errors.start_at" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Fin</label>
                    <input v-model="form.end_at" type="datetime-local" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    <InputError :message="form.errors.end_at" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Coût</label>
                    <input v-model="form.cost" type="number" step="0.01" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    <InputError :message="form.errors.cost" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Devise</label>
                    <input v-model="form.currency" type="text" maxlength="3" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    <InputError :message="form.errors.currency" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    <InputError :message="form.errors.description" />
                </div>
            </div>

            <div class="flex justify-between items-center pt-2">
                <button @click="destroy" type="button" class="px-4 py-2 rounded-2xl border border-red-300 text-red-700 hover:bg-red-50">Supprimer</button>
                <div class="flex gap-2">
                    <Link :href="backToTripUrl" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-2xl hover:bg-gray-200">Annuler</Link>
                    <button @click="save" type="button" class="px-4 py-2 bg-blue-600 text-white rounded-2xl hover:bg-blue-700">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    activity: Object, // id, step_id, title, description, start_at, end_at, external_link, cost, currency, category
    step: Object,     // étape courante (id, order, location, ...)
    steps: Array,     // étapes du même trip pour réassignation
    trip: Object,     // id, title
})

// Helpers datetime-local
function toLocalInput(dt) {
    if (!dt) return ''
    try {
        const d = new Date(dt)
        const pad = (n) => String(n).padStart(2, '0')
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
    } catch { return '' }
}

const form = useForm({
    step_id: props.activity.step_id,
    title: props.activity.title ?? '',
    description: props.activity.description ?? '',
    start_at: toLocalInput(props.activity.start_at),
    end_at: toLocalInput(props.activity.end_at),
    external_link: props.activity.external_link ?? '',
    cost: props.activity.cost ?? '',
    currency: props.activity.currency ?? 'EUR',
    category: props.activity.category ?? '',
})

const backToTripUrl = computed(() => route('trips.show', props.trip.id) + '?tab=activities')

function save() {
    router.put(route('activities.update', props.activity.id), form, {
        preserveScroll: true,
        onSuccess: () => router.visit(backToTripUrl.value, { replace: true })
    })
}

function destroy() {
    if (!confirm('Supprimer cette activité ?')) return
    router.delete(route('activities.destroy', props.activity.id), {
        onSuccess: () => router.visit(backToTripUrl.value, { replace: true })
    })
}
</script>

<style scoped>
</style>
