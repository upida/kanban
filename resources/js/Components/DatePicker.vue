<script setup>
import { Datepicker } from "flowbite-datepicker";
import { onMounted, ref, defineProps, defineEmits, watch, nextTick } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const uniqueId = ref(Math.random().toString(36).substr(2, 9)); // generate ID unik

const props = defineProps({
    // date: {
    //     type: String, // Pastikan prop date bertipe String
    //     required: true,
    // },
    label: {
        type: String,
        default: "your label",
    },
});

const emit = defineEmits(["setEmit"]);

const input = ref(props.date); // Inisialisasi input dengan props.date

// Watch untuk mendeteksi perubahan di input
// watch(input, (value) => {
//     emit("setEmit", { date: formatDateToYyyyMmDd(value) }); // Emit nilai ke parent saat berubah
// });

function formatDateToYyyyMmDd(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0"); // Bulan dimulai dari 0
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}
// nextTick(() => {
//     getDatePicker();
// });

// const getDatePicker = () => {
//     const datepickerEls = document.querySelectorAll('[id^="date-picker-"]'); // ambil semua elemen dengan ID yang diawali 'date-picker-'
//     datepickerEls.forEach((datepickerEl) => {
//         const datepicker = new Datepicker(datepickerEl, {
//             format: "yyyy-mm-dd", // format tanggal
//             autohide: true,
//             clearBtn: true,
//         });
//         // Ambil posisi dari input field
//         const inputEl = datepickerEl;
//         const rect = inputEl.getBoundingClientRect(); // Ambil posisi elemen input
//         // Set posisi datepicker berdasarkan posisi input
//         const datepickerDropdown = document.querySelector(
//             ".datepicker-dropdown"
//         ); // Sesuaikan selector dengan elemen datepicker yang muncul
//         console.log(datepickerDropdown);
//         if (datepickerDropdown) {
//             datepickerDropdown.style.setProperty(
//                 "position",
//                 "absolute",
//                 "important"
//             );
//             datepickerDropdown.style.setProperty(
//                 "top",
//                 `${rect.bottom + window.scrollY}px`,
//                 "important"
//             ); // Posisi dari bawah input
//             datepickerDropdown.style.setProperty(
//                 "left",
//                 `${rect.left + window.scrollX}px`,
//                 "important"
//             ); // Posisi kiri input
//             datepickerDropdown.style.setProperty("z-index", "999", "important"); // Pastikan di atas elemen lainnya
//         }
//         datepickerEl.addEventListener("changeDate", (e) => {
//             input.value = e.detail.date; // update input dengan tanggal yang dipilih
//         });
//     });
// };

// onMounted(() => {
//     getDatePicker();
// });
</script>

<template>
    <VueDatePicker
        class="z-50 relative"
        v-model="model"
        v-bind="$props"
        v-on="$attrs"
    />
    <!-- <div class="relative max-w-sm">
        <div
            class="absolute inset-y-0 start-0 flex items-center pl-3.5 pointer-events-none"
        >
            <svg
                class="w-4 h-4 text-gray-500 dark:text-gray-400"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"
                />
            </svg>
        </div>
        <input
            v-model="model"
            type="text"
            datepicker
            :id="'date-picker-' + uniqueId"
            class="flex-1 bg-gray-50 border pl-10 border-gray-300 block text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Select date"
        />
    </div> -->
</template>
<style scoped>
.dp__theme_dark {
    --dp-background-color: #212121;
}
.dp__theme_light {
    --dp-background-color: #f9fafb;
}
</style>
