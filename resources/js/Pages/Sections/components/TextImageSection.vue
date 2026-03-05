<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";

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

  // ✅ nový tvar (preferuj)
  const btn = x.button ?? null;

  const enabled = btn?.enabled !== undefined ? !!btn.enabled : !!x.buttonEnabled;

  const text =
    typeof btn?.text === "string" ? btn.text : String(x.buttonText ?? "").trim();

  const action =
    btn?.action === "link"
      ? "link"
      : btn?.action === "modal"
      ? "modal"
      : x.buttonAction === "link"
      ? "link"
      : x.buttonAction === "modal"
      ? "modal"
      : ""; // auto níže

  const urlRaw = typeof btn?.url === "string" ? btn.url : String(x.buttonUrl ?? "");

  const url = props.safeUrl(urlRaw);

  const modalNode =
    btn?.modalComponent ?? x.buttonModalComponent ?? x.modalComponent ?? null;

  // ✅ auto fallback: když není action, rozhodni podle dat
  const finalAction = action || (modalNode ? "modal" : url ? "link" : "link");

  return {
    html: x.content ?? x.text ?? "",
    layout: x.layout ?? "text_left",
    image: x.image ?? null,

    buttonEnabled: enabled,
    buttonText: text,
    buttonAction: finalAction,
    buttonUrl: url,
    buttonModalComponent: modalNode,
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

const onButtonClick = () => {
  if (!data.value.buttonEnabled) return;

  if (data.value.buttonAction === "link") {
    openLink(data.value.buttonUrl);
    return;
  }

  if (!data.value.buttonModalComponent) return;
  open.value = true;
};
</script>

<template>
  <div class="w-full">
    <div
      class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center"
      :class="data.layout === 'text_right' ? 'md:[&>div:first-child]:order-2' : ''"
    >
      <div class="prose prose-sm max-w-none">
        <div v-html="data.html"></div>

        <!-- ✅ BUTTON -->
        <div v-if="data.buttonEnabled" class="mt-6">
          <!-- LINK -->
          <Link
            v-if="data.buttonAction === 'link' && data.buttonUrl"
            :href="data.buttonUrl"
            class="btn-custom inline-flex"
          >
            {{ data.buttonText || "Pokračovat" }}
          </Link>

          <!-- MODAL -->
          <button
            v-else-if="data.buttonAction === 'modal' && data.buttonModalComponent"
            type="button"
            class="btn-custom inline-flex"
            @click="onButtonClick"
          >
            {{ data.buttonText || "Pokračovat" }}
          </button>
        </div>
      </div>

      <div v-if="data.image" class="rounded-2xl overflow-hidden">
        <img :src="data.image" class="w-full h-auto object-cover" alt="" />
      </div>
    </div>

    <!-- ✅ MODAL -->
    <ContentModal v-model:open="open" :title="data.buttonText || 'Pokračovat'">
      <ModalContentRenderer :node="data.buttonModalComponent" :mainDesign="mainDesign" />
    </ContentModal>
  </div>
</template>
