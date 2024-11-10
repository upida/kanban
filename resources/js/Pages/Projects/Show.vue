<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableCreate from '@/Components/TableCreate.vue';
import NavLink from '@/Components/NavLink.vue';
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    notifications: {
        type: Array,
        required: true,
    },
});

const newStatusName = ref('');
const newTaskName = ref('');
const isEditTaskModalVisible = ref(false);
const isEditStatusModalVisible = ref(false);
const currentTask = ref(null);
const currentStatus = ref(null);

const columns_status = {
    id: [],
    name: ['editable', 'string'],
    created_at: [],
    updated_at: [],
};

const showEditTaskModal = (task) => {
    currentTask.value = { ...task };
    isEditTaskModalVisible.value = true;
};

const showEditStatusModal = (status) => {
    currentStatus.value = { 
        ...status,
    };
    isEditStatusModalVisible.value = true;
};

const hideEditTaskModal = () => {
    isEditTaskModalVisible.value = false;
};

const hideEditStatusModal = () => {
    isEditStatusModalVisible.value = false;
};

const updateTask = () => {
    if (currentTask.value) {
        router.patch(`/projects/${props.project.id}/tasks/${currentTask.value.id}`, currentTask.value, {
            preserveScroll: true,
            onSuccess: () => {
                router.get('/projects/' + props.project.id, {}, {
                    preserveScroll: true,
                });
                hideEditTaskModal();
            },
            onError: (error) => {
                console.error('Error updating task:', error);
            },
        });
    }
};

const updateStatus = () => {
    if (currentStatus.value) {
        router.patch(`/projects/${props.project.id}/statuses/${currentStatus.value.id}`, {
            name: currentStatus.value.name,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.get('/projects/' + props.project.id, {}, {
                    preserveScroll: true,
                });
                hideEditStatusModal();
            },
            onError: (error) => {
                console.error('Error updating status:', error);
            },
        });
    }
};

function deleteTask(task) {
    router.delete(`/projects/${props.project.id}/tasks/${task.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.get('/projects/' + props.project.id, {}, {
                preserveScroll: true,
            });
        },
        onError: (error) => {
            console.log('error', error);
        },
    });
}

function deleteStatus(status) {
    router.delete(`/projects/${props.project.id}/statuses/${status.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.get('/projects/' + props.project.id, {}, {
                preserveScroll: true,
            });
        },
        onError: (error) => {
            console.log('error', error);
        },
    });
}

function addStatus(data, nextAction, errorAction) {
    data.project_id = props.project.id;
    router.post('/projects/' + props.project.id + '/statuses', data, {
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

function addTask(status) {
    if (newTaskName.value.trim() !== '') {
        try {
            router.post(
                `/projects/${props.project.id}/tasks`,
                {
                    title: newTaskName.value,
                    description: 'Test Description',
                    deadline: '2024-10-26 10:00:00',
                    done: false,
                    status_id: status.id,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.get('/projects/' + props.project.id, {}, {
                            preserveScroll: true,
                        });
                    },
                    onError: (error) => {
                        console.log('error', error);
                    },
                }
            );
        } catch (error) {
            console.error("Error adding task:", error);
        }
    }
}
</script>

<template>
    <Head title="Status" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2 class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                    {{ project.name }}
                </h2>
                <div class="flex items-center gap-4">
                    <NavLink
                        :href="route('projects.notifications', { project: project.id })"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <v-icon icon="mdi-bell" class="mr-2" />
                        {{ project.notifications_count ?? 0 }} unread
                    </NavLink>
                    <NavLink
                        :href="route('projects.analytics', { project: project.id })"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <v-icon icon="mdi-chart-bar" class="mr-2" />
                        Analytics
                    </NavLink>
                    <NavLink
                        :href="route('projects.members', { project: project.id })"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <v-icon icon="mdi-account-multiple" class="mr-2" />
                        {{ project.members_count ?? 0 }} members
                    </NavLink>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <h3 class="text-xl font-semibold leading-tight text-gray-800">
                    Create a new status
                </h3>
                <TableCreate :columns="columns_status" @addData="addStatus" />

                <div class="flex flex-col gap-4 w-full">
                    <div class="flex gap-4 overflow-x-auto w-full pb-6">
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            class="flex-shrink-0 flex flex-col gap-4 bg-gray-100 p-4 rounded shadow-lg w-1/4"
                            style="width: 300px;"
                        >
                            <div class="flex justify-between">
                                <h3 class="font-semibold">{{ status.name }}</h3>
                                <div class="flex space-x-2">
                                    <button @click="showEditStatusModal(status)">
                                        <PencilIcon class="h-5 w-5 text-blue-500 hover:text-blue-700" />
                                    </button>
                                    <button @click="deleteStatus(status)">
                                        <TrashIcon class="h-5 w-5 text-red-500 hover:text-red-700" />
                                    </button>
                                </div>
                            </div>
                            <div>
                                <div
                                    v-for="task in status.tasks"
                                    :key="task.id"
                                    class="p-2 border rounded bg-white shadow mb-2 flex items-center justify-between"
                                >
                                    <span>{{ task.title }}</span>
                                    <div class="flex space-x-2">
                                        <button @click="showEditTaskModal(task)">
                                            <PencilIcon class="h-5 w-5 text-blue-500 hover:text-blue-700" />
                                        </button>
                                        <button @click="deleteTask(task)">
                                            <TrashIcon class="h-5 w-5 text-red-500 hover:text-red-700" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex mb-4">
                                <input 
                                    v-model="newTaskName" 
                                    type="text" 
                                    placeholder="Add new task" 
                                    class="border rounded p-2 flex-grow"
                                />
                                <button @click="addTask(status)" class="ml-2 p-2 bg-green-500 text-white rounded">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal untuk mengedit task -->
        <div v-if="isEditTaskModalVisible" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg w-1/3">
                <h3 class="text-lg font-semibold">Edit Task</h3>
                <input 
                    v-model="currentTask.title" 
                    type="text" 
                    class="border rounded p-2 w-full mt-2" 
                    placeholder="Task Title"
                />
                <textarea 
                    v-model="currentTask.description" 
                    class="border rounded p-2 w-full mt-2" 
                    placeholder="Description"
                ></textarea>
                <input 
                    v-model="currentTask.deadline" 
                    type="datetime-local" 
                    class="border rounded p-2 w-full mt-2"
                />
                <input 
                    v-model="currentTask.done" 
                    type="checkbox" 
                    class="border rounded p-2 w-full mt-2"
                />
                <select 
                    v-model="currentTask.status_id" 
                    class="border rounded p-2 w-full mt-2"
                >
                    <option v-for="status in props.statuses" :key="status.id" :value="status.id">
                        {{ status.name }}
                    </option>
                </select>
                <div class="flex justify-end mt-4">
                    <button @click="hideEditTaskModal" class="mr-2 p-2 bg-gray-500 text-white rounded">Cancel</button>
                    <button @click="updateTask" class="p-2 bg-blue-500 text-white rounded">Save</button>
                </div>
            </div>
        </div>

        <!-- Modal untuk mengedit status -->
        <div v-if="isEditStatusModalVisible" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg w-1/3">
                <h3 class="text-lg font-semibold">Edit Status</h3>
                <input 
                    v-model="currentStatus.name" 
                    type="text" 
                    class="border rounded p-2 w-full mt-2" 
                    placeholder="Status Name"
                />
                <div class="flex justify-end mt-4">
                    <button @click="hideEditStatusModal" class="mr-2 p-2 bg-gray-500 text-white rounded">Cancel</button>
                    <button @click="updateStatus" class="p-2 bg-blue-500 text-white rounded">Save</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
