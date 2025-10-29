<script setup>
import { Head, Link } from "@inertiajs/vue3"
import FavoriteButton from "@/Components/FavoriteButton.vue"

const props = defineProps({
    favorites: { type: Object, required: true },
})

function getFlagEmoji(code) {
    if (!code) return ""
    return code
        .toUpperCase()
        .replace(/./g, (char) => String.fromCodePoint(127397 + char.charCodeAt()))
}
</script>

<template>
    <Head title="Mes favoris" />

    <div class="max-w-6xl mx-auto px-6 py-10 space-y-10">
        <!-- 🌟 Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    <span class="text-gray-800">Mes</span>
                    <span class="text-pink-600"> favoris</span>
                </h1>
                <p class="text-gray-500 mt-1">
                    Retrouvez ici tous les voyages que vous avez aimés ❤️
                </p>
            </div>
        </div>

        <!-- 🧭 Liste -->
        <transition-group
            name="fade-list"
            tag="div"
            class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <div
                v-for="trip in favorites.data"
                :key="trip.id"
                @click="$inertia.visit(route('public.trips.show', trip.id))"
                class="group relative bg-white rounded-2xl shadow-sm hover:shadow-lg transition cursor-pointer border border-gray-200 hover:border-pink-300 overflow-hidden"
            >
                <!-- ❤️ Bouton favori -->
                <div class="absolute top-3 right-3 z-10">
                    <FavoriteButton
                        :trip-id="trip.id"
                        :is-favorite="trip.is_favorite ?? true"
                        class="!bg-white/80 hover:!bg-white text-pink-500 shadow-sm"
                    />
                </div>

                <!-- Contenu -->
                <div class="p-5 space-y-3 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-lg font-semibold text-gray-900 truncate group-hover:text-pink-600"
                        >
                            <span v-if="trip.destination_country_code" class="mr-1">
                                {{ getFlagEmoji(trip.destination_country_code) }}
                            </span>
                            {{ trip.title || "Sans titre" }}
                        </h3>

                        <span
                            class="material-symbols-rounded text-gray-400 group-hover:text-pink-500 transition"
                        >
                            chevron_right
                        </span>
                    </div>

                    <p
                        v-if="trip.description"
                        class="text-sm text-gray-600 line-clamp-2"
                    >
                        {{ trip.description }}
                    </p>

                    <p v-if="trip.creator" class="text-sm text-gray-500 italic">
                        Par {{ trip.creator.name }}
                    </p>
                </div>

                <!-- 🌈 Ligne accent dégradée -->
                <div class="h-1 bg-gradient-to-r from-pink-500 to-purple-500 rounded-b-2xl" />
            </div>
        </transition-group>

        <!-- 🚫 Aucun favori -->
        <div
            v-if="!favorites.data.length"
            class="text-gray-500 border rounded-xl p-6 mt-6 text-center bg-gray-50"
        >
            💔 Vous n’avez encore aucun voyage en favori.
        </div>

        <!-- 📄 Pagination -->
        <div
            v-if="favorites?.links?.length > 3"
            class="flex justify-center mt-10 gap-2 flex-wrap"
        >
            <Link
                v-for="link in favorites.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-3 py-2 rounded-lg border text-sm font-medium"
                :class="{
                    'bg-pink-600 text-white border-pink-600': link.active,
                    'text-gray-600 hover:bg-gray-100': !link.active,
                    'opacity-50 cursor-not-allowed': !link.url
                }"
            />
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.material-symbols-rounded {
    font-variation-settings:
        "FILL" 0,
        "wght" 400,
        "GRAD" 0,
        "opsz" 24;
}

/* ✨ Transition fluide */
.fade-list-enter-active,
.fade-list-leave-active {
    transition: all 0.4s ease;
}
.fade-list-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.fade-list-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
