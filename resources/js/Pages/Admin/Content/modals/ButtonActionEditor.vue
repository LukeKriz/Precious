<script setup>
import { computed, ref, watch } from "vue";

// existující editory
import FormEditor from "./editors/FormEditor.vue";
import TextEditor from "./editors/TextEditor.vue";

import TableBuilder from "./editors/TableBuilder.vue";

const props = defineProps({
  action: { type: String, default: "modal" }, // 'modal' | 'link'
  modalComponent: { type: Object, default: null }, // { type, data }
  url: { type: String, default: "" }, // link url
  allowedTypes: { type: Array, default: () => ["form", "text"] },

  label: { type: String, default: "Akce tlačítka" },
  pickLabel: { type: String, default: "Vybrat obsah" },
  mainDesign: { type: Object, default: () => ({}) },
});

const mainDesign = computed(() => props.mainDesign || {});
const emit = defineEmits(["update:action", "update:modalComponent", "update:url"]);

const actionModel = computed({
  get: () => (props.action === "link" ? "link" : "modal"),
  set: (v) => emit("update:action", v === "link" ? "link" : "modal"),
});

const modalComponentModel = computed({
  get: () => props.modalComponent ?? null,
  set: (v) => emit("update:modalComponent", v),
});

const urlModel = computed({
  get: () => String(props.url ?? ""),
  set: (v) => emit("update:url", String(v ?? "")),
});

const showPicker = ref(false);
const showEditor = ref(false);

// ✅ table builder state
const showTableBuilder = ref(false);

watch(actionModel, (a) => {
  if (a === "link") {
    showPicker.value = false;
    showEditor.value = false;
    showTableBuilder.value = false;
  }
});

const options = computed(() => {
  const base = [
    { type: "form", label: "Formulář", hint: "Otevře formulář v modalu" },
    { type: "text", label: "Text", hint: "Otevře text v modalu" },

    // ✅ NEW
    {
      type: "table",
      label: "Tabulka",
      hint: "Vytvoří tabulku (HTML) a otevře ji v modalu",
    },
  ];

  // allowedTypes řeší jen „obsah do modalu“. Tabulka je vlastně „Text“,
  // takže ji povolíme jen pokud allowedTypes obsahuje 'text'
  const allowTable = props.allowedTypes.includes("text");

  return base.filter((o) => {
    if (o.type === "table") return allowTable;
    return props.allowedTypes.includes(o.type);
  });
});

const defaultPayloadByType = (type) => {
  switch (type) {
    case "text":
      return { html: "", buttonEnabled: false, buttonText: "", buttonUrl: "" };
    case "form":
      return { formId: null, fields: [], sideTextEnabled: false, sideText: "" };
    case "table":
      return {
        className: "modal-table",
        cols: 2,
        rows: 3,
        hasHeader: true,
        headers: ["", ""],
        cells: Array.from({ length: 3 }, () => ["", ""]),
      };
    default:
      return {};
  }
};

const pick = (type) => {
  actionModel.value = "modal";

  if (type === "table") {
    modalComponentModel.value = { type: "table", data: defaultPayloadByType("table") };
    showPicker.value = false;
    showEditor.value = true;
    showTableBuilder.value = false;
    return;
  }

  modalComponentModel.value = { type, data: defaultPayloadByType(type) };
  showPicker.value = false;
  showEditor.value = true;
};

const clear = () => {
  modalComponentModel.value = null;
  showEditor.value = false;
};

const editorTitle = computed(() => {
  const t = modalComponentModel.value?.type;
  if (t === "form") return "Upravit komponentu: Formulář";
  if (t === "text") return "Upravit komponentu: Text";
  return "Upravit obsah modalu";
});

const openPickerOrEditor = () => {
  if (modalComponentModel.value) {
    showEditor.value = true;
    showPicker.value = false;
    showTableBuilder.value = false;
  } else {
    showPicker.value = true;
    showEditor.value = false;
    showTableBuilder.value = false;
  }
};

// ✅ when table builder confirms -> store as text.html
const onTableConfirm = (html) => {
  modalComponentModel.value = {
    type: "text",
    data: {
      ...defaultPayloadByType("text"),
      html: String(html ?? ""),
    },
  };
  showTableBuilder.value = false;
  showEditor.value = true; // otevřeme Text editor, aby šlo ještě doladit
};

const onTableClose = () => {
  showTableBuilder.value = false;
};
</script>

<template>
  <div class="w-full">
    <label class="text-sm font-medium block mb-2">{{ label }}</label>

    <div class="flex items-start flex-col gap-3">
      <div class="flex items-center gap-3">
        <select class="rounded-lg border px-3 py-2 pr-10" v-model="actionModel">
          <option value="modal">Modal</option>
          <option value="link">Odkaz</option>
        </select>

        <!-- MODAL -->
        <template v-if="actionModel === 'modal'">
          <button
            type="button"
            class="rounded-lg border px-4 py-2 font-semibold hover:bg-gray-50 whitespace-nowrap"
            @click="openPickerOrEditor"
          >
            {{ pickLabel }}
          </button>
        </template>

        <!-- LINK -->
        <template v-else>
          <div class="flex-1">
            <input
              class="w-full rounded-lg border px-3 py-2"
              v-model="urlModel"
              placeholder="/kontakt nebo https://… nebo mailto:…"
            />
          </div>
        </template>
      </div>

      <div v-if="modalComponentModel" class="text-sm text-green-700 whitespace-nowrap">
        ✔ Vybráno: <b>{{ modalComponentModel.type }}</b>

        <button
          type="button"
          class="ml-2 text-xs text-blue-700 hover:underline"
          @click="showEditor = true"
        >
          upravit
        </button>

        <button
          type="button"
          class="ml-2 text-xs text-red-600 hover:underline"
          @click="clear"
        >
          odstranit
        </button>
      </div>

      <div v-else class="text-sm text-gray-500 whitespace-nowrap">
        Zatím nic nevybráno
      </div>
    </div>

    <!-- PICKER -->
    <div
      v-if="showPicker"
      class="fixed inset-0 z-[99999] flex items-center justify-center"
    >
      <div class="absolute inset-0 bg-black/40" @click="showPicker = false" />

      <div class="relative bg-white rounded-2xl shadow-xl w-[650px] max-w-[95vw] p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="text-lg font-bold">Vyber obsah do modalu</div>
          <button
            class="text-2xl text-gray-400 hover:text-gray-700"
            @click="showPicker = false"
          >
            ×
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <button
            v-for="c in options"
            :key="c.type"
            type="button"
            class="border rounded-xl p-4 text-left hover:bg-gray-50"
            @click="pick(c.type)"
          >
            <div class="font-semibold">{{ c.label }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ c.hint }}</div>
          </button>
        </div>

        <div class="mt-5 flex justify-end gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200"
            @click="showPicker = false"
          >
            Zavřít
          </button>
        </div>
      </div>
    </div>

    <!-- EDITOR MODAL -->
    <div
      v-if="showEditor && modalComponentModel"
      class="fixed inset-0 z-[99999] flex items-center justify-center"
    >
      <div class="absolute inset-0 bg-black/40" @click="showEditor = false" />

      <div
        class="relative bg-white rounded-2xl shadow-xl w-[95vw] max-w-[1400px] p-0 overflow-hidden"
      >
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <div class="text-2xl font-bold text-blue-800">{{ editorTitle }}</div>
          <button
            class="text-2xl text-gray-400 hover:text-gray-700"
            @click="showEditor = false"
          >
            ×
          </button>
        </div>

        <div class="p-6">
          <FormEditor
            v-if="modalComponentModel.type === 'form'"
            v-model="modalComponentModel.data"
            :mainDesign="mainDesign"
          />
          <TextEditor
            v-else-if="modalComponentModel.type === 'text'"
            v-model="modalComponentModel.data"
            :mainDesign="mainDesign"
          />
          <!-- TABLE BUILDER -->
          <TableBuilder
            v-else-if="modalComponentModel.type === 'table'"
            v-model="modalComponentModel.data"
          />
          <div v-else class="text-sm text-gray-500">Tenhle typ zatím nepodporujeme.</div>
        </div>

        <div class="px-6 py-4 border-t flex items-center justify-end gap-3 flex-nowrap">
          <button
            type="button"
            class="px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 whitespace-nowrap"
            @click="showEditor = false"
          >
            Zavřít
          </button>
          <button
            type="button"
            class="px-5 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 whitespace-nowrap"
            @click="showEditor = false"
          >
            Uložit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
