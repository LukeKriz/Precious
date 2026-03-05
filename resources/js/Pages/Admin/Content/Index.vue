<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

import AddSectionModal from "./modals/AddSectionModal.vue";
import EditSectionModal from "./modals/EditSectionModal.vue";
import EditComponentModal from "./modals/EditComponentModal.vue";

const page = usePage();

const props = defineProps({
  selectedUrl: { type: String, default: "/" },
  pageContent: { type: Array, default: () => [] },
  mainDesign: { type: Object, default: () => ({}) },
});

const mainDesign = computed(() => props.mainDesign || {});
const deepCopy = (v) => JSON.parse(JSON.stringify(v ?? []));

const pages = computed(() => page.props.pages ?? []);
const subpages = computed(() => page.props.subpages ?? []);

const selectedUrl = ref(props.selectedUrl || "/");
const localContent = ref(deepCopy(props.pageContent));

watch(
  () => props.pageContent,
  (v) => (localContent.value = deepCopy(v))
);
watch(
  () => props.selectedUrl,
  (u) => (selectedUrl.value = u || "/")
);

// ===== IFRAME =====
const iframeKey = ref(0);
const reloadIframe = () => iframeKey.value++;

const iframeSrc = computed(() => {
  const u = selectedUrl.value || "/";
  const join = u.includes("?") ? "&" : "?";
  return `${u}${join}preview=1&_ts=${iframeKey.value}`;
});

const selectUrl = (url) => {
  const u = url || "/";
  router.get("/admin/content", { url: u }, { preserveState: false });
};

// ===== SCROLL PERSISTENCE (works on F5) =====
const iframeRef = ref(null);
const scrollKey = computed(() => `admin_iframe_scroll:${selectedUrl.value || "/"}`);
const lastIframeScrollY = ref(0);

const loadSavedScroll = () => {
  try {
    const saved = sessionStorage.getItem(scrollKey.value);
    lastIframeScrollY.value = saved ? Number(saved) : 0;
  } catch {
    lastIframeScrollY.value = 0;
  }
};

const saveScroll = (y) => {
  lastIframeScrollY.value = Number(y || 0);
  try {
    sessionStorage.setItem(scrollKey.value, String(lastIframeScrollY.value));
  } catch {}
};

// ===== IDS =====
const genId = () => `sec_${Date.now()}_${Math.random().toString(16).slice(2)}`;
const genCompId = () => `cmp_${Date.now()}_${Math.random().toString(16).slice(2)}`;

// ===== COMPONENT TYPES + DEFAULTS =====
const componentTypes = [
  { type: "text", label: "Text" },
  { type: "text_image", label: "Text + obrázek" },
  { type: "image", label: "Obrázek" },
  { type: "gallery", label: "Galerie" },
  { type: "cards", label: "Karty" },
  { type: "form", label: "Formulář" },
  { type: "accordion", label: "Rozbalovací blok" },
  { type: "locations_map", label: "Pobočky + mapa" },
  { type: "advanced_gallery", label: "Pokročilá galerie" },
  { type: "youtube", label: "YouTube video" },
];

const typeLabel = (t) => componentTypes.find((x) => x.type === t)?.label ?? t;

const defaultComponentPayload = (type) => {
  switch (type) {
    case "text":
      return {
        html: "",
        buttonEnabled: false,
        buttonText: "",
        buttonUrl: "",
      };
    case "text_image":
      return {
        heading: "",
        text: "",
        align: "left",
        layout: "text_left",
        image: null,
        button: {
          enabled: false,
          text: "",
          action: "modal", // 'modal' | 'link'
          url: "",
          modalComponent: null, // { type, data }
        },
      };
    case "image":
      return {
        image: null,
        widthPx: 100,
        maxHeight: 420,
        fit: "contain", // 'contain' | 'cover'
        align: "center", // 'left' | 'center' | 'right'
        click: {
          enabled: false,
          action: "modal", // 'modal' | 'link'
          url: "",
          modalComponent: null, // { type, data }
        },
      };
    case "gallery":
      return { previewCount: 6, images: [] };
    case "cards":
      return {
        previewCount: 3,
        cards: [
          {
            id: `card_${Date.now()}_${Math.random().toString(16).slice(2)}`,
            title: "",
            text: "",
            image: null,

            // ✅ klik na celou kartu
            clickable: false, // "Proklik celé karty" Ano/Ne
            action: "link", // 'modal' | 'link' (doporučuju default link)
            url: "",
            modalComponent: null, // { type, data }

            // ✅ tlačítko
            buttonEnabled: true,
            buttonText: "Více informací",
            buttonAction: "link", // 'modal' | 'link'
            buttonUrl: "",
            buttonModalComponent: null, // { type, data }
          },
        ],
      };
    case "form":
      return { formId: null };
    case "accordion":
      return { title: "", openFirst: false, items: [] };
    case "locations_map":
      return {
        title: "KONTAKTUJTE NÁS:",
        subtitle: "",
        mapSide: "left",
        cardTitleEnabled: true,
        buttonEnabled: true,
        buttonText: "Napište nám",
        buttonUrl: "",
        locations: [
          {
            id: `loc_${Date.now()}_${Math.random().toString(16).slice(2)}`,
            tabLabel: "Pobočka 1",
            name: "",
            address: "",
            email: "",
            phone: "",
            mapEmbedUrl: "",
            mapLinkUrl: "",
          },
        ],
      };
    case "advanced_gallery":
      return {
        categories: [
          {
            id: `cat_${Date.now()}_${Math.random().toString(16).slice(2)}`,
            label: "Jen tak",
            galleries: [
              {
                id: `gal_${Date.now()}_${Math.random().toString(16).slice(2)}`,
                title: "Galerie 1",
                cover: null,
                images: [],
              },
            ],
          },
          {
            id: `cat_${Date.now() + 1}_${Math.random().toString(16).slice(2)}`,
            label: "Sr…",
            galleries: [
              {
                id: `gal_${Date.now() + 1}_${Math.random().toString(16).slice(2)}`,
                title: "Galerie 1",
                cover: null,
                images: [],
              },
            ],
          },
        ],
      };

    case "youtube":
      return {
        url: "",
        videoId: "",
        widthPx: null,
        maxWidthPx: null,
        aspect: "16:9", // "16:9" | "4:3" | "1:1"
        align: "center", // left|center|right
        start: null,
      };

    default:
      return {};
  }
};

// ===== SAVE (content_json) =====
const isSaving = ref(false);

const save = (forcedPayload = null) => {
  isSaving.value = true;

  const payload =
    forcedPayload !== null
      ? forcedPayload
      : Array.isArray(localContent.value)
      ? localContent.value
      : [];

  router.post(
    "/admin/content/save",
    {
      url: selectedUrl.value,
      content_json: JSON.stringify(payload),
    },
    {
      preserveScroll: true,
      onFinish: () => {
        isSaving.value = false;
        reloadIframe();
      },
    }
  );
};

// ===== utils =====
const normalizeVA = (v) => {
  const s = String(v ?? "").trim();
  return ["top", "center", "bottom"].includes(s) ? s : "center";
};

// ===== SECTION ACTIONS =====
const addSectionAt = (p) => {
  const insertIndex = Math.max(0, Number(p?.insertIndex ?? 0));
  const height = Math.max(80, Number(p?.height ?? 300));

  const sec = {
    id: genId(),
    type: "section",
    height,

    bg: String(p?.bg ?? "").trim(),
    bgImage: String(p?.bgImage ?? "").trim(),
    overlayColor: String(p?.overlayColor ?? "").trim(),
    overlayOpacity: Math.max(0, Math.min(100, Number(p?.overlayOpacity ?? 0))),
    pt: Math.max(0, Number(p?.paddingTop ?? 0)),
    pb: Math.max(0, Number(p?.paddingBottom ?? 0)),

    // ✅ ULOŽÍ SE DO DB
    va: normalizeVA(p?.va ?? "center"),

    components: [],
  };

  // pravidlo: pokud je bgImage, barva se ignoruje
  if (sec.bgImage) sec.bg = "";

  const arr = Array.isArray(localContent.value) ? localContent.value : [];
  arr.splice(insertIndex, 0, sec);

  localContent.value = arr;
  save(arr);
};

const deleteSectionById = (rawId) => {
  const id = String(rawId ?? "").trim();
  if (!id) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];
  const next = arr.filter((x) => String(x?.id ?? "") !== id);

  localContent.value = next;
  save(next);
};

const updateSectionSettings = (sectionIdRaw, patch) => {
  const id = String(sectionIdRaw ?? "").trim();
  if (!id) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];
  const next = arr.map((x) => {
    if (String(x?.id ?? "") !== id) return x;

    const nextBgImage = String(patch?.bgImage ?? x.bgImage ?? "").trim();
    const nextBg = String(patch?.bg ?? x.bg ?? "").trim();

    return {
      ...x,
      height: Math.max(80, Number(patch?.height ?? x.height ?? 300)),

      bgImage: nextBgImage,
      bg: nextBgImage ? "" : nextBg, // image ruší bg

      overlayColor: String(patch?.overlayColor ?? x.overlayColor ?? "").trim(),
      overlayOpacity: Math.max(
        0,
        Math.min(100, Number(patch?.overlayOpacity ?? x.overlayOpacity ?? 0))
      ),

      pt: Math.max(0, Number(patch?.paddingTop ?? x.pt ?? 0)),
      pb: Math.max(0, Number(patch?.paddingBottom ?? x.pb ?? 0)),

      // ✅ ULOŽÍ SE DO DB
      va: normalizeVA(patch?.va ?? x.va ?? "center"),
    };
  });

  localContent.value = next;
  save(next);
};

// ===== COMPONENT ACTIONS =====
const addComponentToSection = (sectionIdRaw, componentTypeRaw) => {
  const sectionId = String(sectionIdRaw ?? "").trim();
  const componentType = String(componentTypeRaw ?? "").trim();
  if (!sectionId || !componentType) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];

  const next = arr.map((sec) => {
    if (String(sec?.id ?? "") !== sectionId) return sec;

    const comps = Array.isArray(sec.components) ? [...sec.components] : [];
    comps.push({
      id: genCompId(),
      type: componentType,
      data: defaultComponentPayload(componentType),
    });

    return { ...sec, components: comps };
  });

  localContent.value = next;
  save(next);
};

const deleteComponentFromSection = (sectionIdRaw, componentIdRaw) => {
  const sectionId = String(sectionIdRaw ?? "").trim();
  const componentId = String(componentIdRaw ?? "").trim();
  if (!sectionId || !componentId) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];

  const next = arr.map((sec) => {
    if (String(sec?.id ?? "") !== sectionId) return sec;

    const comps = Array.isArray(sec.components) ? sec.components : [];
    const nextComps = comps.filter((c) => String(c?.id ?? "") !== componentId);

    return { ...sec, components: nextComps };
  });

  localContent.value = next;
  save(next);
};

const changeComponentType = (sectionIdRaw, componentIdRaw, newTypeRaw) => {
  const sectionId = String(sectionIdRaw ?? "").trim();
  const componentId = String(componentIdRaw ?? "").trim();
  const newType = String(newTypeRaw ?? "").trim();
  if (!sectionId || !componentId || !newType) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];

  const next = arr.map((sec) => {
    if (String(sec?.id ?? "") !== sectionId) return sec;

    const comps = Array.isArray(sec.components) ? sec.components : [];
    const nextComps = comps.map((c) => {
      if (String(c?.id ?? "") !== componentId) return c;
      return { ...c, type: newType, data: defaultComponentPayload(newType) };
    });

    return { ...sec, components: nextComps };
  });

  localContent.value = next;
  save(next);
};

const applyComponentData = (sectionIdRaw, componentIdRaw, newData) => {
  const sectionId = String(sectionIdRaw ?? "").trim();
  const componentId = String(componentIdRaw ?? "").trim();
  if (!sectionId || !componentId) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];

  const next = arr.map((sec) => {
    if (String(sec?.id ?? "") !== sectionId) return sec;

    const comps = Array.isArray(sec.components) ? sec.components : [];
    const nextComps = comps.map((c) => {
      if (String(c?.id ?? "") !== componentId) return c;
      return { ...c, data: newData };
    });

    return { ...sec, components: nextComps };
  });

  localContent.value = next;
  save(next);
};

// ✅ REORDER
const reorderComponentsInSection = (sectionIdRaw, componentIdsRaw) => {
  const sectionId = String(sectionIdRaw ?? "").trim();
  const componentIds = Array.isArray(componentIdsRaw) ? componentIdsRaw : [];
  if (!sectionId || componentIds.length === 0) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];

  const next = arr.map((sec) => {
    if (String(sec?.id ?? "") !== sectionId) return sec;

    const comps = Array.isArray(sec.components) ? sec.components : [];
    const map = new Map(comps.map((c) => [String(c.id), c]));

    const ordered = componentIds.map((id) => map.get(String(id))).filter(Boolean);

    const used = new Set(componentIds.map((x) => String(x)));
    const leftovers = comps.filter((c) => !used.has(String(c.id)));

    return { ...sec, components: [...ordered, ...leftovers] };
  });

  localContent.value = next;
  save(next);
};

// ===== CSRF for fetch =====
const csrf = () =>
  document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

const uploadFile = async (file) => {
  const fd = new FormData();
  fd.append("file", file);

  const res = await fetch("/admin/content/upload-file", {
    method: "POST",
    headers: { "X-CSRF-TOKEN": csrf() },
    body: fd,
  });

  if (!res.ok) {
    const t = await res.text();
    throw new Error(t || "Upload failed");
  }
  return await res.json();
};

const deleteFileOnServer = async (path) => {
  if (!path) return;
  await fetch("/admin/content/delete-file", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrf(),
    },
    body: JSON.stringify({ path }),
  });
};

// ===== MODAL STATES =====
const showAddModal = ref(false);
const addInsertIndex = ref(0);

const openAddAt = (idx = 0) => {
  addInsertIndex.value = Math.max(0, Number(idx || 0));
  showAddModal.value = true;
};
const closeAdd = () => (showAddModal.value = false);

const showEditHeightModal = ref(false);
const editHeightSectionId = ref(null);

const editHeightInitial = ref(300);
const editBgInitial = ref("");
const editBgImageInitial = ref("");
const editOverlayColorInitial = ref("");
const editOverlayOpacityInitial = ref(0);
const editPaddingTopInitial = ref(0);
const editPaddingBottomInitial = ref(0);

// ✅ NOVÉ
const editVerticalAlignInitial = ref("center");

const openEditHeight = (sectionIdRaw) => {
  const id = String(sectionIdRaw ?? "").trim();
  if (!id) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];
  const sec = arr.find((x) => String(x?.id ?? "") === id);
  if (!sec) return;

  editHeightSectionId.value = id;
  editHeightInitial.value = Number(sec?.height ?? 300);

  editBgInitial.value = String(sec?.bg ?? "").trim();
  editBgImageInitial.value = String(sec?.bgImage ?? "").trim();

  editOverlayColorInitial.value = String(sec?.overlayColor ?? "").trim();
  editOverlayOpacityInitial.value = Number(sec?.overlayOpacity ?? 0);

  editPaddingTopInitial.value = Number(sec?.pt ?? 0);
  editPaddingBottomInitial.value = Number(sec?.pb ?? 0);

  // ✅ načti z DB
  editVerticalAlignInitial.value = normalizeVA(sec?.va ?? "center");

  showEditHeightModal.value = true;
};

const closeEditHeight = () => {
  showEditHeightModal.value = false;
  editHeightSectionId.value = null;

  editBgInitial.value = "";
  editBgImageInitial.value = "";
  editOverlayColorInitial.value = "";
  editOverlayOpacityInitial.value = 0;
  editPaddingTopInitial.value = 0;
  editPaddingBottomInitial.value = 0;

  editVerticalAlignInitial.value = "center";
};

// ===== COMPONENT MODAL =====
const showCompModal = ref(false);
const compSectionId = ref(null);
const compId = ref(null);
const compType = ref(null);
const compData = ref({});

const findComponent = (sectionId, componentId) => {
  const arr = Array.isArray(localContent.value) ? localContent.value : [];
  const sec = arr.find((s) => String(s?.id ?? "") === String(sectionId ?? ""));
  const cmp = (sec?.components ?? []).find(
    (c) => String(c?.id ?? "") === String(componentId ?? "")
  );
  return { sec, cmp };
};

const openComponentModal = (sectionIdRaw, componentIdRaw) => {
  const sectionId = String(sectionIdRaw ?? "").trim();
  const componentId = String(componentIdRaw ?? "").trim();
  if (!sectionId || !componentId) return;

  const { cmp } = findComponent(sectionId, componentId);
  if (!cmp) return;

  compSectionId.value = sectionId;
  compId.value = componentId;
  compType.value = cmp.type;
  compData.value = deepCopy(cmp.data ?? {});

  showCompModal.value = true;
};

const closeComponentModal = () => {
  showCompModal.value = false;
  compSectionId.value = null;
  compId.value = null;
  compType.value = null;
  compData.value = {};
};

const reorderSections = (sectionIdsRaw) => {
  const sectionIds = Array.isArray(sectionIdsRaw) ? sectionIdsRaw : [];
  if (sectionIds.length === 0) return;

  const arr = Array.isArray(localContent.value) ? localContent.value : [];
  const map = new Map(arr.map((s) => [String(s.id), s]));

  const ordered = sectionIds.map((id) => map.get(String(id))).filter(Boolean);

  const used = new Set(sectionIds.map((x) => String(x)));
  const leftovers = arr.filter((s) => !used.has(String(s.id)));

  const next = [...ordered, ...leftovers];

  localContent.value = next;
  save(next);
};

// ===== MESSAGE FROM IFRAME =====
const onMessage = (event) => {
  if (event.origin !== window.location.origin) return;

  const msg = event.data;
  if (!msg || typeof msg !== "object") return;

  // ⭐ scroll sync from iframe (persisted for F5)
  if (msg.type === "IFRAME_SCROLL") {
    saveScroll(msg.scrollY);
    return;
  }

  // ⭐ iframe says it is ready -> restore scroll
  if (msg.type === "IFRAME_READY") {
    loadSavedScroll();
    iframeRef.value?.contentWindow?.postMessage(
      { type: "RESTORE_SCROLL", scrollY: lastIframeScrollY.value },
      window.location.origin
    );
    return;
  }

  if (msg.type === "ADD_SECTION") {
    openAddAt(Number(msg.insertIndex ?? 0));
    return;
  }

  if (msg.type === "DELETE_SECTION") {
    deleteSectionById(msg.sectionId ?? msg.id ?? msg.targetId ?? msg.section_id);
    return;
  }

  if (msg.type === "EDIT_SECTION_HEIGHT") {
    openEditHeight(msg.sectionId ?? msg.id ?? msg.targetId ?? msg.section_id);
    return;
  }

  if (msg.type === "ADD_COMPONENT") {
    addComponentToSection(msg.sectionId, msg.componentType);
    return;
  }

  if (msg.type === "CHANGE_COMPONENT_TYPE") {
    changeComponentType(msg.sectionId, msg.componentId, msg.newType);
    return;
  }

  if (msg.type === "DELETE_COMPONENT") {
    deleteComponentFromSection(msg.sectionId, msg.componentId);
    return;
  }

  if (msg.type === "EDIT_COMPONENT") {
    openComponentModal(msg.sectionId, msg.componentId);
    return;
  }

  if (msg.type === "REORDER_COMPONENTS") {
    reorderComponentsInSection(msg.sectionId, msg.componentIds);
    return;
  }
  if (msg.type === "REORDER_SECTIONS") {
    reorderSections(msg.sectionIds);
    return;
  }
};

onMounted(() => {
  loadSavedScroll();
  window.addEventListener("message", onMessage);
});

onBeforeUnmount(() => window.removeEventListener("message", onMessage));
</script>

<template>
  <AdminLayout
    title="Obsah stránek"
    description="Klikni na + v náhledu a přidá se blok. Úprava komponent se otevře v modalu."
    :home="{ icon: 'fas fa-home', route: '/admin' }"
    :breadcrumbItems="[{ label: 'Obsah stránek', route: '/admin/content' }]"
  >
    <div class="grid grid-cols-12 gap-6">
      <!-- LEFT -->
      <aside class="col-span-12 bg-white rounded-xl p-4 border content-menu">
        <div class="font-semibold mb-3">Stránky</div>

        <div class="space-y-2">
          <button
            v-for="p in pages"
            :key="'p-' + p.id"
            type="button"
            class="w-full text-left px-3 py-2 rounded hover:bg-gray-50"
            :class="selectedUrl === p.url ? 'bg-gray-100 font-semibold' : ''"
            @click="selectUrl(p.url)"
          >
            {{ p.title }}
            <div class="text-xs text-gray-500">{{ p.url }}</div>
          </button>
        </div>

        <div class="font-semibold mt-6 mb-3">Podstránky</div>

        <div class="space-y-2">
          <button
            v-for="s in subpages"
            :key="'s-' + s.id"
            type="button"
            class="w-full text-left px-3 py-2 rounded hover:bg-gray-50"
            :class="selectedUrl === s.url ? 'bg-gray-100 font-semibold' : ''"
            @click="selectUrl(s.url)"
          >
            {{ s.title }}
            <div class="text-xs text-gray-500">{{ s.url }}</div>
          </button>
        </div>
      </aside>

      <!-- RIGHT -->
      <main class="col-span-12 bg-white rounded-xl p-4 border content-preview">
        <div class="flex items-center justify-between gap-3 mb-3">
          <div class="min-w-0">
            <div class="font-semibold truncate">Pracovní plocha:</div>
            <div class="text-xs text-gray-500">
              Přidávání/mazání probíhá v náhledu. Úprava komponent se otevře v modalu.
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button
              type="button"
              class="px-3 py-2 rounded border hover:bg-gray-50"
              @click="reloadIframe"
            >
              Reload
            </button>

            <a
              :href="selectedUrl"
              target="_blank"
              class="px-3 py-2 rounded bg-black text-white"
            >
              Otevřít
            </a>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border bg-gray-50" style="height: 94%">
          <iframe
            ref="iframeRef"
            :key="iframeKey"
            :src="iframeSrc"
            class="w-full h-full"
          />
        </div>

        <div v-if="isSaving" class="mt-2 text-xs text-gray-500">Ukládám…</div>
      </main>
    </div>

    <!-- ✅ MODALS -->
    <AddSectionModal
      :open="showAddModal"
      :insert-index="addInsertIndex"
      :mainDesign="mainDesign"
      :upload-file="uploadFile"
      :delete-file="deleteFileOnServer"
      @close="closeAdd"
      @confirm="(p) => addSectionAt(p)"
    />

    <EditSectionModal
      :open="showEditHeightModal"
      :section-id="editHeightSectionId"
      :initial-height="editHeightInitial"
      :initial-bg="editBgInitial"
      :initial-bg-image="editBgImageInitial"
      :initial-overlay-color="editOverlayColorInitial"
      :initial-overlay-opacity="editOverlayOpacityInitial"
      :initial-padding-top="editPaddingTopInitial"
      :initial-padding-bottom="editPaddingBottomInitial"
      :initial-vertical-align="editVerticalAlignInitial"
      :mainDesign="mainDesign"
      :upload-file="uploadFile"
      :delete-file="deleteFileOnServer"
      @close="closeEditHeight"
      @confirm="(payload) => updateSectionSettings(payload.sectionId, payload)"
    />

    <EditComponentModal
      :open="showCompModal"
      :type-label="typeLabel"
      :component-types="componentTypes"
      :section-id="compSectionId"
      :component-id="compId"
      :component-type="compType"
      :initial-data="compData"
      :mainDesign="mainDesign"
      :upload-file="uploadFile"
      :delete-file="deleteFileOnServer"
      @close="closeComponentModal"
      @save="
        ({ sectionId, componentId, data }) =>
          applyComponentData(sectionId, componentId, data)
      "
    />
  </AdminLayout>
</template>

<style>
.content-menu {
  grid-column: span 2 / span 3;
}

.content-preview {
  grid-column: span 10 / span 9;
}
</style>
