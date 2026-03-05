<template>
  <div>
    <div class="py-1">
      <Button
        class="mr-2"
        label="Předchozí krok"
        severity="secondary"
        @click="$emit('goPrev')"
      />
    </div>

    <!-- hidden input pro upload BG obrázku v builderu -->
    <input
      ref="fileInputBannerImage"
      type="file"
      accept="image/*"
      class="hidden"
      @change="handleBannerImageChange"
    />

    <div v-if="!allTargets.length" class="text-sm text-gray-500 mt-4">
      Nejsou k dispozici žádné stránky ani podstránky.
    </div>

    <Tabs v-else :value="selectedKey" scrollable class="w-full">
      <TabList class="mb-5">
        <Tab
          v-for="t in allTargets"
          :key="t.key"
          :value="t.key"
          @click="$emit('selectPage', t.payload)"
          class="!px-4 !py-2"
        >
          <div class="flex flex-col leading-tight">
            <div class="flex items-center gap-2">
              <span v-if="t.kind === 'subpage'" class="text-gray-400">↳</span>
              <span :class="t.kind === 'subpage' ? 'font-semibold' : 'font-bold'">
                {{ t.title }}
              </span>
            </div>

            <div v-if="t.kind === 'subpage'" class="text-[11px] text-gray-500 ml-5">
              Podstránka
            </div>
          </div>
        </Tab>
      </TabList>

      <TabPanels class="w-full">
        <TabPanel v-for="t in allTargets" :key="t.key" :value="t.key" class="w-full">
          <div class="w-full max-w-4xl bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold">Banner pro: {{ t.title }}</h3>
            </div>

            <div class="flex items-center gap-4">
              <button
                type="button"
                @click="addEmptyBanner"
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 active:bg-green-800 disabled:opacity-50"
                :disabled="busy"
              >
                <i class="fa-solid fa-plus"></i>
                Přidat banner
              </button>

              <span class="text-xs text-gray-500">
                Počet bannerů na stránce: {{ localBanners.length }}
              </span>

              <div v-if="busy" class="text-xs text-gray-500">Pracuju…</div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <BannerCard
                v-for="(item, idx) in localBanners"
                :key="item.id"
                :id="item.id"
                :banner-url="item.banner_url"
                :thumbnail-url="metaFor(item).thumbnail_url || item.thumbnail_url"
                :thumb-preview="metaFor(item).thumb_preview"
                :heading-h1="metaFor(item).heading_h1"
                :heading-h2="metaFor(item).heading_h2"
                :bg-key="metaFor(item).bg_key"
                :overlay-key="metaFor(item).overlay_key"
                :overlay-opacity="metaFor(item).overlay_opacity"
                :palette="palette"
                :disabled="busy"
                :draggable="!busy"
                :drag-over="dragOverIndex === idx"
                @open="openBannerDialog(item)"
                @openThumb="openThumbDialog(item)"
                @openText="openTextDialog(item)"
                @delete="requestDeleteSliderItem(item)"
                @dragstart="onDragStart(idx)"
                @dragover="onDragOver(idx)"
                @drop="onDrop(idx)"
                @dragend="onDragEnd"
              />
            </div>

            <div v-if="!localBanners.length" class="text-sm text-gray-400 mt-4">
              Zatím žádné bannery ve slideru.
            </div>
          </div>
        </TabPanel>
      </TabPanels>
    </Tabs>

    <div class="py-6">
      <Button label="Další krok" @click="$emit('goNext')" />
    </div>

    <!-- BUILDER MODAL -->
    <BannerBuilderDialog
      v-model:visible="builderVisible"
      :banner-id="activeBannerId"
      :banner-url="activeBannerUrl"
      :bg-key="builderForm.bg_key"
      :overlay-key="builderForm.overlay_key"
      :overlay-opacity="builderForm.overlay_opacity"
      :palette="palette"
      :busy="busy"
      @pickImage="openBannerImagePicker"
      @removeImage="removeBannerImage"
      @save="saveBuilder"
    />

    <!-- TEXT MODAL -->
    <BannerTextDialog
      v-model:visible="textVisible"
      :banner-id="activeTextBannerId"
      :palette="palette"
      :busy="busy"
      :heading-h1="textForm.heading_h1"
      :heading-h2="textForm.heading_h2"
      :h1-color-key="textForm.h1_color_key"
      :h2-color-key="textForm.h2_color_key"
      :h1-size="textForm.h1_size"
      :h2-size="textForm.h2_size"
      :button-enabled="textForm.button_enabled"
      :button-text="textForm.button_text"
      :button-url="textForm.button_url"
      @save="saveText"
    />

    <!-- THUMB MODAL -->
    <BannerThumbDialog
      v-model:visible="thumbVisible"
      :banner-id="activeThumbBannerId"
      :busy="busy"
      :thumbnail-size="thumbForm.thumbnail_size"
      :thumbnail-url="activeThumbThumbnailUrl"
      :thumb-preview="activeThumbPreviewUrl"
      @saveDraft="saveThumbDraft"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, reactive } from "vue";
import axios from "axios";

import BannerCard from "./components/BannerCard.vue";
import BannerBuilderDialog from "./components/BannerBuilderDialog.vue";
import BannerTextDialog from "./components/BannerTextDialog.vue";
import BannerThumbDialog from "./components/BannerThumbDialog.vue";

const props = defineProps({
  pages: { type: Array, required: true },
  subpages: { type: Array, required: false, default: () => [] },

  selectedPageUrl: { type: String, default: "/" },
  selectedPageId: { type: [Number, String], default: null }, // ✅ DŮLEŽITÉ
  selectedPageType: { type: String, default: "page" }, // ✅ DŮLEŽITÉ

  pageDesignData: { type: Object, required: true },
  mainDesign: { type: Object, required: true },
});

const emit = defineEmits(["goPrev", "goNext", "selectPage", "deleteBannerItem"]);

// ✅ target posíláme podle toho, co parent drží jako vybraný typ
const withTarget = (url) => {
  const t = props.selectedPageType === "subpage" ? "subpage" : "page";
  return url.includes("?") ? `${url}&target=${t}` : `${url}?target=${t}`;
};

const builderVisible = ref(false);
const activeBannerItem = ref(null);
const busy = ref(false);

const thumbVisible = ref(false);
const activeThumbBannerId = ref(null);
const activeThumbItem = ref(null);

const fileInputBannerImage = ref(null);
const resetInput = (r) => {
  if (r?.value) r.value.value = "";
};

// ===== SAFE HEX helper =====
const isHex = (v) =>
  typeof v === "string" && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(v.trim());
const safeHex = (v, fallback) => (isHex(v) ? v.trim() : fallback);

// ===== palette =====
const palette = computed(() => ({
  primary: safeHex(props.mainDesign?.primary_color, "#22c55e"),
  secondary: safeHex(props.mainDesign?.secondary_color, "#ef4444"),
  tertiary: safeHex(props.mainDesign?.tertiary_color, "#3b82f6"),
  quaternary: safeHex(props.mainDesign?.quaternary_color, "#111827"),
  quinary: safeHex(props.mainDesign?.quinary_color, "#f59e0b"),
  transparent: "transparent",
}));

/**
 * ===== TABS: pages + subpages
 */
const allTargets = computed(() => {
  const PARENT_KEY = "page_id"; // ← pokud máš jinak, změň (např. "parent_id")

  const pages = (props.pages || []).map((p) => ({
    key: `page:${p.url}`,
    kind: "page",
    title: p.title,
    payload: { id: p.id, url: p.url, title: p.title, _type: "page" },
  }));

  const subpages = (props.subpages || []).map((s) => ({
    key: `subpage:${s.url ?? s.id}`,
    kind: "subpage",
    title: s.title,
    payload: { id: s.id, url: s.url, title: s.title, _type: "subpage" },
    _parentId: s?.[PARENT_KEY] ?? null, // ← rodič ID
  }));

  // index subpages podle rodiče
  const subsByParent = new Map();
  for (const sp of subpages) {
    const pid = sp._parentId;
    if (!pid) continue;
    if (!subsByParent.has(pid)) subsByParent.set(pid, []);
    subsByParent.get(pid).push(sp);
  }

  // seřazení: stránka + její podstránky hned za ní
  const ordered = [];
  for (const p of pages) {
    ordered.push(p);

    const children = subsByParent.get(p.payload.id) || [];
    // můžeš seřadit podstránky podle title
    children.sort((a, b) => String(a.title).localeCompare(String(b.title), "cs"));
    ordered.push(...children);
  }

  // podstránky bez rodiče (když data nemají vazbu)
  const orphans = subpages.filter((sp) => !sp._parentId);
  if (orphans.length) {
    orphans.sort((a, b) => String(a.title).localeCompare(String(b.title), "cs"));
    ordered.push(...orphans);
  }

  return ordered;
});

const selectedKey = computed(() => {
  const byPage = `page:${props.selectedPageUrl}`;
  if (allTargets.value.some((x) => x.key === byPage)) return byPage;

  const bySub = `subpage:${props.selectedPageUrl}`;
  if (allTargets.value.some((x) => x.key === bySub)) return bySub;

  return allTargets.value[0]?.key ?? `page:${props.selectedPageUrl}`;
});

// ===== META =====
const DEFAULT_META_KEY = "__default__";

const localMeta = ref({
  [DEFAULT_META_KEY]: {
    thumbnail_url: null,
    thumb_preview: null,
    thumbnail_size: 40,

    heading_h1: null,
    heading_h2: null,

    bg_key: "transparent",
    overlay_key: "transparent",
    overlay_opacity: 0,

    h1_color_key: "secondary",
    h2_color_key: "secondary",
    h1_size: 48,
    h2_size: 22,

    button_enabled: false,
    button_text: null,
    button_url: null,
  },
});

const ensureMeta = (key) => {
  if (!localMeta.value[key]) {
    localMeta.value[key] = JSON.parse(JSON.stringify(localMeta.value[DEFAULT_META_KEY]));
  }
  return localMeta.value[key];
};

const itemKey = (item) => String(item?.id ?? "");
const metaFor = (item) => ensureMeta(itemKey(item));

const localBanners = ref([]);

// ===== hydrate banners z pageDesignData =====
watch(
  () => props.pageDesignData,
  (val) => {
    localBanners.value = [...(val?.banners ?? [])].filter((x) => !!x?.id);
    for (const it of localBanners.value) hydrateMetaFromDbItem(metaFor(it), it);

    if (builderVisible.value && activeBannerItem.value?.id) {
      const m = metaFor(activeBannerItem.value);
      builderForm.bg_key = m.bg_key ?? "transparent";
      builderForm.overlay_key = m.overlay_key ?? "transparent";
      builderForm.overlay_opacity = Number(m.overlay_opacity ?? 0);
    }

    if (thumbVisible.value && activeThumbItem.value?.id) {
      activeThumbBannerId.value = activeThumbItem.value.id;
      thumbForm.thumbnail_size = Number(
        metaFor(activeThumbItem.value).thumbnail_size ?? 40
      );
    }
  },
  { deep: true, immediate: true }
);

// ===================================================================
// SLIDER add / delete
// ===================================================================
const addEmptyBanner = async () => {
  try {
    busy.value = true;

    const entityId = Number(props.selectedPageId ?? 0);
    if (!entityId) throw new Error("Vyberte stránku/podstránku (chybí ID).");

    const res = await axios.post(
      withTarget(`/admin/page-design/${entityId}/banner-items/empty`),
      { banner_count: 10 }
    );

    const item = res?.data?.item;
    if (item?.id) localBanners.value = [...localBanners.value, item];
  } catch (e) {
    alert(
      e?.response?.data?.message || e?.message || "Nepodařilo se vytvořit prázdný banner."
    );
  } finally {
    busy.value = false;
  }
};

const requestDeleteSliderItem = (item) => item?.id && emit("deleteBannerItem", item.id);

// ===================================================================
// DRAG & DROP reorder
// ===================================================================
const draggingIndex = ref(null);
const dragOverIndex = ref(null);

const onDragStart = (idx) => (draggingIndex.value = idx);
const onDragOver = (idx) => {
  if (draggingIndex.value === null) return;
  dragOverIndex.value = idx;
};

const onDrop = async (idx) => {
  const from = draggingIndex.value;
  const to = idx;

  draggingIndex.value = null;
  dragOverIndex.value = null;

  if (from === null || to === null || from === to) return;

  const next = [...localBanners.value];
  const moved = next.splice(from, 1)[0];
  next.splice(to, 0, moved);
  localBanners.value = next;

  await persistReorder();
};

const onDragEnd = () => {
  draggingIndex.value = null;
  dragOverIndex.value = null;
};

const persistReorder = async () => {
  try {
    const entityId = Number(props.selectedPageId ?? 0);
    if (!entityId) return;

    const order = (localBanners.value || [])
      .map((x) => x?.id)
      .filter((id) => Number.isInteger(id));

    busy.value = true;

    await axios.post(withTarget(`/admin/page-design/${entityId}/banner-items/reorder`), {
      order,
    });
  } catch (e) {
    alert(
      e?.response?.data?.message || e?.message || "Nepodařilo se uložit pořadí bannerů."
    );
  } finally {
    busy.value = false;
  }
};

// ===================================================================
// BUILDER MODAL
// ===================================================================
const builderForm = reactive({
  bg_key: "transparent",
  overlay_key: "transparent",
  overlay_opacity: 0,
});

const activeBannerId = computed(() => activeBannerItem.value?.id ?? null);
const activeBannerUrl = computed(() => activeBannerItem.value?.banner_url ?? null);

const openBannerDialog = (item) => {
  if (!item?.id) return;

  activeBannerItem.value = item;

  const m = metaFor(item);
  builderForm.bg_key = m.bg_key ?? "transparent";
  builderForm.overlay_key = m.overlay_key ?? "transparent";
  builderForm.overlay_opacity = Number(m.overlay_opacity ?? 0);

  builderVisible.value = true;
};

const keyToHex = (k) => (k && k !== "transparent" ? palette.value[k] : null);

const saveBuilder = async ({ bg_key, overlay_key, overlay_opacity }) => {
  try {
    const entityId = Number(props.selectedPageId ?? 0);
    const bannerId = activeBannerId.value;
    if (!entityId) throw new Error("Chybí page/subpage id");
    if (!bannerId) throw new Error("Chybí bannerId");

    busy.value = true;

    await axios.post(
      withTarget(`/admin/page-design/${entityId}/banner-items/${bannerId}/builder`),
      {
        bg_type: "color",
        bg_color: keyToHex(bg_key) ?? "#f3f4f6",
        overlay_color: keyToHex(overlay_key),
        overlay_opacity: Math.round(Number(overlay_opacity ?? 0) * 100),
      }
    );

    const m = metaFor(activeBannerItem.value);
    m.bg_key = bg_key;
    m.overlay_key = overlay_key;
    m.overlay_opacity = Number(overlay_opacity ?? 0);

    builderVisible.value = false;
  } catch (e) {
    alert(
      e?.response?.data?.message || e?.message || "Nepodařilo se uložit pozadí/overlay."
    );
  } finally {
    busy.value = false;
  }
};

// ===== image upload / remove =====
const openBannerImagePicker = () => {
  if (busy.value) return;
  resetInput(fileInputBannerImage);
  fileInputBannerImage.value?.click();
};

const handleBannerImageChange = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file) return;

  try {
    const entityId = Number(props.selectedPageId ?? 0);
    const bannerId = activeBannerId.value;
    if (!entityId) throw new Error("Chybí page/subpage id");
    if (!bannerId) throw new Error("Chybí bannerId");

    busy.value = true;

    const form = new FormData();
    form.append("banner", file);

    const res = await axios.post(
      withTarget(`/admin/page-design/${entityId}/banner-items/${bannerId}/image`),
      form,
      { headers: { "Content-Type": "multipart/form-data" } }
    );

    const item = res?.data?.item;
    if (!item) return;

    const idx = localBanners.value.findIndex((x) => x.id === bannerId);
    if (idx !== -1) localBanners.value[idx].banner_url = item.banner_url ?? null;

    if (activeBannerItem.value?.id === bannerId) {
      activeBannerItem.value.banner_url = item.banner_url ?? null;
    }
  } catch (e2) {
    alert(e2?.response?.data?.message || e2?.message || "Nepodařilo se nahrát obrázek.");
  } finally {
    busy.value = false;
    resetInput(fileInputBannerImage);
  }
};

const removeBannerImage = async () => {
  try {
    const entityId = Number(props.selectedPageId ?? 0);
    const bannerId = activeBannerId.value;
    if (!entityId) throw new Error("Chybí page/subpage id");
    if (!bannerId) throw new Error("Chybí bannerId");

    busy.value = true;

    await axios.post(
      withTarget(`/admin/page-design/${entityId}/banner-items/${bannerId}/builder`),
      {
        bg_type: "color",
        bg_color: keyToHex(builderForm.bg_key) ?? "#f3f4f6",
        overlay_color: keyToHex(builderForm.overlay_key),
        overlay_opacity: Math.round(Number(builderForm.overlay_opacity ?? 0) * 100),
        banner_url: null,
      }
    );

    const idx = localBanners.value.findIndex((x) => x.id === bannerId);
    if (idx !== -1) localBanners.value[idx].banner_url = null;

    if (activeBannerItem.value?.id === bannerId) {
      activeBannerItem.value.banner_url = null;
    }
  } catch (e) {
    alert(e?.response?.data?.message || e?.message || "Nepodařilo se odebrat obrázek.");
  } finally {
    busy.value = false;
  }
};

// ===================================================================
// TEXT MODAL
// ===================================================================
const textVisible = ref(false);
const activeTextBannerId = ref(null);
const activeTextKey = ref(null);

const textForm = reactive({
  heading_h1: "",
  heading_h2: "",
  h1_color_key: "secondary",
  h2_color_key: "secondary",
  h1_size: 48,
  h2_size: 22,
  button_enabled: false,
  button_text: "",
  button_url: "",
});

const hydrateTextFormFromMeta = (key) => {
  const m = ensureMeta(key);

  textForm.heading_h1 = m.heading_h1 ?? "";
  textForm.heading_h2 = m.heading_h2 ?? "";

  textForm.h1_color_key = m.h1_color_key ?? "secondary";
  textForm.h2_color_key = m.h2_color_key ?? "secondary";
  textForm.h1_size = Number(m.h1_size ?? 48);
  textForm.h2_size = Number(m.h2_size ?? 22);

  textForm.button_enabled = !!(m.button_enabled ?? false);
  textForm.button_text = m.button_text ?? "";
  textForm.button_url = m.button_url ?? "";
};

const openTextDialog = (item) => {
  if (!item?.id) return;

  const key = itemKey(item);
  activeTextBannerId.value = item.id;
  activeTextKey.value = key;

  hydrateTextFormFromMeta(key);
  textVisible.value = true;
};

const saveText = async (payload) => {
  try {
    const entityId = Number(props.selectedPageId ?? 0);
    const bannerId = activeTextBannerId.value;
    const key = activeTextKey.value;

    if (!entityId) throw new Error("Chybí page/subpage id");
    if (!bannerId) throw new Error("Chybí bannerId");
    if (!key) throw new Error("Chybí key");

    busy.value = true;

    await axios.post(
      withTarget(`/admin/page-design/${entityId}/banner-items/${bannerId}/text`),
      {
        heading_h1: payload.heading_h1,
        heading_h2: payload.heading_h2,
        button_enabled: !!payload.button_enabled,
        button_text: payload.button_enabled ? payload.button_text || null : null,
        button_url: payload.button_enabled ? payload.button_url || null : null,
      }
    );

    await axios.post(
      withTarget(`/admin/page-design/${entityId}/banner-items/${bannerId}/text-style`),
      {
        h1_color: keyToHex(payload.h1_color_key),
        h2_color: keyToHex(payload.h2_color_key),
        h1_size: payload.h1_size,
        h2_size: payload.h2_size,
      }
    );

    const m = ensureMeta(key);
    m.heading_h1 = payload.heading_h1;
    m.heading_h2 = payload.heading_h2;
    m.h1_color_key = payload.h1_color_key;
    m.h2_color_key = payload.h2_color_key;
    m.h1_size = payload.h1_size;
    m.h2_size = payload.h2_size;

    m.button_enabled = !!payload.button_enabled;
    m.button_text = payload.button_enabled ? payload.button_text || null : null;
    m.button_url = payload.button_enabled ? payload.button_url || null : null;

    textVisible.value = false;
  } catch (e) {
    alert(
      e?.response?.data?.message || e?.message || "Nepodařilo se uložit text banneru."
    );
  } finally {
    busy.value = false;
  }
};

// ===================================================================
// THUMB MODAL
// ===================================================================
const thumbForm = reactive({ thumbnail_size: 40 });

const openThumbDialog = (item) => {
  if (!item?.id) return;

  activeThumbItem.value = item;
  activeThumbBannerId.value = item.id;
  thumbForm.thumbnail_size = Number(metaFor(item).thumbnail_size ?? 40);

  thumbVisible.value = true;
};

const activeThumbThumbnailUrl = computed(() => {
  if (!activeThumbBannerId.value || !activeThumbItem.value) return null;
  return metaFor(activeThumbItem.value).thumbnail_url ?? null;
});

const activeThumbPreviewUrl = computed(() => {
  if (!activeThumbBannerId.value || !activeThumbItem.value) return null;
  return metaFor(activeThumbItem.value).thumb_preview ?? null;
});

const saveThumbDraft = async ({ banner_id, thumbnail_size, file, remove }) => {
  const entityId = Number(props.selectedPageId ?? 0);
  const key = activeThumbItem.value ? itemKey(activeThumbItem.value) : null;

  try {
    if (!entityId) throw new Error("Chybí page/subpage id");
    if (!banner_id) throw new Error("Chybí banner_id");
    if (!key) throw new Error("Chybí key");

    busy.value = true;

    if (remove) {
      await axios.post(
        withTarget(
          `/admin/page-design/${entityId}/banner-items/${banner_id}/thumbnail-delete`
        )
      );

      ensureMeta(key).thumbnail_url = null;
      ensureMeta(key).thumb_preview = null;

      const idx = localBanners.value.findIndex((x) => x.id === banner_id);
      if (idx !== -1) localBanners.value[idx].thumbnail_url = null;
    }

    if (file) {
      const form = new FormData();
      form.append("thumbnail", file);

      const res = await axios.post(
        withTarget(`/admin/page-design/${entityId}/banner-items/${banner_id}/thumbnail`),
        form,
        { headers: { "Content-Type": "multipart/form-data" } }
      );

      const item = res?.data?.item;
      if (item?.thumbnail_url) {
        ensureMeta(key).thumbnail_url = item.thumbnail_url;
        ensureMeta(key).thumb_preview = null;

        const idx = localBanners.value.findIndex((x) => x.id === banner_id);
        if (idx !== -1) localBanners.value[idx].thumbnail_url = item.thumbnail_url;
      }
    }

    await axios.post(
      withTarget(
        `/admin/page-design/${entityId}/banner-items/${banner_id}/thumbnail-size`
      ),
      { thumbnail_size: Number(thumbnail_size ?? 40) }
    );

    ensureMeta(key).thumbnail_size = Number(thumbnail_size ?? 40);
  } catch (e) {
    alert(e?.response?.data?.message || e?.message || "Nepodařilo se uložit miniaturu.");
  } finally {
    busy.value = false;
  }
};

// ===================================================================
// HELPERS hydrate meta z DB
// ===================================================================
const normalizeHex = (hex) => (hex ? String(hex).trim().toLowerCase() : "");

const hexToKey = (hex) => {
  const n = normalizeHex(hex);
  if (!n) return "transparent";

  const entries = Object.entries(palette.value || {});
  for (const [k, v] of entries) {
    if (k === "transparent") continue;
    if (normalizeHex(v) === n) return k;
  }
  return "transparent";
};

const clamp01 = (v) => {
  const x = Number(v);
  if (Number.isNaN(x)) return 0;
  return Math.max(0, Math.min(1, x));
};

const hydrateMetaFromDbItem = (m, it) => {
  m.heading_h1 = it?.heading_h1 ?? m.heading_h1 ?? null;
  m.heading_h2 = it?.heading_h2 ?? m.heading_h2 ?? null;

  m.thumbnail_url = it?.thumbnail_url ?? m.thumbnail_url ?? null;
  m.thumbnail_size = Number(it?.thumbnail_size ?? m.thumbnail_size ?? 40);

  const bgColor = it?.bg_color ?? null;
  m.bg_key = bgColor ? hexToKey(bgColor) : m.bg_key ?? "transparent";

  const overlayColor = it?.overlay_color ?? null;
  m.overlay_key = overlayColor ? hexToKey(overlayColor) : "transparent";

  const overlayOpacityInt = it?.overlay_opacity ?? 0;
  m.overlay_opacity = clamp01(Number(overlayOpacityInt) / 100);

  const h1ColorHex = it?.h1_color ?? null;
  const h2ColorHex = it?.h2_color ?? null;
  m.h1_color_key = h1ColorHex ? hexToKey(h1ColorHex) : m.h1_color_key ?? "secondary";
  m.h2_color_key = h2ColorHex ? hexToKey(h2ColorHex) : m.h2_color_key ?? "secondary";
  m.h1_size = Number(it?.h1_size ?? m.h1_size ?? 48);
  m.h2_size = Number(it?.h2_size ?? m.h2_size ?? 22);

  m.button_enabled = !!(it?.button_enabled ?? m.button_enabled ?? false);
  m.button_text = it?.button_text ?? m.button_text ?? null;
  m.button_url = it?.button_url ?? m.button_url ?? null;
};
</script>
