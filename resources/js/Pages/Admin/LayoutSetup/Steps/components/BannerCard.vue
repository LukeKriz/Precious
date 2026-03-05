<template>
  <div
    class="border rounded-xl bg-gray-50 overflow-hidden p-3"
    :class="[draggable ? 'cursor-move' : '', dragOver ? 'ring-2 ring-blue-300' : '']"
    :draggable="draggable"
    @dragstart="onDragStart"
    @dragover="onDragOver"
    @drop="onDrop"
    @dragend="onDragEnd"
  >
    <button
      type="button"
      class="w-full text-left"
      @click="$emit('open')"
      :disabled="disabled"
      title="Klikni pro nastavení obsahu banneru"
    >
      <div class="w-full h-32 rounded-lg overflow-hidden bg-white border relative">
        <!-- base -->
        <img
          v-if="hasImage"
          :src="bannerUrl"
          class="absolute inset-0 w-full h-full object-cover"
          alt="Banner"
        />
        <div v-else class="absolute inset-0" :style="bgStyle" />

        <!-- empty text only if really empty -->
        <div
          v-if="showEmpty"
          class="absolute inset-0 flex items-center justify-center text-sm text-gray-400"
        >
          Prázdný banner (klikni a nastav pozadí)
        </div>

        <!-- overlay always on top -->
        <div
          v-if="overlayStyle"
          class="absolute inset-0 pointer-events-none"
          :style="overlayStyle"
        />
      </div>
    </button>

    <div class="mt-3 flex items-center justify-between gap-3">
      <div class="flex items-center gap-3 min-w-0">
        <div class="flex items-center gap-2">
          <div
            class="h-10 w-10 rounded-full border bg-white overflow-hidden flex items-center justify-center"
            title="Miniatura"
          >
            <img
              v-if="thumbShown"
              :src="thumbShown"
              class="h-full w-full object-cover"
              alt="Thumbnail"
            />
            <div v-else class="text-[10px] text-gray-400 leading-none px-1 text-center">
              Bez<br />mini
            </div>
          </div>
          <div class="text-xs text-gray-500 truncate">Miniatura</div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <button
            type="button"
            class="btn-pill"
            :disabled="disabled || !hasId"
            @click.stop="$emit('openThumb')"
          >
            <i class="fa-solid fa-image"></i>
            <span class="hidden sm:inline">Miniatura</span>
          </button>

          <button
            type="button"
            class="btn-pill"
            :disabled="disabled || !hasId"
            @click.stop="$emit('openText')"
          >
            <i class="fa-solid fa-pen-to-square"></i>
            <span class="hidden sm:inline">Text</span>
          </button>
        </div>
      </div>

      <button
        type="button"
        class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700 disabled:opacity-50"
        :disabled="disabled || !hasId || !showDelete"
        @click.stop="$emit('delete')"
        v-if="showDelete"
      >
        Smazat
      </button>
    </div>

    <div v-if="headingH1 || headingH2" class="mt-2 text-xs text-gray-600">
      <div v-if="headingH1"><b>H1:</b> {{ headingH1 }}</div>
      <div v-if="headingH2"><b>H2:</b> {{ headingH2 }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  id: { type: [Number, String], default: null },
  bannerUrl: { type: String, default: null },

  thumbnailUrl: { type: String, default: null },
  thumbPreview: { type: String, default: null },

  headingH1: { type: String, default: null },
  headingH2: { type: String, default: null },

  bgKey: { type: String, default: "transparent" },
  overlayKey: { type: String, default: "transparent" },
  overlayOpacity: { type: Number, default: 0 }, // 0..1

  palette: { type: Object, required: true },

  disabled: { type: Boolean, default: false },
  draggable: { type: Boolean, default: true },
  dragOver: { type: Boolean, default: false },

  showDelete: { type: Boolean, default: true },
});

const emit = defineEmits([
  "open",
  "openThumb",
  "openText",
  "delete",
  "dragstart",
  "dragover",
  "drop",
  "dragend",
]);

const hasId = computed(() => props.id !== null && props.id !== undefined);
const hasImage = computed(() => !!props.bannerUrl);
const hasBg = computed(() => props.bgKey && props.bgKey !== "transparent");
const showEmpty = computed(() => !hasImage.value && !hasBg.value);

const bgStyle = computed(() => ({
  backgroundColor: hasBg.value
    ? props.palette?.[props.bgKey] ?? "transparent"
    : "transparent",
}));

const overlayStyle = computed(() => {
  const k = props.overlayKey;
  const op = Number(props.overlayOpacity ?? 0);
  if (!k || k === "transparent" || op <= 0) return null;
  return { backgroundColor: props.palette?.[k] ?? "transparent", opacity: op };
});

const thumbShown = computed(() => props.thumbPreview || props.thumbnailUrl || null);

// ✅ Drag handlers – event se posílá dál a preventDefault se dělá bezpečně
const onDragStart = (e) => {
  if (!props.draggable) return;
  emit("dragstart", e);
};

const onDragOver = (e) => {
  if (!props.draggable) return;
  if (e && typeof e.preventDefault === "function") e.preventDefault();
  emit("dragover", e);
};

const onDrop = (e) => {
  if (!props.draggable) return;
  if (e && typeof e.preventDefault === "function") e.preventDefault();
  emit("drop", e);
};

const onDragEnd = (e) => {
  if (!props.draggable) return;
  emit("dragend", e);
};
</script>

<style scoped>
.btn-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 9999px;
  border: 1px solid rgb(229 231 235);
  background: white;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(55 65 81);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
  transition: background 120ms ease, transform 120ms ease, opacity 120ms ease;
}
.btn-pill:hover {
  background: rgb(249 250 251);
}
.btn-pill:active {
  transform: translateY(1px);
  background: rgb(243 244 246);
}
.btn-pill:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
