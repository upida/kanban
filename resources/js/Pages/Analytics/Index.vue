
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

// Define props from parent
const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
    total_projects: {
        type: Number,
        required: true
    },
    total_statuses: {
        type: Number,
        required: true
    },
    total_tasks: {
        type: Number,
        required: true
    },
    total_members: {
        type: Number,
        required: true
    }
});

function redirectToProject(project) {
    router.get('/projects/' + project.id);
}
</script>

<template>
    <Head :title="`Analytics`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                Analytics Overview
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Projects</h3>
                        <p class="text-2xl font-bold">{{ total_projects }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Statuses</h3>
                        <p class="text-2xl font-bold">{{ total_statuses }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Tasks</h3>
                        <p class="text-2xl font-bold">{{ total_tasks }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Members</h3>
                        <p class="text-2xl font-bold">{{ total_members }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold mb-4">Projects Activity</h3>
                    <v-timeline align="start">
                        <v-timeline-item
                            v-for="project in projects"
                            :key="project.id"
                            :color="project.started_at ? 'indigo' : 'gray'"
                            :icon="project.started_at ? 'mdi-calendar' : 'mdi-calendar-remove'"
                            fill-dot
                        >
                            <template #icon>
                                <v-icon
                                    :color="project.started_at ? 'indigo' : 'gray'"
                                    icon="mdi-calendar"
                                ></v-icon>
                            </template>
                            <v-card>
                                <v-progress-linear
                                    v-model="project.progress"
                                    color="indigo-darken-2"
                                ></v-progress-linear>
                                <v-card-title class="text-body-2 d-flex align-center">
                                    <v-icon
                                        :color="project.started_at ? 'indigo' : 'gray'"
                                        icon="mdi-calendar"
                                        size="x-small"
                                    ></v-icon>
                                    <span class="text-caption text-medium-emphasis ms-1 font-weight-light">
                                        {{ project.started_at + ' - ' + project.ended_at }}
                                    </span>
                                </v-card-title>
                                <v-card-subtitle>
                                    {{ project.name }}
                                </v-card-subtitle>
                                <v-card-text v-if="project.description">
                                    {{ project.description }}
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn
                                        text="View"
                                        @click="redirectToProject(project)"
                                    ></v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-timeline-item>
                    </v-timeline>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>