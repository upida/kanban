<script setup>
import { onMounted, ref, reactive } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import TextArea from "@/Components/TextArea.vue";

import TextInput from "@/Components/TextInput.vue";
import NavLink from "@/Components/NavLink.vue";
import {
    PencilIcon,
    TrashIcon,
    EllipsisHorizontalIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";
import moment from "moment";
import draggable from "vuedraggable";

const selectedStatus = ref(null);
const formTask = useForm({
    title: "",
    description: "",
    deadline: "",
    status_id: "",
    members: [],
    done: false,
});
const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    task: {
        type: Object,
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

const newTaskName = ref("");
const isEditTaskModalVisible = ref(false);
const isEditStatusModalVisible = ref(false);
const currentTask = ref(null);
const currentStatus = ref(null);
const loading = ref({});

const formStatus = useForm({
    project_id: props.project.id,
    name: "",
});

const state = reactive({
    editing: false,
});
const showEditTaskModal = (task) => {
    Object.assign(formTask, task);
    state.editing = true;
    console.log("Task:", task);
    formTask.members = task.members.map((member) => member.member_id);
    modal.task = true;
};

const hideEditTaskModal = () => {
    modal.task = false;
    router.get("/projects/" + props.project.id, {}, { preserveState: true });
};

const hideEditStatusModal = () => {
    modal.status = false;
    state.editing = false;
};

const copiedStatuses = ref([]);

const moveTask = (evt) => {
    const fromBoard = evt.from.dataset.boardId; // Board name where item started
    // const movedItem = props.statuses.find(
    //     (st) => Number(st.id) == Number(fromBoard)
    // ).tasks[evt.oldIndex];
    const fromStatus = copiedStatuses.value.find(
        (st) => Number(st.id) === Number(fromBoard)
    );

    if (!fromStatus) {
        console.error("Board asal nggak ketemu:", fromBoard);
        return;
    }
    const movedItem = fromStatus.tasks[evt.oldIndex];

    if (!movedItem) {
        console.error("Task di index lama nggak ketemu:", evt.oldIndex);
        return;
    }
    const toBoard = evt.to.dataset.boardId; // Board tujuan
    console.log("Pindah dari board:", fromBoard, "ke:", toBoard);
    console.log("Task yang dipindah:", movedItem);

    // currentTask.value = movedItem;
    Object.assign(formTask, movedItem);
    formTask.status_id = Number(toBoard);
    // currentTask.value.status_id = Number(toBoard);
    updateTask();
};

const modal = reactive({
    status: false,
    task: false,
});

const updateTask = () => {
    if (formTask) {
        loading.value["updateTask"] = true;

        if (formTask.deadline) {
            formTask.deadline = moment(formTask.deadline).format(
                "YYYY-MM-DD HH:mm:ss"
            );
        }

        console.log("Form Task:", formTask.members);
        router.patch(
            `/projects/${props.project.id}/tasks/${formTask.id}`,
            formTask,
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.get(
                        "/projects/" + props.project.id,
                        {},
                        {
                            preserveScroll: true,
                        }
                    );
                    hideEditTaskModal();
                    copiedStatuses.value = JSON.parse(
                        JSON.stringify(props.statuses)
                    );
                    loading.value["updateTask"] = false;
                },
                onError: (error) => {
                    console.error("Error updating task:", error);
                    loading.value["updateTask"] = false;
                },
            }
        );
    }
};

const updateStatus = () => {
    if (formStatus.name) {
        console.log({ formStatus });
        router.patch(
            `/projects/${props.project.id}/statuses/${formStatus.id}`,
            { name: formStatus.name },
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.get(
                        "/projects/" + props.project.id,
                        {},
                        {
                            preserveScroll: true,
                        }
                    );
                    hideEditStatusModal();
                },
                onError: (error) => {
                    console.error("Error updating status:", error);
                },
            }
        );
    }
};
const drag = ref(false);

function deleteTask(task) {
    router.delete(`/projects/${props.project.id}/tasks/${task.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.get(
                "/projects/" + props.project.id,
                {},
                {
                    preserveScroll: true,
                }
            );
        },
        onError: (error) => {
            console.log("error", error);
        },
    });
}

function deleteStatus(status) {
    router.delete(`/projects/${props.project.id}/statuses/${status.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.get(
                "/projects/" + props.project.id,
                {},
                {
                    preserveScroll: true,
                }
            );
        },
        onError: (error) => {
            console.log("error", error);
        },
    });
}

function addStatus() {
    formStatus.post("/projects/" + props.project.id + "/statuses", {
        preserveScroll: true,
        onSuccess: () => {
            formStatus.reset();
            modal.status = false;
        },
    });
}

const clearTask = () => {
    formTask.title = "";
    formTask.description = "";
    formTask.deadline = "";
    formTask.status_id = "";
    formTask.members = [];
    formTask.done = false;
};
function addTask(status) {
    if (formTask.title) {
        const payload = {
            ...formTask,
            deadline: moment(formTask.deadline).format("YYYY-MM-DD HH:mm:ss"),
            status_id: status.id,
        };
        console.log("Payload:", payload);
        try {
            router.post(`/projects/${props.project.id}/tasks`, payload, {
                preserveScroll: true,
                onSuccess: () => {
                    router.get(
                        "/projects/" + props.project.id,
                        {},
                        {
                            preserveScroll: true,
                        }
                    );
                    clearTask();
                    editBoard.value = null;
                },
                onError: (error) => {
                    console.log("error", error);
                },
            });
        } catch (error) {
            console.error("Error adding task:", error);
        }
    }
}

function optionMembers() {
    return props.members.map((member) => {
        return {
            title: member.user.name,
            subtitle: member.user.email,
            value: member.id,
        };
    });
}

const editBoard = ref();
onMounted(() => {
    if (props.task) {
        showEditTaskModal(props.task);
    }
    if (props.statuses) {
        copiedStatuses.value = JSON.parse(JSON.stringify(props.statuses));
    }
});
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2
                    class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800"
                >
                    {{ project.name }} {{ project.progress }}%
                </h2>
                <div class="flex items-center gap-4">
                    <NavLink
                        :href="
                            route('projects.notifications', {
                                project: project.id,
                            })
                        "
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <v-icon icon="mdi-bell" class="mr-2" />
                        {{ project.notifications_count ?? 0 }} unread
                    </NavLink>
                    <NavLink
                        :href="
                            route('projects.members', { project: project.id })
                        "
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
                    <div
                        class="flex gap-4 overflow-x-auto w-full pb-6 h-screen"
                    >
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            class="flex-shrink-0 flex flex-col gap-4 bg-gray-100 p-4 rounded w-1/4"
                            style="width: 300px"
                        >
                            <div
                                class="flex justify-between bg-white items-center rounded-md px-2 py-1"
                            >
                                <div class="flex items-center">
                                    <h3
                                        class="font-semibold text-gray-800 w-full"
                                    >
                                        {{ status.name }}
                                    </h3>
                                    <span class="text-gray-500 ml-1 text-sm">
                                        ({{ status.tasks.length }})</span
                                    >
                                </div>

                                <div class="flex space-x-2">
                                    <v-menu elevated="0">
                                        <template v-slot:activator="{ props }">
                                            <button v-bind="props">
                                                <!-- @click="showEditStatusModal(status)" -->
                                                <EllipsisHorizontalIcon
                                                    class="h-5 w-5 text-gray-700 hover:text-blue-700"
                                                />
                                                <!-- <PencilIcon
                                            class="h-5 w-5 text-blue-500 hover:text-blue-700"
                                        /> -->
                                            </button>
                                        </template>
                                        <v-list elevation="1" density="compact">
                                            <v-list-item>
                                                <button
                                                    @click="
                                                        modal.status = true;
                                                        Object.assign(
                                                            formStatus,
                                                            status
                                                        );
                                                        state.editing = true;
                                                    "
                                                >
                                                    Edit
                                                </button>
                                            </v-list-item>
                                            <v-list-item
                                                @click="
                                                    modal.delete = true;
                                                    selectedStatus = status;
                                                "
                                            >
                                                Delete
                                            </v-list-item>
                                        </v-list>
                                    </v-menu>

                                    <button
                                        @click="
                                            modal.task = true;
                                            selectedStatus = status;
                                        "
                                    >
                                        <!-- @click="deleteStatus(status)" -->
                                        <PlusIcon
                                            class="h-4 w-4 text-gray-700 hover:text-blue-700"
                                        />
                                        <!-- <TrashIcon
                                            class="h-5 w-5 text-red-500 hover:text-red-700"
                                        /> -->
                                    </button>
                                </div>
                            </div>
                            <v-progress-linear
                                v-model="status.progress"
                                color="indigo-darken-2"
                                class="rounded-md"
                            ></v-progress-linear>

                            <draggable
                                :data-board-id="status.id"
                                :list="status.tasks"
                                :group="{
                                    name: 'status',
                                    pull: true,
                                    put: true,
                                }"
                                @end="moveTask"
                                item-key="id"
                            >
                                <template #item="{ element }">
                                    <div
                                        class="p-2 border border-gray-100 rounded bg-none mb-2 flex items-center cursor-pointer justify-between bg-white"
                                    >
                                        <span>
                                            <v-icon
                                                v-if="element.done"
                                                icon="mdi-check"
                                                color="green"
                                                size="x-small"
                                            ></v-icon>
                                            {{ element.title }}
                                        </span>
                                        <div class="flex space-x-2">
                                            <button
                                                @click="
                                                    showEditTaskModal(element)
                                                "
                                            >
                                                <PencilIcon
                                                    class="h-5 w-5 text-blue-500 hover:text-blue-700"
                                                />
                                            </button>
                                            <button
                                                @click="deleteTask(element)"
                                            >
                                                <TrashIcon
                                                    class="h-5 w-5 text-red-500 hover:text-red-700"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </draggable>

                            <div
                                v-if="editBoard && editBoard == status"
                                class="flex mb-4"
                            >
                                <input
                                    v-model="newTaskName"
                                    type="text"
                                    placeholder="Add new task"
                                    class="border rounded p-2 flex-grow"
                                />
                                <button
                                    @click="addTask(status)"
                                    class="ml-2 p-2 bg-green-500 text-white rounded"
                                >
                                    Add
                                </button>
                            </div>
                        </div>
                        <div
                            @click="modal.status = true"
                            class="flex relative flex-col gap-4 hover:bg-gray-100 bg-white cursor-pointer border items-center justify-center h-3/4 border-dashed p-4 rounded w-1/5"
                        >
                            <v-icon
                                color="disabled"
                                icon="mdi-plus"
                                size="large"
                            ></v-icon>

                            <!-- <div class="flex justify-between">
                                <v-text-field
                                    v-model="formStatus.name"
                                    label="Status Name"
                                    required
                                    appendInnerIcon="mdi-plus"
                                    @click:append-inner="addStatus"
                                />
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal untuk mengedit task -->
        <div
            v-if="isEditTaskModalVisible"
            class="fixed inset-0 flex items-center justify-center background-black bg-opacity-50"
        >
            <v-card
                class="bg-white p-6 rounded shadow-lg w-1/3 overflow-y-auto"
            >
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
                            <option
                                v-for="status in props.statuses"
                                :key="status.id"
                                :value="status.id"
                            >
                                {{ status.name }}
                            </option>
                        </select>
                    </div>

                    <v-select
                        :items="optionMembers()"
                        label="Members"
                        multiple
                        v-model="currentTask.members"
                        class="mt-8"
                    />
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text="Close" @click="hideEditTaskModal" />
                    <v-btn
                        v-if="!loading['updateTask']"
                        text="Save"
                        @click="updateTask"
                    />
                    <v-btn v-else class="ml-auto">
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
        <div
            v-if="isEditStatusModalVisible"
            class="fixed inset-0 flex items-center justify-center background-black bg-opacity-50"
        >
            <div class="bg-white p-6 rounded shadow-lg w-1/3">
                <h3 class="text-lg font-semibold">Edit Status</h3>
                <input
                    v-model="currentStatus.name"
                    type="text"
                    class="border rounded p-2 w-full mt-2"
                    placeholder="Status Name"
                />
                <div class="flex justify-end mt-4">
                    <button
                        @click="hideEditStatusModal"
                        class="mr-2 p-2 bg-gray-500 text-white rounded"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateStatus"
                        class="p-2 bg-blue-500 text-white rounded"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
        <Modal :show="modal.status" @close="modal.status = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ state.editing ? "Edit status" : "Create new status" }}
                </h2>

                <div class="mt-6">
                    <label for="">Status Name</label>
                    <TextInput v-model="formStatus.name" />
                </div>

                <div class="mt-6 flex justify-end">
                    <div
                        class="flex justify-between items-center w-full space-x-5"
                    >
                        <v-btn
                            @click="
                                formStatus.name = '';
                                modal.status = false;
                            "
                            class="flex-grow"
                            color="red"
                            variant="outlined"
                        >
                            Cancel
                        </v-btn>
                        <v-btn
                            v-if="state.editing"
                            @click="updateStatus"
                            class="flex-grow"
                            flat
                            color="black"
                        >
                            Update Status
                        </v-btn>
                        <v-btn
                            v-else
                            @click="addStatus"
                            class="flex-grow"
                            flat
                            color="black"
                        >
                            Create Status
                        </v-btn>
                    </div>
                </div>
            </div>
        </Modal>
        <Modal :show="modal.delete" @close="modal.delete = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this status?
                </h2>
                <p>Your action will delete all the tasks inside</p>
                <div class="mt-6 flex justify-end">
                    <div
                        class="flex justify-between items-center w-full space-x-5"
                    >
                        <v-btn
                            @click="modal.delete = false"
                            class="flex-grow"
                            color="black"
                            variant="outlined"
                        >
                            Cancel
                        </v-btn>

                        <v-btn
                            @click="deleteStatus(selectedStatus)"
                            class="flex-grow"
                            flat
                            color="red"
                        >
                            Delete Status
                        </v-btn>
                    </div>
                </div>
            </div>
        </Modal>
        <v-navigation-drawer
            width="480"
            elevation="0"
            v-model="modal.task"
            location="right"
            temporary
        >
            <div class="flex flex-col p-5 space-y-5">
                <div class="flex justify-between items-center">
                    <v-list-subheader>{{
                        state.editing ? "Edit task" : "Add a new task"
                    }}</v-list-subheader>
                    <v-btn
                        icon="mdi-close"
                        flat
                        @click="
                            modal.task = false;
                            state.editing = false;
                        "
                    ></v-btn>
                </div>
                <v-divider></v-divider>
                <div class="grid grid-cols-3 gap-4 items-start">
                    <label for="">Task name</label>
                    <TextInput class="col-span-2" v-model="formTask.title" />
                    <label for="">Assignees</label>
                    <v-combobox
                        v-model="formTask.members"
                        :items="optionMembers()"
                        class="col-span-2"
                        chips
                        clearable
                        density="compact"
                        multiple
                    >
                        <template
                            v-slot:selection="{ attrs, item, select, selected }"
                        >
                            <v-chip
                                v-bind="attrs"
                                :model-value="selected"
                                closable
                                @click="select"
                                @click:close="remove(item)"
                            >
                                <strong>{{ item }}</strong
                                >&nbsp;
                                <span>(interest)</span>
                            </v-chip>
                        </template>
                    </v-combobox>

                    <label for="">Due date</label>
                    <VueDatePicker
                        v-model="formTask.deadline"
                        class="z-50 relative col-span-2"
                        auto-apply
                        :enable-time-picker="false"
                        :auto-position="false"
                        teleport-center
                    />
                    <label for="">Description</label>
                    <TextArea
                        class="col-span-2"
                        v-model="formTask.description"
                    />
                </div>

                <div class="flex justify-between items-center w-full space-x-5">
                    <v-btn
                        @click="clearTask"
                        class="flex-grow"
                        color="red"
                        variant="outlined"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        v-if="state.editing"
                        @click="updateTask"
                        class="flex-grow"
                        flat
                        color="black"
                    >
                        Update Task
                    </v-btn>
                    <v-btn
                        v-else
                        @click="addTask(selectedStatus)"
                        class="flex-grow"
                        flat
                        color="black"
                    >
                        Create Task
                    </v-btn>
                </div>
            </div>

            <v-card-text> </v-card-text>
        </v-navigation-drawer>
    </AuthenticatedLayout>
</template>

<style scoped>
.background-black {
    background-color: rgb(0 0 0 / var(--tw-bg-opacity));
}
</style>
