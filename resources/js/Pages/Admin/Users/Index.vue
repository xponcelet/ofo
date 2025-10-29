<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    users: Object
})
</script>

<template>
    <Head title="Utilisateurs - Admin" />
        <div class="p-6 max-w-6xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">👤 Liste des utilisateurs</h1>

            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Nom</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Rôle</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Créé le</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-700">{{ user.id }}</td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ user.name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ user.email }}</td>
                        <td class="px-4 py-2 text-sm">
                            <span
                                :class="user.role === 'admin'
                                ? 'bg-pink-100 text-pink-800 px-2 py-1 rounded-full text-xs font-semibold'
                                : 'bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs font-semibold'"
                            >
                                {{ user.role }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">
                            {{ new Date(user.created_at).toLocaleDateString() }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center flex-wrap gap-2">
                <template v-for="link in users.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-1 rounded text-sm transition"
                        :class="[
                link.active
                    ? 'bg-pink-600 text-white font-semibold'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="px-3 py-1 rounded text-sm bg-gray-50 text-gray-400 cursor-not-allowed"
                    />
                </template>
            </div>

        </div>
</template>
