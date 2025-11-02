<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const visible = ref(false)
const message = ref('')
const type = ref('info')

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.error) {
            message.value = flash.error
            type.value = 'error'
        } else if (flash.success) {
            message.value = flash.success
            type.value = 'success'
        } else if (flash.info) {
            message.value = flash.info
            type.value = 'info'
        } else {
            visible.value = false
            return
        }

        visible.value = true
        setTimeout(() => (visible.value = false), 5000)
    },
    { deep: true }
)
</script>

<template>
    <transition name="fade">
        <div
            v-if="visible"
            class="fixed bottom-6 right-6 z-50 px-5 py-4 rounded-xl shadow-lg text-white text-sm font-medium"
            :class="{
        'bg-red-600': type === 'error',
        'bg-green-600': type === 'success',
        'bg-blue-600': type === 'info',
      }"
        >
            {{ message }}
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
