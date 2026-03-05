<script setup>
import { ref, watch, computed } from "vue";

// editors
import TextEditor from "./editors/TextEditor.vue";
import TextImageEditor from "./editors/TextImageEditor.vue";
import GalleryEditor from "./editors/GalleryEditor.vue";
import CardsEditor from "./editors/CardsEditor.vue";
import AccordionEditor from "./editors/AccordionEditor.vue";
import FormEditor from "./editors/FormEditor.vue";
import LocationsMapEditor from "./editors/LocationsMapEditor.vue";
import AdvancedGalleryEditor from "./editors/AdvancedGalleryEditor.vue";
import ImageEditor from "./editors/ImageEditor.vue";
import YouTubeEditor from "./editors/YouTubeEditor.vue";

const props = defineProps({
  open: Boolean,

  sectionId: { type: String, default: null },
  componentId: { type: String, default: null },

  componentType: { type: String, default: null },
  initialData: { type: Object, default: () => ({}) },

  typeLabel: { type: Function, required: true },
  componentTypes: { type: Array, required: true },

  uploadFile: { type: Function, required: true },
  deleteFile: { type: Function, required: true },
  mainDesign: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["close", "save"]);

const mainDesign = computed(() => props.mainDesign || {});

const draft = ref({}); // aktuální data editoru
const uploadBusy = ref(false);

// init draft při otevření
watch(
  () => props.open,
  (v) => {
    if (!v) return;
    draft.value = JSON.parse(JSON.stringify(props.initialData ?? {}));
  }
);

const onUpdate = (nextDraft) => {
  draft.value = nextDraft ?? {};
};

const save = () => {
  if (!props.sectionId || !props.componentId || !props.componentType) return;

  emit("save", {
    sectionId: props.sectionId,
    componentId: props.componentId,
    data: draft.value ?? {},
  });
  emit("close");
};

// map typu -> komponenta editoru
const EditorByType = {
  text: TextEditor,
  text_image: TextImageEditor,
  gallery: GalleryEditor,
  cards: CardsEditor,
  accordion: AccordionEditor,
  form: FormEditor,
  locations_map: LocationsMapEditor,
  advanced_gallery: AdvancedGalleryEditor,
  image: ImageEditor,
  youtube: YouTubeEditor,
};

const isForm = computed(() => props.componentType === "form");
const isCards = computed(() => props.componentType === "cards");
const isLocationsMap = computed(() => props.componentType === "locations_map");
const isAdvancedGallery = computed(() => props.componentType === "advanced_gallery");

// ✅ šířka modalu podle typu
const modalWidthClass = computed(() => {
  if (isForm.value || isCards.value || isLocationsMap.value || isAdvancedGallery.value) {
    return "max-w-[1600px]";
  }
  // ostatní
  return "max-w-6xl";
});
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[9999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')" />

    <div
      class="relative bg-white rounded-2xl shadow-xl w-[95vw] p-8 max-h-[95vh] overflow-y-auto"
      :class="modalWidthClass"
    >
      <div class="flex items-start justify-between">
        <div class="text-2xl font-bold text-blue-800">
          Upravit komponentu: {{ typeLabel(componentType) }}
        </div>
        <button class="text-gray-400 hover:text-gray-700 text-2xl" @click="emit('close')">
          ×
        </button>
      </div>

      <div class="mt-6 space-y-6">
        <component
          :is="EditorByType[componentType] || null"
          v-if="EditorByType[componentType]"
          :modelValue="draft"
          @update:modelValue="onUpdate"
          :uploadFile="uploadFile"
          :deleteFile="deleteFile"
          v-model:uploadBusy="uploadBusy"
          :mainDesign="mainDesign"
        />

        <div v-else class="text-sm text-gray-600">
          Tento typ komponenty zatím nemá editor. ({{ componentType }})
        </div>
      </div>

      <div class="mt-10 flex justify-end gap-3">
        <button
          class="px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-blue-700"
          @click="emit('close')"
        >
          Zrušit
        </button>
        <button
          class="px-6 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold"
          @click="save"
        >
          Uložit
        </button>
      </div>
    </div>
  </div>
</template>
