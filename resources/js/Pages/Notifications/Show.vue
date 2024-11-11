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
    notification: {
        type: Object,
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
                    rounded="lg"
                    variant="flat"
                >
                    <v-card-item>
                        <v-card-title class="text-body-2 d-flex align-center">
                            {{ notification.message }}
                        </v-card-title>
                        <v-card-subtitle>
                            {{ notification.created_at }}
                        </v-card-subtitle>
                        <v-card-text>
                            Project: {{ notification.project?.name ?? 'No project' }}
                            <br>
                            Task: {{ notification.task?.title ?? 'No task' }}
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                v-if="notification.project"
                                text="Project"
                                @click="router.get('/projects/' + notification.project.id)"
                            ></v-btn>
                            <v-btn
                                v-if="notification.task"
                                text="Task"
                                @click="router.get('/projects/' + notification.project.id + '/tasks/' + notification.task.id)"
                            ></v-btn>
                        </v-card-actions>
                    </v-card-item>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>