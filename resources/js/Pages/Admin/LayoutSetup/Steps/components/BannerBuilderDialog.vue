<template>
  <Dialog
    v-model:visible="localVisible"
    modal
    :style="{ width: '760px', maxWidth: '95vw' }"
  >
    <template #header>
      <div class="font-bold">Obsah banneru</div>
    </template>

    <div v-if="bannerId" class="space-y-6">
      <!-- preview -->
      <div class="border rounded-xl overflow-hidden bg-white">
        <div class="h-44 w-full relative">
          <img
            v-if="bannerUrl"
            :src="bannerUrl"
            class="absolute inset-0 w-full h-full object-cover"
            alt="Banner preview"
          />
          <div v-else class="absolute inset-0" :style="bgPreviewStyle" />
          <div
            v-if="overlayPreviewStyle"
            class="absolute inset-0"
            :style="overlayPreviewStyle"
          />
          <div
            class="absolute inset-0 flex items-center justify-center text-xs text-white/90"
          >
            Náhled (orientační)
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between gap-3">
        <div class="text-sm text-gray-600">Obrázek banneru (volitelné)</div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 active:bg-green-800 disabled:opacity-50"
            :disabled="busy"
            @click="$emit('pickImage')"
          >
            Vybrat obrázek
          </button>

          <button
            v-if="bannerUrl"
            type="button"
            class="px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 disabled:opacity-50"
            :disabled="busy"
            @click="$emit('removeImage')"
            title="Smaže banner_url a nechá jen builder pozadí"
          >
            Odebrat obrázek
          </button>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="font-medium mb-2">Pozadí z palety</div>
          <div class="flex flex-wrap gap-3">
            <button
              v-for="k in colorKeys"
              :key="'bg-' + k"
              type="button"
              class="swatch"
              :class="{ active: form.bg_key === k }"
              :style="swatchStyle(k)"
              @click="form.bg_key = k"
            />
          </div>
          <div class="mt-4 text-xs text-gray-500">
            Pokud nahraješ obrázek, barva pozadí nebude fungovat.
          </div>
        </div>

        <div>
          <div class="font-medium mb-2">Filtr (překrytí)</div>
          <div class="flex flex-wrap gap-3">
            <button
              v-for="k in colorKeys"
              :key="'ov-' + k"
              type="button"
              class="swatch"
              :class="{ active: form.overlay_key === k }"
              :style="swatchStyle(k)"
              @click="form.overlay_key = k"
            />
          </div>

          <div class="mt-4">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">Opacity</div>
              <div class="text-sm text-gray-500">
                {{ Math.round(form.overlay_opacity * 100) }}%
              </div>
            </div>
            <input
              type="range"
              min="0"
              max="1"
              step="0.01"
              v-model.number="form.overlay_opacity"
              class="w-full"
            />
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button
          type="button"
          class="px-4 py-2 rounded border bg-white hover:bg-gray-50"
          :disabled="busy"
          @click="cancel"
        >
          Zrušit
        </button>

        <button
          type="button"
          class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
          :disabled="busy"
          @click="save"
        >
          Uložit
        </button>
      </div>
    </div>

    <div v-else class="text-sm text-gray-500">Vyber banner.</div>
  </Dialog>
</template>

<script setup>
import { computed, reactive, watch, ref } from "vue";
import Dialog from "primevue/dialog";

const props = defineProps({
  visible: { type: Boolean, default: false },

  bannerId: { type: [Number, String], default: null },
  bannerUrl: { type: String, default: null },

  // initial values
  bgKey: { type: String, default: "transparent" },
  overlayKey: { type: String, default: "transparent" },
  overlayOpacity: { type: Number, default: 0 }, // 0..1

  palette: { type: Object, required: true },
  busy: { type: Boolean, default: false },
});

const emit = defineEmits(["update:visible", "save", "pickImage", "removeImage"]);

const localVisible = ref(props.visible);
watch(
  () => props.visible,
  (v) => (localVisible.value = v)
);
watch(localVisible, (v) => emit("update:visible", v));

const colorKeys = [
  "primary",
  "secondary",
  "tertiary",
  "quaternary",
  "quinary",
  "transparent",
];

const checkerCss =
  "linear-gradient(45deg, #e5e7eb 25%, transparent 25%)," +
  "linear-gradient(-45deg, #e5e7eb 25%, transparent 25%)," +
  "linear-gradient(45deg, transparent 75%, #e5e7eb 75%)," +
  "linear-gradient(-45deg, transparent 75%, #e5e7eb 75%)";

const swatchStyle = (k) => {
  if (k === "transparent") {
    return {
      backgroundImage: checkerCss,
      backgroundSize: "10px 10px",
      backgroundPosition: "0 0, 0 5px, 5px -5px, -5px 0px",
      backgroundColor: "#fff",
    };
  }
  return { backgroundColor: props.palette?.[k] ?? "transparent" };
};

const form = reactive({
  bg_key: props.bgKey,
  overlay_key: props.overlayKey,
  overlay_opacity: props.overlayOpacity,
});

watch(
  () => [props.bgKey, props.overlayKey, props.overlayOpacity, props.bannerId],
  () => {
    form.bg_key = props.bgKey;
    form.overlay_key = props.overlayKey;
    form.overlay_opacity = props.overlayOpacity;
  }
);

const bgPreviewStyle = computed(() => ({
  backgroundColor:
    form.bg_key === "transparent"
      ? "transparent"
      : props.palette?.[form.bg_key] ?? "transparent",
}));

const overlayPreviewStyle = computed(() => {
  const k = form.overlay_key;
  const op = Number(form.overlay_opacity ?? 0);
  if (!k || k === "transparent" || op <= 0) return null;
  return { backgroundColor: props.palette?.[k] ?? "transparent", opacity: op };
});

const cancel = () => {
  // restore to props values
  form.bg_key = props.bgKey;
  form.overlay_key = props.overlayKey;
  form.overlay_opacity = props.overlayOpacity;
  localVisible.value = false;
};

const save = () => {
  emit("save", {
    bg_key: form.bg_key,
    overlay_key: form.overlay_key,
    overlay_opacity: Number(form.overlay_opacity ?? 0),
  });
};
</script>

<style scoped>
.swatch {
  width: 28px;
  height: 28px;
  border-radius: 9999px;
  border: 1px solid rgba(15, 23, 42, 0.18);
  display: inline-block;
  flex: 0 0 auto;
  padding: 0;
  line-height: 0;
}
.swatch.active {
  box-shadow: 0 0 0 2px #22c55e, 0 0 0 4px #ffffff;
}
.swatch:active {
  transform: scale(0.98);
}
</style>
