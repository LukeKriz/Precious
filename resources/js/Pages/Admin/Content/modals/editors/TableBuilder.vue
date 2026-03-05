<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },

  // volitelné limity
  maxRows: { type: Number, default: 20 }, // body rows (bez hlavičky)
  maxCols: { type: Number, default: 12 },
});

const emit = defineEmits(["update:modelValue"]);

const clamp = (n, min, max) => Math.max(min, Math.min(max, Number(n || 0)));

// krok 1 = Nastavení, krok 2 = Obsah
const step = ref(1);

// UI state
const cols = ref(2);
const bodyRows = ref(3);
const hasHeader = ref(true);

const headers = ref([]); // [string...]
const cells = ref([]); // [[string...]...] body

const localClassName = ref("modal-table");

// ---- helpers ----
const ensure2D = (rowsCount, colsCount, source2d) => {
  const src = Array.isArray(source2d) ? source2d : [];
  return Array.from({ length: rowsCount }, (_, r) =>
    Array.from({ length: colsCount }, (_, c) => String(src?.[r]?.[c] ?? ""))
  );
};

const ensure1D = (len, source1d) => {
  const src = Array.isArray(source1d) ? source1d : [];
  return Array.from({ length: len }, (_, i) => String(src?.[i] ?? ""));
};

const normalizeIncoming = (v) => {
  const d = v && typeof v === "object" ? v : {};

  const nextCols = clamp(d.cols ?? 2, 1, props.maxCols);
  const nextHasHeader = !!d.hasHeader;
  const nextBodyRows = clamp(d.rows ?? 3, 1, props.maxRows);

  const nextHeaders = nextHasHeader
    ? ensure1D(nextCols, d.headers)
    : ensure1D(nextCols, []);

  const nextCells = ensure2D(nextBodyRows, nextCols, d.cells);

  return {
    className: String(d.className ?? "modal-table"),
    cols: nextCols,
    rows: nextBodyRows,
    hasHeader: nextHasHeader,
    headers: nextHeaders,
    cells: nextCells,
  };
};

// ---- load from v-model ----
watch(
  () => props.modelValue,
  (v) => {
    const n = normalizeIncoming(v);
    localClassName.value = n.className;
    cols.value = n.cols;
    bodyRows.value = n.rows;
    hasHeader.value = n.hasHeader;
    headers.value = n.headers;
    cells.value = n.cells;
  },
  { immediate: true, deep: true }
);

// ---- resizing behavior ----
watch([cols, bodyRows, hasHeader], () => {
  const c = clamp(cols.value, 1, props.maxCols);
  const r = clamp(bodyRows.value, 1, props.maxRows);

  headers.value = ensure1D(c, headers.value);
  cells.value = ensure2D(r, c, cells.value);
});

// ---- emit to parent (DB payload) ----
const currentTableObject = computed(() => {
  const c = clamp(cols.value, 1, props.maxCols);
  const r = clamp(bodyRows.value, 1, props.maxRows);

  return {
    className: String(localClassName.value || "modal-table"),
    cols: c,
    rows: r, // body rows
    hasHeader: !!hasHeader.value,
    headers: ensure1D(c, hasHeader.value ? headers.value : []),
    cells: ensure2D(r, c, cells.value),
  };
});

watch(currentTableObject, (obj) => emit("update:modelValue", obj), {
  immediate: true,
  deep: true,
});

// ---- setters ----
const setHeader = (ci, val) => {
  const next = headers.value.map((h, i) => (i === ci ? String(val ?? "") : h));
  headers.value = next;
};

const setCell = (ri, ci, val) => {
  const next = cells.value.map((row, r) =>
    row.map((cell, c) => (r === ri && c === ci ? String(val ?? "") : cell))
  );
  cells.value = next;
};

// ---- preview (HTML jen pro render v UI) ----
const escapeHtml = (s) =>
  String(s ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

const previewHtml = computed(() => {
  const d = currentTableObject.value;

  const thead = d.hasHeader
    ? `<thead><tr>${d.headers
        .map((h) => `<th>${escapeHtml(h)}</th>`)
        .join("")}</tr></thead>`
    : "";

  const tbody = `<tbody>${d.cells
    .map(
      (row) => `<tr>${row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join("")}</tr>`
    )
    .join("")}</tbody>`;

  return `<table class="${escapeHtml(d.className)}">${thead}${tbody}</table>`;
});

// ---- step actions ----
const goNext = () => (step.value = 2);
const goBack = () => (step.value = 1);
</script>

<template>
  <div class="space-y-6">
    <!-- HEADER / STEP SWITCH -->
    <div class="flex items-center justify-between gap-3">
      <div class="text-sm text-gray-600">
        <span class="font-medium">Tabulka</span>
        <span class="ml-2 text-gray-400">·</span>
        <span class="ml-2" v-if="step === 1">Nastavení</span>
        <span class="ml-2" v-else>Obsah</span>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="step === 2"
          type="button"
          class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200"
          @click="goBack"
        >
          Zpět
        </button>

        <button
          v-else
          type="button"
          class="px-4 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800"
          @click="goNext"
        >
          Vytvořit tabulku
        </button>
      </div>
    </div>

    <!-- STEP 1: SETTINGS + PREVIEW (tady je to „volné místo“) -->
    <div v-if="step === 1" class="space-y-5">
      <div class="grid grid-cols-12 gap-4 items-end">
        <div class="col-span-12 sm:col-span-4">
          <label class="text-sm font-medium block mb-2">Sloupce</label>
          <input
            type="number"
            min="1"
            :max="maxCols"
            class="w-full rounded-lg border px-3 py-2"
            v-model.number="cols"
          />
          <div class="text-xs text-gray-500 mt-1">1–{{ maxCols }}</div>
        </div>

        <div class="col-span-12 sm:col-span-4">
          <label class="text-sm font-medium block mb-2">Řádky</label>
          <input
            type="number"
            min="1"
            :max="maxRows"
            class="w-full rounded-lg border px-3 py-2"
            v-model.number="bodyRows"
          />
          <div class="text-xs text-gray-500 mt-1">1–{{ maxRows }} (bez hlavičky)</div>
        </div>

        <div class="col-span-12 sm:col-span-4">
          <label class="text-sm font-medium block mb-2">Hlavička</label>
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" class="w-4 h-4" v-model="hasHeader" />
            <span>První řádek jako hlavička</span>
          </label>
        </div>
      </div>
      <!-- 
      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
          <label class="text-sm font-medium block mb-2">CSS třída tabulky</label>
          <input
            type="text"
            class="w-full rounded-lg border px-3 py-2"
            v-model="localClassName"
            placeholder="modal-table"
          />
          <div class="text-xs text-gray-500 mt-1">
            Uloží se do objektu jako <code>className</code>.
          </div>
        </div>
      </div> -->

      <!-- ✅ PREVIEW rovnou pod nastavením -->
      <div class="rounded-xl border p-4 bg-white">
        <div class="text-sm font-medium mb-3">Náhled tabulky</div>

        <div class="overflow-auto">
          <div class="prose prose-sm max-w-none" v-html="previewHtml"></div>
        </div>

        <div class="text-xs text-gray-500 mt-3">
          Náhled je renderovaný z objektu. Do DB se ukládá objekt (ne HTML string). Klikni
          na „Vytvořit tabulku“ a vyplníš obsah buněk.
        </div>
      </div>
    </div>

    <!-- STEP 2: CONTENT -->
    <div v-else class="space-y-4">
      <div class="text-sm text-gray-600">
        Vyplň obsah tabulky:
        <span class="font-medium">{{ bodyRows }}</span> řádků ×
        <span class="font-medium">{{ cols }}</span> sloupců
        <span v-if="hasHeader" class="ml-2 text-gray-500">(+ hlavička)</span>
      </div>

      <!-- HEADER INPUTS -->
      <div v-if="hasHeader" class="rounded-xl border p-4 bg-white">
        <div class="text-sm font-medium mb-3">Hlavička</div>

        <div class="grid grid-cols-12 gap-3">
          <div
            v-for="ci in cols"
            :key="'h-' + ci"
            class="col-span-12 sm:col-span-6 lg:col-span-4"
          >
            <input
              class="w-full rounded-lg border px-3 py-2 text-sm font-semibold bg-gray-50"
              :placeholder="`Hlavička ${ci}`"
              :value="headers[ci - 1]"
              @input="setHeader(ci - 1, $event.target.value)"
            />
          </div>
        </div>
      </div>

      <!-- BODY GRID -->
      <div class="overflow-auto border rounded-xl">
        <table class="min-w-full">
          <tbody>
            <tr v-for="(row, ri) in cells" :key="'r-' + ri">
              <td
                v-for="(cell, ci) in row"
                :key="'c-' + ri + '-' + ci"
                class="p-2 border"
              >
                <input
                  class="w-full rounded-lg border px-3 py-2 text-sm"
                  :placeholder="`Ř${ri + 1} / S${ci + 1}`"
                  :value="cell"
                  @input="setCell(ri, ci, $event.target.value)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PREVIEW -->
      <div class="rounded-xl border p-4 bg-white">
        <div class="text-sm font-medium mb-3">Náhled tabulky</div>

        <div class="overflow-auto">
          <div class="prose prose-sm max-w-none" v-html="previewHtml"></div>
        </div>

        <div class="text-xs text-gray-500 mt-3">
          Náhled je renderovaný z objektu. Do DB se ukládá objekt (ne HTML string).
        </div>
      </div>
    </div>
  </div>
</template>
