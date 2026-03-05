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

    <div class="mt-6 w-full max-w-4xl bg-white p-6 shadow-sm rounded-xl">
      <h3 class="text-lg font-bold mb-6">Nastavení hlavičky</h3>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- ================== LEFT: LOGO + PREVIEW ================== -->
        <div class="rounded-xl border p-5">
          <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold">Vaše logo</h4>
          </div>

          <div class="flex items-start gap-6">
            <div
              class="w-40 h-40 border rounded-xl flex items-center justify-center bg-gray-50 overflow-hidden"
            >
              <img
                v-if="mainDesign.logo_url"
                :src="mainDesign.logo_url"
                class="max-w-full max-h-full"
                alt="Logo preview"
              />
              <span v-else class="text-sm text-gray-400">Bez loga</span>
            </div>

            <div class="flex-1">
              <div class="flex items-center gap-3 flex-wrap">
                <input
                  ref="fileInput"
                  type="file"
                  accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                  class="hidden"
                  @change="handleChange"
                />

                <button
                  type="button"
                  @click="openPicker"
                  class="inline-flex items-center justify-center rounded-lg bg-green-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-600 active:bg-green-700"
                >
                  Vybrat soubor
                </button>

                <button
                  v-if="mainDesign.logo_url"
                  type="button"
                  @click.stop.prevent="$emit('deleteLogo')"
                  class="inline-flex items-center justify-center rounded-lg bg-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700"
                >
                  Smazat logo
                </button>
              </div>

              <!-- ✅ INPUTY: ROZMĚRY LOGA (autosave) -->
              <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-sm">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1"
                    >Šířka loga (px)</label
                  >
                  <div class="flex items-center gap-2">
                    <input
                      v-model="logoWidth"
                      type="number"
                      min="1"
                      max="2000"
                      class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30"
                      placeholder="např. 260"
                    />
                    <span class="text-xs text-gray-400">px</span>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1"
                    >Výška loga (px)</label
                  >
                  <div class="flex items-center gap-2">
                    <input
                      v-model="logoHeight"
                      type="number"
                      min="1"
                      max="2000"
                      class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30"
                      placeholder="např. 80"
                    />
                    <span class="text-xs text-gray-400">px</span>
                  </div>
                </div>

                <p class="sm:col-span-2 text-xs text-gray-500">
                  Rozměry se ukládají hned. Při dalším nahrání se obrázek zmenší a uloží
                  zkomprimovaný.
                </p>
              </div>

              <p class="text-xs text-gray-500 mt-3">
                Doporučení: PNG se světlým pozadím nebo SVG, ideálně min. 300px šířka.
              </p>

              <p v-if="mainDesign.logo_url" class="text-xs text-gray-500 mt-2">
                <i class="fa-solid fa-circle-check text-green-500"></i>
              </p>
            </div>
          </div>

          <!-- PREVIEW (dole vlevo) -->
          <div class="mt-20">
            <div class="text-xs text-gray-500 mb-2">
              Ukázkový náhled hlavičky (obsah je pouze orientační)
            </div>

            <div class="rounded-xl border overflow-hidden">
              <!-- HEADER -->
              <div
                class="px-4 py-3 flex items-center justify-between"
                :style="{ background: previewHeaderBg }"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <!-- logo v preview -->
                  <div
                    class="shrink-0 w-20 h-10 grid place-items-center overflow-hidden"
                    :style="{ borderColor: previewHeaderText + '22' }"
                  >
                    <img
                      v-if="mainDesign.logo_url"
                      :src="mainDesign.logo_url"
                      class="w-full h-full object-contain p-1"
                      alt="Logo"
                      draggable="false"
                    />
                    <span
                      v-else
                      class="text-[10px] opacity-60"
                      :style="{ color: previewHeaderText }"
                      >LOGO</span
                    >
                  </div>
                </div>

                <nav class="flex items-center gap-6 text-sm font-semibold">
                  <span
                    class="cursor-pointer select-none"
                    @mouseenter="menuHoverA = true"
                    @mouseleave="menuHoverA = false"
                    :style="{
                      color: menuHoverA ? previewHeaderHoverText : previewHeaderText,
                    }"
                  >
                    Položka
                  </span>

                  <span
                    class="cursor-pointer select-none"
                    @mouseenter="menuHoverB = true"
                    @mouseleave="menuHoverB = false"
                    :style="{
                      color: menuHoverB ? previewHeaderHoverText : previewHeaderText,
                    }"
                  >
                    Položka
                  </span>
                </nav>
              </div>

              <div
                class="px-4 py-5"
                :style="{ background: previewSubBg, color: previewSubText }"
              >
                <div class="flex flex-col items-center gap-3">
                  <span
                    v-for="(it, idx) in submenuItems"
                    :key="it + idx"
                    class="text-sm font-semibold cursor-pointer select-none"
                    @mouseenter="activeSubIndex = idx"
                    @mouseleave="activeSubIndex = -1"
                    :style="{
                      color:
                        activeSubIndex === idx ? previewSubHoverText : previewSubText,
                    }"
                  >
                    {{ it }}
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-3 text-xs text-gray-500">Ukládá se automaticky při změně.</div>
          </div>
        </div>

        <!-- ================== RIGHT: COLORS ================== -->
        <div class="rounded-xl border p-5">
          <h4 class="font-semibold mb-4">Barvy hlavičky a submenu</h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- HEADER BG -->
            <div>
              <label class="font-medium block mb-2">Pozadí hlavičky</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'hbg-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.header_background === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('header_background', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>

            <!-- HEADER TEXT -->
            <div>
              <label class="font-medium block mb-2">Barva textu</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'hc-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.header_color === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('header_color', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>

            <!-- HEADER HOVER -->
            <div>
              <label class="font-medium block mb-2">Text při najetí myší</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'hch-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.header_color_hover === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('header_color_hover', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>

            <div class="hidden md:block"></div>

            <!-- SUBMENU BG -->
            <div>
              <label class="font-medium block mb-2">Pozadí submenu</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'sbg-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.submenu_background === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('submenu_background', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>

            <!-- SUBMENU COLOR -->
            <div>
              <label class="font-medium block mb-2">Text submenu</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'sc-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.submenu_color === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('submenu_color', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>

            <!-- SUBMENU HOVER -->
            <div>
              <label class="font-medium block mb-2">Text submenu při najetí myší</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'sh-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.submenu_hover === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('submenu_hover', k)"
                  :title="colorTitle(k)"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="py-6 flex items-center justify-between">
      <Button label="Další krok" @click="$emit('goNext')" />
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, watch, ref } from "vue";
import Button from "primevue/button";

const props = defineProps({
  mainDesign: { type: Object, required: true },
});

const emit = defineEmits([
  "goPrev",
  "goNext",
  "onLogoSelected",
  "deleteLogo",
  "updateHeader",
]);

/* ================== LOGO UPLOAD ================== */
const fileInput = ref(null);

// blokace autosave při hydrate (použijeme i pro logo size)
const isHydrating = ref(false);

// ✅ LOGO SIZE INPUTS
const logoWidth = ref(props.mainDesign.logo_width ?? 301);
const logoHeight = ref(props.mainDesign.logo_height ?? 60);

watch(
  () => props.mainDesign,
  (val) => {
    if (!val) return;

    isHydrating.value = true;

    logoWidth.value = val.logo_width ?? 260;
    logoHeight.value = val.logo_height ?? 80;

    // ostatní hydrate (barvy) máš níž, tam se to taky nastavuje
    setTimeout(() => (isHydrating.value = false), 0);
  },
  { immediate: true, deep: true }
);

const resetInput = () => {
  if (fileInput.value) fileInput.value.value = "";
};

const openPicker = () => {
  resetInput();
  fileInput.value?.click();
};

const normalizeSize = (v, min, max, fallback) => {
  const n = parseInt(v, 10);
  if (Number.isNaN(n)) return fallback;
  return Math.max(min, Math.min(max, n));
};

const handleChange = (e) => {
  const file = e.target.files?.[0];
  if (!file) return;

  emit("onLogoSelected", {
    file,
    logo_width: normalizeSize(logoWidth.value, 1, 2000, 260),
    logo_height: normalizeSize(logoHeight.value, 1, 2000, 80),
  });

  resetInput();
};

// ✅ AUTOSAVE rozměrů přes updateHeader (tj. použije se tvůj saveHeader)
let logoSizeTimer = null;
watch([logoWidth, logoHeight], () => {
  if (isHydrating.value) return;

  clearTimeout(logoSizeTimer);
  logoSizeTimer = setTimeout(() => {
    emit("updateHeader", {
      logo_width: normalizeSize(logoWidth.value, 1, 2000, 260),
      logo_height: normalizeSize(logoHeight.value, 1, 2000, 80),
    });
  }, 400);
});

/* ================== COLORS (keys only) ================== */
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

const colorTitle = (k) =>
  k === "transparent" ? "Transparent" : `${colorLabels[k]} ${palette.value[k]}`;

/* ================== MAP DB -> KEY (robust) ================== */
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

/* ================== LOCAL STATE (keys) ================== */
const local = reactive({
  header_background: "transparent",
  header_color: "secondary",
  header_color_hover: "secondary",
  submenu_background: "transparent",
  submenu_color: "secondary",
  submenu_hover: "secondary",
});

// hydrate barev (ponechávám tvůj původní styl, jen hlídáme isHydrating)
watch(
  () => props.mainDesign,
  (val) => {
    if (!val) return;
    isHydrating.value = true;

    const hbg = mapHexToKey(val, val.header_background);
    const hc = mapHexToKey(val, val.header_color);
    const hch = mapHexToKey(val, val.header_color_hover);
    const sbg = mapHexToKey(val, val.submenu_background);
    const sc = mapHexToKey(val, val.submenu_color);
    const sh = mapHexToKey(val, val.submenu_hover);

    if (hbg) local.header_background = hbg;
    if (hc) local.header_color = hc;
    if (hch) local.header_color_hover = hch;
    if (sbg) local.submenu_background = sbg;
    if (sc) local.submenu_color = sc;
    if (sh) local.submenu_hover = sh;

    setTimeout(() => (isHydrating.value = false), 0);
  },
  { immediate: true, deep: true }
);

const keyToDbColor = (k) => (k === "transparent" ? null : palette.value[k]);

const emitSave = () => {
  const payload = {
    header_background: keyToDbColor(local.header_background),
    header_color: keyToDbColor(local.header_color),
    header_color_hover: keyToDbColor(local.header_color_hover),
    submenu_background: keyToDbColor(local.submenu_background),
    submenu_color: keyToDbColor(local.submenu_color),
    submenu_hover: keyToDbColor(local.submenu_hover),
  };
  emit("updateHeader", payload);
};

const pickColor = (key, colorKey) => {
  local[key] = colorKey;
  if (!isHydrating.value) emitSave();
};

/* ================== PREVIEW STATE (real hover simulation) ================== */
const menuHoverA = ref(false);
const menuHoverB = ref(false);
const activeSubIndex = ref(-1);

const cssColorFromKey = (k) => (k === "transparent" ? "transparent" : palette.value[k]);

const previewHeaderBg = computed(() => cssColorFromKey(local.header_background));
const previewHeaderText = computed(() => cssColorFromKey(local.header_color));
const previewHeaderHoverText = computed(() => cssColorFromKey(local.header_color_hover));

const previewSubBg = computed(() => cssColorFromKey(local.submenu_background));
const previewSubText = computed(() => cssColorFromKey(local.submenu_color));
const previewSubHoverText = computed(() => cssColorFromKey(local.submenu_hover));

const submenuItems = ["Položka 1", "Položka 2", "Položka 3"];
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
