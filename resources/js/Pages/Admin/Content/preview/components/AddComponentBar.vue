<script setup>
const props = defineProps({
  componentTypes: { type: Array, required: true },
});
defineEmits(["add"]);
</script>

<template>
  <div class="px-6 pb-6">
    <div class="text-xs text-gray-500 mb-2">Přidat další komponentu do této sekce:</div>
    <div class="flex flex-wrap gap-2">
      <button
        v-for="c in componentTypes"
        :key="'add-' + c.type"
        type="button"
        class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm !text-gray-600"
        @click="$emit('add', c.type)"
      >
        {{ c.icon }} {{ c.label }}
      </button>
    </div>

    <div class="mt-2 text-[11px] text-gray-400">
      Tip: Komponenty můžeš přehazovat přetažením (Drag & Drop).
    </div>
  </div>
</template>
