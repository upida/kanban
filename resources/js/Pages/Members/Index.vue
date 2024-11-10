<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    owner: {
        type: Object,
        required: true,
    },
    members: {
        type: Array,
        required: true,
    },
    invitations: {
        type: Array,
        required: true,
    },
});

const inviteForm = useForm({
    email: '',
});

const user = usePage().props.auth.user;

function invite(isActive) {
    inviteForm.post(route('projects.members.invite', {
        project: props.project.id,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            isActive.value = false;
        },
        onError: (error) => {
            console.log('error', error);
        },
    });
}

function deleteMember(member) {
    if (confirm(`Are you sure you want to delete ${member.user.name}?`)) {
        router.delete('/projects/' + props.project.id + '/members/' + member.id, {
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
</script>

<template>
    <Head :title="'Member ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex h-5 justify-between">
                <h2 @click="router.get('/projects/' + project.id)" class="cursor-pointer flex items-center gap-4 text-xl font-semibold leading-tight text-gray-800">
                    {{ project.name }}
                </h2>

                <div class="flex items-center gap-4">
                    <v-btn
                        color="indigo"
                    >
                        <v-icon icon="mdi-email-plus" class="mr-2" />
                        Invite

                        <v-dialog activator="parent" max-width="500">
                            <template v-slot="{ isActive }">
                                <v-card>
                                    <v-card-title>
                                        Invite Member
                                    </v-card-title>
    
                                    <v-card-text>
                                        <v-text-field
                                            v-model="inviteForm.email"
                                            label="Email"
                                            required
                                        />
                                    </v-card-text>
    
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                            text="Close"
                                            @click="isActive.value = false"
                                        />
                                        <v-btn
                                            text="Invite"
                                            @click="invite(isActive)"
                                        />
                                    </v-card-actions>
                                </v-card>
                            </template>
                        </v-dialog>
                    </v-btn>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <v-card
                    rounded="lg"
                    variant="flat"
                    :title="`Members (${members.length})`"
                >
                    <v-card-item
                        v-for="member in members" :key="member.id"
                    >
                        <v-card-title>
                            {{ member.user.name }}
                            <v-chip
                                class="ms-2 text-medium-emphasis"
                                :color="member.role === 'owner' ? 'indigo-lighten-4' : 'disabled'"
                                size="small"
                                variant="flat"
                            >
                                {{ member.role }}
                            </v-chip>
                        </v-card-title>
                        <v-card-subtitle>
                            {{ member.user.email }}
                        </v-card-subtitle>

                        <template v-if="member.role !== 'owner' && (member.user.id === user.id || owner.id === user.id)" v-slot:append>
                            <v-btn
                                icon="mdi-logout"
                                color="red"
                                variant="text"
                                class="mr-1"
                                @click="deleteMember(member)"
                            />
                        </template>
                    </v-card-item>
                </v-card>
                <v-card
                    rounded="lg"
                    variant="flat"
                    title="Pending Invitation"
                >
                    <v-card-item v-for="invitation in invitations" :key="invitation.id">
                        <v-card-title class="text-body-2 d-flex align-center">
                            <v-icon
                                icon="mdi-account"
                                color="muted"
                                start
                            ></v-icon>
                            {{ invitation.email }}
                        </v-card-title>
                    </v-card-item>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
