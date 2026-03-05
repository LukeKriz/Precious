<script setup>
import { computed, ref, watchEffect } from "vue";
import Editor from "primevue/editor";
import ButtonActionEditor from "../ButtonActionEditor.vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  mainDesign: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["update:modelValue"]);
const mainDesign = computed(() => props.mainDesign || {});
const patch = (p) => emit("update:modelValue", { ...props.modelValue, ...p });

const cardTitleEnabled = computed({
  get: () => props.modelValue?.cardTitleEnabled !== false,
  set: (v) => patch({ cardTitleEnabled: !!v }),
});

const locations = computed({
  get: () =>
    Array.isArray(props.modelValue?.locations) ? props.modelValue.locations : [],
  set: (v) => patch({ locations: Array.isArray(v) ? v : [] }),
});

const activeId = ref(null);

watchEffect(() => {
  if (!locations.value.length) {
    activeId.value = null;
    return;
  }
  const ok = locations.value.some((l) => l.id === activeId.value);
  if (!ok) activeId.value = locations.value[0].id;
});

const activeLoc = computed(
  () => locations.value.find((l) => l.id === activeId.value) ?? null
);

const genLocId = () => `loc_${Date.now()}_${Math.random().toString(16).slice(2)}`;

const defaultCardHtml = () =>
  `
<h2 style="margin:0 0 8px 0;">KONTAKTUJTE NÁS:</h2>
<p style="margin:0; font-weight:700;">Název pobočky</p>
<p style="margin:8px 0 0 0;">Adresa</p>
<p style="margin:12px 0 0 0;">
  <a href="mailto:info@firma.cz">info@firma.cz</a><br/>
  <a href="tel:+420123456789">+420 123 456 789</a>
</p>
`.trim();

const normalizeLocation = (l, idx = 0) => {
  const btn = l?.button ?? {};
  return {
    id: l?.id ?? genLocId(),
    tabLabel: l?.tabLabel ?? `Pobočka ${idx + 1}`,

    mapSide: l?.mapSide === "right" ? "right" : "left",
    mapEmbedUrl: l?.mapEmbedUrl ?? "",
    mapLinkUrl: l?.mapLinkUrl ?? "",

    cardHtml: typeof l?.cardHtml === "string" ? l.cardHtml : defaultCardHtml(),

    button: {
      enabled: !!btn?.enabled,
      text: typeof btn?.text === "string" ? btn.text : "Napište nám",
      action: btn?.action === "link" ? "link" : "modal",
      url: typeof btn?.url === "string" ? btn.url : "",
      modalComponent: btn?.modalComponent ?? null,
    },
  };
};

watchEffect(() => {
  const next = locations.value.map((l, idx) => normalizeLocation(l, idx));
  const changed = JSON.stringify(next) !== JSON.stringify(locations.value ?? []);
  if (changed) locations.value = next;
});

const addLocation = () => {
  const next = [...locations.value];
  const id = genLocId();
  next.push(
    normalizeLocation({ id, tabLabel: `Pobočka ${next.length + 1}` }, next.length)
  );
  locations.value = next;
  activeId.value = id;
};

const removeLocation = (id) => {
  const next = locations.value.filter((l) => l.id !== id);
  locations.value = next;
  if (activeId.value === id) activeId.value = next[0]?.id ?? null;
};

const updateLocation = (id, p) => {
  locations.value = locations.value.map((l) => (l.id === id ? { ...l, ...p } : l));
};

const updateButton = (id, p) => {
  locations.value = locations.value.map((l) => {
    if (l.id !== id) return l;
    return { ...l, button: { ...(l.button || {}), ...p } };
  });
};

// ---- drag & drop reorder ----
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
const onDragEnter = (idx) => (dragOver.value = idx);
const onDragOver = (idx, ev) => {
  ev.preventDefault();
  dragOver.value = idx;
};
const onDrop = (idx, ev) => {
  ev.preventDefault();
  const from =
    dragFrom.value !== null
      ? dragFrom.value
      : Number(ev.dataTransfer?.getData("text/plain"));
  const to = idx;

  if (!Number.isFinite(from) || !Number.isFinite(to) || from === to) {
    dragFrom.value = null;
    dragOver.value = null;
    return;
  }

  const arr = [...locations.value];
  const [moved] = arr.splice(from, 1);
  arr.splice(to, 0, moved);
  locations.value = arr;
  if (moved?.id) activeId.value = moved.id;

  dragFrom.value = null;
  dragOver.value = null;
};
const onDragEnd = () => {
  dragFrom.value = null;
  dragOver.value = null;
};

const safe = (v) => String(v ?? "");
</script>

<template>
  <div class="grid grid-cols-12 gap-5">
    <!-- LEFT: list -->
    <div class="col-span-12 lg:col-span-4 border rounded-2xl bg-white overflow-hidden">
      <div class="px-5 py-4 border-b flex items-center justify-between">
        <div class="font-semibold">Pobočky</div>
      </div>

      <div class="p-4 space-y-3">
        <div v-if="!locations.length" class="text-sm text-gray-500">
          Zatím žádná pobočka.
        </div>

        <button
          v-for="(l, idx) in locations"
          :key="l.id"
          type="button"
          class="w-full text-left border rounded-xl px-4 py-4 bg-white"
          :class="[
            activeId === l.id ? 'ring-2 ring-blue-500' : 'hover:bg-gray-50',
            dragOver === idx && dragFrom !== null ? 'ring-2 ring-blue-300' : '',
          ]"
          draggable="true"
          @dragstart="onDragStart(idx, $event)"
          @dragenter="onDragEnter(idx)"
          @dragover="onDragOver(idx, $event)"
          @drop="onDrop(idx, $event)"
          @dragend="onDragEnd"
          @click="activeId = l.id"
        >
          <div class="font-semibold truncate">{{ l.tabLabel || "Pobočka" }}</div>

          <div class="mt-1 text-xs text-gray-500 truncate">
            <template v-if="l.button?.enabled">
              <span v-if="l.button?.action === 'link' && l.button?.url">↗ Odkaz</span>
              <span v-else-if="l.button?.modalComponent?.type"
                >✓ Modal: {{ l.button.modalComponent.type }}</span
              >
              <span v-else>Modal (bez obsahu)</span>
            </template>
            <template v-else>Tlačítko vypnuto</template>
          </div>
        </button>

        <!-- ✅ ADD TILE like your cards editor -->
        <button
          type="button"
          class="w-full border-2 border-dashed rounded-xl py-10 grid place-items-center text-gray-500 hover:bg-gray-50"
          @click="addLocation"
        >
          <div class="text-2xl leading-none">＋</div>
          <div class="mt-2 font-medium">Přidat pobočku</div>
        </button>
      </div>
    </div>

    <!-- RIGHT: detail -->
    <div class="col-span-12 lg:col-span-8 border rounded-2xl bg-white overflow-hidden">
      <div class="px-5 py-2 border-b flex items-center justify-between">
        <div class="font-semibold">Detail pobočky</div>

        <div class="flex items-center gap-5">
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" class="w-4 h-4" v-model="cardTitleEnabled" />
            <span>Zobrazit název pobočky vpravo</span>
          </label>

          <button
            v-if="activeLoc"
            type="button"
            class="rounded-lg bg-red-600 text-white px-4 py-2 text-sm font-semibold hover:bg-red-700"
            @click="removeLocation(activeLoc.id)"
          >
            Smazat
          </button>
        </div>
      </div>

      <div v-if="!activeLoc" class="p-8 text-sm text-gray-500">
        Vyber pobočku vlevo nebo ji přidej.
      </div>

      <div v-else class="p-2 space-y-5 pb-0">
        <!-- Tab label -->
        <div class="border rounded-2xl p-4">
          <label class="text-sm font-medium block mb-2">Záložka (krátce)</label>
          <input
            class="w-full rounded-lg border px-3 py-2"
            :value="safe(activeLoc.tabLabel)"
            @input="updateLocation(activeLoc.id, { tabLabel: $event.target.value })"
          />
        </div>

        <!-- MAIN: editor left + (map+button) right -->
        <div class="grid grid-cols-12 gap-5 items-start">
          <!-- LEFT: HTML editor -->
          <div class="col-span-12 xl:col-span-7 border rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3 mb-3">
              <div class="font-semibold">Karta pobočky (HTML)</div>
              <div class="text-xs text-gray-500">
                Nadpis / název / adresa / tel / email řeš jen tady v editoru
              </div>
            </div>
            <!-- 
            <Editor
              :modelValue="activeLoc.cardHtml"
              @update:modelValue="updateLocation(activeLoc.id, { cardHtml: $event })"
              editorStyle="height: 300px"
            /> -->

            <Editor
              :modelValue="activeLoc.cardHtml"
              @update:modelValue="updateLocation(activeLoc.id, { cardHtml: $event })"
              editorStyle="height: 300px"
            >
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
                  <option :value="mainDesign.font_type">
                    {{ mainDesign.font_type }}
                  </option>
                  <option :value="mainDesign.font_type_2">
                    {{ mainDesign.font_type_2 }}
                  </option>
                  <option :value="mainDesign.font_type_3">
                    {{ mainDesign.font_type_3 }}
                  </option>
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
          </div>

          <!-- RIGHT: Map + Button -->
          <div class="col-span-12 xl:col-span-5 space-y-5">
            <!-- Map (no preview) -->
            <div class="border rounded-2xl p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Mapa</div>

                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-600">Na straně</span>
                  <select
                    class="rounded-lg border px-3 py-2"
                    :value="activeLoc.mapSide"
                    @change="
                      updateLocation(activeLoc.id, {
                        mapSide: $event.target.value === 'right' ? 'right' : 'left',
                      })
                    "
                  >
                    <option value="left">Vlevo</option>
                    <option value="right">Vpravo</option>
                  </select>
                </div>
              </div>

              <label class="text-sm font-medium block mb-2">
                Google map embed URL (iframe src)
              </label>
              <textarea
                class="w-full rounded-lg border px-3 py-2 font-mono text-xs"
                rows="3"
                :value="safe(activeLoc.mapEmbedUrl)"
                @input="
                  updateLocation(activeLoc.id, { mapEmbedUrl: $event.target.value })
                "
                placeholder="https://www.google.com/maps/embed?pb=..."
              />
              <!-- ✅ žádný iframe preview -->
            </div>

            <!-- Button -->
            <div class="border rounded-2xl p-4">
              <div class="flex items-center justify-between mb-4">
                <div class="font-semibold">Tlačítko (pro tuto pobočku)</div>

                <label class="inline-flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    class="w-4 h-4"
                    :checked="!!activeLoc.button?.enabled"
                    @change="
                      updateButton(activeLoc.id, { enabled: $event.target.checked })
                    "
                  />
                  <span>Zapnuto</span>
                </label>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium block mb-2">Text tlačítka</label>
                  <input
                    class="w-full rounded-lg border px-3 py-2"
                    :disabled="!activeLoc.button?.enabled"
                    :value="safe(activeLoc.button?.text)"
                    @input="updateButton(activeLoc.id, { text: $event.target.value })"
                  />
                </div>

                <div
                  :class="
                    !activeLoc.button?.enabled ? 'opacity-60 pointer-events-none' : ''
                  "
                >
                  <ButtonActionEditor
                    :main-design="mainDesign"
                    :action="activeLoc.button?.action"
                    :modalComponent="activeLoc.button?.modalComponent"
                    :allowedTypes="['form', 'text', 'table']"
                    @update:action="updateButton(activeLoc.id, { action: $event })"
                    @update:modalComponent="
                      updateButton(activeLoc.id, { modalComponent: $event })
                    "
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- tiny spacer -->
        <div class="h-2"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* PrimeVue editor – aby seděl do karty */
:deep(.p-editor-container) {
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.12);
}
:deep(.p-editor-toolbar) {
  border: none;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}
:deep(.p-editor-content) {
  border: none;
}
</style>
