<template>
  <Dialog
    v-model:visible="localVisible"
    modal
    :style="{ width: '720px', maxWidth: '95vw' }"
  >
    <template #header><div class="font-bold">Text banneru</div></template>

    <div v-if="bannerId" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-1">H1 text</label>
          <input
            v-model="form.heading_h1"
            class="w-full rounded border px-3 py-2"
            type="text"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">H1 velikost (px)</label>
          <input
            v-model.number="form.h1_size"
            class="w-full rounded border px-3 py-2"
            type="number"
            min="8"
            max="200"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">H1 barva</label>
          <div class="flex flex-wrap gap-3">
            <button
              v-for="k in colorKeys"
              :key="'h1c-' + k"
              type="button"
              class="swatch"
              :class="{ active: form.h1_color_key === k }"
              :style="swatchStyle(k)"
              @click="form.h1_color_key = k"
            />
          </div>
        </div>

        <div class="hidden md:block"></div>

        <div>
          <label class="block text-sm font-medium mb-1">H2 text</label>
          <input
            v-model="form.heading_h2"
            class="w-full rounded border px-3 py-2"
            type="text"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">H2 velikost (px)</label>
          <input
            v-model.number="form.h2_size"
            class="w-full rounded border px-3 py-2"
            type="number"
            min="8"
            max="200"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">H2 barva</label>
          <div class="flex flex-wrap gap-3">
            <button
              v-for="k in colorKeys"
              :key="'h2c-' + k"
              type="button"
              class="swatch"
              :class="{ active: form.h2_color_key === k }"
              :style="swatchStyle(k)"
              @click="form.h2_color_key = k"
            />
          </div>
        </div>
      </div>

      <!-- BUTTON SECTION -->
      <div class="border-t pt-5 space-y-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <div class="text-sm font-medium">Tlačítko</div>
            <div class="text-xs text-gray-500">
              Zapni, pokud chceš na banneru zobrazit CTA tlačítko.
            </div>
          </div>

          <!-- jednoduchý switch bez extra knihoven -->
          <button
            type="button"
            class="switch"
            :class="{ on: form.button_enabled }"
            :disabled="busy"
            @click="form.button_enabled = !form.button_enabled"
            aria-label="Zapnout/vypnout tlačítko"
          >
            <span class="switch-dot" />
          </button>
        </div>

        <div v-if="form.button_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium mb-1">Text tlačítka</label>
            <input
              v-model="form.button_text"
              class="w-full rounded border px-3 py-2"
              type="text"
              placeholder="Např. Zjistit více"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">URL tlačítka</label>
            <input
              v-model="form.button_url"
              class="w-full rounded border px-3 py-2"
              type="text"
              placeholder="Např. /kontakt"
            />
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button
          class="px-4 py-2 rounded border bg-white hover:bg-gray-50"
          :disabled="busy"
          @click="cancel"
        >
          Zrušit
        </button>
        <button
          class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
          :disabled="busy"
          @click="save"
        >
          Uložit
        </button>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { reactive, ref, watch } from "vue";
import Dialog from "primevue/dialog";

const props = defineProps({
  visible: { type: Boolean, default: false },
  bannerId: { type: [Number, String], default: null },
  busy: { type: Boolean, default: false },

  headingH1: { type: String, default: "" },
  headingH2: { type: String, default: "" },
  h1ColorKey: { type: String, default: "secondary" },
  h2ColorKey: { type: String, default: "secondary" },
  h1Size: { type: Number, default: 48 },
  h2Size: { type: Number, default: 22 },

  buttonEnabled: { type: Boolean, default: false },
  buttonText: { type: String, default: "" },
  buttonUrl: { type: String, default: "" },

  palette: { type: Object, required: true },
});

const emit = defineEmits(["update:visible", "save"]);

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
  heading_h1: props.headingH1,
  heading_h2: props.headingH2,
  h1_color_key: props.h1ColorKey,
  h2_color_key: props.h2ColorKey,
  h1_size: props.h1Size,
  h2_size: props.h2Size,

  button_enabled: !!props.buttonEnabled,
  button_text: props.buttonText,
  button_url: props.buttonUrl,
});

watch(
  () => [
    props.headingH1,
    props.headingH2,
    props.h1ColorKey,
    props.h2ColorKey,
    props.h1Size,
    props.h2Size,
    props.buttonEnabled,
    props.buttonText,
    props.buttonUrl,
    props.bannerId,
  ],
  () => {
    form.heading_h1 = props.headingH1 ?? "";
    form.heading_h2 = props.headingH2 ?? "";
    form.h1_color_key = props.h1ColorKey ?? "secondary";
    form.h2_color_key = props.h2ColorKey ?? "secondary";
    form.h1_size = Number(props.h1Size ?? 48);
    form.h2_size = Number(props.h2Size ?? 22);

    form.button_enabled = !!props.buttonEnabled;
    form.button_text = props.buttonText ?? "";
    form.button_url = props.buttonUrl ?? "";
  }
);

const cancel = () => {
  localVisible.value = false;
};

const save = () => {
  emit("save", {
    heading_h1: form.heading_h1 || null,
    heading_h2: form.heading_h2 || null,
    h1_color_key: form.h1_color_key,
    h2_color_key: form.h2_color_key,
    h1_size: Number(form.h1_size || 48),
    h2_size: Number(form.h2_size || 22),

    button_enabled: !!form.button_enabled,
    button_text: form.button_enabled ? form.button_text || null : null,
    button_url: form.button_enabled ? form.button_url || null : null,
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

/* switch */
.switch {
  width: 48px;
  height: 26px;
  border-radius: 9999px;
  border: 1px solid rgba(15, 23, 42, 0.18);
  background: #e5e7eb;
  position: relative;
  padding: 0;
  cursor: pointer;
  transition: background 150ms ease;
}
.switch.on {
  background: #22c55e;
}
.switch:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.switch-dot {
  width: 22px;
  height: 22px;
  border-radius: 9999px;
  background: white;
  position: absolute;
  top: 1px;
  left: 1px;
  transition: transform 150ms ease;
}
.switch.on .switch-dot {
  transform: translateX(22px);
}
</style>
