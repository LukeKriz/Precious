<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["update:modelValue"]);

const accSelectedId = ref(null);

const patch = (p) => emit("update:modelValue", { ...props.modelValue, ...p });

const previewCount = computed({
  get: () => Number(props.modelValue?.previewCount ?? 6),
  set: (v) => patch({ previewCount: Math.max(1, Number(v || 1)) }),
});

const items = computed({
  get: () => (Array.isArray(props.modelValue?.items) ? props.modelValue.items : []),
  set: (v) => patch({ items: v }),
});

const genAccId = () => `acc_${Date.now()}_${Math.random().toString(16).slice(2)}`;

watch(
  () => items.value.length,
  () => {
    if (!accSelectedId.value && items.value[0]?.id)
      accSelectedId.value = items.value[0].id;
    if (
      accSelectedId.value &&
      !items.value.find((x) => String(x.id) === String(accSelectedId.value))
    ) {
      accSelectedId.value = items.value[0]?.id ?? null;
    }
  },
  { immediate: true }
);

const selected = computed(
  () => items.value.find((x) => String(x.id) === String(accSelectedId.value)) || null
);

const addItem = () => {
  const id = genAccId();
  items.value = [...items.value, { id, heading: "", text: "" }];
  accSelectedId.value = id;
};

const removeItem = (id) => {
  items.value = items.value.filter((x) => String(x.id) !== String(id));
  if (String(accSelectedId.value) === String(id)) accSelectedId.value = null;
};

const patchSelected = (p) => {
  if (!selected.value) return;
  items.value = items.value.map((x) => (x.id === selected.value.id ? { ...x, ...p } : x));
};
</script>

<template>
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 md:col-span-6">
      <label class="text-sm font-medium block mb-2">Počet rozkliků na webu</label>
      <input
        class="w-full rounded border px-3 py-2"
        type="number"
        min="1"
        v-model.number="previewCount"
      />
    </div>

    <div class="col-span-12">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <button
          v-for="it in items"
          :key="it.id"
          type="button"
          class="text-left border rounded-xl p-4 hover:bg-gray-50 transition"
          :class="accSelectedId === it.id ? 'ring-2 ring-blue-500' : ''"
          @click="accSelectedId = it.id"
        >
          <div class="font-semibold truncate">{{ it.heading || "Bez nadpisu" }}</div>
          <div class="text-xs text-gray-500 mt-1 line-clamp-2">
            {{ it.text || "Bez textu" }}
          </div>
        </button>

        <button
          type="button"
          class="border-2 border-dashed rounded-xl p-4 hover:bg-gray-50 transition flex items-center justify-center text-gray-500"
          @click="addItem"
        >
          <div class="text-center">
            <div class="text-3xl leading-none">+</div>
            <div class="text-sm mt-1">Přidat</div>
          </div>
        </button>
      </div>

      <div v-if="selected" class="mt-4 border rounded-xl p-4 bg-gray-50">
        <div class="flex items-start justify-between gap-3">
          <div class="font-semibold">Editace položky</div>
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-600"
            @click="removeItem(selected.id)"
          >
            Smazat
          </button>
        </div>

        <div class="mt-4 space-y-3">
          <div>
            <label class="text-sm font-medium block mb-2">Nadpis</label>
            <input
              class="w-full rounded border px-3 py-2"
              type="text"
              :value="selected.heading"
              @input="patchSelected({ heading: $event.target.value })"
              placeholder="Nadpis…"
            />
          </div>

          <div>
            <label class="text-sm font-medium block mb-2">Text</label>
            <textarea
              class="w-full rounded border px-3 py-2 resize-none"
              rows="6"
              :value="selected.text"
              @input="patchSelected({ text: $event.target.value })"
              placeholder="Text…"
            />
          </div>
        </div>
      </div>

      <div v-else class="mt-3 text-sm text-gray-500">
        Klikni na položku pro editaci nebo přidej novou.
      </div>
    </div>
  </div>
</template>
