<script setup>
const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: "" },
});
const emit = defineEmits(["update:open"]);
const close = () => emit("update:open", false);
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[99999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="close" />
    <div
      class="relative bg-white rounded-2xl shadow-xl w-[95vw] max-w-4xl max-h-[90vh] overflow-y-auto"
    >
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <div class="font-bold text-lg">{{ title }}</div>
        <button class="text-2xl text-gray-400 hover:text-gray-700" @click="close">
          ×
        </button>
      </div>

      <div class="p-6">
        <slot />
      </div>
    </div>
  </div>
</template>
