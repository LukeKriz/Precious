<script setup>
import { computed, ref } from "vue";
import ContentModal from "../modals/ContentModal.vue";
import ModalContentRenderer from "./ModalContentRenderer.vue";

const props = defineProps({
  cmp: { type: Object, required: true },
  safeUrl: { type: Function, required: true },
  mainDesign: { type: Object, default: () => ({}) },
});

const open = ref(false);
const d = computed(() => props.cmp?.data ?? {});

const data = computed(() => {
  const x = d.value ?? {};

  const image = x.image ?? null;

  // ✅ widthPx (preferred), fallback to legacy widthPercent
  let widthPx = x.widthPx;
  if (widthPx === "" || widthPx === undefined) widthPx = null;
  widthPx = widthPx === null ? null : Number(widthPx);

  const hasWidthPx = widthPx !== null && isFinite(widthPx) && widthPx > 0;

  const widthPercent = Math.max(10, Math.min(100, Number(x.widthPercent ?? 100) || 100));

  const maxHeight = Math.max(80, Math.min(1200, Number(x.maxHeight ?? 420) || 420));

  const fit = x.fit === "cover" ? "cover" : "contain";

  const align = ["left", "center", "right"].includes(x.align) ? x.align : "center";

  const c = x.click ?? null;
  const enabled = !!c?.enabled;

  const action = c?.action === "modal" ? "modal" : "link";
  const url = props.safeUrl(typeof c?.url === "string" ? c.url : "");
  const modalNode = c?.modalComponent ?? null;

  const finalAction = enabled && action === "modal" && !modalNode ? "link" : action;

  return {
    image,
    hasWidthPx,
    widthPx: hasWidthPx ? Math.max(20, Math.min(2000, Math.round(widthPx))) : null,
    widthPercent, // legacy fallback
    maxHeight,
    fit,
    align,

    clickEnabled: enabled,
    clickAction: finalAction,
    clickUrl: url,
    clickModalComponent: modalNode,
  };
});

const normalizeUrl = (u) => String(u ?? "").trim();

const openLink = (url) => {
  const u = normalizeUrl(url);
  if (!u) return;

  if (u.startsWith("/")) {
    window.location.href = u;
    return;
  }
  if (/^(https?:\/\/|mailto:|tel:)/i.test(u)) {
    window.location.href = u;
    return;
  }
  window.location.href = "https://" + u;
};

const clickable = computed(() => {
  if (!data.value.clickEnabled) return false;
  if (data.value.clickAction === "modal") return !!data.value.clickModalComponent;
  return !!data.value.clickUrl;
});

const onClick = () => {
  if (!clickable.value) return;

  if (data.value.clickAction === "link") {
    openLink(data.value.clickUrl);
    return;
  }
  open.value = true;
};

const alignClass = computed(() => {
  if (data.value.align === "left") return "justify-start";
  if (data.value.align === "right") return "justify-end";
  return "justify-center";
});
</script>

<template>
  <div class="w-full">
    <div
      v-if="data.image"
      class="w-full flex"
      :class="[alignClass, clickable ? 'cursor-pointer' : '']"
      @click="clickable ? onClick() : null"
    >
      <img
        :src="data.image"
        alt=""
        style="max-width: 100%; height: auto"
        :style="{
          width: data.hasWidthPx ? data.widthPx + 'px' : data.widthPercent + '%',
          maxHeight: data.maxHeight + 'px',
          objectFit: data.fit,
        }"
      />
    </div>

    <div v-else class="text-sm text-gray-500 italic">(Obrázek není nastaven)</div>

    <ContentModal v-model:open="open" title="">
      <ModalContentRenderer :node="data.clickModalComponent" :mainDesign="mainDesign" />
    </ContentModal>
  </div>
</template>
