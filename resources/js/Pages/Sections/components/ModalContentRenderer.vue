<script setup>
import { computed } from "vue";

const props = defineProps({
  node: { type: Object, default: null }, // { type, data }
  mainDesign: { type: Object, default: () => ({}) },
});

const type = computed(() => props.node?.type ?? "");
const data = computed(() => props.node?.data ?? {});

// ---- design vars pro inputy (stejný jako máš ve PageContentRendereru) ----
const inputCssVars = computed(() => {
  const md = props.mainDesign ?? {};

  const bg = md.input_background_color ?? "#ffffff";
  const text = md.input_text_color ?? "#111827";

  const borderEnabled = Number(md.input_border_enabled ?? 1) === 1;
  const borderWidth = Math.max(0, Number(md.input_border_width ?? 1));
  const borderColor = md.input_border_color ?? "#D1D5DB";

  const radius = Math.max(0, Number(md.input_radius ?? 8));
  const fontWeight = (md.input_font_weight ?? "500").toString();

  const hoverBorder = md.input_hover_border_color ?? borderColor;

  const ringEnabled = Number(md.input_focus_ring_enabled ?? 0) === 1;
  const ringColor = md.input_focus_ring_color ?? "#3B82F6";
  const ringWidth = Math.max(0, Number(md.input_focus_ring_width ?? 2));

  return {
    "--md-input-bg": bg,
    "--md-input-text": text,
    "--md-input-border-width": borderEnabled ? `${borderWidth}px` : "0px",
    "--md-input-border-color": borderEnabled ? borderColor : "transparent",
    "--md-input-border-hover": borderEnabled ? hoverBorder : "transparent",
    "--md-input-radius": `${radius}px`,
    "--md-input-font-weight": fontWeight,
    "--md-input-ring-color": ringColor,
    "--md-input-ring-width": ringEnabled ? `${ringWidth}px` : "0px",
  };
});

// ---- normalizace form fields (kompatibilní s tvým admin payloadem) ----
const formFields = computed(() => {
  const fields = Array.isArray(data.value?.fields) ? data.value.fields : [];

  return fields.map((f) => ({
    id: f?.id ?? `fld_${Math.random().toString(16).slice(2)}`,
    type: f?.type ?? "text",
    label: f?.label ?? "",
    name: f?.name ?? "",
    required: !!f?.required,
    placeholder: f?.placeholder ?? "",
    rows: Number(f?.rows ?? 4),
    options:
      f?.type === "select" || f?.type === "radio"
        ? (Array.isArray(f?.options) ? f.options : []).map((o) => ({
            id: o?.id ?? `opt_${Math.random().toString(16).slice(2)}`,
            label: o?.label ?? "",
            value: (o?.value ?? "").toString(),
          }))
        : [],
  }));
});

const sideTextEnabled = computed(() => !!data.value?.sideTextEnabled);
const sideTextPosition = computed(() =>
  data.value?.sideTextPosition === "left" ? "left" : "right"
);
const sideText = computed(() => data.value?.sideText ?? "");
const hasSideText = computed(() => sideTextEnabled.value && !!sideText.value.trim());
</script>

<template>
  <!-- nic vybráno -->
  <div v-if="!node" class="text-sm text-gray-500">V modalu není vybraný žádný obsah.</div>

  <!-- TEXT -->
  <div v-else-if="type === 'text'" class="prose prose-sm max-w-none">
    <div v-html="data.html || data.text || ''"></div>
  </div>

  <!-- FORM -->
  <div v-else-if="type === 'form'" :style="inputCssVars">
    <div
      class="grid gap-8"
      :class="hasSideText ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'"
    >
      <!-- side text left -->
      <div
        v-if="hasSideText && sideTextPosition === 'left'"
        class="prose prose-sm max-w-none"
        v-html="sideText"
      />

      <form class="space-y-4">
        <div v-for="f in formFields" :key="f.id" class="space-y-1">
          <label class="text-sm font-medium">
            {{ f.label || "Bez popisku" }}
            <span v-if="f.required">*</span>
          </label>

          <textarea
            v-if="f.type === 'textarea'"
            class="md-input"
            :rows="Math.max(2, Number(f.rows || 4))"
            :placeholder="f.placeholder || ''"
            :name="f.name || undefined"
          />

          <select
            v-else-if="f.type === 'select'"
            class="md-input"
            :name="f.name || undefined"
          >
            <option value="">{{ f.placeholder || "Vyberte možnost…" }}</option>
            <option v-for="o in f.options" :key="o.id" :value="o.value">
              {{ o.label || o.value }}
            </option>
          </select>

          <div v-else-if="f.type === 'radio'" class="space-y-2">
            <label
              v-for="o in f.options"
              :key="o.id"
              class="flex items-center gap-2 text-sm"
            >
              <input type="radio" :name="f.name || 'radio_' + f.id" :value="o.value" />
              <span>{{ o.label || o.value || "Možnost" }}</span>
            </label>
          </div>

          <label v-else-if="f.type === 'checkbox'" class="flex items-center gap-2">
            <input type="checkbox" :name="f.name || undefined" />
            <span class="text-sm">{{ f.placeholder || f.label || "Souhlasím" }}</span>
          </label>

          <input
            v-else
            class="md-input"
            :type="
              f.type === 'tel'
                ? 'tel'
                : f.type === 'email'
                ? 'email'
                : f.type === 'number'
                ? 'number'
                : f.type === 'date'
                ? 'date'
                : f.type === 'url'
                ? 'url'
                : 'text'
            "
            :name="f.name || undefined"
            :placeholder="f.placeholder || ''"
          />
        </div>

        <button type="button" class="btn-custom">Odeslat</button>
      </form>

      <!-- side text right -->
      <div
        v-if="hasSideText && sideTextPosition === 'right'"
        class="prose prose-sm max-w-none"
        v-html="sideText"
      />
    </div>
  </div>

  <!-- TABLE -->
  <div v-else-if="type === 'table'">
    <div class="overflow-auto">
      <table :class="data.className || 'modal-table'">
        <thead v-if="data.hasHeader">
          <tr>
            <th
              v-for="(h, i) in Array.isArray(data.headers) ? data.headers : []"
              :key="i"
            >
              {{ h }}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(row, r) in Array.isArray(data.cells) ? data.cells : []" :key="r">
            <td v-for="(cell, c) in Array.isArray(row) ? row : []" :key="c">
              {{ cell }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <!-- fallback -->
  <div v-else class="text-sm text-gray-500">
    Tento typ obsahu v modalu zatím neumím vykreslit: <b>{{ type }}</b>
  </div>
</template>

<style scoped>
.md-input {
  width: 100%;
  border-radius: var(--md-input-radius);
  background: var(--md-input-bg);
  color: var(--md-input-text);
  border: var(--md-input-border-width) solid var(--md-input-border-color);
  padding: 0.5rem 0.75rem;
  font-weight: var(--md-input-font-weight);
  outline: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.md-input:hover {
  border-color: var(--md-input-border-hover);
}
.md-input:focus {
  border-color: var(--md-input-border-hover);
  box-shadow: 0 0 0 var(--md-input-ring-width)
    color-mix(in srgb, var(--md-input-ring-color) 40%, transparent);
}

.modal-table {
  width: 100%;
  border-collapse: collapse;
}
.modal-table th,
.modal-table td {
  border: 1px solid #e5e7eb;
  padding: 10px 12px;
  text-align: left;
}
.modal-table thead th {
  background: #f9fafb;
  font-weight: 700;
}

.md-input::placeholder {
  color: var(--md-input-text);
  opacity: 0.6;
}

.md-input::-webkit-input-placeholder {
  color: var(--md-input-text);
  opacity: 0.6;
}

.md-input::-moz-placeholder {
  color: var(--md-input-text);
  opacity: 0.6;
}

.md-input:-ms-input-placeholder {
  color: var(--md-input-text);
  opacity: 0.6;
}
</style>
