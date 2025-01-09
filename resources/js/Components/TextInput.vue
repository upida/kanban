<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

// onMounted(() => {
//     if (input.value.hasAttribute("autofocus")) {
//         input.value.focus();
//     }
// });

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        type="text"
        id="first_name"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        placeholder="John"
        required
        v-model="model"

    />

    <!-- <input
        class="rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 flex w-full sm:text-sm"
        v-model="model"
        ref="input"
    /> -->
</template>
