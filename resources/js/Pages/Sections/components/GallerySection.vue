<script setup>
import { computed } from "vue";

import Carousel from "primevue/carousel";
import Galleria from "primevue/galleria";

const props = defineProps({
  cmp: { type: Object, required: true },

  // sdílené ovládání fullscreen galerie z PageContentRenderer
  openGallery: { type: Function, required: true },

  galleryOpen: { type: Boolean, default: false },
  galleryActiveIndex: { type: Number, default: 0 },
  galleryItems: { type: Array, default: () => [] },
});

const emit = defineEmits([
  "update:galleryOpen",
  "update:galleryActiveIndex",
  "update:galleryItems",
]);

const data = computed(() => {
  const d = props.cmp?.data ?? {};
  const images = Array.isArray(d.images) ? d.images : [];
  const previewCount = Math.max(1, Number(d.previewCount ?? 3));
  return { images, previewCount };
});

// responsive options z původního souboru
const galleryResponsiveOptions = [
  { breakpoint: "1024px", numVisible: 2, numScroll: 1 },
  { breakpoint: "640px", numVisible: 1, numScroll: 1 },
];

// ✅ show arrows only when there is something to scroll
const shouldShowCarouselArrows = (total, visible) => {
  const t = Math.max(0, Number(total || 0));
  const v = Math.max(1, Number(visible || 1));
  return t > v;
};

// jen prop passthrough do parent state:
const setOpen = (v) => emit("update:galleryOpen", !!v);
const setActiveIndex = (v) => emit("update:galleryActiveIndex", Number(v || 0));
const setItems = (v) => emit("update:galleryItems", Array.isArray(v) ? v : []);
</script>

<template>
  <div class="w-full">
    <div class="carousel-outer">
      <div class="carousel-inner">
        <Carousel
          class="gallery-carousel"
          :value="data.images"
          :numVisible="data.previewCount"
          :numScroll="1"
          :responsiveOptions="galleryResponsiveOptions"
          :circular="false"
          :showIndicators="false"
          :showNavigators="
            shouldShowCarouselArrows(data.images.length, data.previewCount)
          "
        >
          <template #item="{ data: img, index }">
            <button
              type="button"
              class="w-full text-left"
              @click="props.openGallery(data.images, index)"
            >
              <div class="px-2">
                <div
                  class="relative w-full aspect-[4/3] rounded-xl overflow-hidden bg-gray-50"
                >
                  <img
                    :src="img"
                    class="w-full h-full object-cover block"
                    draggable="false"
                    alt=""
                  />
                  <div
                    class="absolute inset-0 bg-black/20 opacity-0 hover:opacity-100 transition grid place-items-center"
                  >
                    <i
                      class="fa-solid fa-up-right-and-down-left-from-center text-white"
                    ></i>
                  </div>
                </div>
              </div>
            </button>
          </template>
        </Carousel>
      </div>
    </div>

    <!-- fullscreen viewer (state je sdílený z parentu) -->
    <Galleria
      :visible="galleryOpen"
      :activeIndex="galleryActiveIndex"
      :value="galleryItems"
      :fullScreen="true"
      :circular="true"
      :showThumbnails="false"
      :showItemNavigators="true"
      :showItemNavigatorsOnHover="true"
      :showIndicators="false"
      @update:visible="setOpen"
      @update:activeIndex="setActiveIndex"
      @update:value="setItems"
    >
      <template #item="{ item }">
        <img
          :src="item"
          class="w-full h-[90vh] object-contain block"
          alt=""
          draggable="false"
        />
      </template>
    </Galleria>
  </div>
</template>

<style scoped>
/* Carousel OUTSIDE ARROWS (z původního souboru) */
.carousel-outer {
  position: relative;
  overflow: visible;
}
.carousel-inner {
  overflow: hidden;
}

:deep(.p-carousel-prev-button),
:deep(.p-carousel-next-button) {
  position: absolute !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  z-index: 20 !important;

  background: white !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  border-radius: 9999px !important;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12) !important;
}

:deep(.p-carousel-prev-button) {
  left: -36px !important;
}
:deep(.p-carousel-next-button) {
  right: -36px !important;
}

@media (max-width: 640px) {
  :deep(.p-carousel-prev-button) {
    left: 8px !important;
  }
  :deep(.p-carousel-next-button) {
    right: 8px !important;
  }
}
</style>
