<script setup>
defineProps({ open: { type: Boolean, default: false } });
defineEmits(["close", "confirm"]);
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[9999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="$emit('close')"></div>

    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-xl p-8">
      <div class="flex items-start justify-between">
        <div class="text-2xl font-bold text-blue-800">Smazat komponentu</div>
        <button
          class="text-gray-400 hover:text-gray-700 text-2xl"
          @click="$emit('close')"
        >
          ×
        </button>
      </div>

      <div class="mt-6 flex items-center gap-4">
        <div
          class="w-12 h-12 rounded-full border flex items-center justify-center text-xl"
        >
          ?
        </div>
        <div class="text-gray-700 text-lg">Opravdu chcete smazat tuto komponentu?</div>
      </div>

      <div class="mt-10 flex justify-end gap-4">
        <button
          class="px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-blue-700"
          @click="$emit('close')"
        >
          Ne
        </button>
        <button
          class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold"
          @click="$emit('confirm')"
        >
          Ano, smazat
        </button>
      </div>
    </div>
  </div>
</template>
