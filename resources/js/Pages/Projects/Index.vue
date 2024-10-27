<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import TableActions from '@/Components/TableActions.vue';

const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
});

const filterText = ref('');

const filteredProjects = computed(() => {
    if (!filterText.value) {
        return props.projects; // return all projects if no filter
    }
    return props.projects.filter(project => {
        return (
            project.name.toLowerCase().includes(filterText.value.toLowerCase()) ||
            project.description.toLowerCase().includes(filterText.value.toLowerCase()) ||
            project.deadline.toLowerCase().includes(filterText.value.toLowerCase())
        );
    });
});

const columns = {
    name: ['editable', 'string'],
    description: ['editable', 'string'],
    deadline: ['editable', 'date'],
}

function editData(row, nextAction, errorAction) {
    router.patch('/projects/' + row.id, row, {
        preserveScroll: true,
        onSuccess: () => {
            nextAction();
        },
        onError: (error) => {
            console.log('error', error);
            errorAction(error);
        },
    });
}

function deleteData(row, nextAction) {
    router.delete('/projects/' + row.id, {
        preserveScroll: true,
        onSuccess: () => {
            nextAction();
        },
        onError: (error) => {
            console.log('error', error);
            errorAction(error);
        },
    });
}

function show(row) {
    router.get('/projects/' + row.id);
}
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2 class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                    Projects
                </h2>

                <div class="flex items-center gap-4">
                    <NavLink
                        :href="route('projects.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Create Project
                    </NavLink>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Filter Input -->
                <div>
                    <input
                        v-model="filterText"
                        type="text"
                        placeholder="Filter projects..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                </div>
                
                <TableActions
                    :isShow="true"
                    :columns="columns"
                    :data="filteredProjects"
                    @editData="editData"
                    @deleteData="deleteData"
                    @show="show"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
