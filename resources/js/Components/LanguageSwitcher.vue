<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
    placement: { type: String, default: 'bottom' }, // bottom ou top
})

const open = ref(false)

const locales = [
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'nl', label: 'Nederlands', flag: '🇧🇪' },
]

const page = usePage()
const activeLocale = computed(() => page.props.locale || document.documentElement.lang || 'fr')

const active = computed(() => locales.find(l => l.code === activeLocale.value) || locales[0])
</script>

<template>
    <div class="relative inline-block text-left">
        <!-- Bouton principal -->
        <button
            type="button"
            @click="open = !open"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-surface text-on-surface shadow-sm hover:bg-surface-variant transition"
        >
            <span>{{ active.flag }}</span>
            <span class="text-sm font-medium">{{ active.label }}</span>
        </button>

        <!-- Menu déroulant -->
        <div
            v-if="open"
            class="absolute right-0 w-40 origin-top-right rounded-xl shadow-lg bg-surface border border-outline z-50"
            :class="placement === 'top' ? 'bottom-full mb-2' : 'mt-2'"
        >
            <div class="py-1">
                <Link
                    v-for="locale in locales"
                    :key="locale.code"
                    :href="route('lang.switch', locale.code)"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-on-surface hover:bg-primary hover:text-primary-foreground transition rounded-lg"
                    @click="open = false"
                >
                    <span>{{ locale.flag }}</span>
                    <span>{{ locale.label }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
