<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import Carousel from "primevue/carousel";

import ContentModal from "../modals/ContentModal.vue";
import ModalContentRenderer from "./ModalContentRenderer.vue";

const props = defineProps({
  cmp: { type: Object, required: true },
  safeUrl: { type: Function, required: true },
  mainDesign: { type: Object, default: () => ({}) },
});

const mainDesign = computed(() => props.mainDesign || {});
// ---------- data ----------
const data = computed(() => {
  const d = props.cmp?.data ?? {};
  const cards = Array.isArray(d.cards) ? d.cards : [];
  const previewCount = Math.max(1, Number(d.previewCount ?? 3));

  const s = d.styles ?? {};
  const styles = {
    imageSize: Math.max(24, Number(s?.imageSize ?? 56)),
    headingSize: Math.max(10, Number(s?.headingSize ?? 22)),
    textSize: Math.max(10, Number(s?.textSize ?? 14)),
    textColor: typeof s?.textColor === "string" ? s.textColor.trim() : "",
    bgColor: typeof s?.bgColor === "string" ? s.bgColor.trim() : "",

    borderEnabled: s?.borderEnabled === false ? false : true,
    borderWidth: Math.max(0, Number(s?.borderWidth ?? 1)),
    borderStyle: typeof s?.borderStyle === "string" ? s.borderStyle : "solid",
    borderColor:
      typeof s?.borderColor === "string" && s.borderColor.trim()
        ? s.borderColor.trim()
        : "rgba(15, 23, 42, 0.08)",
    borderRadius: Math.max(0, Number(s?.borderRadius ?? 18)),
  };

  return { cards, previewCount, styles };
});

const cardsResponsiveOptions = [
  { breakpoint: "1280px", numVisible: 4, numScroll: 1 },
  { breakpoint: "1024px", numVisible: 3, numScroll: 1 },
  { breakpoint: "768px", numVisible: 2, numScroll: 1 },
  { breakpoint: "640px", numVisible: 1, numScroll: 1 },
];

// ---------- helpers ----------
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

const shouldShowCarouselArrows = (total, visible) => {
  const t = Math.max(0, Number(total || 0));
  const v = Math.max(1, Number(visible || 1));
  return t > v;
};

const isClickable = (c) => !!c?.clickable;
const isLinkAction = (c) => c?.action !== "modal";
const isModalAction = (c) => c?.action === "modal";

// ---------- modal state ----------
const open = ref(false);
const activeModalTitle = ref("");
const activeModalNode = ref(null);

const openModalForCard = (c) => {
  const node = c?.modalComponent ?? null;
  if (!node) return;

  activeModalNode.value = node;
  activeModalTitle.value = String(c?.heading || c?.buttonText || "Detail");
  open.value = true;
};

// ---------- click handlers ----------
const onCardClick = (c) => {
  if (!isClickable(c)) return;

  // link
  if (isLinkAction(c)) {
    const u = props.safeUrl(c?.url);
    if (!u) return;
    openLink(u);
    return;
  }

  // modal
  if (isModalAction(c)) {
    openModalForCard(c);
  }
};

const onButtonClick = (c) => {
  if (!c?.buttonEnabled) return;

  // link
  if (c.action === "link") {
    const u = props.safeUrl(c?.url);
    if (!u) return;
    openLink(u);
    return;
  }

  // modal
  if (c.action === "modal") {
    openModalForCard(c);
  }
};
</script>

<template>
  <div class="w-full">
    <div class="carousel-outer">
      <div class="carousel-inner">
        <Carousel
          class="cards-carousel"
          :value="data.cards"
          :numVisible="data.previewCount"
          :numScroll="1"
          :responsiveOptions="cardsResponsiveOptions"
          :circular="false"
          :showIndicators="false"
          :showNavigators="shouldShowCarouselArrows(data.cards.length, data.previewCount)"
        >
          <template #item="{ data: c }">
            <div class="px-2 w-full">
              <!--
                Link wrapper použijeme jen když je clickable + link + safeUrl vrátí URL.
                Jinak je wrapper div a klik řešíme ručně (kvůli modalu).
              -->
              <component
                :is="
                  isClickable(c) && isLinkAction(c) && props.safeUrl(c?.url)
                    ? Link
                    : 'div'
                "
                :href="
                  isClickable(c) && isLinkAction(c) && props.safeUrl(c?.url)
                    ? props.safeUrl(c.url)
                    : undefined
                "
                class="card-tile group block h-full"
                :class="isClickable(c) ? 'cursor-pointer' : ''"
                :style="{
                  backgroundColor: data.styles.bgColor || undefined,
                  color: data.styles.textColor || undefined,
                  border: data.styles.borderEnabled
                    ? `${data.styles.borderWidth}px ${data.styles.borderStyle} ${data.styles.borderColor}`
                    : 'none',
                  borderRadius: data.styles.borderRadius + 'px',
                }"
                @click="
                  isClickable(c) && !(isLinkAction(c) && props.safeUrl(c?.url))
                    ? onCardClick(c)
                    : null
                "
              >
                <div
                  class="card-icon-wrap"
                  :style="{
                    width: data.styles.imageSize + 'px',
                    height: data.styles.imageSize + 'px',
                    marginLeft: 'auto',
                    marginRight: 'auto',
                  }"
                >
                  <img
                    v-if="c.image"
                    :src="c.image"
                    :style="{
                      width: data.styles.imageSize + 'px',
                      height: data.styles.imageSize + 'px',
                    }"
                    class="object-contain"
                    alt=""
                    draggable="false"
                  />
                </div>

                <div
                  class="card-title"
                  :style="{ fontSize: data.styles.headingSize + 'px' }"
                >
                  {{ c.heading || "Bez nadpisu" }}
                </div>

                <div class="card-text" :style="{ fontSize: data.styles.textSize + 'px' }">
                  {{ c.text || "" }}
                </div>

                <!-- BUTTON (vždy funkční i když je karta clickable) -->
                <div v-if="c.buttonEnabled && c.buttonText" class="mt-4">
                  <button
                    type="button"
                    class="card-btn btn-custom"
                    @click.stop.prevent="onButtonClick(c)"
                  >
                    {{ c.buttonText }}
                  </button>
                </div>
              </component>
            </div>
          </template>
        </Carousel>
      </div>
    </div>

    <!-- MODAL -->
    <ContentModal v-model:open="open" :title="activeModalTitle">
      <ModalContentRenderer :node="activeModalNode" :mainDesign="mainDesign" />
    </ContentModal>
  </div>
</template>

<style scoped>
.carousel-outer {
  position: relative;
  overflow: visible;
}
.carousel-inner {
  overflow: hidden;
}

:deep(.p-carousel-prev-button),
:deep(.p-carousel-next-button) {
  position: absolute !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  z-index: 20 !important;

  background: white !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  border-radius: 9999px !important;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12) !important;
}

:deep(.p-carousel-prev-button) {
  left: -36px !important;
}
:deep(.p-carousel-next-button) {
  right: -36px !important;
}

@media (max-width: 640px) {
  :deep(.p-carousel-prev-button) {
    left: 8px !important;
  }
  :deep(.p-carousel-next-button) {
    right: 8px !important;
  }
}

.card-tile {
  background: #fff;
  padding: 26px 22px;
  text-align: center;
  height: 100%;
  transition: transform 0.15s ease;
}
.card-tile:hover {
  transform: translateY(-1px);
}

.card-icon-wrap {
  margin: 0 auto 16px auto;
  display: grid;
  place-items: center;
  border-radius: 12px;
}

.card-title {
  font-weight: 800;
  line-height: 1.15;
}
.card-text {
  margin-top: 10px;
  font-weight: 600;
  opacity: 0.9;
}
.card-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 18px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 14px;
  line-height: 1;
}
</style>
