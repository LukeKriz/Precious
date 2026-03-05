<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  uploadFile: { type: Function, required: true },
  deleteFile: { type: Function, required: true },
  uploadBusy: { type: Boolean, default: false },
  mainDesign: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:modelValue", "update:uploadBusy"]);

const patch = (p) => emit("update:modelValue", { ...props.modelValue, ...p });

const genCatId = () => `cat_${Date.now()}_${Math.random().toString(16).slice(2)}`;
const genGalId = () => `gal_${Date.now()}_${Math.random().toString(16).slice(2)}`;

const ensureGallery = (g) => ({
  id: g?.id ?? genGalId(),
  title: (g?.title ?? "").toString(),
  cover: g?.cover ?? null,
  images: Array.isArray(g?.images) ? g.images : [],
});

const ensureCategory = (c) => ({
  id: c?.id ?? genCatId(),
  label: (c?.label ?? "").toString(),
  galleries: (Array.isArray(c?.galleries) ? c.galleries : []).map(ensureGallery),
});

const categories = computed({
  get: () =>
    Array.isArray(props.modelValue?.categories)
      ? props.modelValue.categories.map(ensureCategory)
      : [],
  set: (v) => patch({ categories: (v || []).map(ensureCategory) }),
});

// --- selection ---
const selectedCategoryId = ref(null);
const selectedGalleryId = ref(null);

watch(
  () => categories.value.map((c) => String(c.id)).join("|"),
  () => {
    const cats = categories.value;
    if (!cats.length) {
      selectedCategoryId.value = null;
      selectedGalleryId.value = null;
      return;
    }

    // ensure category selected
    if (
      !selectedCategoryId.value ||
      !cats.find((c) => String(c.id) === String(selectedCategoryId.value))
    ) {
      selectedCategoryId.value = cats[0].id;
    }

    // ensure gallery selected (in selected category)
    const cat = cats.find((c) => String(c.id) === String(selectedCategoryId.value));
    const gals = Array.isArray(cat?.galleries) ? cat.galleries : [];

    if (!gals.length) {
      selectedGalleryId.value = null;
      return;
    }

    if (
      !selectedGalleryId.value ||
      !gals.find((g) => String(g.id) === String(selectedGalleryId.value))
    ) {
      selectedGalleryId.value = gals[0].id;
    }
  },
  { immediate: true }
);

const selectedCategory = computed(() => {
  return (
    categories.value.find((c) => String(c.id) === String(selectedCategoryId.value)) ||
    null
  );
});

const selectedGallery = computed(() => {
  const cat = selectedCategory.value;
  if (!cat) return null;
  return (
    (cat.galleries || []).find((g) => String(g.id) === String(selectedGalleryId.value)) ||
    null
  );
});

// --- actions: categories ---
const addCategory = () => {
  const id = genCatId();
  categories.value = [
    ...categories.value,
    ensureCategory({ id, label: "Nová kategorie", galleries: [] }),
  ];
  selectedCategoryId.value = id;
  selectedGalleryId.value = null;
};

const removeCategory = async (catId) => {
  const cat = categories.value.find((c) => String(c.id) === String(catId));
  if (!cat) return;

  // delete all files in category (covers + images)
  emit("update:uploadBusy", true);
  try {
    for (const g of cat.galleries || []) {
      if (g?.cover) await props.deleteFile(g.cover);
      for (const img of g?.images || []) {
        await props.deleteFile(img);
      }
    }
  } finally {
    emit("update:uploadBusy", false);
  }

  categories.value = categories.value.filter((c) => String(c.id) !== String(catId));
  if (String(selectedCategoryId.value) === String(catId)) {
    selectedCategoryId.value = categories.value[0]?.id ?? null;
    selectedGalleryId.value = null;
  }
};

const patchCategory = (catId, p) => {
  categories.value = categories.value.map((c) =>
    String(c.id) === String(catId) ? { ...c, ...p } : c
  );
};

// --- actions: galleries ---
const addGalleryToSelected = () => {
  const cat = selectedCategory.value;
  if (!cat) return;

  const id = genGalId();
  const next = categories.value.map((c) => {
    if (String(c.id) !== String(cat.id)) return c;
    return {
      ...c,
      galleries: [...(c.galleries || []), ensureGallery({ id, title: "Nová galerie" })],
    };
  });

  categories.value = next;
  selectedGalleryId.value = id;
};

const removeGallery = async (galId) => {
  const cat = selectedCategory.value;
  if (!cat) return;

  const g = (cat.galleries || []).find((x) => String(x.id) === String(galId));
  if (!g) return;

  emit("update:uploadBusy", true);
  try {
    if (g.cover) await props.deleteFile(g.cover);
    for (const img of g.images || []) await props.deleteFile(img);
  } finally {
    emit("update:uploadBusy", false);
  }

  categories.value = categories.value.map((c) => {
    if (String(c.id) !== String(cat.id)) return c;
    return {
      ...c,
      galleries: (c.galleries || []).filter((x) => String(x.id) !== String(galId)),
    };
  });

  if (String(selectedGalleryId.value) === String(galId)) {
    const cat2 = categories.value.find((c) => String(c.id) === String(cat.id));
    selectedGalleryId.value = cat2?.galleries?.[0]?.id ?? null;
  }
};

const patchGallery = (galId, p) => {
  const cat = selectedCategory.value;
  if (!cat) return;

  categories.value = categories.value.map((c) => {
    if (String(c.id) !== String(cat.id)) return c;
    return {
      ...c,
      galleries: (c.galleries || []).map((g) =>
        String(g.id) === String(galId) ? { ...g, ...p } : g
      ),
    };
  });
};

// --- file pickers ---
const coverInput = ref(null);
const imagesInput = ref(null);

const pickCover = () => {
  if (!selectedGallery.value) return;
  if (coverInput.value) coverInput.value.value = "";
  coverInput.value?.click();
};

const onPickCover = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file || !selectedGallery.value) return;

  emit("update:uploadBusy", true);
  try {
    const res = await props.uploadFile(file);
    const path = (res?.url || res?.path || "").toString() || null;
    if (!path) return;

    const old = selectedGallery.value.cover || null;
    patchGallery(selectedGallery.value.id, { cover: path });

    if (old && old !== path) await props.deleteFile(old);
  } finally {
    emit("update:uploadBusy", false);
    if (e?.target) e.target.value = "";
  }
};

const removeCover = async () => {
  const g = selectedGallery.value;
  if (!g?.cover) return;

  const old = g.cover;
  patchGallery(g.id, { cover: null });

  emit("update:uploadBusy", true);
  try {
    await props.deleteFile(old);
  } finally {
    emit("update:uploadBusy", false);
  }
};

const pickImages = () => {
  if (!selectedGallery.value) return;
  if (imagesInput.value) imagesInput.value.value = "";
  imagesInput.value?.click();
};

const onPickImages = async (e) => {
  const files = Array.from(e?.target?.files || []);
  if (!files.length || !selectedGallery.value) return;

  emit("update:uploadBusy", true);
  try {
    const uploaded = [];
    for (const f of files) {
      const res = await props.uploadFile(f);
      const path = (res?.url || res?.path || "").toString() || null;
      if (path) uploaded.push(path);
    }
    if (!uploaded.length) return;

    const nextImages = [...(selectedGallery.value.images || []), ...uploaded];
    patchGallery(selectedGallery.value.id, { images: nextImages });
  } finally {
    emit("update:uploadBusy", false);
    if (e?.target) e.target.value = "";
  }
};

const removeImage = async (imgPath) => {
  const g = selectedGallery.value;
  if (!g) return;

  patchGallery(g.id, { images: (g.images || []).filter((x) => x !== imgPath) });

  emit("update:uploadBusy", true);
  try {
    await props.deleteFile(imgPath);
  } finally {
    emit("update:uploadBusy", false);
  }
};

// --- DND reorder (categories + galleries + photos) ---
const drag = ref({ type: null, fromId: null });

const onDragStart = (type, id, e) => {
  drag.value = { type, fromId: String(id) };
  try {
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", String(id));
  } catch {}
};

const onDragOver = (e) => e.preventDefault();

const onDropCategory = (targetCatId, e) => {
  e.preventDefault();
  if (drag.value.type !== "cat") return;

  const from = drag.value.fromId;
  const to = String(targetCatId);
  if (!from || !to || from === to) return;

  const arr = [...categories.value];
  const fromIdx = arr.findIndex((c) => String(c.id) === from);
  const toIdx = arr.findIndex((c) => String(c.id) === to);
  if (fromIdx < 0 || toIdx < 0) return;

  const [moved] = arr.splice(fromIdx, 1);
  arr.splice(toIdx, 0, moved);
  categories.value = arr;
  drag.value = { type: null, fromId: null };
};

const onDropGallery = (targetGalId, e) => {
  e.preventDefault();
  if (drag.value.type !== "gal") return;

  const cat = selectedCategory.value;
  if (!cat) return;

  const from = drag.value.fromId;
  const to = String(targetGalId);
  if (!from || !to || from === to) return;

  const arr = [...(cat.galleries || [])];
  const fromIdx = arr.findIndex((g) => String(g.id) === from);
  const toIdx = arr.findIndex((g) => String(g.id) === to);
  if (fromIdx < 0 || toIdx < 0) return;

  const [moved] = arr.splice(fromIdx, 1);
  arr.splice(toIdx, 0, moved);

  categories.value = categories.value.map((c) =>
    String(c.id) === String(cat.id) ? { ...c, galleries: arr } : c
  );

  drag.value = { type: null, fromId: null };
};

const onDropImage = (targetIndex, e) => {
  e.preventDefault();
  if (drag.value.type !== "img") return;

  const g = selectedGallery.value;
  if (!g) return;

  const fromIndex = Number(drag.value.fromId);
  const toIndex = Number(targetIndex);
  if (Number.isNaN(fromIndex) || Number.isNaN(toIndex) || fromIndex === toIndex) return;

  const arr = [...(g.images || [])];
  const [moved] = arr.splice(fromIndex, 1);
  arr.splice(toIndex, 0, moved);
  patchGallery(g.id, { images: arr });

  drag.value = { type: null, fromId: null };
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <!-- LEFT: CATEGORIES -->
    <div class="col-span-12 lg:col-span-4">
      <div class="border rounded-xl p-4 bg-white max-h-[75vh] overflow-auto">
        <div class="flex items-center justify-between gap-3">
          <div>
            <div class="font-semibold">Kategorie (menu)</div>
            <div class="text-xs text-gray-500 mt-1">
              Pořadí změníš přetažením (drag &amp; drop).
            </div>
          </div>

          <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
            @click="addCategory"
          >
            + Přidat
          </button>
        </div>

        <div class="mt-4 space-y-3">
          <button
            v-for="c in categories"
            :key="c.id"
            type="button"
            class="w-full text-left border rounded-xl p-4 hover:bg-gray-50 transition"
            :class="
              String(c.id) === String(selectedCategoryId) ? 'ring-2 ring-blue-500' : ''
            "
            @click="
              selectedCategoryId = c.id;
              selectedGalleryId = null;
            "
            draggable="true"
            @dragstart="onDragStart('cat', c.id, $event)"
            @dragover="onDragOver"
            @drop="onDropCategory(c.id, $event)"
            title="Pořadí kategorií změníš přetažením"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ c.label || "Kategorie" }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  Galerií: {{ (c.galleries || []).length }}
                </div>
              </div>

              <button
                type="button"
                class="shrink-0 rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600"
                :disabled="uploadBusy"
                @click.stop.prevent="removeCategory(c.id)"
              >
                Smazat
              </button>
            </div>
          </button>

          <div v-if="!categories.length" class="text-sm text-gray-500 italic">
            Zatím žádné kategorie. Přidej první přes <b>+ Přidat</b>.
          </div>
        </div>
      </div>
    </div>

    <!-- MIDDLE: CATEGORY EDIT + GALLERIES LIST -->
    <div class="col-span-12 lg:col-span-4">
      <div class="border rounded-xl p-4 bg-white max-h-[75vh] overflow-auto">
        <div class="flex items-center justify-between gap-3">
          <div class="font-semibold">Vybraná kategorie</div>

          <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:opacity-60"
            :disabled="!selectedCategory || uploadBusy"
            @click="addGalleryToSelected"
          >
            + Přidat galerii
          </button>
        </div>

        <div v-if="selectedCategory" class="mt-4 space-y-4">
          <div class="rounded-xl border bg-gray-50 p-4">
            <label class="text-sm font-medium block mb-2">Název v menu</label>
            <input
              class="w-full rounded border px-3 py-2 text-sm"
              type="text"
              :value="selectedCategory.label"
              @input="patchCategory(selectedCategory.id, { label: $event.target.value })"
            />
          </div>

          <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between">
              <div class="font-semibold">Galerie v kategorii</div>
              <div class="text-xs text-gray-500">
                Klik = edit • drag&amp;drop = pořadí
              </div>
            </div>

            <div class="mt-3 space-y-3">
              <button
                v-for="g in selectedCategory.galleries || []"
                :key="g.id"
                type="button"
                class="w-full text-left border rounded-xl p-3 hover:bg-gray-50 transition"
                :class="
                  String(g.id) === String(selectedGalleryId) ? 'ring-2 ring-blue-500' : ''
                "
                @click="selectedGalleryId = g.id"
                draggable="true"
                @dragstart="onDragStart('gal', g.id, $event)"
                @dragover="onDragOver"
                @drop="onDropGallery(g.id, $event)"
                title="Pořadí galerií změníš přetažením"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-14 h-14 rounded-lg border bg-gray-50 overflow-hidden flex items-center justify-center shrink-0"
                  >
                    <img
                      v-if="g.cover"
                      :src="g.cover"
                      class="w-full h-full object-cover"
                    />
                    <span v-else class="text-xs text-gray-400">Bez</span>
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="font-semibold truncate">{{ g.title || "Bez názvu" }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ (g.images || []).length }} fotek
                    </div>
                  </div>

                  <button
                    type="button"
                    class="shrink-0 rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600"
                    :disabled="uploadBusy"
                    @click.stop.prevent="removeGallery(g.id)"
                  >
                    Smazat
                  </button>
                </div>
              </button>

              <div
                v-if="!(selectedCategory.galleries || []).length"
                class="text-sm text-gray-500 italic"
              >
                Zatím žádné galerie v této kategorii.
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-sm text-gray-500 italic mt-3">Vyber kategorii vlevo.</div>
      </div>
    </div>

    <!-- RIGHT: GALLERY EDIT -->
    <div class="col-span-12 lg:col-span-4">
      <div class="border rounded-xl p-4 bg-white max-h-[75vh] overflow-auto">
        <div class="font-semibold">Editace galerie</div>

        <input
          ref="coverInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="onPickCover"
        />
        <input
          ref="imagesInput"
          type="file"
          accept="image/*"
          multiple
          class="hidden"
          @change="onPickImages"
        />

        <div v-if="selectedGallery" class="mt-4 space-y-5">
          <div class="rounded-xl border bg-gray-50 p-4">
            <label class="text-sm font-medium block mb-2">Název galerie</label>
            <input
              class="w-full rounded border px-3 py-2 text-sm"
              type="text"
              :value="selectedGallery.title"
              @input="patchGallery(selectedGallery.id, { title: $event.target.value })"
            />
          </div>

          <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between gap-3">
              <div class="font-semibold">Cover (náhled dlaždice)</div>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700 disabled:opacity-60"
                  :disabled="uploadBusy"
                  @click="pickCover"
                >
                  Vybrat cover
                </button>
                <button
                  v-if="selectedGallery.cover"
                  type="button"
                  class="rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600 disabled:opacity-60"
                  :disabled="uploadBusy"
                  @click="removeCover"
                >
                  Smazat cover
                </button>
              </div>
            </div>

            <div class="mt-3">
              <div
                class="w-28 h-28 rounded-xl border bg-white overflow-hidden flex items-center justify-center"
              >
                <img
                  v-if="selectedGallery.cover"
                  :src="selectedGallery.cover"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-xs text-gray-400">Bez</span>
              </div>
            </div>
          </div>

          <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between gap-3">
              <div class="font-semibold">Fotky v galerii</div>

              <button
                type="button"
                class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
                :disabled="uploadBusy"
                @click="pickImages"
              >
                + Přidat fotky
              </button>
            </div>

            <div
              v-if="(selectedGallery.images || []).length"
              class="mt-4 grid grid-cols-3 gap-3"
            >
              <div
                v-for="(img, idx) in selectedGallery.images || []"
                :key="img + idx"
                class="relative rounded-xl overflow-hidden border bg-gray-50"
                draggable="true"
                @dragstart="onDragStart('img', idx, $event)"
                @dragover="onDragOver"
                @drop="onDropImage(idx, $event)"
                title="Pořadí fotek změníš přetažením"
              >
                <img :src="img" class="w-full h-24 object-cover block" />
                <button
                  type="button"
                  class="absolute top-2 right-2 rounded-lg bg-red-500 text-white text-xs px-2 py-1 hover:bg-red-600 disabled:opacity-60"
                  :disabled="uploadBusy"
                  @click.stop.prevent="removeImage(img)"
                >
                  ×
                </button>
              </div>
            </div>

            <div v-else class="mt-3 text-sm text-gray-500 italic">Zatím žádné fotky.</div>

            <div class="mt-3 text-xs text-gray-500">
              Tip: Pořadí fotek změníš přetažením.
            </div>
          </div>

          <div v-if="uploadBusy" class="text-sm text-gray-500">
            Nahrávám / mažu soubory…
          </div>
        </div>

        <div v-else class="mt-4 text-sm text-gray-500 italic">
          Vyber galerii uprostřed.
        </div>
      </div>
    </div>
  </div>
</template>
