<script setup>
import { computed } from "vue";

const props = defineProps({
  cmp: { type: Object, required: true },
  mainDesign: { type: Object, default: () => ({}) },
});

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

const data = computed(() => {
  const d = props.cmp?.data ?? {};
  const fields = Array.isArray(d.fields) ? d.fields : [];

  const norm = fields.map((f) => ({
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

  return {
    fields: norm,
    sideTextEnabled: !!(d.sideTextEnabled ?? false),
    sideTextPosition: d.sideTextPosition === "left" ? "left" : "right",
    sideText: d.sideText ?? "",
    hasSideText: !!(d.sideTextEnabled && (d.sideText || "").trim()),
  };
});
</script>

<template>
  <div class="w-full">
    <div
      class="grid gap-8"
      :class="data.hasSideText ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'"
      :style="inputCssVars"
    >
      <div
        v-if="data.hasSideText && data.sideTextPosition === 'left'"
        class="prose prose-sm max-w-none"
        v-html="data.sideText"
      />

      <form class="space-y-4">
        <div v-for="f in data.fields" :key="f.id" class="space-y-1">
          <label class="text-sm font-medium custom-label">
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
            <option v-for="o in f.options || []" :key="o.id" :value="o.value">
              {{ o.label || o.value }}
            </option>
          </select>

          <div v-else-if="f.type === 'radio'" class="space-y-2">
            <label
              v-for="o in f.options || []"
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

      <div
        v-if="data.hasSideText && data.sideTextPosition === 'right'"
        class="prose prose-sm max-w-none"
        v-html="data.sideText"
      />
    </div>
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
.md-input::placeholder {
  opacity: 0.7;
}
.md-input:hover {
  border-color: var(--md-input-border-hover);
}
.md-input:focus {
  border-color: var(--md-input-border-hover);
  box-shadow: 0 0 0 var(--md-input-ring-width)
    color-mix(in srgb, var(--md-input-ring-color) 40%, transparent);
}
.custom-label {
  color: var(--md-input-bg);
}
textarea.md-input {
  resize: vertical;
  min-height: 44px;
}
:deep(.md-input::placeholder) {
  color: var(--md-input-text) !important;
  opacity: 0.6 !important;
}
</style>
