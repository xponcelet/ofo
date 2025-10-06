<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AddStepButton from '@/Components/AddStepButton.vue'
import { t } from '@/Composables/useTranslations'

const props = defineProps({
    trip: Object,
})

function deleteStep(step) {
    if (confirm(t('steps.confirm_delete', { title: step.title }))) {
        router.delete(route('steps.destroy', step.id))
    }
}

const openMenuId = ref(null)
function toggleMenu(stepId) {
    openMenuId.value = openMenuId.value === stepId ? null : stepId
}

// Helper durée (minutes → h + min)
function formatDuration(minutes) {
    if (!minutes) return null
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return h > 0 ? `${h}h ${m}min` : `${m}min`
}

// Emoji drapeau depuis le code pays (FR → 🇫🇷)
function flagFromCode(code) {
    if (!code) return ''
    const cc = String(code).toUpperCase()
    if (cc.length !== 2) return ''
    const A = 0x1F1E6
    const first = cc.codePointAt(0) - 65 + A
    const second = cc.codePointAt(1) - 65 + A
    if (first < A || second < A) return ''
    return String.fromCodePoint(first, second)
}
</script>

<template>
    <div class="space-y-8 max-w-5xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-800">{{ t('steps.title') }}</h2>

        <!-- Ajouter avant la première -->
        <AddStepButton :trip-id="trip.id" :at-start="true" :label="t('steps.add_before_first')" />

        <!-- Liste des étapes -->
        <div v-if="trip.steps.length" class="space-y-8">
            <div
                v-for="step in trip.steps"
                :key="step.id"
                class="grid grid-cols-[110px_1fr] gap-4 items-start"
            >
                <!-- Colonne gauche -->
                <div class="flex flex-col items-start pt-1">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-pink-600 text-white flex items-center justify-center text-sm font-semibold shadow">
                            {{ step.order }}
                        </div>
                        <div class="text-xs font-medium text-gray-500">
                            <div v-if="step.start_date && step.end_date">
                                {{ step.start_date }} → {{ step.end_date }}
                            </div>
                            <div v-else class="italic text-gray-400">
                                {{ t('steps.no_dates') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte étape / contenu -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <!-- Contenu -->
                    <div class="p-6 relative">
                        <!-- Menu -->
                        <button
                            @click="toggleMenu(step.id)"
                            class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100"
                        >
                            ⋮
                        </button>

                        <div
                            v-if="openMenuId === step.id"
                            class="absolute right-4 top-12 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
                        >
                            <ul class="py-1 text-sm text-gray-700">
                                <li>
                                    <Link :href="route('steps.edit', step.id)" class="block px-4 py-2 hover:bg-gray-50">
                                        ✏️ {{ t('steps.edit') }}
                                    </Link>
                                </li>
                                <li>
                                    <button
                                        @click="deleteStep(step)"
                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50"
                                    >
                                        🗑️ {{ t('steps.delete') }}
                                    </button>
                                </li>
                                <li class="border-t my-1"></li>
                                <li>
                                    <Link
                                        :href="route('steps.move-up', step.id)"
                                        method="patch"
                                        as="button"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-50"
                                    >
                                        ⬆️ {{ t('steps.move_up') }}
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        :href="route('steps.move-down', step.id)"
                                        method="patch"
                                        as="button"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-50"
                                    >
                                        ⬇️ {{ t('steps.move_down') }}
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Titre + lieu -->
                        <div class="mb-3">
                            <h3 class="text-xl font-bold text-gray-800 leading-tight">
                                {{ step.title || t('steps.untitled') }}
                            </h3>

                            <p class="mt-1 text-pink-700 font-medium flex items-center gap-2">
                                <span>📍 {{ step.location || t('steps.no_location') }}</span>
                                <span v-if="step.country_code" class="text-lg" :title="step.country">{{ flagFromCode(step.country_code) }}</span>
                                <span v-else-if="step.country" class="text-xs text-gray-500">({{ step.country }})</span>
                            </p>
                        </div>

                        <!-- Infos -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                            <p v-if="step.transport_mode">🚗 {{ t('steps.transport') }}: {{ step.transport_mode }}</p>
                            <p v-if="step.nights > 0">
                                🌙 {{ step.nights }} {{ t('steps.night', { count: step.nights }) }}
                            </p>
                            <p v-if="step.distance_to_next">
                                📏 {{ t('steps.distance') }} → {{ step.distance_to_next.toFixed(1) }} km
                            </p>
                            <p v-if="step.duration_to_next">
                                ⏱️ {{ t('steps.duration') }} → {{ formatDuration(step.duration_to_next) }}
                            </p>
                        </div>

                        <!-- Itinéraire -->
                        <div class="mt-3">
                            <a
                                v-if="step.latitude && step.longitude"
                                :href="`https://www.google.com/maps/dir/?api=1&destination=${step.latitude},${step.longitude}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-sm text-pink-600 hover:underline"
                            >
                                {{ t('steps.itinerary') }}
                            </a>
                        </div>
                    </div>

                    <!-- Ajouter après -->
                    <div class="bg-gray-50 p-3 border-t text-center">
                        <AddStepButton :trip-id="trip.id" :after-id="step.id" :label="t('steps.add_after')" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Vide -->
        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
            <p class="text-lg mb-4">🚀 {{ t('steps.empty') }}</p>
            <AddStepButton :trip-id="trip.id" :label="t('steps.create_first')" />
        </div>
    </div>
</template>
