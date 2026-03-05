<template>
  <div class="mt-4">
    <div class="py-1">
      <Button
        class="mr-2"
        label="Předchozí krok"
        severity="secondary"
        @click="$emit('goPrev')"
      />
    </div>

    <div class="mt-6 w-full max-w-4xl bg-white p-6 shadow-sm">
      <h3 class="text-lg font-bold mb-4">Vzhled inputů</h3>

      <!-- ================== BARVY ================== -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- BG -->
        <div>
          <label class="font-medium block mb-2">Barva pozadí inputu</label>
          <div class="flex items-center gap-3">
            <button
              v-for="k in colorKeys"
              :key="'bg-' + k"
              type="button"
              class="w-7 h-7 rounded-full border overflow-hidden"
              :class="{
                'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                  local.input_background_color === k,
              }"
              :style="colorCircleStyle(k)"
              @click="pickColor('input_background_color', k)"
              :title="colorTitle(k)"
            />
          </div>
        </div>

        <!-- TEXT -->
        <div>
          <label class="font-medium block mb-2">Barva textu</label>
          <div class="flex items-center gap-3">
            <button
              v-for="k in colorKeys"
              :key="'tx-' + k"
              type="button"
              class="w-7 h-7 rounded-full border overflow-hidden"
              :class="{
                'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                  local.input_text_color === k,
              }"
              :style="colorCircleStyle(k)"
              @click="pickColor('input_text_color', k)"
              :title="colorTitle(k)"
            />
          </div>
        </div>
      </div>

      <!-- ================== BORDER + RADIUS + FONT ================== -->
      <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- BORDER -->
        <div>
          <label class="font-medium block mb-2">Ohraničení</label>

          <div class="mb-4">
            <SelectButton
              v-model="local.input_border_enabled"
              :options="borderToggleOptions"
              optionLabel="label"
              optionValue="value"
              @change="save"
            />
          </div>

          <!-- šířka -->
          <div class="flex items-center gap-3 mb-6" v-if="local.input_border_enabled">
            <label class="text-sm text-gray-700">Šířka (px)</label>
            <input
              type="number"
              min="0"
              max="20"
              v-model.number="local.input_border_width"
              @change="save"
              class="w-24 rounded border px-2 py-1"
              :disabled="!local.input_border_enabled"
            />
          </div>

          <!-- barva borderu -->
          <div v-if="local.input_border_enabled">
            <label class="text-sm text-gray-700 block mb-2">Barva ohraničení</label>
            <div class="flex items-center gap-3">
              <button
                v-for="k in colorKeys"
                :key="'b-' + k"
                type="button"
                class="w-7 h-7 rounded-full border overflow-hidden"
                :class="{
                  'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                    local.input_border_color === k,
                }"
                :style="colorCircleStyle(k)"
                @click="pickColor('input_border_color', k)"
                :title="colorTitle(k)"
              />
            </div>
          </div>

          <!-- RADIUS -->
          <div class="mt-8">
            <label class="font-medium block mb-2">Zaoblení (radius)</label>
            <div class="flex items-center gap-3">
              <input
                type="number"
                min="0"
                max="60"
                v-model.number="local.input_radius"
                @change="save"
                class="w-24 rounded border px-2 py-1"
              />
              <span class="text-sm text-gray-500">px</span>
            </div>
          </div>
        </div>

        <!-- FONT -->
        <div>
          <label class="font-medium block mb-2">Tloušťka písma</label>

          <select
            v-model="local.input_font_weight"
            @change="save"
            class="w-full rounded border px-3 py-2"
          >
            <option v-for="o in fontWeightOptions" :key="o.value" :value="o.value">
              {{ o.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- ================== FOCUS RING ================== -->
      <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <label class="font-medium block mb-2">Glow efekt při kliknutí do inputu</label>

          <!-- ON/OFF -->
          <div class="mb-4">
            <SelectButton
              v-model="local.input_focus_ring_enabled"
              :options="borderToggleOptions"
              optionLabel="label"
              optionValue="value"
              @change="onRingToggle"
            />
          </div>

          <!-- barva + šířka jen když je ring ON -->
          <div v-if="local.input_focus_ring_enabled">
            <div class="mb-4">
              <label class="text-sm text-gray-700 block mb-2">Barva glow efektu</label>
              <div class="flex items-center gap-3">
                <button
                  v-for="k in colorKeys"
                  :key="'fr-' + k"
                  type="button"
                  class="w-7 h-7 rounded-full border overflow-hidden"
                  :class="{
                    'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                      local.input_focus_ring_color === k,
                  }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('input_focus_ring_color', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>

            <div class="flex items-center gap-3">
              <label class="text-sm text-gray-700">Síla glow (px)</label>
              <input
                type="number"
                min="0"
                max="30"
                v-model.number="local.input_focus_ring_width"
                @change="save"
                class="w-24 rounded border px-2 py-1"
              />
            </div>

            <div class="mt-2 text-xs text-gray-500">
              Glow je jemné zvýraznění kolem inputu po kliknutí (focus).
            </div>
          </div>

          <div v-else class="mt-2 text-xs text-gray-500">Glow efekt je vypnutý.</div>
        </div>
      </div>

      <!-- ================== PREVIEW ================== -->
      <div class="mt-10">
        <h3 class="text-lg font-bold mb-4">Ukázkový vzhled inputu</h3>

        <input
          class="w-1/2 px-4 py-3 transition-all outline-none"
          :style="previewStyle"
          :class="previewClass"
          placeholder="Při psaní textu vidíte výsledek jako na webu.."
        />

        <div class="mt-2 text-xs text-gray-500">
          Změna ovlivní všechny inputy na webu.
        </div>
      </div>
    </div>

    <div class="py-6">
      <Button label="Další krok" @click="$emit('goNext')" />
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, watch } from "vue";
import axios from "axios";
import SelectButton from "primevue/selectbutton";

const props = defineProps({
  mainDesign: { type: Object, required: true },
});

defineEmits(["goPrev", "goNext"]);

const colorKeys = [
  "primary",
  "secondary",
  "tertiary",
  "quaternary",
  "quinary",
  "transparent",
];

const colorLabels = {
  primary: "1. Hlavní",
  secondary: "2. Druhá",
  tertiary: "3. Třetí",
  quaternary: "4. Čtvrtá",
  quinary: "5. Pátá",
  transparent: "Transparent",
};

const local = reactive({
  input_background_color: "transparent",
  input_text_color: "secondary",

  input_border_enabled: false,
  input_border_width: 1,
  input_border_color: "secondary",

  input_radius: 8,
  input_font_weight: "400",

  // ✅ ring toggle + values
  input_focus_ring_enabled: false,
  input_focus_ring_color: "transparent",
  input_focus_ring_width: 0,
});

const palette = computed(() => ({
  primary: props.mainDesign.primary_color,
  secondary: props.mainDesign.secondary_color,
  tertiary: props.mainDesign.tertiary_color,
  quaternary: props.mainDesign.quaternary_color,
  quinary: props.mainDesign.quinary_color,
  transparent: "transparent",
}));

const checkerCss =
  "linear-gradient(45deg, #e5e7eb 25%, transparent 25%)," +
  "linear-gradient(-45deg, #e5e7eb 25%, transparent 25%)," +
  "linear-gradient(45deg, transparent 75%, #e5e7eb 75%)," +
  "linear-gradient(-45deg, transparent 75%, #e5e7eb 75%)";

const colorCircleStyle = (k) => {
  if (k === "transparent") {
    return {
      backgroundImage: checkerCss,
      backgroundSize: "10px 10px",
      backgroundPosition: "0 0, 0 5px, 5px -5px, -5px 0px",
      backgroundColor: "#fff",
    };
  }
  return { backgroundColor: palette.value[k] };
};

const colorTitle = (k) => {
  if (k === "transparent") return "Transparent";
  return `${colorLabels[k]} ${palette.value[k]}`;
};

const normalizeHex = (v) => {
  if (v === null || v === undefined) return null;
  const s = String(v).trim().toLowerCase();
  if (s === "" || s === "transparent") return "transparent";
  if (s[0] !== "#") return s;
  if (s.length === 4) return "#" + s[1] + s[1] + s[2] + s[2] + s[3] + s[3];
  return s;
};

const mapHexToKey = (val, incoming) => {
  const s = normalizeHex(incoming);
  if (s === null || s === "transparent") return "transparent";
  if (colorKeys.includes(s)) return s;

  const p = {
    primary: normalizeHex(val.primary_color),
    secondary: normalizeHex(val.secondary_color),
    tertiary: normalizeHex(val.tertiary_color),
    quaternary: normalizeHex(val.quaternary_color),
    quinary: normalizeHex(val.quinary_color),
  };

  const found = Object.entries(p).find(([, hex]) => hex && hex === s);
  return found ? found[0] : null;
};

const borderToggleOptions = [
  { label: "Off", value: false },
  { label: "On", value: true },
];

const fontWeightOptions = [
  { label: "Thin (100)", value: "100" },
  { label: "ExtraLight (200)", value: "200" },
  { label: "Light (300)", value: "300" },
  { label: "Normal (400)", value: "400" },
  { label: "Medium (500)", value: "500" },
  { label: "SemiBold (600)", value: "600" },
  { label: "Bold (700)", value: "700" },
  { label: "ExtraBold (800)", value: "800" },
  { label: "Black (900)", value: "900" },
];

// base border
const borderCss = computed(() => {
  if (!local.input_border_enabled) return "none";
  const w = Math.max(0, Number(local.input_border_width || 0));
  const key = local.input_border_color;
  const col = key === "transparent" ? "transparent" : palette.value[key];
  return `${w}px solid ${col}`;
});

// ring (box-shadow) – jen když enabled
const focusRingCss = computed(() => {
  if (!local.input_focus_ring_enabled) return "none";
  const w = Math.max(0, Number(local.input_focus_ring_width || 0));
  if (w === 0) return "none";
  const key = local.input_focus_ring_color;
  const col = key === "transparent" ? "transparent" : palette.value[key];
  return `0 0 0 ${w}px ${col}`;
});

const previewStyle = computed(() => {
  const bgKey = local.input_background_color;
  const txKey = local.input_text_color;

  const bg = bgKey === "transparent" ? "transparent" : palette.value[bgKey];
  const tx = txKey === "transparent" ? "transparent" : palette.value[txKey];

  const radius = Math.max(0, Number(local.input_radius || 0));

  return {
    backgroundColor: bg,
    color: tx,
    border: borderCss.value,
    borderRadius: `${radius}px`,
    fontWeight: local.input_font_weight,

    "--input-focus-ring": focusRingCss.value,
    "--input-placeholder-color": tx,
  };
});

const previewClass = "input-preview";

watch(
  () => props.mainDesign,
  (val) => {
    if (!val) return;

    const bgKey = mapHexToKey(val, val.input_background_color);
    const txKey = mapHexToKey(val, val.input_text_color);
    const bKey = mapHexToKey(val, val.input_border_color);

    const frKey = mapHexToKey(val, val.input_focus_ring_color);

    if (bgKey) local.input_background_color = bgKey;
    if (txKey) local.input_text_color = txKey;
    if (bKey) local.input_border_color = bKey;

    if (frKey) local.input_focus_ring_color = frKey;

    local.input_border_enabled = Boolean(
      val.input_border_enabled ?? local.input_border_enabled
    );
    local.input_border_width = Number(val.input_border_width ?? local.input_border_width);
    local.input_radius = Number(val.input_radius ?? local.input_radius);
    local.input_font_weight = String(val.input_font_weight ?? local.input_font_weight);

    local.input_focus_ring_enabled = Boolean(
      val.input_focus_ring_enabled ?? local.input_focus_ring_enabled
    );
    local.input_focus_ring_width = Number(
      val.input_focus_ring_width ?? local.input_focus_ring_width
    );
  },
  { immediate: true, deep: true }
);

const pickColor = (key, colorKey) => {
  local[key] = colorKey;
  save();
};

const keyToDbColor = (key) => {
  if (key === "transparent") return null;
  return palette.value[key];
};

const onRingToggle = () => {
  // když se vypne ring, vynutíme width 0 (ať je to konzistentní v DB i preview)
  if (!local.input_focus_ring_enabled) {
    local.input_focus_ring_width = 0;
    local.input_focus_ring_color = "transparent";
  } else {
    // když ho zapneš a je 0, nastav rozumný default
    if (Number(local.input_focus_ring_width || 0) === 0) local.input_focus_ring_width = 2;
    if (local.input_focus_ring_color === "transparent")
      local.input_focus_ring_color = "secondary";
  }
  save();
};

const save = async () => {
  const payload = {
    input_background_color: keyToDbColor(local.input_background_color),
    input_text_color: keyToDbColor(local.input_text_color),

    input_border_enabled: local.input_border_enabled ? 1 : 0,
    input_border_width: Number(local.input_border_width || 0),
    input_border_color: local.input_border_enabled
      ? keyToDbColor(local.input_border_color)
      : null,

    input_radius: Number(local.input_radius || 0),
    input_font_weight: String(local.input_font_weight),

    // ✅ ring
    input_focus_ring_enabled: local.input_focus_ring_enabled ? 1 : 0,
    input_focus_ring_color: local.input_focus_ring_enabled
      ? keyToDbColor(local.input_focus_ring_color)
      : null,
    input_focus_ring_width: local.input_focus_ring_enabled
      ? Number(local.input_focus_ring_width || 0)
      : 0,
  };

  await axios.post("/admin/main-design", payload, {
    headers: { Accept: "application/json" },
  });
};
</script>

<style scoped>
.input-preview:focus {
  box-shadow: var(--input-focus-ring) !important;
}

/* placeholder ve scoped občas nezabere -> použij :deep() */
:deep(.input-preview::placeholder) {
  color: var(--input-placeholder-color) !important;
  opacity: 0.6 !important;
}

:deep(.input-preview::-webkit-input-placeholder) {
  color: var(--input-placeholder-color) !important;
  opacity: 0.6 !important;
}

:deep(.input-preview::-moz-placeholder) {
  color: var(--input-placeholder-color) !important;
  opacity: 0.6 !important;
}

:deep(.input-preview:-ms-input-placeholder) {
  color: var(--input-placeholder-color) !important;
  opacity: 0.6 !important;
}
</style>
