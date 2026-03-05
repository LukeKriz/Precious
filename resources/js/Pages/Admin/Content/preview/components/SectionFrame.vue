<script setup>
import { computed } from "vue";

const props = defineProps({
  sec: { type: Object, required: true },
  index: { type: Number, required: true },
});

const emit = defineEmits([
  "editHeight",
  "deleteSection",
  // ✅ drag & drop sekcí
  "dragStart",
  "dragOver",
  "drop",
  "dragEnd",
]);

const bgValue = computed(() => String(props.sec?.bg ?? "").trim());
const bgImage = computed(() => String(props.sec?.bgImage ?? "").trim());

const overlayColor = computed(() => String(props.sec?.overlayColor ?? "").trim());
const overlayOpacity = computed(() => {
  const v = Number(props.sec?.overlayOpacity ?? 0);
  return Math.max(0, Math.min(100, v));
});

// šachovnice pro "transparent"
const checkerStyle = {
  backgroundImage:
    "linear-gradient(45deg, #d1d5db 25%, transparent 25%)," +
    "linear-gradient(-45deg, #d1d5db 25%, transparent 25%)," +
    "linear-gradient(45deg, transparent 75%, #d1d5db 75%)," +
    "linear-gradient(-45deg, transparent 75%, #d1d5db 75%)",
  backgroundSize: "10px 10px",
  backgroundPosition: "0 0, 0 5px, 5px -5px, -5px 0px",
};

// základ swatche: obrázek má prioritu, jinak barva, jinak šachovnice
const baseStyle = computed(() => {
  if (bgImage.value) {
    return {
      backgroundImage: `url(${bgImage.value})`,
      backgroundSize: "cover",
      backgroundPosition: "center",
      backgroundRepeat: "no-repeat",
    };
  }
  if (bgValue.value) return { backgroundColor: bgValue.value };
  return { ...checkerStyle };
});

// overlay vrstva (jen když je nastavený)
const overlayStyle = computed(() => {
  if (!overlayColor.value || overlayOpacity.value <= 0) return null;
  return {
    backgroundColor: overlayColor.value,
    opacity: overlayOpacity.value / 100,
  };
});

const titleText = computed(() => {
  const parts = [];
  if (bgImage.value) parts.push(`Image: ${bgImage.value}`);
  else parts.push(`BG: ${bgValue.value || "Transparent"}`);

  if (overlayStyle.value)
    parts.push(`Overlay: ${overlayColor.value} (${overlayOpacity.value}%)`);
  return parts.join(" | ");
});

// ✅ drag handlers (emitují do parent preview souboru)
const onDragStart = (e) => {
  try {
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", props.sec?.id || "");
  } catch {}
  emit("dragStart", props.sec.id);
};

const onDragOver = (e) => {
  e.preventDefault();
  emit("dragOver", e, props.sec.id);
};

const onDrop = (e) => {
  e.preventDefault();
  emit("drop", e, props.sec.id);
};

const onDragEnd = () => emit("dragEnd");
</script>

<template>
  <section
    class="w-full bg-white rounded-2xl border overflow-visible relative"
    :style="{ minHeight: (sec.height ?? 200) + 'px' }"
    :draggable="true"
    @dragstart="onDragStart"
    @dragover="onDragOver"
    @drop="onDrop"
    @dragend="onDragEnd"
  >
    <!-- ✅ Header (drop target) -->
    <div
      class="flex items-center justify-between px-6 pt-4"
      @dragover="onDragOver"
      @drop="onDrop"
    >
      <div class="text-sm text-gray-500 flex items-center gap-2 min-w-0">
        <span class="truncate">
          Sekce {{ index + 1 }} (Výška sekce {{ sec.height }}px) – Pozadí:
        </span>

        <!-- ✅ Swatch = base + overlay -->
        <span
          class="relative inline-block h-7 w-7 rounded-full border border-gray-300 overflow-hidden align-middle shrink-0"
          :title="titleText"
        >
          <span class="absolute inset-0" :style="baseStyle" />
          <span v-if="overlayStyle" class="absolute inset-0" :style="overlayStyle" />
        </span>

        <span
          v-if="(sec.pt || 0) > 0 || (sec.pb || 0) > 0"
          class="text-xs text-gray-400 shrink-0"
        >
          <span v-if="(sec.pt || 0) > 0">Odsazení nahoře: {{ sec.pt }}px</span>
          <span v-if="(sec.pt || 0) > 0 && (sec.pb || 0) > 0"> · </span>
          <span v-if="(sec.pb || 0) > 0">Odsazení dole: {{ sec.pb }}px</span>
        </span>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button
          type="button"
          class="px-4 py-2 rounded-lg !text-gray-600 border bg-white hover:bg-gray-50 text-sm font-medium"
          @click="$emit('editHeight')"
        >
          ✏ Upravit
        </button>

        <button
          type="button"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium"
          @click="$emit('deleteSection')"
        >
          🗑 Smazat
        </button>
      </div>
    </div>

    <slot />
  </section>
</template>
