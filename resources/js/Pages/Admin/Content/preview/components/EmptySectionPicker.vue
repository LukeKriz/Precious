<script setup>
const props = defineProps({
  componentTypes: { type: Array, required: true },
});
defineEmits(["pick"]);
</script>

<template>
  <div>
    <div class="text-sm text-gray-500 mb-3">
      Vyber komponentu, kterou chceš vložit do této sekce:
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <button
        v-for="c in componentTypes"
        :key="c.type"
        type="button"
        class="text-left border rounded-xl p-4 hover:bg-gray-50 transition"
        @click="$emit('pick', c.type)"
      >
        <div class="flex items-start gap-3">
          <div class="text-2xl leading-none">{{ c.icon }}</div>
          <div class="min-w-0">
            <div class="font-semibold">{{ c.label }}</div>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>
