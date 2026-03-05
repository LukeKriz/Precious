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
      <h3 class="text-lg font-bold mb-4">Vzhled tlačítek</h3>

      <!-- ================== STYLY ================== -->
      <div class="mb-8">
        <label class="font-medium block mb-2">Styl tlačítka</label>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <button
            v-for="style in buttonStyles"
            :key="style.value"
            type="button"
            class="rounded-lg p-4 text-sm flex justify-center items-center bg-white"
            :class="{ 'ring-2 ring-green-500': local.button_type === style.value }"
            @click="setValue('button_type', style.value)"
          >
            <!-- horní preview je vždy bílé + černý text -->
            <span
              class="bg-white text-black inline-flex items-center justify-center"
              :class="style.previewClass"
              :style="stylePreviewStyle"
            >
              Tlačítko
            </span>
          </button>
        </div>
      </div>

      <!-- ================== BARVY (všude kolečka) ================== -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- BG -->
        <div>
          <label class="font-medium block mb-2">Barva pozadí</label>
          <div class="flex items-center gap-3">
            <button
              v-for="k in colorKeys"
              :key="'bg-' + k"
              type="button"
              class="w-7 h-7 rounded-full border relative overflow-hidden"
              :class="{
                'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                  local.button_color === k,
              }"
              :style="colorCircleStyle(k)"
              @click="pickColor('button_color', k)"
              :title="colorTitle(k)"
            ></button>
          </div>
        </div>

        <!-- TEXT -->
        <div>
          <label class="font-medium block mb-2">Barva textu</label>
          <div class="flex items-center gap-3">
            <button
              v-for="k in colorKeys"
              :key="'text-' + k"
              type="button"
              class="w-7 h-7 rounded-full border relative overflow-hidden"
              :class="{
                'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                  local.button_text_color === k,
              }"
              :style="colorCircleStyle(k)"
              @click="pickColor('button_text_color', k)"
              :title="colorTitle(k)"
            ></button>
          </div>
        </div>

        <!-- HOVER BG -->
        <div>
          <label class="font-medium block mb-2">Pozadí při najetí myší</label>
          <div class="flex items-center gap-3">
            <button
              v-for="k in colorKeys"
              :key="'hbg-' + k"
              type="button"
              class="w-7 h-7 rounded-full border relative overflow-hidden"
              :class="{
                'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                  local.button_hover_color === k,
              }"
              :style="colorCircleStyle(k)"
              @click="pickColor('button_hover_color', k)"
              :title="colorTitle(k)"
            ></button>
          </div>
        </div>

        <!-- HOVER TEXT -->
        <div>
          <label class="font-medium block mb-2">Barva textu při najetí myší</label>
          <div class="flex items-center gap-3">
            <button
              v-for="k in colorKeys"
              :key="'htext-' + k"
              type="button"
              class="w-7 h-7 rounded-full border relative overflow-hidden"
              :class="{
                'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                  local.button_hover_text_color === k,
              }"
              :style="colorCircleStyle(k)"
              @click="pickColor('button_hover_text_color', k)"
              :title="colorTitle(k)"
            ></button>
          </div>
        </div>
      </div>

      <!-- ================== BORDER + FONT ================== -->
      <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- BORDER -->
        <div>
          <label class="font-medium block mb-2">Ohraničení</label>

          <!-- ON/OFF -->
          <div class="mb-4">
            <SelectButton
              v-model="local.button_border_enabled"
              :options="borderToggleOptions"
              optionLabel="label"
              optionValue="value"
              @change="save"
            />
          </div>

          <!-- šířka -->
          <div class="flex items-center gap-3 mb-6" v-if="local.button_border_enabled">
            <label class="text-sm text-gray-700">Šířka (px)</label>
            <input
              type="number"
              min="0"
              max="20"
              v-model.number="local.button_border_width"
              @change="save"
              class="w-24 rounded border px-2 py-1"
              :disabled="!local.button_border_enabled"
            />
          </div>

          <!-- ✅ barvy borderu jen pokud je ON -->
          <div v-if="local.button_border_enabled" class="space-y-6">
            <div>
              <label class="text-sm text-gray-700 block mb-2">Barva ohraničení</label>
              <div class="flex items-center gap-3">
                <button
                  v-for="k in colorKeys"
                  :key="'b-' + k"
                  type="button"
                  class="w-7 h-7 rounded-full border relative overflow-hidden"
                  :class="{
                    'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                      local.button_border_color === k,
                  }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('button_border_color', k)"
                  :title="colorTitle(k)"
                ></button>
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-700 block mb-2"
                >Barva ohraničení při najetí myší</label
              >
              <div class="flex items-center gap-3">
                <button
                  v-for="k in colorKeys"
                  :key="'bh-' + k"
                  type="button"
                  class="w-7 h-7 rounded-full border relative overflow-hidden"
                  :class="{
                    'ring-2 ring-green-500 ring-offset-2 ring-offset-white':
                      local.button_border_hover_color === k,
                  }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('button_border_hover_color', k)"
                  :title="colorTitle(k)"
                ></button>
              </div>
            </div>
          </div>
        </div>

        <!-- FONT -->
        <div>
          <label class="font-medium block mb-2">Tloušťka písma</label>

          <select
            v-model="local.button_font_weight"
            @change="save"
            class="w-full rounded border px-3 py-2"
          >
            <option v-for="o in fontWeightOptions" :key="o.value" :value="o.value">
              {{ o.label }}
            </option>
          </select>

          <div class="mt-3 text-xs text-gray-500">
            Změna ovlivní všechna tlačítka na webu.
          </div>
        </div>
      </div>

      <!-- ================== PREVIEW ================== -->
      <div class="mt-10">
        <label class="font-medium block mb-2 mt-4">
          <h3 class="text-lg font-bold mb-4">Ukázkový vzhled tlačítek</h3>
        </label>

        <button
          class="transition-all"
          :class="previewClass"
          :style="previewStyle"
          @mouseenter="isHover = true"
          @mouseleave="isHover = false"
        >
          Ukázkové tlačítko
        </button>

        <div class="mt-2 text-xs text-gray-500">
          Při najetí myší uvidíte efekt změny barev.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, watch, ref } from "vue";
import axios from "axios";
import SelectButton from "primevue/selectbutton";

const props = defineProps({
  mainDesign: { type: Object, required: true },
});
defineEmits(["goPrev", "goNext"]);

const isHover = ref(false);

// ✅ přidán transparent jako poslední volba
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
  button_type: "style_1",

  button_color: "primary",
  button_text_color: "secondary",
  button_hover_color: "tertiary",
  button_hover_text_color: "quinary",

  button_border_enabled: false,
  button_border_width: 1,
  button_border_color: "secondary",
  button_border_hover_color: "secondary",

  button_font_weight: "400",
});

const palette = computed(() => ({
  primary: props.mainDesign.primary_color,
  secondary: props.mainDesign.secondary_color,
  tertiary: props.mainDesign.tertiary_color,
  quaternary: props.mainDesign.quaternary_color,
  quinary: props.mainDesign.quinary_color,
  transparent: "transparent",
}));

// ✅ checkerboard background pro transparent
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

// -------------------------
// ✅ robustní mapování DB -> key (hex / null / 'transparent' / key / #fff)
// -------------------------
const normalizeHex = (v) => {
  if (v === null || v === undefined) return null;
  const s = String(v).trim().toLowerCase();
  if (s === "" || s === "transparent") return "transparent";
  if (s[0] !== "#") return s; // může být už key
  // #fff -> #ffffff
  if (s.length === 4) {
    return "#" + s[1] + s[1] + s[2] + s[2] + s[3] + s[3];
  }
  return s;
};

const mapHexToKey = (val, incoming) => {
  const s = normalizeHex(incoming);

  // transparent / null
  if (s === null || s === "transparent") return "transparent";

  // už je to key
  if (
    ["primary", "secondary", "tertiary", "quaternary", "quinary", "transparent"].includes(
      s
    )
  ) {
    return s;
  }

  // map hex -> key
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

watch(
  () => props.mainDesign,
  (val) => {
    if (!val) return;

    local.button_type = val.button_type ?? local.button_type;

    const bgKey = mapHexToKey(val, val.button_color);
    const txKey = mapHexToKey(val, val.button_text_color);
    const hbgKey = mapHexToKey(val, val.button_hover_color);
    const htxKey = mapHexToKey(val, val.button_hover_text_color);
    const bKey = mapHexToKey(val, val.button_border_color);
    const bhKey = mapHexToKey(val, val.button_border_hover_color);

    if (bgKey) local.button_color = bgKey;
    if (txKey) local.button_text_color = txKey;
    if (hbgKey) local.button_hover_color = hbgKey;
    if (htxKey) local.button_hover_text_color = htxKey;
    if (bKey) local.button_border_color = bKey;
    if (bhKey) local.button_border_hover_color = bhKey;

    local.button_border_enabled = Boolean(
      val.button_border_enabled ?? local.button_border_enabled
    );
    local.button_border_width = Number(
      val.button_border_width ?? local.button_border_width
    );
    local.button_font_weight = String(val.button_font_weight ?? local.button_font_weight);
  },
  { immediate: true, deep: true }
);

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

const buttonStyles = [
  { value: "style_1", previewClass: "px-4 py-2 rounded-md" },
  { value: "style_2", previewClass: "px-6 py-3 rounded-lg" },
  { value: "style_3", previewClass: "px-8 py-3 rounded-full" },
  { value: "style_4", previewClass: "px-6 py-2 uppercase tracking-wide rounded" },
  { value: "style_5", previewClass: "px-10 py-4 text-lg rounded-xl" },
];

const previewClass = computed(() => {
  const style = buttonStyles.find((s) => s.value === local.button_type);
  return style ? style.previewClass : "px-4 py-2 rounded";
});

const borderCss = computed(() => {
  if (!local.button_border_enabled) return "none";
  const w = Math.max(0, Number(local.button_border_width || 0));

  const borderKey = isHover.value
    ? local.button_border_hover_color
    : local.button_border_color;

  // ✅ transparent border = CSS transparent
  const col = borderKey === "transparent" ? "transparent" : palette.value[borderKey];

  return `${w}px solid ${col}`;
});

const previewStyle = computed(() => {
  const bgKey = isHover.value ? local.button_hover_color : local.button_color;
  const txKey = isHover.value ? local.button_hover_text_color : local.button_text_color;

  const bg = bgKey === "transparent" ? "transparent" : palette.value[bgKey];
  const tx = txKey === "transparent" ? "transparent" : palette.value[txKey];

  return {
    backgroundColor: bg,
    color: tx,
    border: borderCss.value,
    fontWeight: local.button_font_weight,
  };
});

// horní styl preview (bílé, černé)
const stylePreviewStyle = computed(() => {
  const w = Math.max(1, Number(local.button_border_width || 1));
  return {
    border: `${w}px solid #000`,
    fontWeight: local.button_font_weight,
  };
});

const setValue = (key, value) => {
  local[key] = value;
  save();
};

const pickColor = (key, colorKey) => {
  local[key] = colorKey;
  save();
};

// ✅ helper: key -> db value (hex nebo null pro transparent)
const keyToDbColor = (key) => {
  if (key === "transparent") return null;
  return palette.value[key];
};

const save = async () => {
  const payload = {
    button_type: local.button_type,

    button_color: keyToDbColor(local.button_color),
    button_text_color: keyToDbColor(local.button_text_color),
    button_hover_color: keyToDbColor(local.button_hover_color),
    button_hover_text_color: keyToDbColor(local.button_hover_text_color),

    button_border_enabled: local.button_border_enabled ? 1 : 0,
    button_border_width: Number(local.button_border_width || 0),

    button_border_color: local.button_border_enabled
      ? keyToDbColor(local.button_border_color)
      : null,
    button_border_hover_color: local.button_border_enabled
      ? keyToDbColor(local.button_border_hover_color)
      : null,

    button_font_weight: String(local.button_font_weight),
  };

  await axios.post("/admin/main-design", payload);
};
</script>
