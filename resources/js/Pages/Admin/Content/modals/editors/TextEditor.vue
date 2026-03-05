<script setup>
import { computed, onMounted } from "vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  mainDesign: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["update:modelValue"]);

const mainDesign = computed(() => props.mainDesign || {});

const html = computed({
  get: () => props.modelValue?.html ?? "",
  set: (v) => emit("update:modelValue", { ...props.modelValue, html: v }),
});

const buttonEnabled = computed({
  get: () => !!props.modelValue?.buttonEnabled,
  set: (v) => emit("update:modelValue", { ...props.modelValue, buttonEnabled: !!v }),
});

const buttonText = computed({
  get: () => props.modelValue?.buttonText ?? "",
  set: (v) => emit("update:modelValue", { ...props.modelValue, buttonText: v }),
});

const buttonUrl = computed({
  get: () => props.modelValue?.buttonUrl ?? "",
  set: (v) => emit("update:modelValue", { ...props.modelValue, buttonUrl: v }),
});

/**
 * ✅ Quill: povolíme velikosti fontu v PX
 * PrimeVue Editor používá Quill interně, ale Quill si můžeš normálně importnout.
 */
onMounted(async () => {
  const mod = await import("quill");
  const Quill = mod.default ?? mod;

  // ✅ FONT whitelist (jinak Quill často skončí na Sans Serif / ignoruje vlastní fonty)
  const Font = Quill.import("formats/font");
  Font.whitelist = [
    String(mainDesign.value?.font_type ?? "").trim(),
    String(mainDesign.value?.font_type_2 ?? "").trim(),
    String(mainDesign.value?.font_type_3 ?? "").trim(),
  ].filter(Boolean);
  Quill.register(Font, true);

  // ✅ SIZE whitelist (tvé px velikosti)
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

  // (volitelné) ✅ COLOR whitelist – jen pokud chceš omezit paletu na mainDesign
  // const ColorStyle = Quill.import("attributors/style/color");
  // ColorStyle.whitelist = [
  //   String(mainDesign.value?.primary_color ?? "").trim(),
  //   String(mainDesign.value?.secondary_color ?? "").trim(),
  //   String(mainDesign.value?.tertiary_color ?? "").trim(),
  //   String(mainDesign.value?.quaternary_color ?? "").trim(),
  //   String(mainDesign.value?.quinary_color ?? "").trim(),
  // ].filter(Boolean);
  // Quill.register(ColorStyle, true);
});
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <!-- EDITOR -->
    <div class="col-span-12">
      <label class="text-sm font-medium block mb-2">Obsah</label>

      <Editor v-model="html" editorStyle="height: 260px;">
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
          <!-- ✅ FONT SIZE v PX (místo ql-header) -->
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
    </div>

    <!-- BUTTON SETTINGS -->
    <div class="col-span-12 mt-2">
      <div class="rounded-xl border p-4 bg-white">
        <div class="text-base font-semibold mb-3">Tlačítko</div>

        <div class="flex items-center justify-between gap-4">
          <div class="text-sm text-gray-700">Zobrazit tlačítko</div>

          <!-- jednoduchý switch -->
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
          class="grid grid-cols-12 gap-4 mt-4"
          :class="!buttonEnabled ? 'opacity-50' : ''"
        >
          <div class="col-span-12 md:col-span-6">
            <label class="text-sm font-medium block mb-2">Text tlačítka</label>
            <input
              type="text"
              class="w-full rounded border px-3 py-2"
              placeholder="Např. Více informací"
              v-model="buttonText"
              :disabled="!buttonEnabled"
            />
          </div>

          <div class="col-span-12 md:col-span-6">
            <label class="text-sm font-medium block mb-2">URL</label>
            <input
              type="text"
              class="w-full rounded border px-3 py-2"
              placeholder="/kontakt nebo https://..."
              v-model="buttonUrl"
              :disabled="!buttonEnabled"
            />
          </div>

          <div class="col-span-12 text-xs text-gray-500">
            Tip: když je URL prázdné, tlačítko se na webu nezobrazí (i když je zapnuté).
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
