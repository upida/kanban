<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NavLink from '@/Components/NavLink.vue';
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import moment from 'moment';

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
    members: {
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
const loading = ref({});

const formStatus = useForm({
    project_id: props.project.id,
    name: '',
});

const showEditTaskModal = (task) => {
    currentTask.value = { ...task };
    currentTask.value.members = currentTask.value.members.map(member => member.member_id);
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
        loading.value['updateTask'] = true;

        if (currentTask.value.deadline) {
            currentTask.value.deadline = moment(currentTask.value.deadline).format('YYYY-MM-DD HH:mm:ss');
        }
        router.patch(`/projects/${props.project.id}/tasks/${currentTask.value.id}`, currentTask.value, {
            preserveScroll: true,
            onSuccess: () => {
                router.get('/projects/' + props.project.id, {}, {
                    preserveScroll: true,
                });
                hideEditTaskModal();
                loading.value['updateTask'] = false;
            },
            onError: (error) => {
                console.error('Error updating task:', error);
                loading.value['updateTask'] = false;
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

function addStatus() {
    formStatus.post('/projects/' + props.project.id + '/statuses', {
        preserveScroll: true,
        onSuccess: () => {
            formStatus.reset();
        }
    });
}

function addTask(status) {
    if (newTaskName.value.trim() !== '') {
        try {
            router.post(
                `/projects/${props.project.id}/tasks`,
                {
                    title: newTaskName.value,
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

function optionMembers() {
    return props.members.map(member => {
        return {
            title: member.user.name,
            subtitle: member.user.email,
            value: member.id,
        }
    });
}
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2 class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                    {{ project.name }} {{ project.progress }}%
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
                <div class="flex flex-col gap-4 w-full">
                    <div class="flex gap-4 overflow-x-auto w-full pb-6">
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            class="flex-shrink-0 flex flex-col gap-4 bg-gray-100 p-4 rounded shadow-lg w-1/4"
                            style="width: 300px;"
                        >
                            <v-progress-linear
                                v-model="status.progress"
                                color="indigo-darken-2"
                            ></v-progress-linear>
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
                                    <span>
                                        <v-icon
                                            v-if="task.done"
                                            icon="mdi-check"
                                            color="green"
                                            size="x-small"
                                        ></v-icon>
                                        {{ task.title }}
                                    </span>
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
                        <div
                            class="flex-shrink-0 flex flex-col gap-4 bg-gray-100 p-4 rounded shadow-lg w-1/4"
                            style="width: 300px;"
                        >
                            <div class="flex justify-between">
                                <v-text-field
                                    v-model="formStatus.name"
                                    label="Status Name"
                                    required
                                    appendInnerIcon="mdi-plus"
                                    @click:append-inner="addStatus"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal untuk mengedit task -->
        <div v-if="isEditTaskModalVisible" class="fixed inset-0 flex items-center justify-center background-black bg-opacity-50">
            <v-card class="bg-white p-6 rounded shadow-lg w-1/3 overflow-y-auto">
                <v-toolbar class="bg-white">
                    <v-toolbar-title>Edit Task</v-toolbar-title>
                    <v-toolbar-items>
                        <v-switch
                            v-model="currentTask.done"
                            :color="currentTask.done ? 'indigo' : 'gray'"
                            class="mr-4"
                        />
                    </v-toolbar-items>
                </v-toolbar>
                <v-card-text>
                    <v-text-field
                        v-model="currentTask.title"
                        label="Task Title"
                        required
                    />
                    <v-text-field
                        v-model="currentTask.description"
                        label="Description"
                        required
                    />
                    <div class="mt-2">
                        <label class="mr-3">Deadline</label>
                        <input
                            v-model="currentTask.deadline"
                            type="datetime-local"
                            label="deadline"
                            required
                        />
                    </div>
                    <div class="mt-5">
                        <label class="mr-3">Status</label>
                        <select
                            v-model="currentTask.status_id"
                            class="border rounded p-2 w-full mt-2"
                        >
                            <option v-for="status in props.statuses" :key="status.id" :value="status.id">
                                {{ status.name }}
                            </option>
                        </select>
                    </div>
                    <v-select :items="optionMembers()" label="Members" multiple v-model="currentTask.members" class="mt-8" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        text="Close"
                        @click="hideEditTaskModal"
                    />
                    <v-btn
                        v-if="!loading['updateTask']"
                        text="Save"
                        @click="updateTask"
                    />
                    <v-btn
                        v-else
                        class="ml-auto"
                    >
                        <v-progress-circular
                            color="indigo"
                            indeterminate
                            size="24"
                            width="2"
                        ></v-progress-circular>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </div>

        <!-- Modal untuk mengedit status -->
        <div v-if="isEditStatusModalVisible" class="fixed inset-0 flex items-center justify-center background-black bg-opacity-50">
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

<style scoped>

.background-black {
    background-color: rgb(0 0 0 / var(--tw-bg-opacity));
}

</style>