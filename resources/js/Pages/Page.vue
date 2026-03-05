<script setup>
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
  pages: Array,
  subpages: Array,
  mainDesign: Object,
  siteSettings: Object,
});

const pages = computed(() => props.pages || []);
const subpages = computed(() => props.subpages || []);
const mainDesign = computed(() => props.mainDesign || {});
const siteSettings = computed(() => props.siteSettings || {});

// ✅ fallbacky z global settings
const defaultTitle = computed(() => {
  const t = String(siteSettings.value?.default_title ?? "").trim();
  return t || "Precious"; // fallback úplně poslední
});

const defaultDescription = computed(() => {
  const d = String(siteSettings.value?.default_description ?? "").trim();
  return d || "";
});

// ✅ tady jednou nastavíš, co se má použít na téhle stránce
// (později sem dáš pageSeo.title / pageSeo.description)
const metaTitle = computed(() => defaultTitle.value);
const metaDescription = computed(() => defaultDescription.value);
</script>

<template>
  <Head :title="metaTitle">
    <meta v-if="metaDescription" name="description" :content="metaDescription" />
  </Head>

  <PublicLayout :pages="pages" :subpages="subpages" :mainDesign="mainDesign">
    <div class="py-10">
      <div class="mx-auto max-w-3xl bg-white/90 shadow-sm p-8">
        <h1 class="text-3xl font-bold"></h1>

        <div class="mt-6 opacity-80">
          Tady pak bude obsah stránky (podle toho, co budeš ukládat do DB).
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
