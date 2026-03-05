<template>
  <Dialog
    v-model:visible="localVisible"
    modal
    :style="{ width: '560px', maxWidth: '95vw' }"
  >
    <template #header>
      <div class="font-bold">Miniatura</div>
    </template>

    <div v-if="bannerId" class="space-y-5">
      <!-- PREVIEW -->
      <div class="flex justify-center">
        <div class="w-full rounded-2xl border border-gray-200 bg-gray-50 p-4">
          <div v-if="previewToShow" class="flex items-center justify-center">
            <img
              :src="previewToShow"
              alt="Náhled miniatury"
              class="max-h-52 max-w-full object-contain rounded-xl shadow-sm bg-white"
              draggable="false"
            />
          </div>

          <div v-else class="text-sm text-gray-400 text-center py-10">
            Miniatura zatím není nastavena
          </div>

          <div v-if="removePlanned" class="text-xs text-red-600 text-center mt-3">
            Miniatura bude po uložení smazána.
          </div>
        </div>
      </div>

      <!-- CONTROLS -->
      <div class="flex flex-col gap-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-2 h-10 rounded-lg bg-white px-4 text-sm font-semibold text-gray-700 border border-gray-200 shadow-sm hover:bg-gray-50 active:bg-gray-100 disabled:opacity-50"
              :disabled="busy"
              @click="pickFile"
            >
              <i class="fa-regular fa-image"></i>
              Vybrat
            </button>

            <button
              v-if="hasExistingOrDraft"
              type="button"
              class="inline-flex items-center gap-2 h-10 rounded-lg bg-white px-4 text-sm font-semibold text-red-600 border border-red-200 shadow-sm hover:bg-red-50 active:bg-red-100 disabled:opacity-50"
              :disabled="busy"
              @click="planRemove"
            >
              <i class="fa-regular fa-trash-can"></i>
              Smazat
            </button>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Velikost miniatury (px)</label>

            <div class="flex items-center gap-3">
              <input
                v-model.number="form.thumbnail_size"
                type="number"
                min="16"
                max="512"
                class="w-28 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200"
              />
              <span class="text-xs text-gray-400">Velikost v banneru</span>
            </div>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="flex justify-end gap-2 pt-1">
          <button
            class="inline-flex items-center gap-2 h-10 rounded-lg bg-white px-4 text-sm font-semibold text-gray-700 border border-gray-200 shadow-sm hover:bg-gray-50 active:bg-gray-100 disabled:opacity-50"
            :disabled="busy"
            @click="cancel"
          >
            <i class="fa-solid fa-xmark"></i>
            Zrušit
          </button>

          <button
            class="inline-flex items-center gap-2 h-10 rounded-lg bg-green-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-green-700 active:bg-green-800 disabled:opacity-50"
            :disabled="busy"
            @click="save"
          >
            <i class="fa-solid fa-check"></i>
            Uložit
          </button>
        </div>
      </div>

      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        class="hidden"
        @change="onFileChange"
      />
    </div>
  </Dialog>
</template>

<script setup>
import { reactive, ref, watch, computed, onBeforeUnmount } from "vue";
import Dialog from "primevue/dialog";

const props = defineProps({
  visible: { type: Boolean, default: false },
  bannerId: { type: [Number, String], default: null },
  busy: { type: Boolean, default: false },

  thumbnailSize: { type: Number, default: 40 },
  thumbnailUrl: { type: String, default: null },
  thumbPreview: { type: String, default: null },
});

const emit = defineEmits(["update:visible", "saveDraft"]);

const localVisible = ref(props.visible);
watch(
  () => props.visible,
  (v) => (localVisible.value = v)
);
watch(localVisible, (v) => emit("update:visible", v));

const form = reactive({
  thumbnail_size: Number(props.thumbnailSize ?? 40),
});

const fileInput = ref(null);
const draftFile = ref(null);
const draftPreviewUrl = ref(null);
const removePlanned = ref(false);

const revokePreview = () => {
  if (draftPreviewUrl.value) {
    URL.revokeObjectURL(draftPreviewUrl.value);
    draftPreviewUrl.value = null;
  }
};

const clearDraft = () => {
  draftFile.value = null;
  revokePreview();
  if (fileInput.value) fileInput.value.value = "";
};

onBeforeUnmount(revokePreview);

const hasExisting = computed(() => !!(props.thumbPreview || props.thumbnailUrl));
const hasDraft = computed(() => !!draftPreviewUrl.value);
const hasExistingOrDraft = computed(() => hasDraft.value || hasExisting.value);

const previewToShow = computed(() => {
  if (removePlanned.value) return null;
  return draftPreviewUrl.value || props.thumbPreview || props.thumbnailUrl || null;
});

watch(
  () => [props.visible, props.bannerId],
  ([vis]) => {
    if (vis) {
      form.thumbnail_size = Number(props.thumbnailSize ?? 40);
      clearDraft();
      removePlanned.value = false;
    }
  }
);

watch(
  () => props.thumbnailSize,
  (v) => {
    form.thumbnail_size = Number(v ?? 40);
  }
);

const pickFile = () => {
  if (props.busy) return;
  fileInput.value.value = "";
  fileInput.value.click();
};

const onFileChange = (e) => {
  const file = e?.target?.files?.[0];
  if (!file) return;

  removePlanned.value = false;
  draftFile.value = file;

  revokePreview();
  draftPreviewUrl.value = URL.createObjectURL(file);
};

const planRemove = () => {
  clearDraft();
  removePlanned.value = true;
};

const cancel = () => {
  clearDraft();
  removePlanned.value = false;
  localVisible.value = false;
};

const save = () => {
  emit("saveDraft", {
    banner_id: props.bannerId,
    thumbnail_size: Number(form.thumbnail_size || 40),
    file: draftFile.value || null,
    remove: !!removePlanned.value,
  });
  localVisible.value = false;
};
</script>
