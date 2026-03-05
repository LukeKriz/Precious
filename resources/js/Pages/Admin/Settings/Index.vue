<script setup>
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  settings: { type: Object, default: () => ({}) },
});

const deepCopy = (v) => JSON.parse(JSON.stringify(v ?? {}));
const draft = ref(deepCopy(props.settings));

watch(
  () => props.settings,
  (v) => (draft.value = deepCopy(v))
);

const isSaving = ref(false);
const uploadBusy = ref(false);

// cache-buster, ať to nebliká na každém renderu
const bust = ref(Date.now());
const bumpBust = () => (bust.value = Date.now());

const csrf = () =>
  document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

// ===== API =====
const uploadIcons = async (file) => {
  const fd = new FormData();
  fd.append("file", file);

  const res = await fetch("/admin/settings/upload-icons", {
    method: "POST",
    headers: { "X-CSRF-TOKEN": csrf() },
    body: fd,
  });

  if (!res.ok) {
    const t = await res.text();
    throw new Error(t || "Upload failed");
  }
  return await res.json(); // { favicon_ico, favicon_apple, favicon_pwa }
};

const uploadAsset = async (file) => {
  const fd = new FormData();
  fd.append("file", file);

  const res = await fetch("/admin/settings/upload-asset", {
    method: "POST",
    headers: { "X-CSRF-TOKEN": csrf() },
    body: fd,
  });

  if (!res.ok) {
    const t = await res.text();
    throw new Error(t || "Upload failed");
  }
  return await res.json(); // { path }
};

const deleteAsset = async (path) => {
  if (!path) return;
  await fetch("/admin/settings/delete-asset", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrf(),
    },
    body: JSON.stringify({ path }),
  });
};

// ===== helpers =====
const hasIcons = computed(
  () =>
    !!(draft.value.favicon_ico || draft.value.favicon_apple || draft.value.favicon_pwa)
);

const previewSrc = (u) => {
  const s = String(u ?? "").trim();
  if (!s) return "";
  const join = s.includes("?") ? "&" : "?";
  return `${s}${join}v=${bust.value}`;
};

// vezmeme nejlepší dostupný (nejdřív ico, pak apple, pak pwa – nebo opačně, je to jedno pro mini náhled)
const iconPreview = computed(() => {
  return (
    draft.value.favicon_ico || draft.value.favicon_apple || draft.value.favicon_pwa || ""
  );
});

// ===== UI: ICONS (one upload) =====
const iconInput = ref(null);

const openIconPicker = () => {
  if (uploadBusy.value) return;
  iconInput.value?.click?.();
};

const onPickIcons = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file) return;

  uploadBusy.value = true;
  try {
    const r = await uploadIcons(file);

    draft.value.favicon_ico = r.favicon_ico ?? null;
    draft.value.favicon_apple = r.favicon_apple ?? null;
    draft.value.favicon_pwa = r.favicon_pwa ?? null;

    bumpBust();
  } finally {
    uploadBusy.value = false;
    if (iconInput.value) iconInput.value.value = "";
  }
};

const removeIcons = async () => {
  uploadBusy.value = true;
  try {
    const ico = draft.value.favicon_ico;
    const apple = draft.value.favicon_apple;
    const pwa = draft.value.favicon_pwa;

    draft.value.favicon_ico = null;
    draft.value.favicon_apple = null;
    draft.value.favicon_pwa = null;

    bumpBust();
    await Promise.all([deleteAsset(ico), deleteAsset(apple), deleteAsset(pwa)]);
  } finally {
    uploadBusy.value = false;
  }
};

// ===== UI: OG IMAGE =====
const ogInput = ref(null);

const pickOgImage = () => {
  if (uploadBusy.value) return;
  ogInput.value?.click?.();
};

const onPickOg = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file) return;

  uploadBusy.value = true;
  try {
    const r = await uploadAsset(file);
    draft.value.og_image = r.path ?? null;
    bumpBust();
  } finally {
    uploadBusy.value = false;
    if (ogInput.value) ogInput.value.value = "";
  }
};

const removeOgImage = async () => {
  const old = draft.value.og_image;
  draft.value.og_image = null;
  bumpBust();
  if (old) await deleteAsset(old);
};

// ===== SAVE =====
const save = () => {
  isSaving.value = true;

  router.post(
    "/admin/settings/save",
    { ...draft.value },
    {
      preserveScroll: true,
      onFinish: () => (isSaving.value = false),
    }
  );
};
</script>

<template>
  <AdminLayout
    title="Nastavení a SEO"
    description="Globální nastavení webu (default SEO, OG, ikony, noindex, analytics)."
    :home="{ icon: 'fas fa-home', route: '/admin' }"
    :breadcrumbItems="[{ label: 'Nastavení a SEO', route: '/admin/settings' }]"
  >
    <div class="max-w-6xl w-full">
      <div class="grid grid-cols-12 gap-6">
        <!-- LEFT COLUMN -->
        <div class="col-span-12 lg:col-span-7 space-y-6">
          <!-- BASIC -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-4">Základ</div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium block mb-2">Název webu</label>
                <input
                  class="w-full rounded border px-3 py-2"
                  placeholder="Např. Zavlažujeme to"
                  v-model="draft.site_name"
                />
              </div>

              <div>
                <label class="text-sm font-medium block mb-2">Base URL (canonical)</label>
                <input
                  class="w-full rounded border px-3 py-2"
                  placeholder="https://example.cz"
                  v-model="draft.base_url"
                />
              </div>
            </div>
          </div>

          <!-- DEFAULT SEO -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-4">Default SEO</div>

            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium block mb-2">Default meta title</label>
                <input
                  class="w-full rounded border px-3 py-2"
                  placeholder="Výchozí title"
                  v-model="draft.default_title"
                />
                <div class="text-xs text-gray-500 mt-1">
                  Použije se, když stránka nemá vlastní title.
                </div>
              </div>

              <div>
                <label class="text-sm font-medium block mb-2">
                  Default meta description
                </label>
                <textarea
                  class="w-full rounded border px-3 py-2"
                  rows="3"
                  placeholder="Výchozí description"
                  v-model="draft.default_description"
                />
              </div>
            </div>
          </div>

          <!-- OPEN GRAPH -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-4">Open Graph (default)</div>

            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium block mb-2">OG title</label>
                <input
                  class="w-full rounded border px-3 py-2"
                  placeholder="OG title"
                  v-model="draft.og_title"
                />
              </div>

              <div>
                <label class="text-sm font-medium block mb-2">OG description</label>
                <textarea
                  class="w-full rounded border px-3 py-2"
                  rows="3"
                  placeholder="OG description"
                  v-model="draft.og_description"
                />
              </div>

              <div>
                <label class="text-sm font-medium block mb-2">OG image</label>

                <div class="flex items-center gap-3 flex-wrap">
                  <button
                    type="button"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                    :disabled="uploadBusy"
                    @click="pickOgImage"
                  >
                    Nahrát OG image
                  </button>

                  <button
                    v-if="draft.og_image"
                    type="button"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                    :disabled="uploadBusy"
                    @click="removeOgImage"
                  >
                    Smazat
                  </button>

                  <div v-if="uploadBusy" class="text-sm text-gray-500">Pracuji…</div>

                  <input
                    ref="ogInput"
                    type="file"
                    accept="image/*,.svg"
                    class="hidden"
                    @change="onPickOg"
                  />
                </div>

                <div
                  class="mt-4 border rounded-xl bg-gray-50 overflow-hidden"
                  :class="draft.og_image ? '' : 'p-4'"
                >
                  <div
                    v-if="draft.og_image"
                    class="w-full h-44 flex items-center justify-center"
                  >
                    <img
                      :src="previewSrc(draft.og_image)"
                      alt=""
                      class="w-full h-full object-contain"
                    />
                  </div>
                  <div v-else class="text-sm text-gray-400 italic">
                    (Zatím žádný OG image)
                  </div>
                </div>

                <div class="mt-2 text-xs text-gray-500">
                  Ukládá se jako cesta v DB (např. /storage/img/settings/...).
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-span-12 lg:col-span-5 space-y-6">
          <!-- ICONS (ONE UPLOAD) – SMALL PREVIEW BELOW BUTTONS -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-1">Ikona webu</div>
            <div class="text-xs text-gray-500 mb-4">
              Nahraj 1 obrázek (PNG/JPG/WebP). Systém z něj vygeneruje: favicon.ico, Apple
              touch, PWA 512.
            </div>

            <div class="flex items-center gap-3 flex-wrap">
              <button
                type="button"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                :disabled="uploadBusy"
                @click="openIconPicker"
              >
                Nahrát ikonu webu
              </button>

              <button
                v-if="hasIcons"
                type="button"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                :disabled="uploadBusy"
                @click="removeIcons"
              >
                Smazat ikony
              </button>

              <div v-if="uploadBusy" class="text-sm text-gray-500">Nahrávám…</div>

              <input
                ref="iconInput"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="hidden"
                @change="onPickIcons"
              />
            </div>

            <!-- ✅ small preview under buttons -->
            <div class="mt-4 flex items-center gap-3">
              <div
                class="w-[50px] h-[50px] rounded-xl border bg-gray-50 flex items-center justify-center overflow-hidden"
              >
                <img
                  v-if="iconPreview"
                  :src="previewSrc(iconPreview)"
                  alt="Ikona"
                  class="w-[50px] h-[50px] object-contain"
                />
                <div v-else class="text-[11px] text-gray-400 italic">—</div>
              </div>

              <div class="text-xs text-gray-500 leading-5">
                <div v-if="hasIcons">
                  Vygenerováno:
                  <span class="text-gray-700 font-medium">favicon</span>,
                  <span class="text-gray-700 font-medium">apple</span>,
                  <span class="text-gray-700 font-medium">pwa</span>
                </div>
                <div v-else>Zatím nic nahráno.</div>
              </div>
            </div>
          </div>

          <!-- ROBOTS -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-4">Indexace (globálně)</div>

            <div class="flex items-center justify-between gap-4">
              <div>
                <div class="text-sm font-medium">Zakázat indexaci (noindex)</div>
                <div class="text-xs text-gray-500">Užitečné pro staging.</div>
              </div>

              <button
                type="button"
                class="relative inline-flex h-7 w-12 items-center rounded-full transition"
                :class="draft.site_noindex ? 'bg-emerald-600' : 'bg-gray-300'"
                @click="draft.site_noindex = !draft.site_noindex"
              >
                <span
                  class="inline-block h-5 w-5 transform rounded-full bg-white transition"
                  :class="draft.site_noindex ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
            </div>
          </div>

          <!-- ANALYTICS -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-4">Analytics</div>

            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium block mb-2">GA4 ID</label>
                <input
                  class="w-full rounded border px-3 py-2"
                  placeholder="G-XXXXXXX"
                  v-model="draft.ga4_id"
                />
              </div>

              <div>
                <label class="text-sm font-medium block mb-2">GTM ID</label>
                <input
                  class="w-full rounded border px-3 py-2"
                  placeholder="GTM-XXXXXXX"
                  v-model="draft.gtm_id"
                />
              </div>
            </div>
          </div>

          <!-- CUSTOM HEAD -->
          <div class="rounded-xl border bg-white p-5">
            <div class="text-lg font-semibold mb-4">Vlastní tagy do &lt;head&gt;</div>
            <textarea
              class="w-full rounded border px-3 py-2 font-mono text-xs"
              rows="6"
              placeholder="Např. ověřovací meta tagy..."
              v-model="draft.meta_head_custom"
            />
            <div class="text-xs text-gray-500 mt-2">
              V dalším kroku to propsáme do layoutu webu.
            </div>
          </div>

          <!-- SAVE -->
          <div class="flex items-center justify-end gap-3">
            <div v-if="isSaving" class="text-sm text-gray-500 mr-auto">Ukládám…</div>
            <button
              class="px-6 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold disabled:opacity-60"
              @click="save"
              :disabled="uploadBusy"
            >
              Uložit
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
