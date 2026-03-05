<script setup>
import { computed, ref, watch } from "vue";
import CardActionEditor from "../CardActionEditor.vue";
const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  uploadFile: { type: Function, required: true },
  deleteFile: { type: Function, required: true },
  uploadBusy: { type: Boolean, default: false },
  mainDesign: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:modelValue", "update:uploadBusy"]);

const mainDesign = computed(() => props.mainDesign || {});
const cardsFileInput = ref(null);
const selectedCardId = ref(null);

const patch = (p) => emit("update:modelValue", { ...props.modelValue, ...p });
const genCardId = () => `card_${Date.now()}_${Math.random().toString(16).slice(2)}`;

const selectedCardProxy = computed({
  get: () => selectedCard.value,
  set: (v) => {
    if (!v) return;
    cards.value = cards.value.map((c) => (c.id === v.id ? { ...c, ...v } : c));
  },
});
// ===== palette from mainDesign =====
const palette = computed(() => {
  const d = mainDesign.value || {};
  return [
    d.primary_color,
    d.secondary_color,
    d.tertiary_color,
    d.quaternary_color,
    d.quinary_color,
  ].filter((c) => typeof c === "string" && c.trim().length);
});

const checkerStyle = {
  backgroundImage:
    "linear-gradient(45deg, #d1d5db 25%, transparent 25%)," +
    "linear-gradient(-45deg, #d1d5db 25%, transparent 25%)," +
    "linear-gradient(45deg, transparent 75%, #d1d5db 75%)," +
    "linear-gradient(-45deg, transparent 75%, #d1d5db 75%)",
  backgroundSize: "10px 10px",
  backgroundPosition: "0 0, 0 5px, 5px -5px, -5px 0px",
};

// ===== helpers: color parsing (HEX -> rgba) =====
const clamp01 = (n) => Math.min(1, Math.max(0, Number(n) || 0));

const hexToRgba = (hex, alpha01 = 1) => {
  if (typeof hex !== "string") return "";
  let h = hex.trim();
  if (!h) return "";
  if (h[0] === "#") h = h.slice(1);

  if (![3, 6, 8].includes(h.length)) return "";
  const a = clamp01(alpha01);

  const expand3 = (s) =>
    s
      .split("")
      .map((ch) => ch + ch)
      .join("");
  if (h.length === 3) h = expand3(h);

  let r = parseInt(h.slice(0, 2), 16);
  let g = parseInt(h.slice(2, 4), 16);
  let b = parseInt(h.slice(4, 6), 16);
  if ([r, g, b].some((v) => Number.isNaN(v))) return "";

  if (h.length === 8) {
    const aHex = parseInt(h.slice(6, 8), 16);
    if (!Number.isNaN(aHex)) {
      const aFromHex = aHex / 255;
      return `rgba(${r}, ${g}, ${b}, ${clamp01(a * aFromHex)})`;
    }
  }

  return `rgba(${r}, ${g}, ${b}, ${a})`;
};

// ===== global styles (applies to ALL cards) =====
const ensureStyles = (s) => ({
  imageSize: Math.max(0, Number(s?.imageSize ?? 56)),
  headingSize: Math.max(0, Number(s?.headingSize ?? 22)),
  textSize: Math.max(0, Number(s?.textSize ?? 14)),
  textColor: String(s?.textColor ?? "").trim(),
  bgColor: String(s?.bgColor ?? "").trim(),

  borderColor: String(s?.borderColor ?? "").trim(),
  borderWidth: Math.max(0, Number(s?.borderWidth ?? 1)),
  borderOpacity: Math.min(100, Math.max(0, Number(s?.borderOpacity ?? 100))),
});

const styles = computed({
  get: () => ensureStyles(props.modelValue?.styles ?? {}),
  set: (v) => patch({ styles: ensureStyles(v) }),
});

const setStyle = (p) => (styles.value = { ...styles.value, ...p });
const setTextColor = (c) => setStyle({ textColor: String(c || "").trim() });
const setBgColor = (c) => setStyle({ bgColor: String(c || "").trim() });

const setBorderColor = (c) => setStyle({ borderColor: String(c || "").trim() });
const setBorderWidth = (v) => setStyle({ borderWidth: Math.max(0, Number(v || 0)) });
const setBorderOpacity = (v) =>
  setStyle({ borderOpacity: Math.min(100, Math.max(0, Number(v || 0))) });

// style for borders in card list (editor preview)
const cardBorderStyle = computed(() => {
  const bw = Math.max(0, Number(styles.value.borderWidth || 0));
  const alpha = clamp01(Number(styles.value.borderOpacity || 0) / 100);
  const bc = String(styles.value.borderColor || "").trim();
  const borderColor = bc ? hexToRgba(bc, alpha) : "";

  return {
    borderWidth: `${bw}px`,
    borderStyle: "solid",
    ...(borderColor ? { borderColor } : {}),
  };
});

// ===== data shape (✅ UPDATED) =====
// NOVÉ: akce pro kartu i pro tlačítko (modal/link + modalComponent)
const ensureCardShape = (c) => ({
  id: c?.id ?? genCardId(),
  heading: c?.heading ?? "",
  text: c?.text ?? "",
  image: c?.image ?? null,

  // klik na kartu
  clickable: !!(c?.clickable ?? c?.cardLink ?? false),
  action: c?.action === "modal" ? "modal" : "link",
  url: (c?.url ?? c?.href ?? c?.link ?? "") || "",
  modalComponent: c?.modalComponent ?? null,

  // tlačítko
  buttonEnabled: !!(c?.buttonEnabled ?? c?.showButton ?? false),
  buttonText: (c?.buttonText ?? c?.buttonLabel ?? "Více informací") || "Více infromací",
  buttonAction: c?.buttonAction === "modal" ? "modal" : "link",
  buttonUrl: (c?.buttonUrl ?? "") || "",
  buttonModalComponent: c?.buttonModalComponent ?? null,
});

// ===== preview count + cards =====
const previewCount = computed({
  get: () => Number(props.modelValue?.previewCount ?? 3),
  set: (v) => patch({ previewCount: Math.max(0, Number(v || 1)) }),
});

const cards = computed({
  get: () =>
    Array.isArray(props.modelValue?.cards)
      ? props.modelValue.cards.map(ensureCardShape)
      : [],
  set: (v) => patch({ cards: v.map(ensureCardShape) }),
});

watch(
  () => cards.value.length,
  () => {
    if (!selectedCardId.value && cards.value[0]?.id)
      selectedCardId.value = cards.value[0].id;

    if (
      selectedCardId.value &&
      !cards.value.find((c) => String(c.id) === String(selectedCardId.value))
    ) {
      selectedCardId.value = cards.value[0]?.id ?? null;
    }
  },
  { immediate: true }
);

const selectedCard = computed(() => {
  return cards.value.find((c) => String(c.id) === String(selectedCardId.value)) || null;
});

// ===== actions =====
const addCard = () => {
  const id = genCardId();
  cards.value = [
    ...cards.value,
    ensureCardShape({
      id,
      heading: "",
      text: "",
      image: null,

      clickable: false,
      action: "link",
      url: "",
      modalComponent: null,

      buttonEnabled: false,
      buttonText: "Více informací",
      buttonAction: "link",
      buttonUrl: "",
      buttonModalComponent: null,
    }),
  ];
  selectedCardId.value = id;
};

const removeCard = async (cardId) => {
  const card = cards.value.find((c) => String(c.id) === String(cardId));
  const oldImg = card?.image || null;

  cards.value = cards.value.filter((c) => String(c.id) !== String(cardId));
  if (String(selectedCardId.value) === String(cardId)) selectedCardId.value = null;

  if (oldImg) await props.deleteFile(oldImg);
};

const openCardImagePicker = (cardId) => {
  selectedCardId.value = cardId;
  if (cardsFileInput.value) cardsFileInput.value.value = "";
  cardsFileInput.value?.click();
};

const onPickCardImage = async (e) => {
  const file = e?.target?.files?.[0];
  if (!file) return;

  const card = selectedCard.value;
  if (!card) return;

  emit("update:uploadBusy", true);
  try {
    const res = await props.uploadFile(file);
    const path = (res?.url || res?.path || "").toString() || null;

    const old = card.image || null;
    cards.value = cards.value.map((c) => (c.id === card.id ? { ...c, image: path } : c));

    if (old && old !== path) await props.deleteFile(old);
  } finally {
    emit("update:uploadBusy", false);
    if (e?.target) e.target.value = "";
  }
};

const removeCardImage = async (cardId) => {
  const card = cards.value.find((c) => String(c.id) === String(cardId));
  if (!card) return;

  const old = card.image || null;
  cards.value = cards.value.map((c) =>
    String(c.id) === String(cardId) ? { ...c, image: null } : c
  );
  if (old) await props.deleteFile(old);
};

// ✅ UPDATED toggles: už nemažeme url “globálně”
// (každý cíl má vlastní url)
const setClickable = (cardId, yes) => {
  cards.value = cards.value.map((c) => {
    if (String(c.id) !== String(cardId)) return c;
    const clickable = !!yes;

    // když vypínáš klik, necháme data zachovaná (ať se to neztratí)
    return { ...c, clickable };
  });
};

const setButtonEnabled = (cardId, yes) => {
  cards.value = cards.value.map((c) => {
    if (String(c.id) !== String(cardId)) return c;
    const buttonEnabled = !!yes;
    const buttonText = buttonEnabled && !c.buttonText ? "Více infromací" : c.buttonText;
    return { ...c, buttonEnabled, buttonText };
  });
};

const patchSelected = (p) => {
  const card = selectedCard.value;
  if (!card) return;
  cards.value = cards.value.map((c) => (c.id === card.id ? { ...c, ...p } : c));
};

// ===== DND (HTML5) =====
const dragId = ref(null);

const onDragStart = (cardId, e) => {
  dragId.value = String(cardId);
  try {
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", String(cardId));
  } catch {}
};

const onDropOn = (targetId, e) => {
  e.preventDefault();
  const from = dragId.value || (e?.dataTransfer?.getData?.("text/plain") ?? "");
  const to = String(targetId);
  if (!from || !to || from === to) return;

  const arr = [...cards.value];
  const fromIdx = arr.findIndex((c) => String(c.id) === String(from));
  const toIdx = arr.findIndex((c) => String(c.id) === String(to));
  if (fromIdx < 0 || toIdx < 0) return;

  const [moved] = arr.splice(fromIdx, 1);
  arr.splice(toIdx, 0, moved);
  cards.value = arr;
  dragId.value = null;
};

const onDragOver = (e) => e.preventDefault();
</script>

<template>
  <!-- 3-column layout on xl: Settings | Cards | Edit -->
  <div class="grid grid-cols-12 gap-6">
    <!-- SETTINGS -->
    <div class="col-span-12 xl:col-span-4">
      <div class="border rounded-xl p-4 bg-white max-h-[75vh] overflow-auto">
        <div class="flex items-start justify-between gap-3">
          <div class="font-semibold">Nastavení</div>
        </div>

        <div class="mt-3">
          <label class="text-sm font-medium block mb-2">Počet karet na webu</label>
          <input
            class="w-full rounded border px-3 py-2"
            type="number"
            min="0"
            v-model.number="previewCount"
          />
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="text-xs font-medium block mb-1">Velikost obrázku (px)</label>
            <input
              class="w-full rounded border px-3 py-2 text-sm"
              type="number"
              :value="styles.imageSize"
              @input="setStyle({ imageSize: $event.target.value })"
            />
          </div>

          <div>
            <label class="text-xs font-medium block mb-1">Font nadpis (px)</label>
            <input
              class="w-full rounded border px-3 py-2 text-sm"
              type="number"
              :value="styles.headingSize"
              @input="setStyle({ headingSize: $event.target.value })"
            />
          </div>

          <div>
            <label class="text-xs font-medium block mb-1">Font text (px)</label>
            <input
              class="w-full rounded border px-3 py-2 text-sm"
              type="number"
              :value="styles.textSize"
              @input="setStyle({ textSize: $event.target.value })"
            />
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-xs font-medium mb-2">Barva textu</div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="h-8 w-8 rounded-full border border-gray-300 shadow-sm"
                :class="!styles.textColor ? 'ring-2 ring-emerald-500 ring-offset-2' : ''"
                :style="checkerStyle"
                title="Default"
                @click="setTextColor('')"
              />
              <button
                v-for="(c, i) in palette"
                :key="'tc_' + c + '_' + i"
                type="button"
                class="h-8 w-8 rounded-full border border-gray-300 shadow-sm"
                :class="
                  styles.textColor === c ? 'ring-2 ring-emerald-500 ring-offset-2' : ''
                "
                :style="{ backgroundColor: c }"
                :title="c"
                @click="setTextColor(c)"
              />
            </div>
          </div>

          <div>
            <div class="text-xs font-medium mb-2">Pozadí karty</div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="h-8 w-8 rounded-full border border-gray-300 shadow-sm"
                :class="!styles.bgColor ? 'ring-2 ring-emerald-500 ring-offset-2' : ''"
                :style="checkerStyle"
                title="Default/transparent"
                @click="setBgColor('')"
              />
              <button
                v-for="(c, i) in palette"
                :key="'bg_' + c + '_' + i"
                type="button"
                class="h-8 w-8 rounded-full border border-gray-300 shadow-sm"
                :class="
                  styles.bgColor === c ? 'ring-2 ring-emerald-500 ring-offset-2' : ''
                "
                :style="{ backgroundColor: c }"
                :title="c"
                @click="setBgColor(c)"
              />
            </div>
          </div>
        </div>

        <!-- Border controls -->
        <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-medium block mb-1">Šířka borderu (px)</label>
            <input
              class="w-full rounded border px-3 py-2 text-sm"
              type="number"
              min="0"
              :value="styles.borderWidth"
              @input="setBorderWidth($event.target.value)"
            />
          </div>

          <div>
            <label class="text-xs font-medium block mb-1">Opacity borderu (%)</label>
            <input
              class="w-full"
              type="range"
              min="0"
              max="100"
              :value="styles.borderOpacity"
              @input="setBorderOpacity($event.target.value)"
            />
            <div class="text-xs text-gray-500 mt-1">{{ styles.borderOpacity }}%</div>
          </div>

          <div>
            <div class="text-xs font-medium mb-2">Barva borderu</div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="h-8 w-8 rounded-full border border-gray-300 shadow-sm"
                :class="
                  !styles.borderColor ? 'ring-2 ring-emerald-500 ring-offset-2' : ''
                "
                :style="checkerStyle"
                title="Default"
                @click="setBorderColor('')"
              />
              <button
                v-for="(c, i) in palette"
                :key="'bc_' + c + '_' + i"
                type="button"
                class="h-8 w-8 rounded-full border border-gray-300 shadow-sm"
                :class="
                  styles.borderColor === c ? 'ring-2 ring-emerald-500 ring-offset-2' : ''
                "
                :style="{ backgroundColor: c }"
                :title="c"
                @click="setBorderColor(c)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CARDS -->
    <div class="col-span-12 xl:col-span-4">
      <div class="border rounded-xl p-4 bg-white max-h-[75vh] overflow-auto">
        <label class="text-sm font-medium block mb-2">Karty</label>

        <input
          ref="cardsFileInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="onPickCardImage"
        />

        <div class="grid grid-cols-1 gap-3">
          <button
            v-for="c in cards"
            :key="c.id"
            type="button"
            class="relative text-left border rounded-xl p-4 hover:bg-gray-50 transition"
            :class="selectedCardId === c.id ? 'ring-2 ring-blue-500' : ''"
            :style="cardBorderStyle"
            @click="selectedCardId = c.id"
            draggable="true"
            @dragstart="onDragStart(c.id, $event)"
            @dragover="onDragOver"
            @drop="onDropOn(c.id, $event)"
            title="Pořadí změníš přetažením"
          >
            <div class="flex items-start gap-3">
              <div
                class="w-14 h-14 rounded-lg border bg-gray-50 overflow-hidden flex items-center justify-center shrink-0"
              >
                <img v-if="c.image" :src="c.image" class="w-full h-full object-cover" />
                <span v-else class="text-xs text-gray-400">Bez</span>
              </div>

              <div class="min-w-0">
                <div class="font-semibold truncate">
                  {{ c.heading || "Bez nadpisu" }}
                </div>
                <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                  {{ c.text || "Bez textu" }}
                </div>
              </div>
            </div>
          </button>

          <button
            type="button"
            class="border-2 border-dashed rounded-xl p-4 hover:bg-gray-50 transition flex items-center justify-center text-gray-500"
            @click="addCard"
          >
            <div class="text-center">
              <div class="text-3xl leading-none">+</div>
              <div class="text-sm mt-1">Přidat kartu</div>
            </div>
          </button>
        </div>

        <div class="mt-3 text-xs text-gray-500">
          Tip: Pořadí změníš přetažením karty (drag &amp; drop).
        </div>

        <div v-if="!cards.length" class="mt-3 text-sm text-gray-500">
          Zatím žádné karty. Přidej první přes <b>+</b>.
        </div>
      </div>
    </div>

    <!-- EDIT -->
    <div class="col-span-12 xl:col-span-4">
      <div
        v-if="selectedCard"
        class="border rounded-xl p-4 bg-gray-50 max-h-[75vh] overflow-auto"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="font-semibold">Editace karty</div>

          <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700 disabled:opacity-60"
            :disabled="uploadBusy"
            @click="removeCard(selectedCard.id)"
          >
            Smazat kartu
          </button>
        </div>

        <div class="mt-4 grid grid-cols-[90px_1fr] gap-4 items-start">
          <!-- THUMB -->
          <div>
            <div
              v-if="selectedCard.image"
              class="w-[90px] h-[90px] rounded-lg border bg-white overflow-hidden"
            >
              <img :src="selectedCard.image" class="w-full h-full object-cover" alt="" />
            </div>
            <div
              v-else
              class="w-[90px] h-[90px] rounded-lg border bg-white flex items-center justify-center text-xs text-gray-400"
            >
              Bez
            </div>

            <button
              type="button"
              class="mt-2 w-full inline-flex items-center justify-center rounded-lg bg-green-500 px-2 py-2 text-xs font-semibold text-white shadow-sm hover:bg-green-600 active:bg-green-700 disabled:opacity-60"
              :disabled="uploadBusy"
              @click="openCardImagePicker(selectedCard.id)"
            >
              Vybrat
            </button>

            <button
              v-if="selectedCard.image"
              type="button"
              class="mt-2 w-full inline-flex items-center justify-center rounded-lg bg-red-500 px-2 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700 disabled:opacity-60"
              :disabled="uploadBusy"
              @click="removeCardImage(selectedCard.id)"
            >
              Smazat
            </button>
          </div>

          <!-- FIELDS -->
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium block mb-2">Nadpis</label>
              <input
                class="w-full rounded border px-3 py-2 text-sm"
                type="text"
                :value="selectedCard.heading"
                @input="patchSelected({ heading: $event.target.value })"
                placeholder="Nadpis…"
              />
            </div>

            <div>
              <label class="text-sm font-medium block mb-2">Text</label>
              <textarea
                class="w-full rounded border px-3 py-2 text-sm resize-none"
                rows="3"
                :value="selectedCard.text"
                @input="patchSelected({ text: $event.target.value })"
                placeholder="Text…"
              />
            </div>

            <!-- ✅ NEW: Akce karty + tlačítka (modal/link + modal content) -->
            <div class="pt-2 border-t">
              <CardActionEditor v-model="selectedCardProxy" :mainDesign="mainDesign" />
              <!-- rychlé toggle zachované (na stejné místo) -->
              <!-- <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <div class="text-sm font-medium mb-2">Proklik celé karty</div>
                  <div class="inline-flex rounded-lg border overflow-hidden">
                    <button
                      type="button"
                      class="px-4 py-2 text-sm"
                      :class="
                        selectedCard.clickable
                          ? 'bg-blue-700 text-white'
                          : 'bg-white hover:bg-gray-50'
                      "
                      @click="setClickable(selectedCard.id, true)"
                    >
                      Ano
                    </button>
                    <button
                      type="button"
                      class="px-4 py-2 text-sm border-l"
                      :class="
                        !selectedCard.clickable
                          ? 'bg-blue-700 text-white'
                          : 'bg-white hover:bg-gray-50'
                      "
                      @click="setClickable(selectedCard.id, false)"
                    >
                      Ne
                    </button>
                  </div>
                </div>

                <div>
                  <div class="text-sm font-medium mb-2">Zobrazit tlačítko</div>
                  <div class="inline-flex rounded-lg border overflow-hidden">
                    <button
                      type="button"
                      class="px-4 py-2 text-sm"
                      :class="
                        selectedCard.buttonEnabled
                          ? 'bg-blue-700 text-white'
                          : 'bg-white hover:bg-gray-50'
                      "
                      @click="setButtonEnabled(selectedCard.id, true)"
                    >
                      Ano
                    </button>
                    <button
                      type="button"
                      class="px-4 py-2 text-sm border-l"
                      :class="
                        !selectedCard.buttonEnabled
                          ? 'bg-blue-700 text-white'
                          : 'bg-white hover:bg-gray-50'
                      "
                      @click="setButtonEnabled(selectedCard.id, false)"
                    >
                      Ne
                    </button>
                  </div>
                </div>
              </div> -->
            </div>

            <div v-if="uploadBusy" class="text-sm text-gray-500">Nahrávám…</div>
          </div>
        </div>
      </div>

      <div
        v-else
        class="text-sm text-gray-500 border rounded-xl p-4 bg-gray-50 max-h-[75vh] overflow-auto"
      >
        Klikni na kartu vlevo pro editaci, nebo přidej novou pomocí +.
      </div>
    </div>
  </div>
</template>
