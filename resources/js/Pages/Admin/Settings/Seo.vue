<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  pages: { type: Array, default: () => [] },
  subpages: { type: Array, default: () => [] },
});

const q = ref("");

const norm = (v) =>
  String(v ?? "")
    .toLowerCase()
    .trim();

const pageDraft = ref({});
const subDraft = ref({});

const initDrafts = () => {
  const p = {};
  for (const it of props.pages) {
    p[it.id] = {
      meta_title: it.meta_title ?? null,
      meta_description: it.meta_description ?? null,
    };
  }
  pageDraft.value = p;

  const s = {};
  for (const it of props.subpages) {
    s[it.id] = {
      meta_title: it.meta_title ?? null,
      meta_description: it.meta_description ?? null,
    };
  }
  subDraft.value = s;
};

watch(
  () => [props.pages, props.subpages],
  () => initDrafts(),
  { immediate: true }
);

const pagesFiltered = computed(() => {
  const query = norm(q.value);
  if (!query) return props.pages;

  const subByPage = new Map();
  for (const sp of props.subpages) {
    const hay = `${sp.title ?? ""} ${sp.url ?? ""} ${sp.meta_title ?? ""} ${
      sp.meta_description ?? ""
    }`.toLowerCase();
    if (hay.includes(query)) subByPage.set(sp.page_id, true);
  }

  return props.pages.filter((p) => {
    const hay = `${p.title ?? ""} ${p.url ?? ""} ${p.meta_title ?? ""} ${
      p.meta_description ?? ""
    }`.toLowerCase();
    return hay.includes(query) || subByPage.has(p.id);
  });
});

const subpagesByPage = computed(() => {
  const map = new Map();
  for (const sp of props.subpages) {
    if (!map.has(sp.page_id)) map.set(sp.page_id, []);
    map.get(sp.page_id).push(sp);
  }
  for (const [k, arr] of map.entries()) {
    arr.sort((a, b) => String(a.title ?? "").localeCompare(String(b.title ?? "")));
    map.set(k, arr);
  }
  return map;
});

const selectedPageId = ref(null);
const selectedSubpageId = ref(null);

watch(
  pagesFiltered,
  (arr) => {
    if (!arr.length) {
      selectedPageId.value = null;
      selectedSubpageId.value = null;
      return;
    }
    if (!selectedPageId.value || !arr.some((x) => x.id === selectedPageId.value)) {
      selectedPageId.value = arr[0].id;
    }
  },
  { immediate: true }
);

watch(
  selectedPageId,
  () => {
    selectedSubpageId.value = null;
  },
  { immediate: true }
);

const selectedPage = computed(
  () => props.pages.find((p) => p.id === selectedPageId.value) ?? null
);

const selectedSubpages = computed(() => {
  const pid = selectedPageId.value;
  if (!pid) return [];
  let subs = subpagesByPage.value.get(pid) || [];
  const query = norm(q.value);
  if (!query) return subs;

  return subs.filter((sp) => {
    const hay = `${sp.title ?? ""} ${sp.url ?? ""} ${sp.meta_title ?? ""} ${
      sp.meta_description ?? ""
    }`.toLowerCase();
    return hay.includes(query);
  });
});

const editingMode = computed(() => (selectedSubpageId.value ? "subpage" : "page"));

const editingEntity = computed(() => {
  if (editingMode.value === "subpage") {
    const sp = props.subpages.find((x) => x.id === selectedSubpageId.value) ?? null;
    return sp ? { type: "subpage", data: sp } : null;
  }
  const p = selectedPage.value;
  return p ? { type: "page", data: p } : null;
});

const draftMeta = computed(() => {
  const ent = editingEntity.value;
  if (!ent) return null;
  if (ent.type === "page") return pageDraft.value?.[ent.data.id] ?? null;
  return subDraft.value?.[ent.data.id] ?? null;
});

const setMetaTitleFromTitle = () => {
  const ent = editingEntity.value;
  const d = draftMeta.value;
  if (!ent || !d) return;
  d.meta_title = ent.data.title ?? null;
};

const clearMeta = () => {
  const d = draftMeta.value;
  if (!d) return;
  d.meta_title = null;
  d.meta_description = null;
};

const isSaving = ref(false);

const save = () => {
  isSaving.value = true;

  router.post(
    "/admin/settings/seo/save",
    { pages: pageDraft.value, subpages: subDraft.value },
    {
      preserveScroll: true,
      onFinish: () => (isSaving.value = false),
    }
  );
};
</script>

<template>
  <AdminLayout
    title="Pokročilé SEO"
    description="Nastavení meta title a meta description pro stránky a podstránky."
    :home="{ icon: 'fas fa-home', route: '/admin' }"
    :breadcrumbItems="[
      { label: 'Základní nastavení', route: '/admin/settings' },
      { label: 'Pokročilé SEO', route: '/admin/settings/seo' },
    ]"
  >
    <div class="max-w-6xl w-full space-y-4">
      <!-- Search ONLY -->
      <div class="flex items-center gap-3">
        <input
          v-model="q"
          class="w-full rounded border px-3 py-2"
          placeholder="Hledat (title, url, meta title, meta description)…"
        />
      </div>

      <!-- Pages tabs + editor -->
      <div class="rounded-xl border bg-white p-4">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="p in pagesFiltered"
            :key="p.id"
            type="button"
            class="px-3 py-2 rounded-lg border text-sm font-semibold transition"
            :class="
              selectedPageId === p.id
                ? 'bg-gray-800 text-white border-gray-800'
                : 'bg-white hover:bg-gray-50 text-gray-700'
            "
            @click="selectedPageId = p.id"
          >
            <span>{{ p.title }}</span>
            <span class="text-xs opacity-70 ml-2">{{ p.url }}</span>
          </button>

          <div v-if="!pagesFiltered.length" class="text-sm text-gray-500">
            Nic nenalezeno.
          </div>
        </div>

        <div class="mt-4 grid grid-cols-12 gap-4">
          <!-- LEFT -->
          <div class="col-span-12 lg:col-span-4">
            <div class="text-sm font-semibold mb-2">Výběr</div>

            <div class="space-y-2">
              <button
                type="button"
                class="w-full text-left px-3 py-2 rounded-lg border text-sm font-semibold transition"
                :class="
                  !selectedSubpageId
                    ? 'bg-gray-800 text-white border-gray-800'
                    : 'bg-white hover:bg-gray-50 text-gray-700'
                "
                @click="selectedSubpageId = null"
              >
                Stránka: <span class="font-bold">{{ selectedPage?.title }}</span>
                <span class="text-xs opacity-70 ml-2">{{ selectedPage?.url }}</span>
              </button>

              <div class="text-xs text-gray-500 mt-2">Podstránky</div>

              <button
                v-for="sp in selectedSubpages"
                :key="sp.id"
                type="button"
                class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold transition"
                :class="
                  selectedSubpageId === sp.id
                    ? 'bg-gray-800 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-100'
                "
                @click="selectedSubpageId = sp.id"
              >
                <div class="flex items-center justify-between">
                  <span class="truncate">{{ sp.title }}</span>
                  <span class="text-xs opacity-70 ml-2">{{ sp.url }}</span>
                </div>
              </button>

              <div v-if="!selectedSubpages.length" class="text-sm text-gray-400 italic">
                (Žádné podstránky)
              </div>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="col-span-12 lg:col-span-8">
            <div class="rounded-xl border bg-gray-50 p-4">
              <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                  <div class="text-sm font-semibold">
                    <span v-if="editingMode === 'page'">SEO – stránka</span>
                    <span v-else>SEO – podstránka</span>
                  </div>
                  <div class="text-xs text-gray-600">
                    <span class="font-semibold">{{ editingEntity?.data?.title }}</span>
                    <span class="opacity-70 ml-2">{{ editingEntity?.data?.url }}</span>
                    <span class="opacity-60 ml-2">ID: {{ editingEntity?.data?.id }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-100 text-sm font-semibold"
                    @click="setMetaTitleFromTitle"
                    :disabled="!editingEntity || !draftMeta"
                  >
                    Použít title stránky
                  </button>
                  <button
                    type="button"
                    class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-100 text-sm font-semibold text-red-600"
                    @click="clearMeta"
                    :disabled="!draftMeta"
                  >
                    Vyčistit
                  </button>
                </div>
              </div>

              <div v-if="draftMeta" class="mt-4 space-y-4">
                <div>
                  <label class="text-sm font-semibold block mb-2">
                    Meta title ({{ String(draftMeta.meta_title ?? "").length }} znaků)
                  </label>
                  <input
                    class="w-full rounded border px-3 py-2"
                    placeholder="Např. Zavlažování zahrady | Firma"
                    v-model="draftMeta.meta_title"
                  />
                </div>

                <div>
                  <label class="text-sm font-semibold block mb-2">
                    Meta description ({{
                      String(draftMeta.meta_description ?? "").length
                    }}
                    znaků)
                  </label>
                  <textarea
                    class="w-full rounded border px-3 py-2"
                    rows="4"
                    placeholder="Krátký popis stránky do vyhledávačů…"
                    v-model="draftMeta.meta_description"
                  />
                </div>

                <!-- ✅ SAVE BUTTON MOVED HERE (UNDER FORM) -->
                <div class="pt-2 flex items-center gap-3">
                  <button
                    type="button"
                    class="px-5 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold disabled:opacity-60"
                    :disabled="isSaving"
                    @click="save"
                  >
                    Uložit
                  </button>
                  <div v-if="isSaving" class="text-sm text-gray-500">Ukládám…</div>
                </div>
              </div>

              <div v-else class="text-sm text-gray-500 italic mt-4">
                Vyber stránku nebo podstránku.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
