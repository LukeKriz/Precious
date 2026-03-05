<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import SectionWrapper from "./components/SectionWrapper.vue";
import TextSection from "./components/TextSection.vue";
import TextImageSection from "./components/TextImageSection.vue";
import ImageSection from "./components/ImageSection.vue";
import GallerySection from "./components/GallerySection.vue";
import CardsSection from "./components/CardsSection.vue";
import AccordionSection from "./components/AccordionSection.vue";
import FormSection from "./components/FormSection.vue";
import AdvancedGallerySection from "./components/AdvancedGallerySection.vue";
import LocationsMapSection from "./components/LocationsMapSection.vue";
import YouTubeSection from "./components/YouTubeSection.vue";

const page = usePage();

const sections = computed(() => page.props.pageContent ?? []);
const mainDesign = computed(() => page.props.mainDesign ?? {});

// ===== shared small utils (z původního souboru) =====
const safeUrl = (u) => (typeof u === "string" ? u.trim() : "");

// ===== FULLSCREEN GALLERIA STATE (shared) =====
const galleryOpen = ref(false);
const galleryActiveIndex = ref(0);
const galleryItems = ref([]);

const openGallery = (images, index = 0) => {
  galleryItems.value = Array.isArray(images) ? images : [];
  galleryActiveIndex.value = Math.max(0, Number(index || 0));
  galleryOpen.value = true;
};
</script>

<template>
  <div class="w-full">
    <template v-for="sec in sections" :key="sec.id">
      <SectionWrapper :sec="sec">
        <div class="space-y-2 relative z-1 w-full h-full">
          <template v-for="cmp in sec.components || []" :key="cmp.id">
            <TextSection v-if="cmp.type === 'text'" :cmp="cmp" :safeUrl="safeUrl" />

            <TextImageSection
              v-else-if="cmp.type === 'text_image'"
              :cmp="cmp"
              :safeUrl="safeUrl"
            />

            <ImageSection
              v-else-if="cmp.type === 'image'"
              :cmp="cmp"
              :safeUrl="safeUrl"
              :mainDesign="mainDesign"
            />

            <GallerySection
              v-else-if="cmp.type === 'gallery'"
              :cmp="cmp"
              :openGallery="openGallery"
              v-model:galleryOpen="galleryOpen"
              v-model:galleryActiveIndex="galleryActiveIndex"
              v-model:galleryItems="galleryItems"
            />

            <CardsSection
              v-else-if="cmp.type === 'cards'"
              :cmp="cmp"
              :safeUrl="safeUrl"
              :mainDesign="mainDesign"
            />

            <AccordionSection v-else-if="cmp.type === 'accordion'" :cmp="cmp" />

            <FormSection
              v-else-if="cmp.type === 'form'"
              :cmp="cmp"
              :mainDesign="mainDesign"
            />

            <!-- pokud už máš: -->
            <LocationsMapSection
              v-else-if="cmp.type === 'locations_map'"
              :cmp="cmp"
              :mainDesign="mainDesign"
            />

            <AdvancedGallerySection
              v-else-if="cmp.type === 'advanced_gallery'"
              :cmp="cmp"
              :openGallery="openGallery"
              v-model:galleryOpen="galleryOpen"
              v-model:galleryActiveIndex="galleryActiveIndex"
              v-model:galleryItems="galleryItems"
            />

            <YouTubeSection
              v-else-if="cmp.type === 'youtube'"
              :cmp="cmp"
              :safeUrl="safeUrl"
              :mainDesign="mainDesign"
            />

            <div v-else class="text-sm text-gray-500">
              Neznámý typ komponenty: <b>{{ cmp.type }}</b>
            </div>
          </template>
        </div>
      </SectionWrapper>
    </template>
  </div>
</template>
