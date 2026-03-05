<script setup>
const props = defineProps({
  open: { type: Boolean, default: false },
  componentTypes: { type: Array, required: true },
  selectedType: { type: String, default: "text" },
});
const emit = defineEmits(["close", "confirm", "update:selectedType"]);
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[9999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')"></div>

    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-xl p-8">
      <div class="flex items-start justify-between">
        <div class="text-2xl font-bold text-blue-800">Změnit komponentu</div>
        <button class="text-gray-400 hover:text-gray-700 text-2xl" @click="emit('close')">
          ×
        </button>
      </div>

      <div class="mt-6 text-gray-700">
        Vyber nový typ komponenty. (Data komponenty se resetnou na default pro nový typ.)
      </div>

      <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-3">
        <label
          v-for="c in componentTypes"
          :key="'pick-' + c.type"
          class="border rounded-xl p-4 cursor-pointer hover:bg-gray-50"
          :class="selectedType === c.type ? 'ring-2 ring-blue-500' : ''"
        >
          <input
            class="hidden"
            type="radio"
            :value="c.type"
            :checked="selectedType === c.type"
            @change="emit('update:selectedType', c.type)"
          />
          <div class="flex items-start gap-3">
            <div class="text-2xl leading-none">{{ c.icon }}</div>
            <div>
              <div class="font-semibold">{{ c.label }}</div>
            </div>
          </div>
        </label>
      </div>

      <div class="mt-10 flex justify-end gap-4">
        <button
          class="px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-blue-700"
          @click="emit('close')"
        >
          Ne
        </button>
        <button
          class="px-6 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold"
          @click="emit('confirm')"
        >
          Ano, změnit
        </button>
      </div>
    </div>
  </div>
</template>
