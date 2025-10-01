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
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_3fr] gap-4 lg:gap-6">
        <!-- Étapes -->
        <aside class="lg:h-[calc(100vh-220px)]">
            <div class="rounded-2xl border border-gray-200 bg-white h-full">
                <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-sm font-semibold tracking-wide text-gray-700">
                        Étapes
                    </h2>
                    <span class="text-xs text-gray-500">{{ steps.length }} total</span>
                </div>
                <nav class="max-h-[60vh] overflow-y-auto p-2">
                    <button
                        v-for="s in steps"
                        :key="s.id"
                        @click="activeId = s.id"
                        class="w-full text-left rounded-xl px-3 py-3 mb-1 transition hover:bg-gray-50 flex items-center gap-3"
                        :class="s.id === activeId ? 'bg-emerald-50 ring-1 ring-emerald-200' : ''"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border"
                            :class="s.id === activeId
                ? 'border-emerald-400 text-emerald-700'
                : 'border-gray-300 text-gray-600'"
                        >
                            {{ s.order ?? '?' }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900">
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
            <TripMap
                :steps="steps"
                :active-id="activeId"
                @update:activeId="activeId = $event"
            />
        </section>
    </div>
</template>
