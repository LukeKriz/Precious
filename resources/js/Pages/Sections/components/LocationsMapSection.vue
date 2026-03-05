<script setup>
import { computed, ref, watch } from "vue";
import ContentModal from "../modals/ContentModal.vue";
import ModalContentRenderer from "./ModalContentRenderer.vue";

const props = defineProps({
  cmp: { type: Object, required: true },
  mainDesign: { type: Object, default: () => ({}) },
});

const d = computed(() => props.cmp?.data ?? {});

const title = computed(() => d.value.title ?? "");
const subtitle = computed(() => d.value.subtitle ?? "");

// globální fallback (když location nemá mapSide)
const globalMapSide = computed(() => (d.value.mapSide === "right" ? "right" : "left"));

// pokud ještě někde používáš
const cardTitleEnabled = computed(() => d.value.cardTitleEnabled !== false);

const locations = computed(() =>
  Array.isArray(d.value.locations) ? d.value.locations : []
);

const activeId = ref(null);
watch(
  locations,
  (arr) => {
    if (!arr?.length) {
      activeId.value = null;
      return;
    }
    if (!activeId.value || !arr.some((x) => x.id === activeId.value)) {
      activeId.value = arr[0].id;
    }
  },
  { immediate: true }
);

const activeLoc = computed(() => {
  const arr = locations.value || [];
  return arr.find((l) => l.id === activeId.value) ?? arr[0] ?? null;
});

const locMapSide = computed(() => {
  const s = activeLoc.value?.mapSide;
  if (s === "right") return "right";
  if (s === "left") return "left";
  return globalMapSide.value;
});

const btn = computed(() => activeLoc.value?.button ?? {});
const buttonEnabled = computed(() => !!btn.value?.enabled);
const buttonText = computed(() => String(btn.value?.text ?? "Napište nám"));
const buttonAction = computed(() => (btn.value?.action === "link" ? "link" : "modal"));
const buttonUrl = computed(() => String(btn.value?.url ?? ""));
const modalComponent = computed(() => btn.value?.modalComponent ?? null);

// modal open state
const open = ref(false);

const normalizeUrl = (u) => String(u ?? "").trim();

const openLink = (url) => {
  const u = normalizeUrl(url);
  if (!u) return;

  // /route -> same tab
  if (u.startsWith("/")) {
    window.location.href = u;
    return;
  }

  // mailto:, tel:, https:// -> use as-is
  if (/^(https?:\/\/|mailto:|tel:)/i.test(u)) {
    window.location.href = u;
    return;
  }

  // fallback: treat as https
  window.location.href = "https://" + u;
};

const onButtonClick = () => {
  if (!buttonEnabled.value) return;

  if (buttonAction.value === "link") {
    openLink(buttonUrl.value);
    return;
  }

  // modal
  if (!modalComponent.value) return;
  open.value = true;
};
</script>

<template>
  <div class="w-full">
    <!-- tabs -->
    <div v-if="locations.length" class="flex gap-2 justify-center mb-6 flex-wrap">
      <button
        v-for="l in locations"
        :key="l.id"
        type="button"
        class="px-6 py-3 rounded-t-xl font-bold"
        :class="
          activeLoc?.id === l.id
            ? 'bg-blue-800 text-white'
            : 'bg-slate-300 text-white/90 hover:bg-slate-400'
        "
        @click="activeId = l.id"
      >
        {{ l.tabLabel || "Pobočka" }}
      </button>
    </div>

    <div class="rounded-3xl bg-white shadow-sm overflow-hidden">
      <div class="grid grid-cols-1 lg:grid-cols-2">
        <!-- map -->
        <div
          class="min-h-[420px] bg-gray-100"
          :class="locMapSide === 'right' ? 'lg:order-2' : ''"
        >
          <iframe
            v-if="activeLoc?.mapEmbedUrl"
            :src="activeLoc.mapEmbedUrl"
            class="w-full h-full min-h-[420px]"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          />
          <div
            v-else
            class="w-full h-full min-h-[420px] grid place-items-center text-gray-500"
          >
            Mapa není nastavena
          </div>
        </div>

        <!-- info -->
        <div class="p-10 flex flex-col justify-center">
          <div v-if="title" class="text-4xl font-extrabold text-blue-900">
            {{ title }}
          </div>
          <div v-if="subtitle" class="mt-2 text-gray-600">
            {{ subtitle }}
          </div>

          <!-- ✅ nově: HTML karta pobočky -->
          <div class="mt-10 text-blue-900">
            <div
              v-if="activeLoc?.cardHtml"
              class="prose prose-sm max-w-none"
              v-html="activeLoc.cardHtml"
            />
            <!-- fallback, kdyby cardHtml nebylo -->
            <div v-else class="space-y-2">
              <div v-if="cardTitleEnabled && activeLoc?.name" class="font-bold">
                {{ activeLoc.name }}
              </div>
              <div v-if="activeLoc?.address" class="text-sm">
                {{ activeLoc.address }}
              </div>
              <div v-if="activeLoc?.email" class="mt-6 font-bold">
                <a :href="`mailto:${activeLoc.email}`">{{ activeLoc.email }}</a>
              </div>
              <div v-if="activeLoc?.phone" class="font-bold">
                <a :href="`tel:${activeLoc.phone}`">{{ activeLoc.phone }}</a>
              </div>
            </div>
          </div>

          <!-- button -->
          <div v-if="buttonEnabled" class="mt-10">
            <button type="button" class="btn-custom" @click="onButtonClick">
              {{ buttonText }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- modal -->
    <ContentModal v-model:open="open" :title="buttonText">
      <ModalContentRenderer :node="modalComponent" :mainDesign="mainDesign" />
    </ContentModal>
  </div>
</template>
