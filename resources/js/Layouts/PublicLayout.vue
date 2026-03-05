<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

import NavMenu from "@/Pages/Sections/NavMenu.vue";
import Banner from "@/Pages/Sections/Banner.vue";
import PageContentPreview from "@/Pages/Admin/Content/preview/PageContentPreview.vue";
import PageContentRender from "@/Pages/Sections/PageContentRender.vue";
import Footer from "@/Pages/Sections/Footer.vue";

const inertia = usePage();

const pages = computed(() => inertia.props.pages ?? []);
const subpages = computed(() => inertia.props.subpages ?? []);
const mainDesign = computed(() => inertia.props.mainDesign ?? {});
const pageDesign = computed(() => inertia.props.pageDesign ?? {});
const banner = computed(() => inertia.props.banner ?? null);

const primary = computed(() => mainDesign.value?.primary_color || "#f3f4f6");
const secondary = computed(() => mainDesign.value?.secondary_color || "#111827");

const isPreview = computed(() => inertia.props.isPreview);

// layout id
const layoutId = computed(() => Number(pageDesign.value?.layout_id || 1));
const isLayout2 = computed(() => layoutId.value === 2);

const headerHeight = computed(() => {
  const h = pageDesign.value?.header_height;
  if (h === null || h === undefined || h === "" || Number(h) <= 0) return "65px";
  return `${Number(h)}px`;
});

const footerHeight = computed(() => {
  const h = pageDesign.value?.footer_height;
  if (h === null || h === undefined || h === "" || Number(h) <= 0) return "60px";
  return `${Number(h)}px`;
});

const categoryWeight = computed(() => {
  const w = pageDesign.value?.category_weight;
  if (w === null || w === undefined || w === "" || Number(w) <= 0) return "250px";
  return Number(w) + "px";
});
const categoryHeight = computed(() => {
  const h = pageDesign.value?.category_height;
  if (h === null || h === undefined || h === "" || Number(h) <= 0) return "100%";
  return Number(h) + "px";
});

// helper: NULL / "" / "null" -> fallback
const valOr = (v, fallback) => {
  if (v === null || v === undefined) return fallback;
  const s = String(v).trim();
  if (!s) return fallback;
  if (s.toLowerCase() === "null") return fallback;
  return s;
};

// ✅ Globální proměnné pro tlačítka
const themeVars = computed(() => {
  const d = mainDesign.value ?? {};

  // základ
  const bg = valOr(d.button_color, "transparent");
  const tx = valOr(d.button_text_color, "#000");

  // hover: když je NULL => transparent (NE fallback na bg)
  const hbg = valOr(d.button_hover_color, "transparent");
  const htx = valOr(d.button_hover_text_color, tx);

  const borderEnabled =
    Number(d.button_border_enabled ?? 0) === 1 || d.button_border_enabled === true;

  const borderWidth = Math.max(0, Number(d.button_border_width ?? 0));
  const borderColor = valOr(d.button_border_color, "transparent");

  // border hover: když je NULL => transparent (NE fallback)
  const borderHoverColor = valOr(d.button_border_hover_color, "transparent");

  const fw = String(d.button_font_weight ?? "500");

  // preset padding/radius dle typu
  const t = valOr(d.button_type, "style_1");
  const map = {
    style_1: { py: "0.5rem", px: "1rem", r: "0.375rem" },
    style_2: { py: "0.75rem", px: "1.5rem", r: "0.5rem" },
    style_3: { py: "0.75rem", px: "2rem", r: "9999px" },
    style_4: { py: "0.5rem", px: "1.5rem", r: "0.375rem" },
    style_5: { py: "1rem", px: "2.5rem", r: "0.75rem" },
  };
  const preset = map[t] ?? map.style_1;

  return {
    "--btn-bg": bg,
    "--btn-text": tx,
    "--btn-bg-hover": hbg,
    "--btn-text-hover": htx,

    "--btn-border-width": borderEnabled ? `${borderWidth}px` : "0px",
    "--btn-border-color": borderEnabled ? borderColor : "transparent",
    "--btn-border-hover-color": borderEnabled ? borderHoverColor : "transparent",

    "--btn-fw": fw,
    "--btn-px": preset.px,
    "--btn-py": preset.py,
    "--btn-radius": preset.r,
  };
});

/**
 * ✅ Banner se má vykreslit i když:
 * - top-level `banner` není (null),
 * ale existují slider položky v `pageDesign.banners` (např. jen bg_color)
 * nebo existuje `pageDesign.banner` (pokud ho někdy posíláš).
 */
const hasAnyBanner = computed(() => {
  const hasLegacy = !!banner.value;

  const hasItems =
    Array.isArray(pageDesign.value?.banners) && pageDesign.value.banners.length > 0;

  const hasSingle = !!pageDesign.value?.banner;

  return hasLegacy || hasItems || hasSingle;
});
</script>

<template>
  <div
    class="min-h-screen flex flex-col -z-10"
    :style="[{ backgroundColor: mainDesign?.page_background }, themeVars]"
  >
    <!-- HEADER -->
    <div :style="{ height: headerHeight }" class="shrink-0">
      <NavMenu
        :pages="pages"
        :subpages="subpages"
        :mainDesign="mainDesign"
        :pageDesign="pageDesign"
        :style="{ height: headerHeight }"
      />
    </div>

    <!-- BANNER -->
    <Banner
      v-if="hasAnyBanner"
      :banner="banner"
      :mainDesign="mainDesign"
      :pageDesign="pageDesign"
    />

    <!-- CONTENT -->
    <main v-if="!isLayout2" class="mx-auto max-w-6xl px-4 w-full flex-1">
      <PageContentPreview :mainDesign="mainDesign" v-if="isPreview" />
      <PageContentRender v-else />
    </main>

    <!-- Layout 2 -->
    <main v-else class="w-full flex-1">
      <div class="mx-auto max-w-6xl px-4 w-full">
        <div class="flex gap-6 py-6">
          <aside
            class="shrink-0 rounded-2xl"
            :style="{
              width: categoryWeight,
              height: categoryHeight,
              backgroundColor: '#000',
              color: '#fff',
            }"
          >
            <div class="p-5">
              <div class="text-sm font-semibold opacity-80 mb-3">Navigace / box</div>
              <ul class="space-y-2 text-sm">
                <li class="opacity-90">Položka 1</li>
                <li class="opacity-90">Položka 2</li>
                <li class="opacity-90">Položka 3</li>
              </ul>
            </div>
          </aside>

          <section class="min-w-0 flex-1">
            <PageContentRender />
          </section>
        </div>
      </div>
    </main>

    <Footer
      :mainDesign="mainDesign"
      :pages="pages"
      :subpages="subpages"
      :footerHeight="footerHeight"
    />
  </div>
</template>

<style>
/* ✅ Jednotný vzhled buttonu v celém projektu */
.btn-custom {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  background: var(--btn-bg);
  color: var(--btn-text);

  border: var(--btn-border-width) solid var(--btn-border-color);
  font-weight: var(--btn-fw);

  padding: var(--btn-py) var(--btn-px);
  border-radius: var(--btn-radius);

  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
  text-decoration: none;
}

.btn-custom:hover {
  background: var(--btn-bg-hover);
  color: var(--btn-text-hover);
  border-color: var(--btn-border-hover-color);
}
</style>
