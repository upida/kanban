<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableActions from '@/Components/TableActions.vue';
import TableCreate from '@/Components/TableCreate.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    statuses: {
        type: Array,
        required: true,
    },
});

const columns = {
    id: [],
    name: ['editable', 'string'],
    notification: ['editable', 'boolean'],
    created_at: [],
    updated_at: [],
}

function addData(data, nextAction, errorAction) {
    data.project_id = 14;
    router.post('/statuses', data, {
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

function editData(row, nextAction, errorAction) {
    router.patch('/statuses/' + row.id, row, {
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
    router.delete('/statuses/' + row.id, {
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
    <Head title="Status" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Status
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <h3
                    class="text-xl font-semibold leading-tight text-gray-800"
                >
                    Create a new status
                </h3>
                <TableCreate :columns="columns" @addData="addData" />

                <h3
                    class="text-xl font-semibold leading-tight text-gray-800"
                >
                    Statuses
                </h3>
                <TableActions :columns="columns" :data="statuses" @editData="editData" @deleteData="deleteData" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
