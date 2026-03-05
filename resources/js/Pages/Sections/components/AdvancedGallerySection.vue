<script setup>
import { computed, ref, watch } from "vue";
import Galleria from "primevue/galleria";

const props = defineProps({
  cmp: { type: Object, required: true },

  // sdílené ovládání fullscreen galerie z PageContentRenderer
  openGallery: { type: Function, required: true },

  // ✅ sdílený state (stejně jako GallerySection)
  galleryOpen: { type: Boolean, default: false },
  galleryActiveIndex: { type: Number, default: 0 },
  galleryItems: { type: Array, default: () => [] },
});

const emit = defineEmits([
  "update:galleryOpen",
  "update:galleryActiveIndex",
  "update:galleryItems",
]);

// passthrough do parent state:
const setOpen = (v) => emit("update:galleryOpen", !!v);
const setActiveIndex = (v) => emit("update:galleryActiveIndex", Number(v || 0));
const setItems = (v) => emit("update:galleryItems", Array.isArray(v) ? v : []);

const data = computed(() => {
  const d = props.cmp?.data ?? {};
  const previewCount = Math.max(1, Number(d.previewCount ?? 6));
  const categories = Array.isArray(d.categories) ? d.categories : [];

  const norm = categories.map((c) => ({
    id: c?.id ?? `cat_${Math.random().toString(16).slice(2)}`,
    label: (c?.label ?? "").toString(),
    galleries: (Array.isArray(c?.galleries) ? c.galleries : []).map((g) => ({
      id: g?.id ?? `gal_${Math.random().toString(16).slice(2)}`,
      title: (g?.title ?? "").toString(),
      cover: g?.cover ?? null,
      images: Array.isArray(g?.images) ? g.images : [],
    })),
  }));

  return { previewCount, categories: norm };
});

const activeCategoryId = ref(null);

watch(
  () => data.value.categories.map((c) => String(c.id)).join("|"),
  () => {
    if (!activeCategoryId.value && data.value.categories[0]?.id)
      activeCategoryId.value = data.value.categories[0].id;

    if (
      activeCategoryId.value &&
      !data.value.categories.find((c) => String(c.id) === String(activeCategoryId.value))
    ) {
      activeCategoryId.value = data.value.categories[0]?.id ?? null;
    }
  },
  { immediate: true }
);

const activeCategory = computed(() => {
  return (
    data.value.categories.find((c) => String(c.id) === String(activeCategoryId.value)) ||
    null
  );
});

const shownGalleries = computed(() => {
  return activeCategory.value?.galleries || [];
});

// ✅ otevře fullscreen galerii (přes sdílený parent state)
const openOneGallery = (g) => {
  const imgs = Array.isArray(g?.images) ? g.images : [];
  if (!imgs.length) return;

  // naplní state a otevře viewer
  props.openGallery(imgs, 0);
};
</script>

<template>
  <div class="w-full">
    <!-- TOP MENU: categories -->
    <div v-if="data.categories.length" class="ag-tabs">
      <button
        v-for="c in data.categories"
        :key="c.id"
        type="button"
        class="ag-tab"
        :class="String(c.id) === String(activeCategoryId) ? 'is-active' : ''"
        @click="activeCategoryId = c.id"
      >
        {{ c.label || "Kategorie" }}
      </button>
    </div>

    <!-- GRID: galleries inside active category -->
    <div v-if="shownGalleries.length" class="ag-grid">
      <button
        v-for="g in shownGalleries"
        :key="g.id"
        type="button"
        class="ag-tile"
        @click="openOneGallery(g)"
      >
        <div class="ag-img">
          <img v-if="g.cover" :src="g.cover" alt="" class="ag-img-el" />
          <div v-else class="ag-img-empty">Bez náhledu</div>

          <div class="ag-overlay">
            <div class="ag-title">{{ g.title || "Bez názvu" }}</div>
            <div class="ag-meta">{{ (g.images || []).length }} fotek</div>
          </div>

          <!-- hover icon (stejný vibe jako GallerySection) -->
          <div class="ag-hover">
            <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
          </div>
        </div>
      </button>
    </div>

    <div v-else class="text-sm text-gray-500 italic">
      (V této kategorii zatím nejsou žádné galerie)
    </div>

    <!-- ✅ FULLSCREEN VIEWER (stejně jako GallerySection) -->
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
.ag-tabs {
  display: flex;
  gap: 28px;
  justify-content: center;
  align-items: center;
  margin-bottom: 26px;
  flex-wrap: wrap;
}
.ag-tab {
  font-weight: 800;
  font-size: 18px;
  padding: 10px 4px;
  border: 0;
  background: transparent;
  color: #0f172a;
  opacity: 0.75;
  cursor: pointer;
  border-bottom: 2px solid transparent;
}
.ag-tab.is-active {
  opacity: 1;
  border-bottom-color: #dc2626;
}

.ag-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 56px;
  align-items: start;
  justify-items: center;
}
@media (max-width: 900px) {
  .ag-grid {
    grid-template-columns: 1fr;
    gap: 28px;
  }
}

.ag-tile {
  width: min(720px, 100%);
  border: 0;
  background: transparent;
  cursor: pointer;
  text-align: left;
}

.ag-img {
  width: 100%;
  border-radius: 18px;
  overflow: hidden;
  position: relative;
  background: #f8fafc;
  border: 1px solid rgba(15, 23, 42, 0.08);
}
.ag-img-el {
  width: 100%;
  height: 280px;
  object-fit: cover;
  display: block;
}
.ag-img-empty {
  height: 280px;
  display: grid;
  place-items: center;
  color: #94a3b8;
  font-size: 13px;
}
.ag-overlay {
  position: absolute;
  left: 14px;
  right: 14px;
  bottom: 14px;
  background: rgba(15, 23, 42, 0.55);
  color: white;
  border-radius: 14px;
  padding: 10px 14px;
  backdrop-filter: blur(2px);
}
.ag-title {
  font-weight: 900;
  font-size: 18px;
  line-height: 1.2;
}
.ag-meta {
  margin-top: 4px;
  font-size: 12px;
  opacity: 0.9;
}

/* hover icon */
.ag-hover {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  color: white;
  opacity: 0;
  transition: opacity 0.15s ease;
  background: rgba(0, 0, 0, 0.18);
}
.ag-img:hover .ag-hover {
  opacity: 1;
}
.ag-hover i {
  font-size: 20px;
}
</style>
