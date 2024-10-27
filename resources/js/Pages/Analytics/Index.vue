<template>
    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                Analytics Overview
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Projects</h3>
                        <p class="text-2xl font-bold">{{ projectsCount }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Statuses</h3>
                        <p class="text-2xl font-bold">{{ statusesCount }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold">Total Tasks</h3>
                        <p class="text-2xl font-bold">{{ tasksCount }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold mb-4">Project Activity</h3>
                    <Bar :chart-data="chartData" :options="chartOptions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
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
    projectsCount: {
        type: Number,
        required: true,
    },
    statusesCount: {
        type: Number,
        required: true,
    },
    tasksCount: {
        type: Number,
        required: true,
    },
    projects: {
        type: Array,
        required: true,
    },
});

// Use reactive for chart data
const chartData = reactive({
    labels: [],
    datasets: [
        {
            label: 'Number of Statuses',
            data: [],
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
        }
    ],
});

// Watch for changes in projects and update chartData
watch(() => props.projects, (newProjects) => {
    if (newProjects && newProjects.length > 0) {
        chartData.labels = newProjects.map(project => project.name); // Project names
        chartData.datasets[0].data = newProjects.map(project => project.statuses.length); // Count of statuses
    } else {
        // Handle the case when there are no projects
        chartData.labels = [];
        chartData.datasets[0].data = [];
    }
}, { immediate: true }); // Run immediately to set initial state

// Chart options for customization
const chartOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Number of Statuses per Project',
        },
    },
};
</script>
