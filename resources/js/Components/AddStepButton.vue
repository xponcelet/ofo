<template>
    <Link
        :href="href"
        class="inline-flex items-center px-3 py-1 rounded text-sm font-medium bg-blue-100 text-blue-700 hover:bg-blue-200"
    >
        {{ label || t('common.add_step') }}
    </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { t } from '@/Composables/useTranslations'

const props = defineProps({
    tripId: { type: [Number, String], required: true },
    afterId: { type: [Number, String], default: null },
    atStart: { type: Boolean, default: false },
    label: { type: String, default: '' },
})

const href = computed(() => {
    let url = `/trips/${props.tripId}/steps/create`
    if (props.afterId !== null && props.afterId !== undefined) {
        url += `?after=${props.afterId}`
    } else if (props.atStart) {
        url += `?at_start=1`
    }
    return url
})
</script>
