<template>
    <v-dialog v-model="model" activator="parent" max-width="500">
        <template v-slot:default="{ isActive }">
            <v-card prepend-icon="mdi-pencil" :title="props.title">
                <slot name="main" />
                <template v-slot:actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        class="ml-auto"
                        :text="props.noLabel"
                        @click="$emit('cancel')"
                    ></v-btn>
                    <v-btn v-if="loading" color="indigo">
                        <v-progress-circular
                            color="indigo"
                            indeterminate
                            size="24"
                            width="2"
                        ></v-progress-circular>
                    </v-btn>
                    <v-btn
                        v-else
                        :text="props.yesLabel"
                        @click="$emit('submit')"
                        color="indigo"
                    ></v-btn>
                </template>
            </v-card>
        </template>
    </v-dialog>
</template>
<script setup>
// import { defineModel } from "vue";
const model = defineModel();

const props = defineProps({
    title: {
        type: String,
        default: "Popup",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    yesLabel: {
        type: String,
        default: "Save",
    },
    noLabel: {
        type: String,
        default: "Cancel",
    },
});
</script>
