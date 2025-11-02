<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    users: Object,
})

function toggleBlock(user) {
    if (confirm(`Confirmer ${user.is_blocked ? 'le déblocage' : 'le blocage'} de ${user.name} ?`)) {
        router.patch(route('admin.users.toggle-block', user.id))
    }
}

function deleteUser(user) {
    if (confirm(`Supprimer définitivement ${user.name} ?`)) {
        router.delete(route('admin.users.destroy', user.id))
    }
}
</script>

<template>
    <Head title="Utilisateurs - Admin" />

    <div class="p-6 max-w-6xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">👥 Liste des utilisateurs</h1>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Nom</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Rôle</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">État</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Créé le</th>
                    <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr
                    v-for="user in users.data"
                    :key="user.id"
                    class="hover:bg-gray-50"
                >
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                        {{ user.name }}
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ user.email }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700 capitalize">
                        {{ user.role }}
                    </td>
                    <td class="px-4 py-2 text-sm">
                            <span
                                :class="user.is_blocked
                                    ? 'bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold'
                                    : 'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold'"
                            >
                                {{ user.is_blocked ? 'Bloqué' : 'Actif' }}
                            </span>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-500">
                        {{ new Date(user.created_at).toLocaleDateString() }}
                    </td>
                    <td class="px-4 py-2 text-sm text-right space-x-2">
                        <button
                            @click="toggleBlock(user)"
                            class="text-indigo-600 hover:underline"
                        >
                            {{ user.is_blocked ? 'Débloquer' : 'Bloquer' }}
                        </button>
                        <button
                            @click="deleteUser(user)"
                            class="text-red-600 hover:underline"
                        >
                            Supprimer
                        </button>
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
