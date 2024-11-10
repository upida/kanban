<script setup>
import TableCreate from '@/Components/TableCreate.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import moment from 'moment';

defineProps({
});

const columns = {
    id: [],
    name: ['editable', 'string'],
    description: ['editable', 'string'],
    started_at: ['editable', 'date'],
    ended_at: ['editable', 'date'],
    created_at: [],
    updated_at: [],
}

function addData(data, nextAction, errorAction) {
    if (!data.description) delete data.description;
    data.started_at = moment(data.started_at).format('YYYY-MM-DD HH:mm:ss');
    data.ended_at = moment(data.ended_at).format('YYYY-MM-DD HH:mm:ss');

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
