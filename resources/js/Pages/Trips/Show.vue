<script setup>
import { ref, computed } from 'vue'
import RootLayout from "@/Layouts/RootLayout.vue";
import TripSteps from '@/Components/TripSteps.vue'
import StepsMap from '@/Components/StepsMap.vue'
import GoogleMapsFullTripLink from '@/Components/GoogleMapsFullTripLink.vue'
import TripShowView from '@/Components/trip/TripShowView.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    trip: Object,
    steps: Array,
    favs: Number,    // total des likes
    likers: Object,  // paginator OU null si non-proprio
})

// ✅ nouvel onglet par défaut
const currentTab = ref('resume')

// --- Helpers format ---
function fmtDateTime(dt) {
    if (!dt) return null
    try {
        const d = new Date(dt)
        return new Intl.DateTimeFormat(undefined, {
            year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'
        }).format(d)
    } catch { return null }
}

function fmtMoney(amount, currency) {
    if (amount == null || amount === '') return null
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: (currency || 'EUR') }).format(Number(amount))
    } catch {
        return `${amount} ${currency || ''}`.trim()
    }
}

const hasAnyActivities = computed(() => (props.steps || []).some(s => (s.activities || []).length))
const stepCount = computed(() => (props.steps || []).length)
const totalActivitiesCount = computed(() => (props.steps || []).reduce((n, s) => n + ((s.activities || []).length), 0))

function tabClass(val) {
    return currentTab.value === val
        ? 'bg-white shadow ring-1 ring-gray-200 text-gray-900'
        : 'text-gray-600 hover:text-gray-900'
}

// 👉 Normalisation pour la vue Résumé (3 colonnes)
const stepsSummary = computed(() => {
    const raw = Array.isArray(props.steps) ? props.steps : []
    return [...raw]
        .sort((a,b) => (a.order ?? 0) - (b.order ?? 0))
        .map((s, idx) => {
            const day = s.order ?? (idx + 1)
            return {
                id: s.id,
                day,
                short_title: `Jour ${day}`,
                title: s.title || s.location || `Étape ${day}`,
                photo_url: s.photo_url ?? null,
                description_html: s.description_html ?? null,
                coords: { lat: Number(s.latitude), lng: Number(s.longitude) },
            }
        })
})

const tripMeta = computed(() => ({
    title: props.trip?.title,
    country: props.trip?.destination || props.trip?.location,
    start_date: props.trip?.start_date,
    end_date: props.trip?.end_date,
    visibility: props.trip?.is_public ? 'public' : 'private',
}))
</script>

<template>
    <div class="max-w-5xl mx-auto py-10 px-4 space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ trip.title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Aperçu, étapes, activités et carte du voyage.</p>
            </div>
            <div class="pt-1 inline-flex items-center gap-2 text-sm text-gray-700" :title="`${favs ?? 0} favoris`" aria-label="Nombre de favoris">
                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-red-500"><path d="M12 21s-7.5-4.6-9.4-8.5A5.6 5.6 0 0 1 12 5.7a5.6 5.6 0 0 1 9.4 6.8C19.5 16.4 12 21 12 21Z"/></svg>
                <span><strong>{{ favs ?? 0 }}</strong></span>
            </div>
        </div>

        <!-- Tabs (segmented) -->
        <div class="mb-2">
            <nav class="bg-gray-50 rounded-xl p-1 flex flex-wrap gap-1">
                <!-- ✅ Nouvel onglet Résumé -->
                <button @click="currentTab = 'resume'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('resume')">🧾 Résumé</button>

                <button @click="currentTab = 'infos'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('infos')">ℹ️ Infos</button>
                <button @click="currentTab = 'steps'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('steps')">📍 Étapes <span class="ml-1 text-xs text-gray-500" v-if="stepCount">({{ stepCount }})</span></button>
                <button @click="currentTab = 'activities'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('activities')">🧭 Activités <span class="ml-1 text-xs text-gray-500" v-if="totalActivitiesCount">({{ totalActivitiesCount }})</span></button>
                <button @click="currentTab = 'map'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('map')">🗺️ Carte</button>
            </nav>
        </div>

        <!-- ✅ Onglet: Résumé (3 colonnes: étapes / détail / carte) -->
        <div v-if="currentTab === 'resume'">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">
                        {{ tripMeta.country }}
                        <span v-if="tripMeta.start_date && tripMeta.end_date"> · {{ tripMeta.start_date }} → {{ tripMeta.end_date }}</span>
                    </p>
                </div>
                <span class="hidden sm:inline-block rounded-full px-3 py-1 text-xs font-medium"
                      :class="tripMeta.visibility === 'public' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700'">
                {{ tripMeta.visibility === 'public' ? 'Public' : 'Privé' }}
              </span>
            </div>

            <TripShowView :trip-meta="tripMeta" :steps="stepsSummary" />
        </div>

        <!-- Onglet: Infos générales -->
        <div v-else-if="currentTab === 'infos'" class="space-y-6">
            <div class="bg-white rounded-2xl shadow p-6 space-y-4 ring-1 ring-gray-100">
                <p class="text-gray-700">{{ trip.description || 'Aucune description.' }}</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm text-gray-600">
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Destination</div>
                        <div>{{ trip.destination }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Dates</div>
                        <div>{{ trip.start_date }} → {{ trip.end_date }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Budget</div>
                        <div>{{ trip.budget }} {{ trip.currency }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Visibilité</div>
                        <div>{{ trip.is_public ? 'Public' : 'Privé' }}</div>
                    </div>
                </div>

                <div v-if="trip.image" class="mt-2">
                    <img :src="trip.image" alt="Image du voyage" class="rounded-xl shadow w-full max-h-96 object-cover">
                </div>
            </div>

            <!-- Likers (proprio seulement) -->
            <section v-if="likers && likers.data?.length" class="bg-white rounded-2xl shadow p-6 ring-1 ring-gray-100">
                <h2 class="text-lg font-semibold mb-3">Personnes ayant aimé ({{ likers.total }})</h2>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <li v-for="u in likers.data" :key="u.id" class="p-3 border rounded-xl flex items-center gap-3">
                        <img :src="u.profile_photo_url || '/img/default-avatar.png'" class="w-10 h-10 rounded-full object-cover" alt="" />
                        <div>
                            <div class="font-medium">{{ u.name }}</div>
                            <div class="text-xs text-gray-500" v-if="u.email">{{ u.email }}</div>
                        </div>
                    </li>
                </ul>
                <div class="flex items-center gap-2 mt-4">
                    <Link v-if="likers.prev_page_url" :href="likers.prev_page_url" preserve-scroll class="px-3 py-1 border rounded">Précédent</Link>
                    <span class="text-sm">Page {{ likers.current_page }} / {{ likers.last_page }}</span>
                    <Link v-if="likers.next_page_url" :href="likers.next_page_url" preserve-scroll class="px-3 py-1 border rounded">Suivant</Link>
                </div>
            </section>
        </div>

        <!-- Onglet: Étapes -->
        <div v-else-if="currentTab === 'steps'">
            <section class="bg-white rounded-2xl shadow p-6 ring-1 ring-gray-100">
                <header class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">📍 Étapes du voyage</h2>
                    <span class="text-xs text-gray-500">{{ stepCount }} étape(s)</span>
                </header>
                <TripSteps :trip="trip" />
            </section>
        </div>

        <!-- Onglet: Activités -->
        <div v-else-if="currentTab === 'activities'" class="space-y-4">
            <div v-if="hasAnyActivities" class="space-y-5">
                <section v-for="s in steps" :key="s.id" class="bg-white rounded-2xl shadow p-5 ring-1 ring-gray-100">
                    <header class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-lg">Étape #{{ s.order }} — {{ s.location || s.title || 'Lieu non précisé' }}</h3>
                            <p class="text-xs text-gray-500" v-if="s.start_date || s.end_date">📅 {{ s.start_date }} → {{ s.end_date }}</p>
                        </div>
                        <div class="text-xs text-gray-500">{{ (s.activities || []).length }} activité(s)</div>
                    </header>

                    <div v-if="(s.activities || []).length" class="mt-4 space-y-3">
                        <article v-for="a in s.activities" :key="a.id" class="p-3 rounded-xl border border-gray-200 hover:border-gray-300 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h4 class="font-medium truncate">{{ a.title }}</h4>
                                    <p class="text-sm text-gray-600" v-if="a.description">{{ a.description }}</p>

                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                                      <span v-if="a.start_at || a.end_at" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2m0 15H5V9h14z"/></svg>
                                        <span>
                                          <template v-if="a.start_at && a.end_at">{{ fmtDateTime(a.start_at) }} → {{ fmtDateTime(a.end_at) }}</template>
                                          <template v-else-if="a.start_at">Le {{ fmtDateTime(a.start_at) }}</template>
                                        </span>
                                      </span>

                                        <span v-if="a.category" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">#{{ a.category }}</span>
                                        <span v-if="a.cost != null" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ fmtMoney(a.cost, a.currency) }}</span>
                                        <a v-if="a.external_link" :href="a.external_link" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 hover:bg-purple-100">
                                            🔗 Lien
                                        </a>
                                    </div>
                                </div>
                                <Link :href="route('activities.edit', a.id)" class="text-sm px-3 py-1.5 rounded-2xl bg-blue-100 hover:bg-blue-200">
                                    ✏️ Modifier
                                </Link>
                            </div>
                        </article>
                    </div>
                    <p v-else class="text-sm text-gray-500">— Aucune activité sur cette étape.</p>
                </section>
            </div>
            <p v-else class="text-sm text-gray-500">Aucune activité n’a encore été planifiée.</p>
        </div>

        <!-- Onglet: Carte -->
        <div v-else-if="currentTab === 'map'">
            <section class="bg-white rounded-2xl shadow p-6 ring-1 ring-gray-100">
                <header class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">🗺️ Carte du voyage</h2>
                    <GoogleMapsFullTripLink :steps="steps" />
                </header>
                <div class="rounded-xl overflow-hidden ring-1 ring-gray-100">
                    <StepsMap :steps="steps" />
                </div>
            </section>
        </div>

    </div>
</template>
