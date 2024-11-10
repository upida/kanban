<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import moment from 'moment';

const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
});

const user = usePage().props.auth.user;

const loading = ref({});

props.projects.forEach((project) => {
    loading.value[project.id] = false;
});

const filterText = ref('');

const filteredProjects = computed(() => {
    if (!filterText.value) {
        return props.projects; // return all projects if no filter
    }
    return props.projects.filter(project => {
        return (
            project.name.toLowerCase().includes(filterText.value.toLowerCase()) ||
            project.description?.toLowerCase().includes(filterText.value.toLowerCase()) ||
            project.started_at.toLowerCase().includes(filterText.value.toLowerCase()) ||
            project.ended_at.toLowerCase().includes(filterText.value.toLowerCase())
        );
    });
});

const columns = {
    name: ['editable', 'string'],
    description: ['editable', 'string'],
    started_at: ['editable', 'date'],
    ended_at: ['editable', 'date'],
}

function toggelEdit(row) {
    
}

function editData(row) {
    loading.value[row.id] = true;

    row.started_at = moment(row.started_at).format('YYYY-MM-DD HH:mm:ss');
    row.ended_at = moment(row.ended_at).format('YYYY-MM-DD HH:mm:ss');

    router.patch('/projects/' + row.id, row, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value[row.id] = false;
        },
        onError: (error) => {
            console.log('error', error);
            loading.value[row.id] = false;
        },
    });
}

function deleteData(row) {
    if (confirm(`Are you sure you want to delete ${row.name}?`)) {
        router.delete('/projects/' + row.id, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('success');
            },
            onError: (error) => {
                console.log('error', error);
            },
        });
    }
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
                <v-card
                    v-for="project in filteredProjects" :key="project.id"
                    rounded="lg"
                    variant="flat"
                >
                    <v-card-item>
                        <v-card-title class="text-body-2 d-flex align-center">
                        <v-icon
                            :color="(project.notifications_count > 0) ? 'red-lighten-4' : 'disabled'"
                            icon="mdi-bell"
                            size="x-small"
                        ></v-icon>

                        <span class="text-caption text-medium-emphasis ms-1 font-weight-light">
                            {{ project.notifications_count }} unread
                        </span>

                        <v-spacer></v-spacer>

                        <v-chip
                            class="ms-2 text-medium-emphasis"
                            color="indigo-lighten-4"
                            prepend-icon="mdi-account"
                            size="small"
                            variant="flat"
                        >
                            {{ (user.id === project.owner.id) ? 'You' : project.owner.name }}
                        </v-chip>

                        <v-chip
                            class="ms-2 text-medium-emphasis"
                            color="grey-lighten-4"
                            prepend-icon="mdi-account-multiple"
                            size="small"
                            variant="flat"
                        >
                            {{ project.members_count }}
                        </v-chip>
                        </v-card-title>

                        <div class="py-2">
                        <div class="text-h6">{{ project.name }}</div>

                        <div v-if="project.description" class="font-weight-light text-medium-emphasis">
                           {{ project.description }}
                        </div>
                        </div>
                    </v-card-item>

                    <v-divider></v-divider>

                    <div class="pa-4 d-flex align-center">
                        <v-icon
                            icon="mdi-calendar"
                            color="indigo"
                            start
                        ></v-icon>

                        <span class="text-medium-emphasis font-weight-bold">{{ project.started_at + ' - ' + project.ended_at }}</span>

                        <v-spacer></v-spacer>

                        <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn icon="mdi-dots-vertical" variant="text" class="mr-1" v-bind="props"></v-btn>
                            </template>

                            <v-list>
                                <v-list-item class="cursor-pointer">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-pencil" color="indigo"></v-icon>
                                    </template>
                                    <v-list-item-title>Edit</v-list-item-title>
                                    <v-dialog activator="parent" max-width="500">
                                        <template v-slot:default="{ isActive }">
                                            <v-card
                                                prepend-icon="mdi-pencil"
                                                :title="'Edit ' + project.name"
                                            >
                                                <v-card-text>
                                                    <v-text-field
                                                        v-model="project.name"
                                                        label="Name"
                                                        required
                                                    />
                                                    <v-textarea
                                                        v-model="project.description"
                                                        label="Description"
                                                        required
                                                    />
                                                    <div class="mb-4">
                                                        <label class="mr-3">Started at</label>
                                                        <input
                                                            v-model="project.started_at"
                                                            type="datetime-local"
                                                            label="started_at"
                                                            required
                                                        />
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="mr-3">Ended at</label>
                                                        <input
                                                            v-model="project.ended_at"
                                                            type="datetime-local"
                                                            label="ended_at"
                                                            required
                                                        />
                                                    </div>
                                                </v-card-text>
                                                <template v-slot:actions>
                                                    <v-spacer></v-spacer>
                                                    <v-btn v-if="loading[project.id] ?? false" color="indigo">
                                                        <v-progress-circular
                                                            color="indigo"
                                                            indeterminate
                                                            size="24"
                                                            width="2"
                                                        ></v-progress-circular>
                                                    </v-btn>
                                                    <v-btn
                                                        v-else
                                                        text="Save"
                                                        @click="editData(project)"
                                                        color="indigo"
                                                    ></v-btn>
                                                    <v-btn
                                                        class="ml-auto"
                                                        text="Close"
                                                        @click="isActive.value = false; router.reload()"
                                                    ></v-btn>
                                                </template>
                                            </v-card>
                                        </template>
                                    </v-dialog>
                                </v-list-item>
                                <v-list-item @click="deleteData(project)" class="cursor-pointer">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-delete" color="red"></v-icon>
                                    </template>
                                    <v-list-item-title>Delete</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>

                        <v-btn
                            prepend-icon="mdi-book"
                            color="indigo-darken-4"
                            variant="outlined"
                            @click="show(project)"
                        >
                            Tasks
                        </v-btn>
                    </div>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
