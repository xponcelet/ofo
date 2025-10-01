<script setup>
import { ref, computed, watch } from 'vue'
import TripMap from '@/Components/Trip/TripMap.vue'

const props = defineProps({
    steps: { type: Array, default: () => [] },
    initialActiveId: [Number, String],
})
const emit = defineEmits(['update:activeStep'])

const activeId = ref(
    props.initialActiveId ?? (props.steps.length ? props.steps[0].id : null)
)

const activeStep = computed(() =>
    props.steps.find((s) => s.id === activeId.value) || null
)

// ⚡ Informe le parent quand l’étape change
watch(activeStep, (s) => emit('update:activeStep', s))
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_3fr] gap-6">
        <!-- Étapes -->
        <aside class="lg:h-[calc(100vh-220px)]">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm h-full flex flex-col">
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">
                        Étapes
                    </h2>
                    <span class="text-xs text-gray-500">{{ steps.length }} total</span>
                </div>

                <!-- Liste -->
                <nav class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                    <button
                        v-for="s in steps"
                        :key="s.id"
                        @click="activeId = s.id"
                        class="w-full text-left rounded-xl px-4 py-3 transition flex items-center gap-3 border border-transparent"
                        :class="s.id === activeId
                            ? 'bg-emerald-50 border-emerald-200 ring-1 ring-emerald-300'
                            : 'hover:bg-gray-50 border-gray-100'"
                    >
                        <!-- Badge ordre -->
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border text-sm font-medium"
                            :class="s.id === activeId
                                ? 'border-emerald-400 text-emerald-700 bg-white shadow-sm'
                                : 'border-gray-300 text-gray-600 bg-gray-50'"
                        >
                            {{ s.order ?? '?' }}
                        </div>

                        <!-- Infos -->
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900">
                                {{ s.title || s.location }}
                            </p>
                            <p class="truncate text-xs text-gray-500">
                                {{ s.start_date }} → {{ s.end_date }}
                            </p>
                        </div>
                    </button>
                </nav>
            </div>
        </aside>

        <!-- Carte -->
        <section class="lg:h-[calc(100vh-220px)]">
            <div class="rounded-2xl border border-gray-200 overflow-hidden shadow-sm h-full">
                <TripMap
                    :steps="steps"
                    :active-id="activeId"
                    @update:activeId="activeId = $event"
                />
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Scrollbar discrète */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.15);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
</style>
