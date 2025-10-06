<script setup>
import { t } from '@/Composables/useTranslations'

const props = defineProps({
    step: { type: Object, required: false },
})
</script>

<template>
    <section v-if="step" class="rounded-2xl border border-gray-200 bg-white p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">{{ step.title }}</h3>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">
                {{ t('step.day') }} {{ step.day }}
            </span>
        </div>

        <div v-if="step.description" class="prose prose-sm max-w-none">
            <p class="whitespace-pre-line text-gray-700">{{ step.description }}</p>
        </div>

        <p v-else class="text-sm text-gray-500">
            {{ t('step.no_description') }}
        </p>
    </section>

    <p v-else class="text-sm text-gray-500">
        {{ t('step.no_step') }}
    </p>
</template>
