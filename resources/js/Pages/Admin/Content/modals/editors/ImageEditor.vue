<script setup>
import { computed, ref, watchEffect } from "vue";
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

// ===== image =====
const image = computed({
  get: () => props.modelValue?.image ?? null,
  set: (v) => patch({ image: v }),
});

// ===== size settings =====
const widthPx = computed({
  get: () => {
    const v = props.modelValue?.widthPx;
    // null/undefined = auto (necháme přirozenou velikost / max 100%)
    if (v === null || v === undefined || v === "") return null;

    const n = Number(v);
    if (!isFinite(n)) return null;

    // rozumné limity
    return Math.max(0, Math.min(2000, Math.round(n)));
  },
  set: (v) => {
    // dovolíme vymazat input -> auto
    if (v === "" || v === null || v === undefined) {
      patch({ widthPx: null });
      return;
    }
    patch({ widthPx: Math.max(0, Math.min(2000, Number(v) || 0)) });
  },
});

const maxHeight = computed({
  get: () => {
    const n = Number(props.modelValue?.maxHeight ?? 420);
    return Math.max(80, Math.min(1200, isFinite(n) ? n : 420));
  },
  set: (v) => patch({ maxHeight: Math.max(80, Math.min(1200, Number(v) || 420)) }),
});

const fit = computed({
  get: () => (props.modelValue?.fit === "cover" ? "cover" : "contain"),
  set: (v) => patch({ fit: v === "cover" ? "cover" : "contain" }),
});

// ===== click action (link / modal) =====
const click = computed({
  get: () => props.modelValue?.click ?? null,
  set: (v) => patch({ click: v }),
});

// volitelná migrace, kdyby někde existovala legacy pole (není nutné, ale hodí se)
watchEffect(() => {
  const mv = props.modelValue || {};
  if (mv.click && typeof mv.click === "object") return;

  const hasLegacy =
    mv.clickEnabled !== undefined ||
    mv.action !== undefined ||
    mv.url !== undefined ||
    mv.modalComponent !== undefined;

  if (!hasLegacy) return;

  patch({
    click: {
      enabled: !!mv.clickEnabled,
      action: mv.action === "link" ? "link" : "modal",
      url: typeof mv.url === "string" ? mv.url : "",
      modalComponent: mv.modalComponent ?? null,
    },
  });
});

const clickEnabled = computed({
  get: () => !!click.value?.enabled,
  set: (v) =>
    click.value
      ? (click.value = { ...click.value, enabled: !!v })
      : (click.value = {
          enabled: !!v,
          action: "modal",
          url: "",
          modalComponent: null,
        }),
});

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

const align = computed({
  get: () => {
    const a = String(props.modelValue?.align ?? "center");
    return ["left", "center", "right"].includes(a) ? a : "center";
  },
  set: (v) => patch({ align: ["left", "center", "right"].includes(v) ? v : "center" }),
});
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <!-- LEFT: image upload + preview -->
    <div class="col-span-12 md:col-span-5">
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
        <div
          v-if="image"
          class="w-full flex items-center justify-center"
          :style="{ height: '220px' }"
        >
          <img :src="image" alt="" class="w-full h-full" :style="{ objectFit: fit }" />
        </div>
        <div v-else class="text-sm text-gray-400 italic">(Zatím žádný obrázek)</div>
      </div>
    </div>

    <!-- RIGHT: size + click -->
    <div class="col-span-12 md:col-span-7">
      <div class="text-sm font-semibold mb-3">Nastavení</div>

      <!-- SIZE -->
      <div class="rounded-xl border p-4 bg-white">
        <div class="text-base font-semibold mb-3">Velikost</div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium block mb-2">Šířka (px)</label>
            <input
              type="number"
              class="w-full rounded border px-3 py-2"
              placeholder="Auto"
              :value="widthPx ?? ''"
              @input="widthPx = $event.target.value"
            />
            <div class="text-xs text-gray-500 mt-1">
              Nech prázdné = automatická šířka (max 100%).
            </div>
          </div>

          <div>
            <label class="text-sm font-medium block mb-2">Max. výška (px)</label>
            <input
              type="number"
              class="w-full rounded border px-3 py-2"
              min="80"
              max="1200"
              step="10"
              v-model="maxHeight"
            />
            <div class="text-xs text-gray-500 mt-1">
              Na webu se obrázek vejde do této výšky.
            </div>
          </div>
        </div>
        <div class="mt-4">
          <label class="text-sm font-medium block mb-2">Zarovnání</label>
          <select class="w-full rounded border px-3 py-2" v-model="align">
            <option value="left">Vlevo</option>
            <option value="center">Na střed</option>
            <option value="right">Vpravo</option>
          </select>
        </div>
        <div class="mt-4">
          <label class="text-sm font-medium block mb-2">Ořez / přizpůsobení</label>
          <select class="w-full rounded border px-3 py-2" v-model="fit">
            <option value="contain">Contain (bez ořezu)</option>
            <option value="cover">Cover (může oříznout)</option>
          </select>
        </div>
      </div>

      <!-- CLICK -->
      <div class="mt-6 rounded-xl border p-4 bg-white">
        <div class="text-base font-semibold mb-3">Proklik obrázku</div>

        <div class="flex items-center justify-between gap-4">
          <div class="text-sm text-gray-700">Povolit proklik</div>

          <button
            type="button"
            class="relative inline-flex h-7 w-12 items-center rounded-full transition"
            :class="clickEnabled ? 'bg-emerald-600' : 'bg-gray-300'"
            @click="clickEnabled = !clickEnabled"
          >
            <span
              class="inline-block h-5 w-5 transform rounded-full bg-white transition"
              :class="clickEnabled ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>

        <div
          class="mt-4 space-y-4"
          :class="!clickEnabled ? 'opacity-60 pointer-events-none' : ''"
        >
          <ButtonActionEditor
            :mainDesign="mainDesign"
            :action="click?.action"
            :modalComponent="click?.modalComponent"
            :url="click?.url"
            :allowedTypes="['form', 'text', 'table']"
            @update:action="click = { ...(click || {}), action: $event }"
            @update:modalComponent="click = { ...(click || {}), modalComponent: $event }"
            @update:url="click = { ...(click || {}), url: $event }"
          />

          <div class="text-xs text-gray-500">
            Když je akce <b>Odkaz</b> a URL je prázdné, proklik nemá smysl.
          </div>
        </div>
      </div>

      <!-- QUICK PREVIEW INFO -->
      <div class="mt-6 text-xs text-gray-500">
        Uloženo bude do JSON: <b>image</b>, <b>widthPercent</b>, <b>maxHeight</b>,
        <b>fit</b>, <b>click</b>.
      </div>
    </div>
  </div>
</template>
