<script setup>
import { computed, ref, onMounted, watchEffect } from "vue";
import Editor from "primevue/editor";
import ButtonActionEditor from "../ButtonActionEditor.vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  uploadFile: { type: Function, required: true },
  deleteFile: { type: Function, required: true },
  uploadBusy: { type: Boolean, default: false },
  mainDesign: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["update:modelValue", "update:uploadBusy"]);
const mainDesign = computed(() => props.mainDesign || {});

const fileInput = ref(null);
const patch = (p) => emit("update:modelValue", { ...(props.modelValue || {}), ...p });

// ✅ HTML obsah z Editoru
const content = computed({
  get: () => props.modelValue?.content ?? "",
  set: (v) => patch({ content: v }),
});

const layout = computed({
  get: () => props.modelValue?.layout ?? "text_left",
  set: (v) => patch({ layout: v }),
});

const image = computed({
  get: () => props.modelValue?.image ?? null,
  set: (v) => patch({ image: v }),
});

// ======================================================
// ✅ NEW: button object (kompatibilní s locations_map)
// ======================================================
const button = computed({
  get: () => props.modelValue?.button ?? null,
  set: (v) => patch({ button: v }),
});

// ✅ MIGRACE starých polí -> button objekt (proběhne automaticky)
watchEffect(() => {
  const mv = props.modelValue || {};

  // pokud už button existuje, nic nedělej
  if (mv.button && typeof mv.button === "object") return;

  // pokud existují stará pole, přehoď je do button objektu
  const hasLegacy =
    mv.buttonEnabled !== undefined ||
    mv.buttonText !== undefined ||
    mv.buttonUrl !== undefined ||
    mv.modalComponent !== undefined ||
    mv.action !== undefined;

  if (!hasLegacy) return;

  const nextButton = {
    enabled: !!mv.buttonEnabled,
    text: typeof mv.buttonText === "string" ? mv.buttonText : "",
    action: mv.action === "link" ? "link" : "modal",
    url: typeof mv.buttonUrl === "string" ? mv.buttonUrl : "",
    modalComponent: mv.modalComponent ?? null,
  };

  // uložíme button + volitelně necháme staré klíče být (nevadí),
  // ale můžeš je i vymazat, pokud chceš (viz komentář níže).
  patch({ button: nextButton });

  // Pokud chceš zároveň mazat legacy klíče, odkomentuj:
  // patch({ button: nextButton, buttonEnabled: undefined, buttonText: undefined, buttonUrl: undefined, modalComponent: undefined, action: undefined });
});

const buttonEnabled = computed({
  get: () => !!button.value?.enabled,
  set: (v) =>
    button.value
      ? (button.value = { ...button.value, enabled: !!v })
      : (button.value = {
          enabled: !!v,
          text: "",
          action: "modal",
          url: "",
          modalComponent: null,
        }),
});

const buttonText = computed({
  get: () => button.value?.text ?? "",
  set: (v) => (button.value = { ...(button.value || {}), text: String(v ?? "") }),
});

// upload image
const openPicker = () => {
  if (fileInput.value) fileInput.value.value = "";
  fileInput.value?.click();
};

const onPick = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file) return;

  emit("update:uploadBusy", true);
  try {
    const res = await props.uploadFile(file);
    const path = (res?.path || res?.url || "").toString();
    image.value = path || null;
  } finally {
    emit("update:uploadBusy", false);
    if (e?.target) e.target.value = "";
  }
};

const removeImage = async () => {
  const old = image.value;
  image.value = null;
  if (old) await props.deleteFile(old);
};

// ✅ Quill sizes in px
onMounted(async () => {
  const mod = await import("quill");
  const Quill = mod.default ?? mod;

  const SizeStyle = Quill.import("attributors/style/size");
  SizeStyle.whitelist = [
    "10px",
    "11px",
    "12px",
    "13px",
    "14px",
    "15px",
    "16px",
    "17px",
    "18px",
    "19px",
    "20px",
    "21px",
    "22px",
    "23px",
    "24px",
    "25px",
    "26px",
    "27px",
    "28px",
    "29px",
    "30px",
    "32px",
    "36px",
    "40px",
  ];
  Quill.register(SizeStyle, true);
});
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <!-- LEFT: image preview -->
    <div class="col-span-12 md:col-span-4">
      <div class="text-sm font-semibold mb-2">Obrázek</div>

      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        class="hidden"
        @change="onPick"
      />

      <div class="flex items-center gap-3 flex-wrap">
        <button
          type="button"
          @click="openPicker"
          class="inline-flex items-center justify-center rounded-lg bg-green-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 active:bg-green-700 disabled:opacity-60"
          :disabled="uploadBusy"
        >
          Vybrat
        </button>

        <button
          v-if="image"
          type="button"
          @click="removeImage"
          class="inline-flex items-center justify-center rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700 disabled:opacity-60"
          :disabled="uploadBusy"
        >
          Smazat
        </button>

        <div v-if="uploadBusy" class="text-sm text-gray-500">Nahrávám…</div>
      </div>

      <p class="text-xs text-gray-500 mt-2">PNG/JPG/WebP, ideálně min. 300px šířka.</p>

      <div
        class="mt-4 border rounded-xl bg-gray-50 overflow-hidden"
        :class="image ? '' : 'p-4'"
      >
        <div v-if="image" class="w-full h-40 flex items-center justify-center">
          <img :src="image" alt="" class="w-full h-full object-contain" />
        </div>
        <div v-else class="text-sm text-gray-400 italic">(Zatím žádný obrázek)</div>
      </div>
    </div>

    <!-- RIGHT: editor + layout + button -->
    <div class="col-span-12 md:col-span-8">
      <div class="text-sm font-semibold mb-2">Obsah</div>

      <Editor v-model="content" editorStyle="height: 260px">
        <template v-slot:toolbar>
          <span>
            <select class="ql-header">
              <option value="1">Nadpis 1</option>
              <option value="2">Nadpis 2</option>
              <option value="3">Nadpis 3</option>
              <option selected></option>
            </select>
          </span>

          <select class="ql-font">
            <option :value="mainDesign.font_type">{{ mainDesign.font_type }}</option>
            <option :value="mainDesign.font_type_2">{{ mainDesign.font_type_2 }}</option>
            <option :value="mainDesign.font_type_3">{{ mainDesign.font_type_3 }}</option>
          </select>

          <select class="ql-size">
            <option value="10px">10</option>
            <option value="12px">12</option>
            <option value="14px">14</option>
            <option value="16px" selected>16</option>
            <option value="18px">18</option>
            <option value="20px">20</option>
            <option value="22px">22</option>
            <option value="24px">24</option>
            <option value="28px">28</option>
            <option value="32px">32</option>
            <option value="36px">36</option>
            <option value="40px">40</option>
          </select>

          <span class="ql-formats">
            <button v-tooltip.bottom="'Bold'" class="ql-bold"></button>
            <button v-tooltip.bottom="'Italic'" class="ql-italic"></button>
            <button v-tooltip.bottom="'Underline'" class="ql-underline"></button>
          </span>

          <span class="ql-formats">
            <select class="ql-color">
              <option selected></option>
              <option :value="mainDesign.primary_color"></option>
              <option :value="mainDesign.secondary_color"></option>
              <option :value="mainDesign.tertiary_color"></option>
              <option :value="mainDesign.quaternary_color"></option>
              <option :value="mainDesign.quinary_color"></option>
            </select>

            <select class="ql-background">
              <option selected></option>
              <option :value="mainDesign.primary_color"></option>
              <option :value="mainDesign.secondary_color"></option>
              <option :value="mainDesign.tertiary_color"></option>
              <option :value="mainDesign.quaternary_color"></option>
              <option :value="mainDesign.quinary_color"></option>
            </select>
          </span>

          <span class="ql-formats">
            <select class="ql-align">
              <option selected></option>
              <option value="center"></option>
              <option value="right"></option>
              <option value="justify"></option>
            </select>
            <button class="ql-code-block"></button>
            <button class="ql-link"></button>
          </span>
        </template>
      </Editor>

      <div class="mt-5">
        <label class="text-sm font-medium block mb-2">Rozložení</label>
        <select class="w-full rounded border px-3 py-2" v-model="layout">
          <option value="text_left">Text vlevo / Obrázek vpravo</option>
          <option value="text_right">Obrázek vlevo / Text vpravo</option>
        </select>
      </div>

      <!-- ✅ BUTTON SETTINGS (nově přes ButtonActionEditor) -->
      <div class="mt-6 rounded-xl border p-4 bg-white">
        <div class="text-base font-semibold mb-3">Tlačítko</div>

        <div class="flex items-center justify-between gap-4">
          <div class="text-sm text-gray-700">Zobrazit tlačítko</div>

          <button
            type="button"
            class="relative inline-flex h-7 w-12 items-center rounded-full transition"
            :class="buttonEnabled ? 'bg-emerald-600' : 'bg-gray-300'"
            @click="buttonEnabled = !buttonEnabled"
          >
            <span
              class="inline-block h-5 w-5 transform rounded-full bg-white transition"
              :class="buttonEnabled ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>

        <div
          class="mt-4 space-y-4"
          :class="!buttonEnabled ? 'opacity-60 pointer-events-none' : ''"
        >
          <div>
            <label class="text-sm font-medium block mb-2">Text tlačítka</label>
            <input
              type="text"
              class="w-full rounded border px-3 py-2"
              placeholder="Např. Více informací"
              v-model="buttonText"
              :disabled="!buttonEnabled"
            />
          </div>

          <ButtonActionEditor
            :mainDesign="mainDesign"
            :action="button?.action"
            :modalComponent="button?.modalComponent"
            :url="button?.url"
            :allowedTypes="['form', 'text', 'table']"
            @update:action="button = { ...(button || {}), action: $event }"
            @update:modalComponent="
              button = { ...(button || {}), modalComponent: $event }
            "
            @update:url="button = { ...(button || {}), url: $event }"
          />

          <div class="text-xs text-gray-500">
            Tip: když je akce <b>Odkaz</b> a URL je prázdné, na webu tlačítko nedává
            smysl.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
