<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Banner from '@/Components/Banner.vue'

defineProps({
    title: { type: String, default: '' },
})

const page = usePage()
const isAuth = computed(() => !!page.props?.auth?.user)
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-white">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <!-- On injecte ici TOUTE la nav -->
                        <component :is="isAuth ? AppLayout : GuestLayout" :title="title" />
                    </div>
                </div>
            </nav>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
