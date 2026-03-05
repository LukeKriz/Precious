<script setup>
import { computed } from "vue";

const props = defineProps({
  cmp: { type: Object, required: true },
  safeUrl: { type: Function, required: true }, // tady jen kvůli konzistenci
});

const d = computed(() => props.cmp?.data ?? {});

const extractYouTubeId = (raw) => {
  const s = String(raw || "").trim();
  if (!s) return "";

  if (/^[a-zA-Z0-9_-]{11}$/.test(s)) return s;

  try {
    const u = new URL(s.startsWith("http") ? s : "https://" + s);

    if (u.hostname.includes("youtu.be")) {
      const id = u.pathname.split("/").filter(Boolean)[0] || "";
      return /^[a-zA-Z0-9_-]{11}$/.test(id) ? id : "";
    }

    const v = u.searchParams.get("v");
    if (v && /^[a-zA-Z0-9_-]{11}$/.test(v)) return v;

    const parts = u.pathname.split("/").filter(Boolean);
    const idxEmbed = parts.indexOf("embed");
    if (
      idxEmbed !== -1 &&
      parts[idxEmbed + 1] &&
      /^[a-zA-Z0-9_-]{11}$/.test(parts[idxEmbed + 1])
    ) {
      return parts[idxEmbed + 1];
    }
    const idxShorts = parts.indexOf("shorts");
    if (
      idxShorts !== -1 &&
      parts[idxShorts + 1] &&
      /^[a-zA-Z0-9_-]{11}$/.test(parts[idxShorts + 1])
    ) {
      return parts[idxShorts + 1];
    }

    return "";
  } catch {
    return "";
  }
};

const data = computed(() => {
  const x = d.value ?? {};

  const url = String(x.url ?? "");
  const id =
    x.videoId && /^[a-zA-Z0-9_-]{11}$/.test(String(x.videoId))
      ? String(x.videoId)
      : extractYouTubeId(url);

  const widthPxRaw = x.widthPx;
  const widthPx =
    widthPxRaw === null || widthPxRaw === undefined || widthPxRaw === ""
      ? null
      : Number(widthPxRaw);
  const hasWidthPx = widthPx !== null && isFinite(widthPx) && widthPx > 0;

  const maxWidthPxRaw = x.maxWidthPx;
  const maxWidthPx =
    maxWidthPxRaw === null || maxWidthPxRaw === undefined || maxWidthPxRaw === ""
      ? null
      : Number(maxWidthPxRaw);
  const hasMaxWidthPx = maxWidthPx !== null && isFinite(maxWidthPx) && maxWidthPx > 0;

  const aspect = ["16:9", "4:3", "1:1"].includes(x.aspect) ? x.aspect : "16:9";
  const align = ["left", "center", "right"].includes(x.align) ? x.align : "center";

  const start =
    x.start === null || x.start === undefined || x.start === "" ? null : Number(x.start);
  const startSec =
    start !== null && isFinite(start) && start > 0 ? Math.floor(start) : null;

  // padding-top for responsive wrapper
  const pad = aspect === "4:3" ? "75%" : aspect === "1:1" ? "100%" : "56.25%";

  // privacy enhanced embed domain
  const qs = new URLSearchParams();
  if (startSec !== null) qs.set("start", String(startSec));
  // (volitelně) qs.set("rel","0"); apod.
  const src = id
    ? `https://www.youtube-nocookie.com/embed/${id}${
        qs.toString() ? "?" + qs.toString() : ""
      }`
    : "";

  return {
    id,
    src,
    pad,
    align,
    hasWidthPx,
    widthPx: hasWidthPx ? Math.max(0, Math.min(2000, Math.round(widthPx))) : null,
    hasMaxWidthPx,
    maxWidthPx: hasMaxWidthPx
      ? Math.max(0, Math.min(2200, Math.round(maxWidthPx)))
      : null,
  };
});

const alignClass = computed(() => {
  if (data.value.align === "left") return "justify-start";
  if (data.value.align === "right") return "justify-end";
  return "justify-center";
});
</script>

<template>
  <div class="w-full">
    <div v-if="data.id" class="w-full flex" :class="alignClass">
      <div
        class="w-full"
        :style="{
          width: data.hasWidthPx ? data.widthPx + 'px' : '100%',
          maxWidth: data.hasMaxWidthPx ? data.maxWidthPx + 'px' : '100%',
        }"
      >
        <div
          class="relative w-full overflow-hidden rounded-2xl"
          :style="{ paddingTop: data.pad }"
        >
          <iframe
            class="absolute inset-0 w-full h-full"
            :src="data.src"
            title="YouTube video"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
          />
        </div>
      </div>
    </div>

    <div v-else class="text-sm text-gray-500 italic">(YouTube video není nastaveno)</div>
  </div>
</template>
