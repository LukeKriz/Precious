<script setup>
defineEmits(["click"]);
</script>

<template>
  <button
    type="button"
    class="w-12 h-12 rounded-full border bg-white !text-gray-500 hover:bg-gray-50 text-2xl flex items-center justify-center"
    @click="$emit('click')"
  >
    +
  </button>
</template>
