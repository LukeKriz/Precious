<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch } from "vue";

const props = defineProps({
  banner: { type: Object, required: false, default: null },
  autoplayMs: { type: Number, default: 4500 },

  mainDesign: { type: Object, default: () => ({}) },
  pageDesign: { type: Object, default: () => ({}) },
});

/**
 * ===== HELPERS =====
 */
const toBool = (v) => v === 1 || v === true || v === "1";
const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
const asNumber = (v, def = 0) => {
  const n = Number(v);
  return Number.isFinite(n) ? n : def;
};
const isHex = (s) =>
  typeof s === "string" && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(s.trim());

/**
 * ===== COLORS (fallback pro text apod.) =====
 */
const secondary = computed(() => props.mainDesign?.secondary_color || "#ef4444");
const tertiary = computed(() => props.mainDesign?.tertiary_color || "#6366f1");

/**
 * ===== ITEMS =====
 * Bereme pouze slider items z pageDesign.banners.
 * Pokud někdy používáš "single banner" mimo banners, můžeš sem přidat fallback,
 * ale podle tvého pravidla slider = 2+ položek, takže tohle je nejčistší.
 */
const items = computed(() => {
  const arr = Array.isArray(props.pageDesign?.banners) ? props.pageDesign.banners : [];
  return arr;
});

/** ✅ slider je až když jsou 2+ bannery */
const isSlider = computed(() => items.value.length >= 2);

const index = ref(0);
let timer = null;

const currentItem = computed(() => items.value[index.value] || items.value[0] || null);

/**
 * ===== HEIGHT =====
 */
const heightPx = computed(() => {
  const h = Number(props.pageDesign?.banner_height ?? props.banner?.height ?? 260);
  return `${h > 0 ? h : 260}px`;
});

/**
 * ===== BG / OVERLAY (PRAVIDLA 1:1) =====
 */
const bannerUrl = computed(() => String(currentItem.value?.banner_url ?? "").trim());

const bgColor = computed(() => {
  const c = currentItem.value?.bg_color;
  const s = typeof c === "string" ? c.trim() : "";
  return isHex(s) ? s : ""; // jen validní hex, jinak prázdno
});

const overlayColor = computed(() => {
  const c = currentItem.value?.overlay_color;
  const s = typeof c === "string" ? c.trim() : "";
  return isHex(s) ? s : "";
});

const overlayOpacity01 = computed(() => {
  const raw = asNumber(currentItem.value?.overlay_opacity, 0); // 0..100
  return clamp(raw, 0, 100) / 100;
});

const showImage = computed(() => !!bannerUrl.value);
const showBgColor = computed(() => !showImage.value && !!bgColor.value); // barva jen pokud není obrázek
const hasOverlay = computed(() => !!overlayColor.value && overlayOpacity01.value > 0);

/**
 * ✅ žádný fallback background!
 * pokud není ani image ani barva -> background none a transparent
 */
const rootStyle = computed(() => {
  const style = {
    height: heightPx.value,
    backgroundSize: "cover",
    backgroundPosition: "center",
    backgroundRepeat: "no-repeat",
    backgroundImage: "none",
    backgroundColor: "transparent",
  };

  if (showImage.value) {
    style.backgroundImage = `url("${bannerUrl.value}")`;
    style.backgroundColor = "transparent";
  } else if (showBgColor.value) {
    style.backgroundImage = "none";
    style.backgroundColor = bgColor.value;
  }

  return style;
});

/**
 * ===== TEXT / BUTTON =====
 */
const h1 = computed(() => currentItem.value?.heading_h1 || "");
const h2 = computed(() => currentItem.value?.heading_h2 || "");

const h1Color = computed(() => {
  const c = currentItem.value?.h1_color;
  const s = typeof c === "string" ? c.trim() : "";
  return isHex(s) ? s : secondary.value;
});
const h2Color = computed(() => {
  const c = currentItem.value?.h2_color;
  const s = typeof c === "string" ? c.trim() : "";
  return isHex(s) ? s : secondary.value;
});

const h1SizePx = computed(() => {
  const n = asNumber(currentItem.value?.h1_size, 48);
  return `${clamp(n, 10, 160)}px`;
});
const h2SizePx = computed(() => {
  const n = asNumber(currentItem.value?.h2_size, 22);
  return `${clamp(n, 10, 120)}px`;
});

const buttonEnabled = computed(() => {
  const v = currentItem.value?.button_enabled ?? currentItem.value?.button_enable;
  return toBool(v);
});
const buttonText = computed(() => currentItem.value?.button_text || "");
const buttonUrl = computed(() => currentItem.value?.button_url || "#");

/**
 * ===== THUMB =====
 */
const thumbUrl = computed(() => currentItem.value?.thumbnail_url || "");
const thumbSize = computed(() => asNumber(currentItem.value?.thumbnail_size, 65));

/**
 * ===== NAV =====
 */
const go = (i) => {
  if (!items.value.length) return;
  const n = items.value.length;
  index.value = (i + n) % n;
};
const next = () => go(index.value + 1);

const start = () => {
  stop();
  if (!isSlider.value) return; // ✅ jen když 2+
  timer = setInterval(next, props.autoplayMs);
};
const stop = () => {
  if (timer) clearInterval(timer);
  timer = null;
};

watch(isSlider, () => {
  index.value = 0;
  start();
});

watch(
  items,
  () => {
    index.value = 0;
    start();
  },
  { deep: true }
);

onMounted(start);
onBeforeUnmount(stop);

/**
 * ===== DOT STYLE =====
 */
const dotStyle = (active = false) => ({
  width: "14px",
  height: "14px",
  borderRadius: "9999px",
  backgroundColor: active ? tertiary.value : "#ffffff",
  opacity: active ? 1 : 0.7,
  transition: "all 0.25s ease",
});
</script>

<template>
  <!-- když nejsou žádné bannery, nic nezobrazuj -->
  <section class="w-full mt-0" v-if="items.length">
    <div
      class="relative w-full overflow-hidden"
      :style="rootStyle"
      @mouseenter="stop"
      @mouseleave="start"
    >
      <!-- overlay jen když existuje -->
      <div
        v-if="hasOverlay"
        class="absolute inset-0 pointer-events-none"
        :style="{ backgroundColor: overlayColor, opacity: overlayOpacity01 }"
      />

      <!-- CONTENT -->
      <div class="relative mx-auto max-w-6xl px-4 h-full">
        <div class="h-full flex items-center justify-between gap-8">
          <!-- LEFT -->
          <div class="min-w-0">
            <h1
              v-if="h1"
              class="font-extrabold tracking-tight"
              :style="{ fontSize: h1SizePx, lineHeight: 1.05, color: h1Color }"
            >
              {{ h1 }}
            </h1>

            <h2
              v-if="h2"
              class="mt-3 mb-5 font-semibold"
              :style="{ fontSize: h2SizePx, lineHeight: 1.2, color: h2Color }"
            >
              {{ h2 }}
            </h2>

            <div class="mt-5">
              <a v-if="buttonEnabled && buttonText" :href="buttonUrl" class="btn-custom">
                {{ buttonText }}
              </a>
            </div>

            <!-- ✅ DOTS jen pokud 2+ bannery -->
            <div v-if="isSlider" class="mt-6 flex gap-3">
              <button
                v-for="(it, i) in items"
                :key="(it?.id ?? it?.banner_url ?? 'slide') + '-' + i"
                type="button"
                class="cursor-pointer"
                :style="dotStyle(i === index)"
                @click="go(i)"
                :aria-label="`Go to slide ${i + 1}`"
              />
            </div>
          </div>

          <!-- RIGHT THUMB -->
          <div v-if="thumbUrl" class="shrink-0 hidden sm:block">
            <div class="rounded-3xl overflow-hidden" :style="{ width: `${thumbSize}px` }">
              <div class="p-4">
                <div class="rounded-2xl overflow-hidden">
                  <img
                    :src="thumbUrl"
                    alt="Thumbnail"
                    class="w-full h-auto object-contain"
                    draggable="false"
                  />
                </div>
              </div>
            </div>
          </div>
          <!-- /RIGHT -->
        </div>
      </div>
    </div>
  </section>
</template>
