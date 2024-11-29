<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Dialog from "@/Components/Dialog.vue";

import { ref, computed, reactive } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import moment from "moment";
import { useHelper } from "@/helper";

const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
});

const user = usePage().props.auth.user;

const modal = reactive({
    detail: false,
    delete: false,
});
const loading = ref({});

props.projects.forEach((project) => {
    loading.value[project.id] = false;
});

const filterText = ref("");

const filteredProjects = computed(() => {
    if (!filterText.value) {
        return props.projects; // return all projects if no filter
    }
    return props.projects.filter((project) => {
        return (
            project.name
                .toLowerCase()
                .includes(filterText.value.toLowerCase()) ||
            project.description
                ?.toLowerCase()
                .includes(filterText.value.toLowerCase()) ||
            project.started_at
                .toLowerCase()
                .includes(filterText.value.toLowerCase()) ||
            project.ended_at
                .toLowerCase()
                .includes(filterText.value.toLowerCase())
        );
    });
});

const columns = {
    name: ["editable", "string"],
    description: ["editable", "string"],
    started_at: ["editable", "date"],
    ended_at: ["editable", "date"],
};

function toggelEdit(row) {}

function editData(row) {
    loading.value[row.id] = true;

    row.started_at = moment(row.started_at).format("YYYY-MM-DD HH:mm:ss");
    row.ended_at = moment(row.ended_at).format("YYYY-MM-DD HH:mm:ss");

    router.patch("/projects/" + row.id, row, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value[row.id] = false;
        },
        onError: (error) => {
            console.log("error", error);
            loading.value[row.id] = false;
        },
    });
}

function deleteData(row) {
    if (confirm(`Are you sure you want to delete ${row.name}?`)) {
        router.delete("/projects/" + row.id, {
            preserveScroll: true,
            onSuccess: () => {
                console.log("success");
            },
            onError: (error) => {
                console.log("error", error);
            },
        });
    }
}

const selectedData = ref();
const openDetail = (data) => {
    selectedData.value = data;
    modal.detail = true;
};
function show(row) {
    router.get("/projects/" + row.id);
}
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2
                    class="flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800"
                >
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
                <v-row>
                    <v-col
                        v-for="project in filteredProjects"
                        :key="project.id"
                        cols="12"
                        md="4"
                    >
                        <v-card
                            @click="openDetail(project)"
                            rounded="lg"
                            variant="flat"
                            :class="[
                                modal.detail &&
                                selectedData &&
                                Object.keys(selectedData).length > 0 &&
                                selectedData.id === project.id
                                    ? ' border-2 border-black bg-gray-100'
                                    : '',
                                'hover:cursor-pointer',
                            ]"
                        >
                            <v-card-item>
                                <v-card-title
                                    class="text-body-2 d-flex align-center"
                                >
                                    <v-icon
                                        :color="
                                            project.notifications_count > 0
                                                ? 'red-lighten-4'
                                                : 'disabled'
                                        "
                                        icon="mdi-bell"
                                        size="x-small"
                                    ></v-icon>

                                    <span
                                        class="text-caption text-medium-emphasis ms-1 font-weight-light"
                                    >
                                        {{ project.notifications_count }}
                                        unread
                                    </span>

                                    <v-spacer></v-spacer>
                                </v-card-title>

                                <div class="py-2 d-flex align-center">
                                    <div
                                        @click.prevent="show(project)"
                                        class="text-h6 hover:underline hover:cursor-pointer"
                                    >
                                        {{ project.name }}
                                    </div>
                                </div>
                                <div class="d-flex align-center">
                                    <div>
                                        <v-icon
                                            icon="mdi-calendar"
                                            size="small"
                                            start
                                        ></v-icon>
                                        <span
                                            class="text-medium-emphasis font-weight-bold"
                                            >Start:
                                            {{
                                                useHelper().formatDate(
                                                    project.started_at
                                                )
                                            }}</span
                                        >
                                    </div>

                                    <v-spacer></v-spacer>
                                    <div>
                                        <v-icon
                                            icon="mdi-bullseye-arrow"
                                            size="small"
                                            start
                                        ></v-icon>
                                        <span
                                            class="text-medium-emphasis font-weight-bold"
                                            >End:
                                            {{
                                                useHelper().formatDate(
                                                    project.ended_at
                                                )
                                            }}</span
                                        >
                                    </div>

                                    <!-- <v-btn
                                    prepend-icon="mdi-book"
                                    color="indigo-darken-4"
                                    variant="outlined"
                                    @click="show(project)"
                                >
                                    View
                                </v-btn> -->
                                </div>
                                <v-progress-linear
                                    class="my-3"
                                    v-model="project.progress"
                                    color="indigo-darken-2"
                                ></v-progress-linear>
                                <div
                                    class="text-caption text-medium-emphasis ms-1 font-weight-light"
                                >
                                    Last updated:
                                    <span class="font-weight-bold">{{
                                        useHelper().formatDate(
                                            project.updated_at
                                        )
                                    }}</span>
                                </div>
                            </v-card-item>
                        </v-card>
                    </v-col>
                </v-row>
            </div>
        </div>
        <!-- Drawer detail-->

        <v-navigation-drawer
            v-if="selectedData"
            width="480"
            elevation="0"
            v-model="modal.detail"
            location="right"
            temporary
        >
            <v-form>
                <v-list>
                    <div class="flex justify-between items-center">
                        <v-list-subheader>Project Detail</v-list-subheader>
                        <v-btn
                            icon="mdi-close"
                            flat
                            @click="modal.detail = false"
                        ></v-btn>
                    </div>

                    <v-list-item
                        ><label for="">Project Name</label>
                        <v-text-field
                            v-model="selectedData.name"
                            label="Name"
                            required
                        />
                        <v-divider></v-divider>
                    </v-list-item>
                    <v-list-item
                        ><label for="">Description</label>
                        <v-textarea
                            v-model="selectedData.description"
                            label="Description"
                            required
                        />
                        <v-divider></v-divider>
                    </v-list-item>
                    <v-list-item
                        ><label for="">Date</label>
                        <div>
                            <input
                                v-model="selectedData.started_at"
                                type="datetime-local"
                                label="started_at"
                                required
                            />

                            <input
                                v-model="selectedData.ended_at"
                                type="datetime-local"
                                label="ended_at"
                                required
                            />
                        </div>
                        <v-divider></v-divider>
                    </v-list-item>
                    <v-list-item>
                        <div
                            class="flex justify-between items-center w-full space-x-5"
                        >
                            <v-btn
                                class="flex-grow"
                                color="red"
                                variant="outlined"
                            >
                                Delete Project
                            </v-btn>
                            <v-btn class="flex-grow" flat color="black">
                                Update Data
                            </v-btn>
                        </div>
                    </v-list-item>
                </v-list>
            </v-form>

            <v-card-text> </v-card-text>
        </v-navigation-drawer>
    </AuthenticatedLayout>
</template>
