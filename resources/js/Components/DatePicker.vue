<script setup>
import { Datepicker } from 'flowbite-datepicker';
import { onMounted, ref, defineProps, defineEmits, watch } from 'vue';

const props = defineProps({
  date: {
    type: String, // Pastikan prop date bertipe String
    required: true,
  },
  label: {
    type: String,
    default: 'your label',
  },
});

const emit = defineEmits(['setEmit']);

const input = ref(props.date); // Inisialisasi input dengan props.date

// Watch untuk mendeteksi perubahan di input
watch(input, (value) => {
  emit('setEmit', { date: formatDateToYyyyMmDd(value) }); // Emit nilai ke parent saat berubah
});

function formatDateToYyyyMmDd(date) {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

onMounted(() => {
  const datepickerEl = document.getElementById('date-picker');
  if (datepickerEl) {
    // Inisialisasi datepicker
    const datepicker = new Datepicker(datepickerEl, {
      format: 'yyyy-mm-dd', // Pastikan format yang diinginkan
      autohide: true,
      clearBtn: true,
    });

    // Event listener untuk mengupdate input saat tanggal berubah
    datepickerEl.addEventListener('changeDate', (e) => {
      input.value = e.detail.date; // Update input dengan nilai tanggal yang dipilih
    });
  }
});
</script>

<template>
  <div class="relative max-w-sm">
    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
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
      v-model="input"
      type="text"
      id="date-picker"
      class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
      placeholder="Select date"
    />
  </div>
</template>
