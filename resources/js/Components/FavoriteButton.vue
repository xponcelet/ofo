<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    tripId: { type: Number, required: true },
    isFavorite: { type: Boolean, default: false },
})

const loading = ref(false)
// null => on suit la prop ; true/false => état optimiste temporaire
const optimistic = ref(null) // null | boolean

const displayedFavorite = computed(() =>
    optimistic.value === null ? props.isFavorite : optimistic.value
)

function toggleFavorite() {
    if (loading.value) return
    loading.value = true

    const was = displayedFavorite.value
    const method = was ? 'delete' : 'post'
    const url = route(was ? 'favorites.destroy' : 'favorites.store', props.tripId)

    // UI optimiste immédiate
    optimistic.value = !was

    router[method](url, {}, {
        preserveScroll: true,
        onError: () => {
            // rollback si erreur
            optimistic.value = was
        },
        onSuccess: () => {
            // 🔄 Reload CONTEXTUEL + état non préservé pour forcer de nouvelles props
            const path = window.location.pathname
            const only = path.includes('/voyages') ? ['trips'] : ['trip']

            router.reload({
                only,
                preserveScroll: true,
                preserveState: false, // <- clé pour éviter l’état coincé
                onSuccess: () => {
                    // on repasse en mode "prop source de vérité"
                    optimistic.value = null
                },
            })
        },
        onFinish: () => {
            loading.value = false
        },
    })
}
</script>

<template>
    <button
        @click.stop.prevent="toggleFavorite"
        :disabled="loading"
        class="p-1 rounded-full bg-white/20 hover:bg-white/30 transition"
        :title="displayedFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'"
    >
    <span
        class="material-symbols-rounded text-white text-[18px] transition-all duration-200"
        :class="displayedFavorite ? 'text-yellow-400 scale-110' : 'opacity-90'"
    >
      {{ displayedFavorite ? 'bookmark' : 'bookmark_add' }}
    </span>
    </button>
</template>
