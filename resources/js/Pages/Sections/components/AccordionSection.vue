<script setup>
import { computed } from "vue";

const props = defineProps({
  cmp: { type: Object, required: true },
});

const data = computed(() => {
  const d = props.cmp?.data ?? {};
  const items = Array.isArray(d.items) ? d.items : [];
  return { title: d.title ?? "", openFirst: !!d.openFirst, items };
});
</script>

<template>
  <div class="w-full">
    <div v-if="data.title" class="text-2xl font-bold mb-4">
      {{ data.title }}
    </div>

    <div class="space-y-3 flex flex-col items-center">
      <details
        v-for="(it, idx) in data.items"
        :key="it.id || idx"
        class="border rounded-xl p-4 bg-white w-3/4"
        :open="data.openFirst && idx === 0"
      >
        <summary class="cursor-pointer font-semibold">
          {{ it.heading || "Bez nadpisu" }}
        </summary>
        <div class="mt-3 text-sm text-gray-700 whitespace-pre-line">
          {{ it.text || "" }}
        </div>
      </details>
    </div>
  </div>
</template>
