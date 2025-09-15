<script setup>
import { ref, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StepList from '@/Components/trip/StepList.vue'
import StepDetail from '@/Components/trip/StepDetail.vue'
import StepsMap from '@/Components/trip/StepsMap.vue'

const props = defineProps({
    trip: { type: Object, required: true },
    steps: { type: Array, default: () => [] },
})

const activeId = ref(props.steps?.[0]?.id ?? null)
const activeStep = computed(() => props.steps.find(s => s.id === activeId.value) || null)

watch(() => props.steps, (arr) => {
    if (!arr?.length) { activeId.value = null; return }
    if (!arr.some(s => s.id === activeId.value)) activeId.value = arr[0].id
})
</script>

<template>
    <AppLayout :title="trip.title">
        <Head :title="trip.title" />
        <div class="mx-auto max-w-7xl px-4 py-6 lg:py-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">{{ trip.title }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ trip.country }} ·
                        <span v-if="trip.start_date && trip.end_date">
              {{ trip.start_date }} → {{ trip.end_date }}
            </span>
                    </p>
                </div>
                <span class="hidden sm:inline-block rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
          Public
        </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,520px)_1fr] gap-4 lg:gap-6">
                <aside class="lg:h-[calc(100vh-180px)] lg:sticky lg:top-24">
                    <div class="rounded-2xl border border-gray-200 bg-white">
                        <StepList :steps="steps" :active-id="activeId" @select="id => activeId = id" />
                    </div>
                </aside>

                <section class="lg:h-[calc(100vh-180px)]">
                    <div class="rounded-2xl border border-gray-200 bg-white lg:h-full lg:overflow-y-auto">
                        <StepDetail v-if="activeStep" :step="activeStep" />
                        <div v-else class="p-6 text-sm text-gray-500">Aucune étape.</div>
                    </div>
                </section>

                <section class="h-[420px] lg:h-[calc(100vh-180px)]">
                    <div class="rounded-2xl border border-gray-200 overflow-hidden bg-white h-full">
                        <StepsMap :steps="steps" :active-id="activeId" class="h-full" @focusStep="id => activeId = id" />
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
