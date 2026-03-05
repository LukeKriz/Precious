<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["update:modelValue"]);

const patch = (p) => emit("update:modelValue", { ...(props.modelValue || {}), ...p });

// =====================
// URL
// =====================
const url = computed({
  get: () => props.modelValue?.url ?? "",
  set: (v) => patch({ url: String(v ?? "") }),
});

// =====================
// ALIGN
// =====================
const align = computed({
  get: () => {
    const a = String(props.modelValue?.align ?? "center");
    return ["left", "center", "right"].includes(a) ? a : "center";
  },
  set: (v) =>
    patch({
      align: ["left", "center", "right"].includes(v) ? v : "center",
    }),
});

// =====================
// WIDTH (PX) - NO LIMITS
// =====================
// input vrací string; ukládáme number nebo null
const widthPx = computed({
  get: () => {
    const v = props.modelValue?.widthPx;
    return v === null || v === undefined ? "" : String(v);
  },
  set: (v) => {
    if (v === "" || v === null || v === undefined) {
      patch({ widthPx: null });
      return;
    }
    const n = Number(v);
    if (!Number.isFinite(n)) return;
    patch({ widthPx: n });
  },
});

const maxWidthPx = computed({
  get: () => {
    const v = props.modelValue?.maxWidthPx;
    return v === null || v === undefined ? "" : String(v);
  },
  set: (v) => {
    if (v === "" || v === null || v === undefined) {
      patch({ maxWidthPx: null });
      return;
    }
    const n = Number(v);
    if (!Number.isFinite(n)) return;
    patch({ maxWidthPx: n });
  },
});

// =====================
// ASPECT
// =====================
const aspect = computed({
  get: () =>
    ["16:9", "4:3", "1:1"].includes(props.modelValue?.aspect)
      ? props.modelValue.aspect
      : "16:9",
  set: (v) =>
    patch({
      aspect: ["16:9", "4:3", "1:1"].includes(v) ? v : "16:9",
    }),
});

// =====================
// YouTube URL -> videoId
// =====================
const extractYouTubeId = (raw) => {
  const s = String(raw || "").trim();
  if (!s) return "";

  // already looks like id
  if (/^[a-zA-Z0-9_-]{11}$/.test(s)) return s;

  try {
    const u = new URL(s.startsWith("http") ? s : "https://" + s);

    // youtu.be/<id>
    if (u.hostname.includes("youtu.be")) {
      const id = u.pathname.split("/").filter(Boolean)[0] || "";
      return /^[a-zA-Z0-9_-]{11}$/.test(id) ? id : "";
    }

    // youtube.com/watch?v=<id>
    const v = u.searchParams.get("v");
    if (v && /^[a-zA-Z0-9_-]{11}$/.test(v)) return v;

    // youtube.com/embed/<id> | /shorts/<id>
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

const videoId = computed(() => extractYouTubeId(url.value));

// =====================
// START (seconds) - keep mild sanity, but no hard min/max during typing
// =====================
const start = computed({
  get: () => {
    const v = props.modelValue?.start;
    return v === null || v === undefined ? "" : String(v);
  },
  set: (v) => {
    if (v === "" || v === null || v === undefined) {
      patch({ start: null });
      return;
    }
    const n = Number(v);
    if (!Number.isFinite(n)) return;
    patch({ start: Math.floor(n) });
  },
});

// ulož si videoId do dat automaticky (užitečné)
const syncId = () => patch({ videoId: videoId.value });
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="text-sm font-semibold mb-2">YouTube URL</div>

      <input
        type="text"
        class="w-full rounded border px-3 py-2"
        placeholder="https://www.youtube.com/watch?v=..."
        v-model="url"
        @blur="syncId"
      />

      <div class="text-xs mt-2" :class="videoId ? 'text-green-700' : 'text-gray-500'">
        <template v-if="videoId">
          ✓ Video ID: <b>{{ videoId }}</b>
        </template>
        <template v-else>
          Vlož platnou URL (watch?v= / youtu.be / shorts / embed).
        </template>
      </div>
    </div>

    <!-- SETTINGS -->
    <div class="col-span-12 md:col-span-6 rounded-xl border p-4 bg-white">
      <div class="text-base font-semibold mb-3">Velikost</div>

      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="text-sm font-medium block mb-2">Šířka (px)</label>
          <input
            type="number"
            class="w-full rounded border px-3 py-2"
            placeholder="Auto"
            v-model="widthPx"
          />
          <div class="text-xs text-gray-500 mt-1">Prázdné = auto (max 100%).</div>
        </div>

        <div>
          <label class="text-sm font-medium block mb-2"
            >Max. šířka (px) – volitelné</label
          >
          <input
            type="number"
            class="w-full rounded border px-3 py-2"
            placeholder="Bez limitu"
            v-model="maxWidthPx"
          />
        </div>

        <div>
          <label class="text-sm font-medium block mb-2">Poměr stran</label>
          <select class="w-full rounded border px-3 py-2" v-model="aspect">
            <option value="16:9">16:9</option>
            <option value="4:3">4:3</option>
            <option value="1:1">1:1</option>
          </select>
        </div>

        <div>
          <label class="text-sm font-medium block mb-2"
            >Start (sekundy) – volitelné</label
          >
          <input
            type="number"
            class="w-full rounded border px-3 py-2"
            placeholder="např. 30"
            v-model="start"
          />
        </div>

        <div>
          <label class="text-sm font-medium block mb-2">Zarovnání</label>
          <select class="w-full rounded border px-3 py-2" v-model="align">
            <option value="left">Vlevo</option>
            <option value="center">Na střed</option>
            <option value="right">Vpravo</option>
          </select>
        </div>
      </div>
    </div>

    <!-- PREVIEW -->
    <div class="col-span-12 md:col-span-6 rounded-xl border p-4 bg-white">
      <div class="text-base font-semibold mb-3">Náhled</div>

      <div class="text-xs text-gray-500 mb-3">
        Náhled je jen orientační, na webu se iframe chová responsivně.
      </div>

      <div class="border rounded-xl bg-gray-50 p-3">
        <div v-if="videoId" class="w-full">
          <div class="relative w-full" style="padding-top: 56.25%">
            <iframe
              class="absolute inset-0 w-full h-full rounded-lg"
              :src="`https://www.youtube-nocookie.com/embed/${videoId}`"
              title="YouTube video"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
            />
          </div>
        </div>

        <div v-else class="text-sm text-gray-400 italic">(Zadej URL pro náhled)</div>
      </div>
    </div>
  </div>
</template>
