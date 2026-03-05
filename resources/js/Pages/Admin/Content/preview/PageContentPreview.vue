<script setup>
import { computed, ref, onMounted, onBeforeUnmount, nextTick, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

import AddSectionButton from "./components/AddSectionButton.vue";
import SectionFrame from "./components/SectionFrame.vue";
import EmptySectionPicker from "./components/EmptySectionPicker.vue";
import ComponentItem from "./components/ComponentItem.vue";
import AddComponentBar from "./components/AddComponentBar.vue";

import ConfirmDeleteSectionModal from "../modals/ConfirmDeleteSectionModal.vue";
import ConfirmDeleteComponentModal from "../modals/ConfirmDeleteComponentModal.vue";
import ChangeComponentTypeModal from "../modals/ChangeComponentTypeModal.vue";

const page = usePage();

const isPreview = computed(() => !!page.props.isPreview);
const sections = computed(() => page.props.pageContent ?? []);
const url = computed(() => window.location.pathname || "/");

// ===== SECTION: ADD =====
const postAddSection = (insertIndex) => {
  if (!isPreview.value) return;

  window.parent?.postMessage(
    { type: "ADD_SECTION", url: url.value, insertIndex },
    window.location.origin
  );
};

// ===== SECTION: DELETE (confirm modal) =====
const showDeleteSectionModal = ref(false);
const deleteSectionTargetId = ref(null);

const openDeleteSection = (id) => {
  deleteSectionTargetId.value = id;
  showDeleteSectionModal.value = true;
};
const closeDeleteSection = () => {
  showDeleteSectionModal.value = false;
  deleteSectionTargetId.value = null;
};
const confirmDeleteSection = () => {
  if (!isPreview.value) return;
  if (!deleteSectionTargetId.value) return;

  window.parent?.postMessage(
    { type: "DELETE_SECTION", sectionId: deleteSectionTargetId.value, url: url.value },
    window.location.origin
  );

  closeDeleteSection();
};

// ===== SECTION: EDIT HEIGHT =====
const postEditHeight = (sectionId) => {
  if (!isPreview.value) return;

  window.parent?.postMessage(
    { type: "EDIT_SECTION_HEIGHT", url: url.value, sectionId },
    window.location.origin
  );
};

// ===== COMPONENT TYPES =====
const componentTypes = [
  { type: "text", label: "Text", icon: "📝" },
  { type: "text_image", label: "Text + obrázek", icon: "🖼️" },
  { type: "image", label: "Obrázek", icon: "🖼️" },
  { type: "gallery", label: "Galerie", icon: "🧩" },
  { type: "cards", label: "Karty", icon: "🃏" },
  { type: "form", label: "Formulář", icon: "📨" },
  { type: "accordion", label: "Rozbalovací blok", icon: "➕" },
  { type: "locations_map", label: "Pobočky + mapa", icon: "🗺️" },
  { type: "advanced_gallery", label: "Pokročilá galerie", icon: "🖼️" },
  { type: "youtube", label: "YouTube video", icon: "▶️" },
];

const typeLabel = (t) => componentTypes.find((x) => x.type === t)?.label ?? t;

// ===== COMPONENT: ADD =====
const postAddComponent = (sectionId, componentType) => {
  if (!isPreview.value) return;

  window.parent?.postMessage(
    { type: "ADD_COMPONENT", url: url.value, sectionId, componentType },
    window.location.origin
  );
};

const renderLocationsMap = (cmp) => {
  const d = cmp?.data ?? {};
  const locations = Array.isArray(d.locations) ? d.locations : [];
  const first = locations[0] ?? null;

  return {
    title: d.title ?? "KONTAKTUJTE NÁS:",
    subtitle: d.subtitle ?? "",
    mapSide: d.mapSide === "right" ? "right" : "left",
    cardTitleEnabled: !!d.cardTitleEnabled,
    buttonEnabled: !!d.buttonEnabled,
    buttonText: d.buttonText ?? "Napište nám",
    buttonUrl: d.buttonUrl ?? "",
    locations,
    first,
  };
};

const renderImage = (cmp) => {
  const d = cmp?.data ?? {};
  return {
    image: d.image ?? null,
    widthPercent: Math.max(10, Math.min(100, Number(d.widthPercent ?? 100) || 100)),
    maxHeight: Math.max(80, Math.min(1200, Number(d.maxHeight ?? 420) || 420)),
    fit: d.fit === "cover" ? "cover" : "contain",
    click: d.click ?? null,
  };
};

// ===== COMPONENT: EDIT =====
const postEditComponent = (sectionId, componentId) => {
  if (!isPreview.value) return;

  window.parent?.postMessage(
    { type: "EDIT_COMPONENT", url: url.value, sectionId, componentId },
    window.location.origin
  );
};

// ===== COMPONENT: CHANGE TYPE (confirm modal) =====
const showChangeModal = ref(false);
const changeSectionId = ref(null);
const changeComponentId = ref(null);
const selectedType = ref("text");

const openChangeType = (sectionId, componentId, currentType) => {
  changeSectionId.value = sectionId;
  changeComponentId.value = componentId;
  selectedType.value = currentType || "text";
  showChangeModal.value = true;
};

const closeChangeType = () => {
  showChangeModal.value = false;
  changeSectionId.value = null;
  changeComponentId.value = null;
};

const confirmChangeType = () => {
  if (!isPreview.value) return;
  if (!changeSectionId.value || !changeComponentId.value) return;

  window.parent?.postMessage(
    {
      type: "CHANGE_COMPONENT_TYPE",
      url: url.value,
      sectionId: changeSectionId.value,
      componentId: changeComponentId.value,
      newType: selectedType.value,
    },
    window.location.origin
  );

  closeChangeType();
};

// ===== COMPONENT: DELETE (confirm modal) =====
const showDeleteComponentModal = ref(false);
const deleteComponentSectionId = ref(null);
const deleteComponentId = ref(null);

const openDeleteComponent = (sectionId, componentId) => {
  deleteComponentSectionId.value = sectionId;
  deleteComponentId.value = componentId;
  showDeleteComponentModal.value = true;
};

const closeDeleteComponent = () => {
  showDeleteComponentModal.value = false;
  deleteComponentSectionId.value = null;
  deleteComponentId.value = null;
};

const confirmDeleteComponent = () => {
  if (!isPreview.value) return;
  if (!deleteComponentSectionId.value || !deleteComponentId.value) return;

  window.parent?.postMessage(
    {
      type: "DELETE_COMPONENT",
      url: url.value,
      sectionId: deleteComponentSectionId.value,
      componentId: deleteComponentId.value,
    },
    window.location.origin
  );

  closeDeleteComponent();
};

// ===== DROPDOWN "..." per component =====
const openMenuKey = ref(null);
const menuKey = (sectionId, componentId) => `${sectionId}::${componentId}`;

const toggleMenu = async (sectionId, componentId) => {
  const key = menuKey(sectionId, componentId);
  openMenuKey.value = openMenuKey.value === key ? null : key;
  await nextTick();
};

const closeAllMenus = () => {
  openMenuKey.value = null;
};

const onDocClick = (e) => {
  const el = e.target;
  if (!el) return;
  if (el.closest?.('[data-menu="1"]')) return;
  closeAllMenus();
};

const onKeyDown = (e) => {
  if (e.key === "Escape") closeAllMenus();
};

// ✅ BLOCK NAVIGATION INSIDE PREVIEW (only <a> clicks)
const onPreviewClickBlockNav = (e) => {
  if (!isPreview.value) return;

  const el = e.target;
  if (!el) return;

  if (el.closest?.('[data-menu="1"]')) return;

  const a = el.closest?.("a[href]");
  if (!a) return;

  const href = (a.getAttribute("href") || "").trim();
  if (
    !href ||
    href === "#" ||
    href.startsWith("#") ||
    href.startsWith("mailto:") ||
    href.startsWith("tel:") ||
    href.startsWith("javascript:")
  ) {
    return;
  }

  if (a.dataset?.allowNav === "1") return;

  e.preventDefault();
  e.stopPropagation();
};

// ===== SCROLL SYNC (persist via parent sessionStorage) =====
const sendScroll = () => {
  window.parent?.postMessage(
    { type: "IFRAME_SCROLL", scrollY: window.scrollY },
    window.location.origin
  );
};

const restoreScroll = (y) => {
  window.scrollTo({ top: Number(y || 0), behavior: "auto" });
};

const onMsg = (e) => {
  if (e.origin !== window.location.origin) return;
  const msg = e.data;
  if (!msg || typeof msg !== "object") return;

  if (msg.type === "RESTORE_SCROLL") {
    // počkej na layout, aby už existovaly sekce
    requestAnimationFrame(() => restoreScroll(msg.scrollY));
  }
};

onMounted(() => {
  document.addEventListener("click", onDocClick, true);
  document.addEventListener("keydown", onKeyDown);
  document.addEventListener("click", onPreviewClickBlockNav, true);

  // řekni rodiči, že iframe je ready
  window.parent?.postMessage({ type: "IFRAME_READY" }, window.location.origin);

  // scroll listener (throttle)
  let t = null;
  window.addEventListener(
    "scroll",
    () => {
      if (t) return;
      t = setTimeout(() => {
        t = null;
        sendScroll();
      }, 100);
    },
    { passive: true }
  );

  window.addEventListener("message", onMsg);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", onDocClick, true);
  document.removeEventListener("keydown", onKeyDown);
  document.removeEventListener("click", onPreviewClickBlockNav, true);

  window.removeEventListener("message", onMsg);
});

// ===== SIMPLE RENDER PREVIEW =====
const alignClass = (a) =>
  a === "center" ? "text-center" : a === "right" ? "text-right" : "text-left";

const renderText = (cmp) => {
  const d = cmp?.data ?? {};
  const html = d.html ?? "";
  const fallbackText = d.text ?? "";
  const content = html && String(html).trim() ? html : fallbackText;

  return { html: content };
};

const renderTextImage = (cmp) => {
  const d = cmp?.data ?? {};
  return {
    html: d.content ?? d.text ?? "",
    layout: d.layout ?? "text_left",
    image: d.image ?? null,
  };
};

const renderGallery = (cmp) => {
  const d = cmp?.data ?? {};
  const images = Array.isArray(d.images) ? d.images : [];
  const previewCount = Math.max(1, Number(d.previewCount ?? 6));
  return { previewCount, images, shown: images.slice(0, previewCount) };
};

const renderAdvancedGallery = (cmp) => {
  const d = cmp?.data ?? {};
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
};

const renderCards = (cmp) => {
  const d = cmp?.data ?? {};
  const cards = Array.isArray(d.cards) ? d.cards : [];
  const previewCount = Math.max(1, Number(d.previewCount ?? 3));
  return { previewCount, cards, shown: cards.slice(0, previewCount) };
};

const renderAccordion = (cmp) => {
  const d = cmp?.data ?? {};
  const items = Array.isArray(d.items) ? d.items : [];
  return { title: d.title ?? "", openFirst: !!d.openFirst, items };
};

const renderYouTube = (cmp) => {
  const d = cmp?.data ?? {};
  return {
    url: d.url ?? "",
    videoId: d.videoId ?? "",
    widthPx: d.widthPx ?? null,
    maxWidthPx: d.maxWidthPx ?? null,
    aspect: d.aspect ?? "16:9",
    align: ["left", "center", "right"].includes(d.align) ? d.align : "center",
    start: d.start ?? null,
  };
};

const renderForm = (cmp) => {
  const d = cmp?.data ?? {};
  const fields = Array.isArray(d.fields) ? d.fields : [];

  const labelByType = {
    text: "Text",
    textarea: "Dlouhý text",
    email: "E-mail",
    tel: "Telefon",
    number: "Číslo",
    date: "Datum",
    url: "URL",
    checkbox: "Checkbox",
    select: "Select",
    radio: "Radio",
  };

  const norm = fields.map((f) => ({
    id: f?.id ?? `fld_${Math.random().toString(16).slice(2)}`,
    type: f?.type ?? "text",
    typeLabel: labelByType[f?.type] ?? f?.type ?? "text",
    label: f?.label ?? "",
    name: f?.name ?? "",
    required: !!f?.required,
    placeholder: f?.placeholder ?? "",
    rows: Number(f?.rows ?? 4),
    options:
      f?.type === "select" || f?.type === "radio"
        ? (Array.isArray(f?.options) ? f.options : []).map((o) => ({
            id: o?.id ?? `opt_${Math.random().toString(16).slice(2)}`,
            label: o?.label ?? "",
            value: (o?.value ?? "").toString(),
          }))
        : [],
  }));

  const shown = norm.slice(0, 6);

  return {
    total: norm.length,
    shown,
    hasMore: norm.length > shown.length,

    sideTextEnabled: !!(d.sideTextEnabled ?? false),
    sideTextPosition: d.sideTextPosition === "left" ? "left" : "right",
    sideText: d.sideText ?? "",
    hasSideText: !!(d.sideTextEnabled && (d.sideText || "").trim()),
  };
};

// ===== DRAG & DROP reorder components (preview only) =====
const localSections = ref([]);
const dragState = ref({ sectionId: null, draggingId: null, overId: null });

// ===== DRAG & DROP reorder SECTIONS (preview only) – řízeno přes SectionFrame handle =====
const secDrag = ref({ draggingId: null, overId: null });

const onSectionDragStart = (sectionId) => {
  if (!isPreview.value) return;
  closeAllMenus();
  secDrag.value = { draggingId: sectionId, overId: sectionId };
};

const onSectionDragOver = (e, overSectionId) => {
  if (!isPreview.value) return;
  if (!secDrag.value.draggingId) return;
  e.preventDefault();
  secDrag.value.overId = overSectionId;
};

const onSectionDrop = () => {
  if (!isPreview.value) return;

  const fromId = secDrag.value.draggingId;
  const toId = secDrag.value.overId;

  if (!fromId || !toId || fromId === toId) {
    secDrag.value = { draggingId: null, overId: null };
    return;
  }

  const from = localSections.value.findIndex((s) => s.id === fromId);
  const to = localSections.value.findIndex((s) => s.id === toId);
  if (from === -1 || to === -1) {
    secDrag.value = { draggingId: null, overId: null };
    return;
  }

  const next = [...localSections.value];
  const [moved] = next.splice(from, 1);
  next.splice(to, 0, moved);
  localSections.value = next;

  const orderedIds = next.map((s) => s.id);

  window.parent?.postMessage(
    { type: "REORDER_SECTIONS", url: url.value, sectionIds: orderedIds },
    window.location.origin
  );

  secDrag.value = { draggingId: null, overId: null };
};

const onSectionDragEnd = () => {
  secDrag.value = { draggingId: null, overId: null };
};

const isSectionOver = (secId) =>
  secDrag.value?.overId === secId && !!secDrag.value?.draggingId;

const syncLocalSections = () => {
  localSections.value = (sections.value || []).map((s) => ({
    ...s,
    components: Array.isArray(s.components) ? [...s.components] : [],
  }));
};
watch(sections, syncLocalSections, { immediate: true });

// ===== component drag =====
const onDragStart = (sectionId, componentId) => {
  if (!isPreview.value) return;
  closeAllMenus();
  dragState.value = { sectionId, draggingId: componentId, overId: componentId };
};

const onDragOver = (e, sectionId, overComponentId) => {
  if (!isPreview.value) return;
  if (dragState.value.sectionId !== sectionId) return;
  e.preventDefault();
  dragState.value.overId = overComponentId;
};

const onDrop = (sectionId) => {
  if (!isPreview.value) return;

  const { draggingId, overId } = dragState.value;

  if (!draggingId || !overId || draggingId === overId) {
    dragState.value = { sectionId: null, draggingId: null, overId: null };
    return;
  }

  const secIndex = localSections.value.findIndex((s) => s.id === sectionId);
  if (secIndex === -1) return;

  const sec = localSections.value[secIndex];
  if (!sec || !Array.isArray(sec.components)) return;

  const from = sec.components.findIndex((c) => c.id === draggingId);
  const to = sec.components.findIndex((c) => c.id === overId);
  if (from === -1 || to === -1) return;

  const nextComponents = [...sec.components];
  const [moved] = nextComponents.splice(from, 1);
  nextComponents.splice(to, 0, moved);

  const nextSection = { ...sec, components: nextComponents };
  const nextSections = [...localSections.value];
  nextSections.splice(secIndex, 1, nextSection);
  localSections.value = nextSections;

  const orderedIds = nextComponents.map((c) => c.id);

  window.parent?.postMessage(
    { type: "REORDER_COMPONENTS", url: url.value, sectionId, componentIds: orderedIds },
    window.location.origin
  );

  dragState.value = { sectionId: null, draggingId: null, overId: null };
};

const onDragEnd = () => {
  dragState.value = { sectionId: null, draggingId: null, overId: null };
};
</script>

<template>
  <div v-if="isPreview" class="w-full">
    <!-- + before -->
    <div class="py-10 flex justify-center">
      <AddSectionButton @click="postAddSection(0)" />
    </div>

    <div
      v-for="(sec, i) in localSections"
      :key="sec.id"
      class="w-full"
      :class="isSectionOver(sec.id) ? 'ring-2 ring-blue-300 rounded-2xl' : ''"
    >
      <SectionFrame
        :sec="sec"
        :index="i"
        @editHeight="postEditHeight(sec.id)"
        @deleteSection="openDeleteSection(sec.id)"
        @dragStart="onSectionDragStart"
        @dragOver="onSectionDragOver"
        @drop="onSectionDrop"
        @dragEnd="onSectionDragEnd"
      >
        <div class="p-6">
          <!-- EMPTY => picker -->
          <EmptySectionPicker
            v-if="!sec.components || sec.components.length === 0"
            :componentTypes="componentTypes"
            @pick="(t) => postAddComponent(sec.id, t)"
          />

          <!-- NON-EMPTY -->
          <div v-else class="space-y-8">
            <ComponentItem
              v-for="cmp in sec.components"
              :key="cmp.id"
              :secId="sec.id"
              :cmp="cmp"
              :isPreview="isPreview"
              :dragState="dragState"
              :openMenuKey="openMenuKey"
              :menuKeyFn="menuKey"
              :componentTypes="componentTypes"
              :typeLabel="typeLabel"
              :alignClass="alignClass"
              :renderText="renderText"
              :renderTextImage="renderTextImage"
              :renderImage="renderImage"
              :renderGallery="renderGallery"
              :renderAdvancedGallery="renderAdvancedGallery"
              :renderCards="renderCards"
              :renderAccordion="renderAccordion"
              :renderForm="renderForm"
              :renderLocationsMap="renderLocationsMap"
              :renderYouTube="renderYouTube"
              @edit="postEditComponent(sec.id, cmp.id)"
              @toggleMenu="toggleMenu"
              @closeMenus="closeAllMenus"
              @openChangeType="openChangeType(sec.id, cmp.id, cmp.type)"
              @openDeleteComponent="openDeleteComponent(sec.id, cmp.id)"
              @dragStart="onDragStart"
              @dragOver="onDragOver"
              @drop="onDrop"
              @dragEnd="onDragEnd"
            />

            <AddComponentBar
              :componentTypes="componentTypes"
              @add="(t) => postAddComponent(sec.id, t)"
            />
          </div>
        </div>
      </SectionFrame>

      <!-- + after -->
      <div class="py-10 flex justify-center">
        <AddSectionButton @click="postAddSection(i + 1)" />
      </div>
    </div>

    <!-- Modals -->
    <ConfirmDeleteSectionModal
      :open="showDeleteSectionModal"
      @close="closeDeleteSection"
      @confirm="confirmDeleteSection"
    />

    <ChangeComponentTypeModal
      :open="showChangeModal"
      :componentTypes="componentTypes"
      v-model:selectedType="selectedType"
      @close="closeChangeType"
      @confirm="confirmChangeType"
    />

    <ConfirmDeleteComponentModal
      :open="showDeleteComponentModal"
      @close="closeDeleteComponent"
      @confirm="confirmDeleteComponent"
    />
  </div>
</template>
