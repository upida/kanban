<script setup>
import TableCreate from '@/Components/TableCreate.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
});

const columns = {
    id: [],
    name: ['editable', 'string'],
    description: ['editable', 'string'],
    deadline: ['editable', 'date'],
    created_at: [],
    updated_at: [],
}

function addData(data, nextAction, errorAction) {
    router.post('/projects', data, {
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
</script>

<template>
    <Head title="Create Project" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Create Project
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <TableCreate :columns="columns" @addData="addData" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
