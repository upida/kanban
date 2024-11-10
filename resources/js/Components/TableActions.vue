<script setup>
import { onMounted, ref } from 'vue';
import TextInput from './TextInput.vue';
import { Switch } from '@headlessui/vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import moment from 'moment';

const props = defineProps({
    columns: {
        type: Object,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    isShow: {
        type: Boolean,
        default: false,
    },
});

const loading = ref({});
const errors = ref({});
const emit = defineEmits(['editData', 'deleteData', 'show']);

function editData(key, row) {
    if (row.edit) {
        loading.value[row.id] = true;

        emit('editData', row, 
            () => {
                loading.value[row.id] = false;
                row.edit = false;
                errors.value = {};
            },
            (e) => {
                props.data[key]['edit'] = true;
                loading.value[row.id] = false;
                errors.value = {};
                errors.value[row.id] = e;
            }
        );
    } else {
        row.edit = true;
    }
}

function deleteData(row) {
    loading.value[row.id] = true;
    if (confirm('Are you sure you want to delete this item?')) {
        emit('deleteData', row,
            () => {
                loading.value[row.id] = false;
            },
            (e) => {
                loading.value[row.id] = false;
                // errors.value = {};
                // errors.value[row.id] = e;
            }
        );
    }
}

function show(key, row) {
    emit('show', row);
}

onMounted(() => {
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <table class="min-w-full divide-y divide-gray-300">
        <thead>
            <tr class="bg-gray-50" v-if="data.length > 0">
                <th
                    v-for="column in Object.keys(columns)"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    scope="col"
                    :key="column"
                >
                    {{ column }}
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Edit</span>
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Delete</span>
                </th>
                <th v-if="isShow" scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Show</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(row, key) in data" :key="row.id">
                <td
                    v-for="(setting, column) in columns"
                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                    :key="column"
                >
                    <div v-if="setting.includes('editable')" class="flex flex-col">
                        <template v-if="setting.includes('boolean')">
                            <span
                                v-if="!row.edit"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="row[column] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            >
                                {{ row[column] ? 'Yes' : 'No' }}
                            </span>
                            <span v-else class="flex-1">
                                <Switch
                                    :value="row[column] ? 'true' : 'false'"
                                    @update:modelValue="row[column] = Number($event)"
                                    :class="row[column] ? 'bg-blue-600' : 'bg-gray-200'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full"
                                >
                                    <span class="sr-only">Enable</span>
                                    <span
                                        :class="row[column] ? 'translate-x-6' : 'translate-x-1'"
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                                    />
                                </Switch>
                            </span>
                        </template>
                        <template v-else-if="setting.includes('string')">
                            <span v-if="!row.edit" class="flex-1">
                                {{ row[column] }}
                            </span>
                            <TextInput
                                v-else
                                v-model="row[column]"
                                class="flex-1"
                                :autofocus="row.edit === column"
                            />
                        </template>
                        <template v-else-if="setting.includes('date')">
                            <span
                                v-if="!row.edit"
                                class="flex-1"
                            >
                                {{ row[column] }}
                            </span>
                            <input
                                v-else
                                v-model="row[column]"
                                type="datetime-local"
                                class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Select date"
                            />
                        </template>
                        <small v-if="errors[row.id]?.[column]" class="text-red-600" style="display:inline-block;">{{ errors[row.id][column] }}</small>
                    </div>
                    <template v-else>
                        {{ row[column] }}
                    </template>
                </td>
                <td class="px-2 py-4">
                    <button v-if="loading[row.id] ?? false" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <ArrowPathIcon class="animate-spin h-6 w-6 stroke-indigo-700" />
                    </button>
                    <button v-else-if="row.edit" @click="editData(key, row)" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save
                    </button>
                    <button v-else @click="editData(key, row)" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Edit
                    </button>
                </td>
                <td class="px-2 py-4">
                    <button @click="deleteData(row)" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </td>
                <td v-if="isShow" class="px-2 py-4">
                    <button @click="show(key, row)" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-600 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Show
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
