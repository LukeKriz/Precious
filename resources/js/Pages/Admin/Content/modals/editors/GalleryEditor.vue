<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  uploadFile: { type: Function, required: true },
  deleteFile: { type: Function, required: true },
  uploadBusy: { type: Boolean, default: false },
});
const emit = defineEmits(["update:modelValue", "update:uploadBusy"]);

const fileInput = ref(null);

const patch = (p) => emit("update:modelValue", { ...props.modelValue, ...p });

const previewCount = computed({
  get: () => Number(props.modelValue?.previewCount ?? 6),
  set: (v) => patch({ previewCount: Math.max(1, Number(v || 1)) }),
});

const images = computed({
  get: () => (Array.isArray(props.modelValue?.images) ? props.modelValue.images : []),
  set: (v) => patch({ images: Array.isArray(v) ? v : [] }),
});

const openPicker = () => {
  if (fileInput.value) fileInput.value.value = "";
  fileInput.value?.click();
};

const onPick = async (e) => {
  const files = Array.from(e?.target?.files ?? []);
  if (!files.length) return;

  emit("update:uploadBusy", true);
  try {
    const next = [...images.value];
    for (const f of files) {
      const { path } = await props.uploadFile(f);
      if (path) next.push(path);
    }
    images.value = next;
  } finally {
    emit("update:uploadBusy", false);
    if (e?.target) e.target.value = "";
  }
};

const removeImage = async (idx) => {
  const arr = [...images.value];
  const path = arr[idx];
  arr.splice(idx, 1);
  images.value = arr;
  if (path) await props.deleteFile(path);
};

/* =======================
   ✅ DRAG & DROP reorder
   ======================= */
const dragFrom = ref(null);
const dragOver = ref(null);

const onDragStart = (idx, ev) => {
  dragFrom.value = idx;
  dragOver.value = idx;
  try {
    ev.dataTransfer.effectAllowed = "move";
    ev.dataTransfer.setData("text/plain", String(idx));
  } catch (_) {}
};

const onDragEnter = (idx) => {
  dragOver.value = idx;
};

const onDragOver = (idx, ev) => {
  // nutné, aby drop fungoval
  ev.preventDefault();
  dragOver.value = idx;
};

const moveItem = (from, to) => {
  if (from === null || to === null) return;
  if (from === to) return;

  const arr = [...images.value];
  const [item] = arr.splice(from, 1);
  arr.splice(to, 0, item);
  images.value = arr;
};

const onDrop = (idx, ev) => {
  ev.preventDefault();
  const from =
    dragFrom.value !== null
      ? dragFrom.value
      : Number(ev.dataTransfer?.getData("text/plain"));
  const to = idx;

  if (Number.isFinite(from) && Number.isFinite(to)) {
    moveItem(from, to);
  }

  dragFrom.value = null;
  dragOver.value = null;
};

const onDragEnd = () => {
  dragFrom.value = null;
  dragOver.value = null;
};
</script>

<template>
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 md:col-span-6">
      <label class="text-sm font-medium block mb-2">Počet náhledů</label>
      <input
        class="w-full rounded border px-3 py-2"
        type="number"
        min="1"
        v-model.number="previewCount"
      />
    </div>

    <div class="col-span-12">
      <label class="text-sm font-medium block mb-2">Obrázky</label>

      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        multiple
        class="hidden"
        @change="onPick"
      />

      <div class="flex items-center gap-4 flex-wrap">
        <button
          type="button"
          @click="openPicker"
          class="inline-flex items-center justify-center rounded-lg bg-green-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-600 active:bg-green-700 disabled:opacity-60"
          :disabled="uploadBusy"
        >
          Vybrat soubory
        </button>

        <div v-if="uploadBusy" class="text-sm text-gray-500">Nahrávám…</div>
        <div v-if="images.length" class="text-xs text-gray-500">
          Tip: Pořadí změníš přetažením (drag & drop).
        </div>
      </div>

      <div v-if="images.length" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
        <div
          v-for="(img, idx) in images"
          :key="img + idx"
          class="border rounded-xl p-2 bg-gray-50 relative select-none"
          :class="dragOver === idx && dragFrom !== null ? 'ring-2 ring-blue-500' : ''"
          draggable="true"
          @dragstart="onDragStart(idx, $event)"
          @dragenter="onDragEnter(idx)"
          @dragover="onDragOver(idx, $event)"
          @drop="onDrop(idx, $event)"
          @dragend="onDragEnd"
        >
          <img :src="img" class="w-full h-28 object-cover rounded-lg" />

          <button
            type="button"
            class="mt-2 w-full inline-flex items-center justify-center rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700 disabled:opacity-60"
            @click="removeImage(idx)"
            :disabled="uploadBusy"
          >
            🗑 Smazat
          </button>
        </div>
      </div>

      <div v-else class="mt-3 text-sm text-gray-500">Zatím žádné obrázky.</div>
    </div>
  </div>
</template>
