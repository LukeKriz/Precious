<script setup>
const props = defineProps({
  isOpen: { type: Boolean, default: false },
});
defineEmits(["toggle", "edit", "changeType", "delete"]);
</script>

<template>
  <div class="absolute top-2 right-4" data-menu="1">
    <button
      type="button"
      class="w-11 h-11 bg-white hover:bg-gray-50 flex items-center justify-center text-xl !text-gray-600"
      title="Akce"
      @click="$emit('toggle')"
      data-menu="1"
    >
      …
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-64 rounded-2xl border bg-white shadow-xl p-2 z-[9999]"
      data-menu="1"
    >
      <button
        type="button"
        class="w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50 text-sm flex items-center gap-2 !text-gray-600"
        @click="$emit('edit')"
        data-menu="1"
      >
        Upravit
      </button>

      <button
        type="button"
        class="w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50 text-sm flex items-center gap-2 !text-gray-600"
        @click="$emit('changeType')"
        data-menu="1"
      >
        Změnit typ komponenty
      </button>

      <button
        type="button"
        class="w-full text-left px-3 py-2 rounded-xl hover:bg-red-50 text-sm flex items-center gap-2 text-red-700"
        @click="$emit('delete')"
        data-menu="1"
      >
        Smazat komponentu
      </button>
    </div>
  </div>
</template>
