<!-- preview/components/editors/FormEditor.vue -->
<script setup>
import { computed, ref, watch, onMounted } from "vue";
import Editor from "primevue/editor";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  mainDesign: { type: Object, default: () => ({}) },
});

const mainDesign = computed(() => props.mainDesign || {});
const emit = defineEmits(["update:modelValue"]);

const types = [
  { value: "text", label: "Text" },
  { value: "textarea", label: "Dlouhý text" },
  { value: "email", label: "E-mail" },
  { value: "tel", label: "Telefon" },
  { value: "number", label: "Číslo" },
  { value: "date", label: "Datum" },
  { value: "url", label: "Odkaz (URL)" },
  { value: "checkbox", label: "Checkbox" },
  { value: "select", label: "Select" },
  { value: "radio", label: "Radio" },
];

const local = ref({
  fields: [],
  // ✅ side text
  sideTextEnabled: false,
  sideTextPosition: "right", // right | left (pro web render, tady editor zobrazujeme vpravo)
  sideText: "", // HTML z PrimeVue Editoru
});

const selectedId = ref(null);

const clone = (v) => JSON.parse(JSON.stringify(v ?? {}));
const uid = () => `fld_${Date.now()}_${Math.random().toString(16).slice(2)}`;
const optId = () => `opt_${Date.now()}_${Math.random().toString(16).slice(2)}`;

const ensureField = (f) => {
  const id = f?.id ?? uid();
  const type = f?.type ?? "text";
  const label = f?.label ?? "Nové pole";
  const name = (f?.name ?? "").trim() || `pole_${id.slice(-4)}`;
  const placeholder = f?.placeholder ?? "";
  const required = !!(f?.required ?? false);

  const base = { id, type, label, name, placeholder, required };

  if (type === "textarea") return { ...base, rows: Number(f?.rows ?? 4) };

  if (type === "select" || type === "radio") {
    const options = Array.isArray(f?.options) ? f.options : [];
    return {
      ...base,
      options: options.map((o) => ({
        id: o?.id ?? optId(),
        label: o?.label ?? "Možnost",
        value: (o?.value ?? "").toString(),
      })),
    };
  }

  return base;
};

const syncFromProps = () => {
  const v = clone(props.modelValue);
  const fields = Array.isArray(v.fields) ? v.fields : [];

  local.value = {
    fields: fields.map(ensureField),

    sideTextEnabled: !!(v.sideTextEnabled ?? false),
    sideTextPosition: v.sideTextPosition === "left" ? "left" : "right",
    sideText: v.sideText ?? "",
  };

  if (!selectedId.value && local.value.fields.length)
    selectedId.value = local.value.fields[0].id;

  if (
    selectedId.value &&
    !local.value.fields.find((f) => f.id === selectedId.value) &&
    local.value.fields.length
  ) {
    selectedId.value = local.value.fields[0].id;
  }
};

watch(() => props.modelValue, syncFromProps, { immediate: true, deep: true });

const selectedField = computed(
  () => local.value.fields.find((f) => f.id === selectedId.value) || null
);

const commit = () => emit("update:modelValue", clone(local.value));

// ===== quick templates (4) =====
const addTemplate = (t) => {
  const id = uid();

  if (t === "name") {
    local.value.fields.push(
      ensureField({
        id,
        type: "text",
        label: "Jméno",
        name: `name_${id.slice(-4)}`,
        placeholder: "Vyplňte své jméno…",
        required: true,
      })
    );
  } else if (t === "email") {
    local.value.fields.push(
      ensureField({
        id,
        type: "email",
        label: "E-mail",
        name: `email_${id.slice(-4)}`,
        placeholder: "např. jana@email.cz",
        required: true,
      })
    );
  } else if (t === "phone") {
    local.value.fields.push(
      ensureField({
        id,
        type: "tel",
        label: "Telefon",
        name: `phone_${id.slice(-4)}`,
        placeholder: "např. +420 777 000 000",
        required: false,
      })
    );
  } else if (t === "message") {
    local.value.fields.push(
      ensureField({
        id,
        type: "textarea",
        label: "Zpráva",
        name: `message_${id.slice(-4)}`,
        placeholder: "Napište nám, co potřebujete…",
        required: true,
        rows: 5,
      })
    );
  }

  selectedId.value = id;
  commit();
};

// ===== actions =====
const addField = () => {
  const id = uid();
  local.value.fields.push(
    ensureField({
      id,
      type: "text",
      label: "Nové pole",
      name: `nove_pole_${id.slice(-4)}`,
      placeholder: "",
      required: false,
    })
  );
  selectedId.value = id;
  commit();
};

const removeField = (id) => {
  local.value.fields = local.value.fields.filter((f) => f.id !== id);
  if (selectedId.value === id) selectedId.value = local.value.fields[0]?.id ?? null;
  commit();
};

const moveField = (id, dir) => {
  const idx = local.value.fields.findIndex((f) => f.id === id);
  if (idx === -1) return;
  const nextIdx = idx + dir;
  if (nextIdx < 0 || nextIdx >= local.value.fields.length) return;

  const arr = [...local.value.fields];
  const [x] = arr.splice(idx, 1);
  arr.splice(nextIdx, 0, x);
  local.value.fields = arr;
  commit();
};

const onTypeChange = () => {
  const f = selectedField.value;
  if (!f) return;

  const rebuilt = ensureField(f);
  const idx = local.value.fields.findIndex((x) => x.id === f.id);
  if (idx !== -1) local.value.fields[idx] = rebuilt;

  commit();
};

// options
const addOption = () => {
  const f = selectedField.value;
  if (!f || !(f.type === "select" || f.type === "radio")) return;

  f.options = Array.isArray(f.options) ? f.options : [];
  f.options.push({ id: optId(), label: "Možnost", value: "" });
  commit();
};

const removeOption = (optIdToRemove) => {
  const f = selectedField.value;
  if (!f || !(f.type === "select" || f.type === "radio")) return;

  f.options = (f.options || []).filter((o) => o.id !== optIdToRemove);
  commit();
};

// ===== DRAG & DROP reorder (editor only) =====
const drag = ref({ draggingId: null, overId: null });

const onDragStart = (id) => {
  drag.value.draggingId = id;
  drag.value.overId = id;
};

const onDragOver = (e, overId) => {
  e.preventDefault();
  drag.value.overId = overId;
};

const onDrop = () => {
  const { draggingId, overId } = drag.value;
  if (!draggingId || !overId || draggingId === overId) {
    drag.value = { draggingId: null, overId: null };
    return;
  }

  const from = local.value.fields.findIndex((f) => f.id === draggingId);
  const to = local.value.fields.findIndex((f) => f.id === overId);
  if (from === -1 || to === -1) return;

  const arr = [...local.value.fields];
  const [moved] = arr.splice(from, 1);
  arr.splice(to, 0, moved);
  local.value.fields = arr;

  commit();
  drag.value = { draggingId: null, overId: null };
};

const onDragEnd = () => {
  drag.value = { draggingId: null, overId: null };
};

// ✅ side text toggles
const setSideTextEnabled = (yes) => {
  local.value.sideTextEnabled = !!yes;
  if (local.value.sideTextEnabled && !local.value.sideTextPosition) {
    local.value.sideTextPosition = "right";
  }
  commit();
};

const setSideTextPosition = (pos) => {
  local.value.sideTextPosition = pos === "left" ? "left" : "right";
  commit();
};

/**
 * ✅ Quill: povolíme velikosti fontu v PX
 * PrimeVue Editor používá Quill interně, ale Quill si můžeš normálně importnout.
 */
onMounted(async () => {
  // Quill import (funguje ve Vite většinou takhle)
  const mod = await import("quill");
  const Quill = mod.default ?? mod;

  // Style attributor pro size (umožní "12px" atd.)
  const SizeStyle = Quill.import("attributors/style/size");

  // povolené px hodnoty v dropdownu
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
  <!-- 3 sloupce: list (4), nastavení pole (4), text vedle (4) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- LEFT: list -->
    <div class="lg:col-span-4">
      <div class="text-sm font-semibold mb-2">Pole ve formuláři</div>
      <div class="text-xs text-gray-500 mb-3">
        Klikni na pole pro editaci. Pořadí změníš přetažením nebo šipkami.
      </div>

      <!-- quick templates -->
      <div class="flex flex-wrap gap-2 mb-4">
        <button
          type="button"
          class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
          @click="addTemplate('name')"
        >
          + Jméno
        </button>
        <button
          type="button"
          class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
          @click="addTemplate('email')"
        >
          + E-mail
        </button>
        <button
          type="button"
          class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
          @click="addTemplate('phone')"
        >
          + Telefon
        </button>
        <button
          type="button"
          class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
          @click="addTemplate('message')"
        >
          + Zpráva
        </button>
      </div>

      <!-- add tile -->
      <button
        type="button"
        class="w-full min-h-[92px] rounded-2xl border-2 border-dashed border-gray-200 bg-white hover:bg-gray-50 transition flex flex-col items-center justify-center"
        @click="addField"
      >
        <div class="text-4xl leading-none text-gray-400">+</div>
        <div class="mt-1 text-sm text-gray-600">Přidat pole</div>
      </button>

      <div class="mt-4 space-y-2">
        <div
          v-for="f in local.fields"
          :key="f.id"
          class="w-full text-left rounded-xl border bg-white hover:bg-gray-50 transition px-3 py-3 cursor-pointer select-none"
          :class="[
            selectedId === f.id ? 'ring-2 ring-blue-500' : '',
            drag.draggingId && drag.overId === f.id ? 'ring-2 ring-blue-300' : '',
          ]"
          @click="selectedId = f.id"
          draggable="true"
          @dragstart="onDragStart(f.id)"
          @dragover="onDragOver($event, f.id)"
          @drop="onDrop"
          @dragend="onDragEnd"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ f.label || "Bez popisku" }}</div>
              <div class="text-xs text-gray-500">
                {{ types.find((t) => t.value === f.type)?.label ?? f.type }}
                <span class="mx-1">•</span>
                <span class="font-mono">name: {{ f.name }}</span>
                <span v-if="f.required" class="ml-2 text-red-600">povinné</span>
              </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
              <button
                type="button"
                class="w-9 h-9 rounded-lg border hover:bg-gray-50"
                title="Posunout nahoru"
                @click.stop="moveField(f.id, -1)"
              >
                ↑
              </button>
              <button
                type="button"
                class="w-9 h-9 rounded-lg border hover:bg-gray-50"
                title="Posunout dolů"
                @click.stop="moveField(f.id, 1)"
              >
                ↓
              </button>
              <button
                type="button"
                class="px-3 h-9 rounded-lg bg-red-600 text-white hover:bg-red-700"
                title="Smazat pole"
                @click.stop="removeField(f.id)"
              >
                Smazat
              </button>
            </div>
          </div>
        </div>

        <div v-if="!local.fields.length" class="text-sm text-gray-500 mt-4">
          Zatím nemáš žádná pole. Přidej první přes “Přidat pole”.
        </div>
      </div>
    </div>

    <!-- MIDDLE: details -->
    <div class="lg:col-span-4">
      <div class="text-sm font-semibold mb-2">Nastavení pole</div>

      <div v-if="selectedField" class="rounded-2xl border bg-white p-5">
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="text-sm font-medium block mb-2">Typ pole</label>
            <select
              class="w-full rounded border px-3 py-2"
              v-model="selectedField.type"
              @change="onTypeChange"
            >
              <option v-for="t in types" :key="t.value" :value="t.value">
                {{ t.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium block mb-2">Popisek (label)</label>
            <input
              class="w-full rounded border px-3 py-2"
              type="text"
              v-model="selectedField.label"
              @input="commit"
              placeholder="Např. Jméno"
            />
          </div>

          <div>
            <label class="text-sm font-medium block mb-2">Placeholder</label>
            <input
              class="w-full rounded border px-3 py-2"
              type="text"
              v-model="selectedField.placeholder"
              @input="commit"
              placeholder="Např. Vyplňte své jméno…"
            />
          </div>

          <div>
            <label class="text-sm font-medium block mb-2">Technický název (name)</label>
            <input
              class="w-full rounded border px-3 py-2 font-mono"
              type="text"
              v-model="selectedField.name"
              @input="commit"
              placeholder="napr. email"
            />
            <div class="text-xs text-gray-500 mt-1">Posílá se na backend jako klíč.</div>
          </div>

          <div>
            <label class="inline-flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                class="w-4 h-4"
                v-model="selectedField.required"
                @change="commit"
              />
              Povinné pole
            </label>
          </div>

          <!-- textarea extra -->
          <div v-if="selectedField.type === 'textarea'">
            <label class="text-sm font-medium block mb-2">Řádky</label>
            <input
              class="w-40 rounded border px-3 py-2"
              type="number"
              min="2"
              max="20"
              v-model.number="selectedField.rows"
              @input="commit"
            />
          </div>

          <!-- select/radio options -->
          <div v-if="selectedField.type === 'select' || selectedField.type === 'radio'">
            <div class="text-sm font-semibold mb-2">Možnosti</div>

            <div class="space-y-2">
              <div
                v-for="opt in selectedField.options || []"
                :key="opt.id"
                class="grid grid-cols-1 gap-2 items-center border rounded-xl p-3"
              >
                <input
                  class="w-full rounded border px-3 py-2"
                  type="text"
                  v-model="opt.label"
                  @input="commit"
                  placeholder="Text možnosti (např. Praha)"
                />
                <input
                  class="w-full rounded border px-3 py-2 font-mono"
                  type="text"
                  v-model="opt.value"
                  @input="commit"
                  placeholder="value (např. praha)"
                />
                <button
                  type="button"
                  class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                  @click="removeOption(opt.id)"
                >
                  Smazat
                </button>
              </div>

              <!-- add option tile -->
              <button
                type="button"
                class="w-full rounded-xl border-2 border-dashed border-gray-200 bg-white hover:bg-gray-50 transition py-5 flex flex-col items-center justify-center"
                @click="addOption"
              >
                <div class="text-3xl leading-none text-gray-400">+</div>
                <div class="mt-1 text-xs text-gray-600">Přidat možnost</div>
              </button>
            </div>
          </div>

          <!-- checkbox hint -->
          <div v-if="selectedField.type === 'checkbox'">
            <div class="text-xs text-gray-500">
              Checkbox je jedno pole (true/false). Popisek se bere z “Popisek (label)”.
            </div>
          </div>
        </div>
      </div>

      <div v-else class="rounded-2xl border bg-white p-5 text-sm text-gray-500">
        Vyber pole vlevo nebo přidej nové.
      </div>
    </div>

    <!-- RIGHT: side text (PrimeVue Editor) -->
    <div class="lg:col-span-4">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm font-semibold">Text vedle formuláře</div>

        <div class="inline-flex rounded-lg border overflow-hidden">
          <button
            type="button"
            class="px-3 py-1.5 text-sm"
            :class="
              !local.sideTextEnabled
                ? 'bg-blue-700 text-white'
                : 'bg-white hover:bg-gray-50'
            "
            @click="setSideTextEnabled(false)"
          >
            Ne
          </button>
          <button
            type="button"
            class="px-3 py-1.5 text-sm border-l"
            :class="
              local.sideTextEnabled
                ? 'bg-blue-700 text-white'
                : 'bg-white hover:bg-gray-50'
            "
            @click="setSideTextEnabled(true)"
          >
            Ano
          </button>
        </div>
      </div>

      <div v-if="local.sideTextEnabled" class="rounded-2xl border bg-white p-4">
        <div class="flex items-center justify-between gap-2 mb-3">
          <div class="text-xs text-gray-500">Zobrazí se na webu vedle formuláře.</div>

          <div class="inline-flex rounded-lg border overflow-hidden">
            <button
              type="button"
              class="px-3 py-1.5 text-sm"
              :class="
                local.sideTextPosition === 'left'
                  ? 'bg-blue-700 text-white'
                  : 'bg-white hover:bg-gray-50'
              "
              @click="setSideTextPosition('left')"
              title="Na webu vlevo od formuláře"
            >
              Vlevo
            </button>
            <button
              type="button"
              class="px-3 py-1.5 text-sm border-l"
              :class="
                local.sideTextPosition === 'right'
                  ? 'bg-blue-700 text-white'
                  : 'bg-white hover:bg-gray-50'
              "
              @click="setSideTextPosition('right')"
              title="Na webu vpravo od formuláře"
            >
              Vpravo
            </button>
          </div>
        </div>

        <Editor
          v-model="local.sideText"
          @text-change="commit"
          editorStyle="height: 240px"
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
                {{ mainDesign.font_type_ }}
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
              <!-- Align -->
              <select class="ql-align">
                <option selected></option>
                <!-- default (left) -->
                <option value="center"></option>
                <option value="right"></option>
                <option value="justify"></option>
              </select>

              <button class="ql-code-block"></button>
              <!-- Link -->
              <button class="ql-link"></button>
            </span>
          </template>
        </Editor>
        <div class="text-[11px] text-gray-500 mt-2">
          Ukládá se jako HTML (PrimeVue Editor).
        </div>
      </div>

      <div v-else class="rounded-2xl border bg-white p-4 text-sm text-gray-500">
        Zapni „Ano“, pokud chceš vedle formuláře zobrazit text (např. kontakty, adresu,
        otevírací dobu…).
      </div>
    </div>
  </div>
</template>
