<script setup>
import TripMap from '@/Components/Trip/TripMap.vue'

const props = defineProps({
    trip: Object,
})

// 🇫🇷 Convertit un code pays (FR → 🇫🇷)
function flagFromCode(code) {
    if (!code) return ''
    const cc = code.toUpperCase()
    const base = 0x1f1e6
    return String.fromCodePoint(...[...cc].map(c => base + c.charCodeAt(0) - 65))
}
</script>

<template>
    <div class="max-w-7xl mx-auto py-10 px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-10">
            <!-- =======================
                 📍 Liste des étapes
            ======================== -->
            <section class="space-y-8">
                <div
                    v-for="(step, i) in trip.steps"
                    :key="step.id"
                    class="bg-white rounded-2xl shadow-sm border border-outline-variant overflow-hidden transition hover:shadow-md"
                >
                    <!-- Image -->
                    <div v-if="step.photo_url" class="h-52 w-full overflow-hidden">
                        <img
                            :src="step.photo_url"
                            :alt="step.title"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Contenu -->
                    <div class="p-6 space-y-4">
                        <!-- En-tête -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm shadow"
                                >
                                    {{ step.order || i + 1 }}
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 leading-tight">
                                    {{ step.title || 'Étape sans titre' }}
                                </h3>
                            </div>
                            <div class="text-right text-xs text-on-surface-variant">
                                <p>
                                    {{ step.start_date || '—' }} → {{ step.end_date || '—' }}
                                </p>
                                <p v-if="step.nights" class="text-gray-500">
                                    🌙 {{ step.nights }} nuit<span v-if="step.nights > 1">s</span>
                                </p>
                            </div>
                        </div>

                        <!-- Lieu -->
                        <p class="flex items-center gap-2 text-sm text-gray-600">
                            📍 {{ step.city || step.location || 'Localisation inconnue' }}
                            <span v-if="step.country_code" class="text-lg">{{ flagFromCode(step.country_code) }}</span>
                        </p>

                        <!-- Description -->
                        <p v-if="step.description" class="text-gray-700 leading-relaxed text-sm">
                            {{ step.description }}
                        </p>

                        <!-- Activités -->
                        <div v-if="step.activities?.length" class="pt-2">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                🧭 Activités
                                <span class="text-xs text-gray-400">
                                    ({{ step.activities.length }})
                                </span>
                            </h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <li
                                    v-for="a in [...step.activities].sort((a,b) => new Date(a.start_at) - new Date(b.start_at))"
                                    :key="a.id"
                                    class="bg-gray-50 border border-gray-200 rounded-xl p-3 hover:bg-gray-100 transition"
                                >
                                    <div class="flex justify-between items-start">
                                        <div class="font-medium text-gray-800 truncate">
                                            {{ a.title }}
                                        </div>
                                        <span v-if="a.start_at" class="text-xs text-gray-500 whitespace-nowrap">
                                            {{ new Date(a.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                        </span>
                                    </div>
                                    <p v-if="a.description" class="text-xs text-gray-600 line-clamp-2 mt-1">
                                        {{ a.description }}
                                    </p>
                                </li>
                            </ul>
                        </div>

                        <!-- ... le reste du composant inchangé -->

                        <!-- Conseils -->
                        <div
                            v-if="step.tips"
                            class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 p-4 rounded-xl mt-3"
                        >
                            💡 {{ step.tips }}
                        </div>

                        <!-- Notes personnelles -->
                        <div
                            class="mt-4 border border-dashed border-gray-300 rounded-xl p-4 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        >
                            <div>
                                <p class="font-medium text-gray-800 flex items-center gap-2">
                                    📝 Notes personnelles
                                </p>
                                <p v-if="!step.note" class="text-sm italic text-gray-500">
                                    Aucune note pour cette étape. Ajoutez vos impressions et souvenirs !
                                </p>
                                <p v-else class="text-sm text-gray-700 mt-1 whitespace-pre-line">
                                    {{ step.note }}
                                </p>
                            </div>

                            <button
                                class="self-start sm:self-auto px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium hover:bg-gray-100 transition"
                                @click="alert('Formulaire ou modal pour ajouter une note ici ✏️')"
                            >
                                ➕ Ajouter une note
                            </button>
                        </div>

                    </div>
                </div>
            </section>

            <!-- =======================
                 🗺️ Carte
            ======================== -->
            <aside
                class="sticky top-24 h-[75vh] rounded-2xl overflow-hidden border border-outline-variant shadow-md bg-white"
            >
                <TripMap :steps="trip.steps" />
            </aside>
        </div>
    </div>
</template>

<style scoped>
@media (max-width: 1024px) {
    aside {
        position: relative;
        height: 400px;
    }
}
</style>
