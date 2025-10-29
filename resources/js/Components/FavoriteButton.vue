<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    tripId: { type: Number, required: true },
    isFavorite: { type: Boolean, default: false },
})

const loading = ref(false)
const optimistic = ref(null)

const displayedFavorite = computed(() =>
    optimistic.value === null ? props.isFavorite : optimistic.value
)

function toggleFavorite() {
    if (loading.value) return
    loading.value = true

    const was = displayedFavorite.value
    const method = was ? 'delete' : 'post'
    const url = route(was ? 'favorites.destroy' : 'favorites.store', props.tripId)

    optimistic.value = !was

    router[method](url, {}, {
        preserveScroll: true,
        onError: () => (optimistic.value = was),
        onSuccess: () => {
            const path = window.location.pathname
            const only = path.includes('/voyages') ? ['trips'] : ['trip']

            router.reload({
                only,
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => (optimistic.value = null),
            })
        },
        onFinish: () => (loading.value = false),
    })
}
</script>

<template>
    <button
        @click.stop.prevent="toggleFavorite"
        :disabled="loading"
        class="p-2 rounded-full shadow-sm border transition
               bg-white hover:bg-gray-50
               hover:shadow-md focus:outline-none focus:ring-2 focus:ring-pink-400"
        :title="displayedFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'"
    >
        <span
            class="material-symbols-rounded text-[20px] transition-all duration-200"
            :class="displayedFavorite
                ? 'text-yellow-500 scale-110'
                : 'text-gray-500 hover:text-pink-500'"
        >
            {{ displayedFavorite ? 'bookmark' : 'bookmark_add' }}
        </span>
    </button>
</template>
