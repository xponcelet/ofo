<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const page = usePage()
const isAuth = computed(() => !!page.props?.auth?.user)
</script>

<template>
    <component :is="isAuth ? AppLayout : GuestLayout">
        <slot />
    </component>
</template>
