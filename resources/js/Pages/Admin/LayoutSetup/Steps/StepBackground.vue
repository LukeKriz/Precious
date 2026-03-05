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

    <!-- CARD -->
    <div class="mt-6 w-full max-w-4xl bg-white p-6 shadow-sm rounded-xl">
      <h3 class="text-lg font-bold mb-6">Vzhled inputů</h3>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- LEFT: PAGE BG -->
        <div class="rounded-xl border p-5">
          <h4 class="font-semibold mb-4">Pozadí stránky</h4>

          <label class="font-medium block mb-2">Barva pozadí stránky</label>
          <div class="flex items-center gap-3 flex-wrap">
            <button
              v-for="k in colorKeys"
              :key="'pagebg-' + k"
              type="button"
              class="swatch"
              :class="{ active: local.page_background === k }"
              :style="colorCircleStyle(k)"
              @click="pickColor('page_background', k)"
              :title="colorTitle(k)"
            />
          </div>

          <div class="mt-4 text-xs text-gray-500">
            Transparent = pozadí bude “žádné” (vezme se z layoutu / prohlížeče).
          </div>

          <!-- PREVIEW -->
          <div class="mt-6">
            <div class="text-xs text-gray-500 mb-2">Náhled pozadí</div>
            <div
              class="rounded-xl border overflow-hidden p-6"
              :style="{ background: previewPageBg }"
            >
              <div class="text-sm font-semibold mb-2">Ukázka obsahu</div>
              <div class="text-sm opacity-80">
                Tady uvidíš, jak bude pozadí stránky působit.
              </div>

              <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="h-16 rounded-lg bg-white/70 border"></div>
                <div class="h-16 rounded-lg bg-white/70 border"></div>
              </div>
            </div>

            <div class="mt-3 text-xs text-gray-500">Ukládá se automaticky při změně.</div>
          </div>
        </div>

        <!-- RIGHT: (můžeš sem později dát další nastavení) -->
        <!-- <div class="rounded-xl border p-5">
          <h4 class="font-semibold mb-4">Tip</h4>
          <div class="text-sm text-gray-600 leading-relaxed">
            Pokud chceš, můžeme sem doplnit i pozadí sekcí, pattern, nebo třeba jemný
            gradient (taky z palety).
          </div>
        </div> -->
      </div>
    </div>

    <div class="py-6">
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

const emit = defineEmits(["goPrev", "goNext", "saveMainDesign"]);

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

// checkerboard pro transparent swatch
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
  if (s[0] !== "#") return s; // může být už key
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
  page_background: "transparent",
});

const isHydrating = ref(false);

watch(
  () => props.mainDesign,
  (val) => {
    if (!val) return;
    isHydrating.value = true;

    // ✅ čti z DB: mainDesign.page_background (hex nebo key nebo null)
    const bgKey = mapHexToKey(val, val.page_background);
    if (bgKey) local.page_background = bgKey;

    setTimeout(() => (isHydrating.value = false), 0);
  },
  { immediate: true, deep: true }
);

const keyToDbColor = (k) => (k === "transparent" ? null : palette.value[k]);

const emitSave = () => {
  emit("saveMainDesign", {
    page_background: keyToDbColor(local.page_background),
  });
};

const pickColor = (key, colorKey) => {
  local[key] = colorKey;
  if (!isHydrating.value) emitSave();
};

/* ================== PREVIEW ================== */
const previewPageBg = computed(() =>
  local.page_background === "transparent"
    ? "transparent"
    : palette.value[local.page_background]
);
</script>

<style scoped>
/* dokonale kulaté swatche */
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

/* zelený ring */
.swatch.active {
  box-shadow: 0 0 0 2px #22c55e, 0 0 0 4px #ffffff;
}

.swatch:active {
  transform: scale(0.98);
}
</style>
