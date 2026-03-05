<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
  pages: Array,
  subpages: Array,
  mainDesign: Object,
  siteSettings: Object,
});

const inertiaPage = usePage();

const pages = computed(() => props.pages || []);
const subpages = computed(() => props.subpages || []);
const mainDesign = computed(() => props.mainDesign || {});
const siteSettings = computed(() => props.siteSettings || {});

// --- helpers ---
const normUrl = (u) => {
  const s = String(u ?? "").trim();
  if (!s) return "/";
  // odstraní query/hash
  const clean = s.split("?")[0].split("#")[0];
  // zajistí "/" na začátku
  return clean.startsWith("/") ? clean : `/${clean}`;
};

const currentUrl = computed(() => normUrl(inertiaPage?.url ?? "/"));

// fallbacky z global settings
const globalTitle = computed(() => {
  const t = String(siteSettings.value?.default_title ?? "").trim();
  return t || "";
});
const globalDescription = computed(() => {
  const d = String(siteSettings.value?.default_description ?? "").trim();
  return d || "";
});

// najdi SEO pro aktuální URL (nejdřív subpage, pak page)
const currentSeo = computed(() => {
  const url = currentUrl.value;

  // 1) subpage match
  const sp = subpages.value.find((x) => normUrl(x?.url) === url);
  if (sp) {
    return {
      source: "subpage",
      meta_title: String(sp.meta_title ?? "").trim(),
      meta_description: String(sp.meta_description ?? "").trim(),
      title_fallback: String(sp.title ?? "").trim(),
    };
  }

  // 2) page match
  const p = pages.value.find((x) => normUrl(x?.url) === url);
  if (p) {
    return {
      source: "page",
      meta_title: String(p.meta_title ?? "").trim(),
      meta_description: String(p.meta_description ?? "").trim(),
      title_fallback: String(p.title ?? "").trim(),
    };
  }

  // 3) nic nenalezeno
  return {
    source: "none",
    meta_title: "",
    meta_description: "",
    title_fallback: "",
  };
});

const metaTitle = computed(() => {
  if (currentSeo.value.meta_title) return currentSeo.value.meta_title;

  if (currentSeo.value.title_fallback) return currentSeo.value.title_fallback;

  if (globalTitle.value) return globalTitle.value;

  return "Precious - Administrace pro Váš web";
});

const metaDescription = computed(() => {
  if (currentSeo.value.meta_description) return currentSeo.value.meta_description;

  if (globalDescription.value) return globalDescription.value;

  return "";
});
</script>

<template>
  <Head :title="metaTitle">
    <meta v-if="metaDescription" name="description" :content="metaDescription" />
  </Head>

  <PublicLayout :pages="pages" :subpages="subpages" :mainDesign="mainDesign">
    <div class="py-10">
      <div class="mx-auto max-w-3xl bg-white/90 shadow-sm p-8 text-center"></div>
    </div>
  </PublicLayout>
</template>
