<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import moment from 'moment';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    notifications: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Notifications - ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2 @click="router.get('/projects/' + project.id)" class="cursor-pointer flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                    {{ project.name }} Notifications
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <v-card
                    v-for="notification in notifications"
                    :key="notification.id"
                    rounded="lg"
                    variant="flat"
                    @click="router.get('/projects/' + project.id + '/notifications/' + notification.id)"
                >
                    <v-card-item>
                        <v-card-title class="text-body-2 d-flex align-center">
                            <v-icon
                                :color="notification.read ? 'green' : 'red'"
                                icon="mdi-bell"
                                size="x-small"
                            ></v-icon>
                            <span class="text-caption text-medium-emphasis ms-1 font-weight-light">
                                {{ notification.message }}
                            </span>
                        </v-card-title>
                        <v-card-subtitle>
                            {{ notification.created_at }}
                        </v-card-subtitle>
                    </v-card-item>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
