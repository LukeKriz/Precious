<script setup>
import { computed } from "vue";
import ButtonActionEditor from "./ButtonActionEditor.vue";

const props = defineProps({
  modelValue: { type: Object, required: true },
  mainDesign: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:modelValue"]);

const card = computed({
  get: () => props.modelValue || {},
  set: (v) => emit("update:modelValue", v),
});

const set = (patch) => {
  card.value = { ...card.value, ...patch };
};

// ✅ pouze 2 přepínače (žádné duplikáty)
const setClickable = (v) => {
  const next = { ...card.value, clickable: !!v };
  if (!next.clickable && !next.buttonEnabled) {
    next.action = "link";
    next.url = "";
    next.modalComponent = null;
  }
  card.value = next;
};

const setButtonEnabled = (v) => {
  const next = { ...card.value, buttonEnabled: !!v };

  if (next.buttonEnabled && !String(next.buttonText ?? "").trim()) {
    next.buttonText = "Více informací";
  }

  if (!next.clickable && !next.buttonEnabled) {
    next.action = "link";
    next.url = "";
    next.modalComponent = null;
  }
  card.value = next;
};

const anyActive = computed(() => !!(card.value.clickable || card.value.buttonEnabled));

// ✅ JEDNA sdílená akce pro kartu i tlačítko
const actionModel = computed({
  get: () => (card.value.action === "modal" ? "modal" : "link"),
  set: (v) => set({ action: v === "modal" ? "modal" : "link" }),
});

const urlModel = computed({
  get: () => String(card.value.url ?? ""),
  set: (v) => set({ url: String(v ?? "") }),
});

const modalComponentModel = computed({
  get: () => card.value.modalComponent ?? null,
  set: (v) => set({ modalComponent: v }),
});

const buttonTextModel = computed({
  get: () => String(card.value.buttonText ?? "Více informací"),
  set: (v) => set({ buttonText: String(v ?? "") }),
});
</script>

<template>
  <div class="rounded-xl border p-3 bg-white">
    <div class="font-semibold mb-3">Proklik</div>

    <!-- ROW 1: dva přepínače vedle sebe -->
    <div class="grid grid-cols-2 gap-3">
      <!-- Proklik celé karty -->
      <div class="min-w-0">
        <div class="text-sm font-medium mb-2">Proklik celé karty</div>
        <div class="inline-flex rounded-lg border overflow-hidden">
          <button
            type="button"
            class="px-3 py-2 text-sm font-semibold"
            :class="
              card.clickable ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'
            "
            @click="setClickable(true)"
          >
            Ano
          </button>
          <button
            type="button"
            class="px-3 py-2 text-sm font-semibold border-l"
            :class="
              !card.clickable ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'
            "
            @click="setClickable(false)"
          >
            Ne
          </button>
        </div>
      </div>

      <!-- Zobrazit tlačítko -->
      <div class="min-w-0">
        <div class="text-sm font-medium mb-2">Zobrazit tlačítko</div>
        <div class="inline-flex rounded-lg border overflow-hidden">
          <button
            type="button"
            class="px-3 py-2 text-sm font-semibold"
            :class="
              card.buttonEnabled ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'
            "
            @click="setButtonEnabled(true)"
          >
            Ano
          </button>
          <button
            type="button"
            class="px-3 py-2 text-sm font-semibold border-l"
            :class="
              !card.buttonEnabled ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'
            "
            @click="setButtonEnabled(false)"
          >
            Ne
          </button>
        </div>
      </div>
    </div>

    <!-- ROW 2: dole "malá tabulka" -->
    <div v-if="anyActive" class="mt-4 grid grid-cols-1 gap-3 items-start">
      <div class="min-w-0">
        <ButtonActionEditor
          v-model:action="actionModel"
          v-model:url="urlModel"
          v-model:modalComponent="modalComponentModel"
          :allowedTypes="['form', 'text', 'table']"
          :label="'Akce (sdílená)'"
          :pickLabel="'Vybrat obsah'"
          :mainDesign="mainDesign"
        />
      </div>

      <div v-if="card.buttonEnabled" class="min-w-0">
        <div class="text-sm font-medium mb-2">Text tlačítka</div>
        <input
          class="w-1/2 rounded-lg border px-3 py-2 text-sm"
          v-model="buttonTextModel"
          placeholder="Např. Více"
        />
        <div class="text-xs text-gray-500 mt-2 leading-snug">
          Tip: když je URL prázdné a akce je link, tlačítko na webu nedává smysl (i když
          je zapnuté).
        </div>
      </div>
    </div>

    <div v-else class="mt-3 text-xs text-gray-500">
      Zapni proklik nebo tlačítko, aby šlo nastavit akci.
    </div>
  </div>
</template>
