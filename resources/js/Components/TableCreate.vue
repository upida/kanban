<script setup>
import { onMounted, ref, defineProps, defineEmits, watch } from 'vue';
import TextInput from './TextInput.vue';
import { Switch } from '@headlessui/vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import { Datepicker } from 'flowbite-datepicker';

const props = defineProps({
    columns: {
        type: Object,
        required: true,
    }
});

const emit = defineEmits(['addData']);
const loading = ref(false);
const errors = ref({});

// Filter kolom editable dan buat data default
const editableColumns = ref(
    Object.entries(props.columns)
        .filter(([key, value]) => value && value.includes('editable'))
        .map(([key, value]) => [key, value])
);

const defaultData = ref(
    Object.fromEntries(
        editableColumns.value.map(([key, value]) => {
            if (value.includes('string')) return [key, ''];
            else if (value.includes('boolean')) return [key, 0];
            else if (value.includes('date')) return [key, null];
            return [key, undefined]; // Ensure all keys have defined values
        }).filter(([key, val]) => val !== undefined) // Filter out undefined values
    )
);

const data = ref({ ...defaultData.value });

function addData(newval) {
    loading.value = true;
    emit('addData', newval,
        () => {
            loading.value = false;
            errors.value = {};
            data.value = { ...defaultData.value }; // Reset data dengan membuat salinan defaultData
        },
        (e) => {
            loading.value = false;
            errors.value = e;
        }
    );
}

// Inisialisasi datepicker di dalam onMounted
onMounted(() => {
    editableColumns.value.forEach(([key, value]) => {
        if (value.includes('date')) {
            const datepickerEl = document.getElementById(`date-picker-${key}`);
            if (datepickerEl) {
                new Datepicker(datepickerEl, {
                    format: 'yyyy-mm-dd',
                    autohide: true,
                    clearBtn: true,
                });
                datepickerEl.addEventListener('changeDate', (e) => {
                    data.value[key] = e.target.value;
                });
            }
        }
    });
});

</script>

<template>
    <table class="min-w-full divide-y divide-gray-300" v-if="Object.values(data).length > 0">
        <thead class="bg-gray-50">
            <tr class="bg-gray-50">
                <th
                    v-for="column in editableColumns"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    scope="col"
                    :key="column[0]"
                >
                    {{ column[0] }}
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Create</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr>
                <td
                    v-for="column in editableColumns" :key="column[0]"
                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                >
                    <div class="flex flex-col">
                        <TextInput
                            v-if="column[1].includes('string')"
                            v-model="data[column[0]]"
                            class="flex-1"
                            autofocus
                        />
                        <Switch
                            v-else-if="column[1].includes('boolean')"
                            v-model="data[column[0]]"
                            :class="data[column[0]] ? 'bg-blue-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full"
                        >
                            <span class="sr-only">{{data[column[0]]}}</span>
                            <span
                                :class="data[column[0]] ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                            />
                        </Switch>
                        <input
                            v-else-if="column[1].includes('date')"
                            v-model="data[column[0]]"
                            type="text"
                            :id="`date-picker-${column[0]}`"
                            class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Select date"
                        />
                        <small v-if="errors[column[0]]" class="text-red-600" style="display:inline-block;">{{ errors[column[0]] }}</small>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <button v-if="!loading" @click="addData(data)" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save
                    </button>
                    <button v-else class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <ArrowPathIcon class="animate-spin h-6 w-6 stroke-indigo-700" />
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
