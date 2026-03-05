<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  cmp: { type: Object, required: true },
  safeUrl: { type: Function, required: true },
});

const data = computed(() => {
  const d = props.cmp?.data ?? {};
  const html = d.html ?? "";
  const fallbackText = d.text ?? "";
  const content = html && String(html).trim() ? html : fallbackText;

  return {
    html: content,
    buttonEnabled: !!d.buttonEnabled,
    buttonText: String(d.buttonText ?? "").trim(),
    buttonUrl: props.safeUrl(d.buttonUrl ?? ""),
  };
});
</script>

<template>
  <div class="w-full prose prose-sm max-w-none">
    <div v-html="data.html"></div>

    <div v-if="data.buttonEnabled && data.buttonUrl" class="mt-6">
      <Link :href="data.buttonUrl" class="btn-custom inline-flex">
        {{ data.buttonText || "Pokračovat" }}
      </Link>
    </div>
  </div>
</template>
