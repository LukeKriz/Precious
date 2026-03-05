<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
  open: Boolean,
  insertIndex: { type: Number, default: 0 },
  mainDesign: { type: Object, default: () => ({}) },

  // ✅ upload funkce z parentu
  uploadFile: { type: Function, default: null },
  deleteFile: { type: Function, default: null },
});

const emit = defineEmits(["close", "confirm"]);
const mainDesign = computed(() => props.mainDesign || {});

const height = ref(300);
const bg = ref("");
const bgImage = ref("");

const overlayColor = ref("");
const overlayOpacity = ref(0);

const paddingTop = ref(0);
const paddingBottom = ref(0);

// ✅ NEW
const va = ref("center");

const palette = computed(() => {
  const d = mainDesign.value || {};
  return [
    d.primary_color,
    d.secondary_color,
    d.tertiary_color,
    d.quaternary_color,
    d.quinary_color,
  ].filter((c) => typeof c === "string" && c.trim().length);
});

const checkerStyle = {
  backgroundImage:
    "linear-gradient(45deg, #d1d5db 25%, transparent 25%)," +
    "linear-gradient(-45deg, #d1d5db 25%, transparent 25%)," +
    "linear-gradient(45deg, transparent 75%, #d1d5db 75%)," +
    "linear-gradient(-45deg, transparent 75%, #d1d5db 75%)",
  backgroundSize: "10px 10px",
  backgroundPosition: "0 0, 0 5px, 5px -5px, -5px 0px",
};

watch(
  () => props.open,
  (v) => {
    if (!v) return;
    height.value = 300;
    bg.value = "";
    bgImage.value = "";
    overlayColor.value = "";
    overlayOpacity.value = 0;
    paddingTop.value = 0;
    paddingBottom.value = 0;
    va.value = "center";
  }
);

const pickFile = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file || typeof props.uploadFile !== "function") return;

  try {
    const res = await props.uploadFile(file);
    bgImage.value = (res?.url || res?.path || "").toString();
  } catch (e) {
    alert("Upload se nezdařil.");
  } finally {
    e.target.value = "";
  }
};

const removeImage = async () => {
  const old = bgImage.value;
  bgImage.value = "";

  if (old && typeof props.deleteFile === "function") {
    try {
      await props.deleteFile(old);
    } catch {}
  }
};

const confirm = () => {
  emit("confirm", {
    insertIndex: props.insertIndex,
    height: height.value,

    bg: bg.value,
    bgImage: bgImage.value,

    overlayColor: overlayColor.value,
    overlayOpacity: overlayOpacity.value,

    paddingTop: paddingTop.value,
    paddingBottom: paddingBottom.value,

    // ✅ uloží se jako va
    va: va.value,
  });
  emit("close");
};
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[9999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')" />

    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="text-lg font-bold">Nový blok</div>
        <button class="text-gray-500 hover:text-black" @click="emit('close')">✕</button>
      </div>

      <!-- PREVIEW -->
      <div class="rounded-2xl border overflow-hidden mb-6">
        <div class="relative w-full h-40">
          <div
            class="absolute inset-0"
            :style="{ backgroundColor: bg || 'transparent' }"
          />

          <div
            v-if="bgImage"
            class="absolute inset-0"
            :style="{
              backgroundImage: `url(${bgImage})`,
              backgroundSize: 'cover',
              backgroundPosition: 'center',
            }"
          />

          <div
            v-if="overlayColor && overlayOpacity > 0"
            class="absolute inset-0"
            :style="{ backgroundColor: overlayColor, opacity: overlayOpacity / 100 }"
          />

          <div
            class="absolute inset-0 grid place-items-center text-white font-semibold drop-shadow"
          >
            Náhled
          </div>
        </div>
      </div>

      <!-- BG + OVERLAY -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- BG -->
        <div>
          <div class="text-base font-semibold mb-2">Pozadí z palety</div>
          <div class="flex flex-wrap gap-3">
            <button
              type="button"
              class="h-9 w-9 rounded-full border border-gray-300 shadow-sm"
              :class="!bg ? 'ring-2 ring-emerald-500 ring-offset-2' : ''"
              :style="checkerStyle"
              @click="bg = ''"
            />
            <button
              v-for="(c, i) in palette"
              :key="'bg_' + c + '_' + i"
              type="button"
              class="h-9 w-9 rounded-full border border-gray-300 shadow-sm"
              :class="bg === c ? 'ring-2 ring-emerald-500 ring-offset-2' : ''"
              :style="{ backgroundColor: c }"
              @click="bg = c"
            />
          </div>
        </div>

        <!-- OVERLAY -->
        <div>
          <div class="text-base font-semibold mb-2">Filtr (překrytí)</div>
          <div class="flex flex-wrap gap-3 mb-3">
            <button
              type="button"
              class="h-9 w-9 rounded-full border border-gray-300 shadow-sm"
              :class="!overlayColor ? 'ring-2 ring-emerald-500 ring-offset-2' : ''"
              :style="checkerStyle"
              @click="overlayColor = ''"
            />
            <button
              v-for="(c, i) in palette"
              :key="'ov_' + c + '_' + i"
              type="button"
              class="h-9 w-9 rounded-full border border-gray-300 shadow-sm"
              :class="overlayColor === c ? 'ring-2 ring-emerald-500 ring-offset-2' : ''"
              :style="{ backgroundColor: c }"
              @click="overlayColor = c"
            />
          </div>

          <input
            type="range"
            min="0"
            max="100"
            class="w-full"
            v-model.number="overlayOpacity"
            :disabled="!overlayColor"
          />
        </div>
      </div>

      <!-- IMAGE -->
      <div class="mt-8">
        <div class="text-sm font-medium mb-2">Obrázek sekce (volitelné)</div>

        <div class="flex flex-wrap items-center gap-3">
          <label
            class="inline-flex items-center px-4 py-2 rounded bg-emerald-600 text-white cursor-pointer hover:bg-emerald-700"
          >
            Vybrat obrázek
            <input type="file" accept="image/*" class="hidden" @change="pickFile" />
          </label>

          <button
            type="button"
            class="inline-flex items-center px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
            :disabled="!bgImage"
            @click="removeImage"
          >
            Odebrat obrázek
          </button>

          <div v-if="bgImage" class="text-xs text-gray-500 break-all">
            {{ bgImage }}
          </div>
        </div>
      </div>

      <!-- HEIGHT + PADDING -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-medium block mb-2">Výška (px)</label>
          <input
            type="number"
            min="80"
            class="w-full rounded border px-3 py-2"
            v-model.number="height"
          />
        </div>

        <div>
          <label class="text-sm font-medium block mb-2">Odsazení nahoře (px)</label>
          <input
            type="number"
            min="0"
            class="w-full rounded border px-3 py-2"
            v-model.number="paddingTop"
          />
        </div>

        <div>
          <label class="text-sm font-medium block mb-2">Odsazení dole (px)</label>
          <input
            type="number"
            min="0"
            class="w-full rounded border px-3 py-2"
            v-model.number="paddingBottom"
          />
        </div>
      </div>

      <!-- ✅ VERTICAL ALIGN -->
      <div class="mt-6 rounded-xl border p-4 bg-white">
        <div class="text-sm font-semibold mb-3">Vertikální zarovnání obsahu</div>

        <div class="flex gap-2">
          <button
            type="button"
            class="px-4 py-2 rounded-lg border text-sm font-medium"
            :class="
              va === 'top'
                ? 'bg-black text-white border-black'
                : 'bg-white hover:bg-gray-50'
            "
            @click="va = 'top'"
          >
            Nahoru
          </button>

          <button
            type="button"
            class="px-4 py-2 rounded-lg border text-sm font-medium"
            :class="
              va === 'center'
                ? 'bg-black text-white border-black'
                : 'bg-white hover:bg-gray-50'
            "
            @click="va = 'center'"
          >
            Na střed
          </button>

          <button
            type="button"
            class="px-4 py-2 rounded-lg border text-sm font-medium"
            :class="
              va === 'bottom'
                ? 'bg-black text-white border-black'
                : 'bg-white hover:bg-gray-50'
            "
            @click="va = 'bottom'"
          >
            Dolů
          </button>
        </div>
      </div>

      <div class="mt-8 flex justify-end gap-2">
        <button class="px-4 py-2 rounded border" @click="emit('close')">Zrušit</button>
        <button
          class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700"
          @click="confirm"
        >
          Přidat
        </button>
      </div>
    </div>
  </div>
</template>
