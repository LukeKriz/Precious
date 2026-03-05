<script setup>
import { computed, ref, watch } from "vue";
import ComponentActionsMenu from "./ComponentActionsMenu.vue";

const props = defineProps({
  secId: { type: [String, Number], required: true },
  cmp: { type: Object, required: true },

  isPreview: { type: Boolean, required: true },
  dragState: { type: Object, required: true },

  openMenuKey: { type: [String, null], default: null },
  menuKeyFn: { type: Function, required: true },

  componentTypes: { type: Array, required: true }, // (nepoužito zde, jen kdybys chtěl)
  typeLabel: { type: Function, required: true },

  alignClass: { type: Function, required: true },
  renderText: { type: Function, required: true },
  renderTextImage: { type: Function, required: true },
  renderImage: { type: Function, required: true },
  renderGallery: { type: Function, required: true },
  renderCards: { type: Function, required: true },
  renderAccordion: { type: Function, required: true },
  renderForm: { type: Function, required: true },
  renderLocationsMap: { type: Function, required: true },
  renderAdvancedGallery: { type: Function, required: true },
  renderYouTube: { type: Function, required: true },
});

const emit = defineEmits([
  "edit",
  "toggleMenu",
  "closeMenus",
  "openChangeType",
  "openDeleteComponent",
  "dragStart",
  "dragOver",
  "drop",
  "dragEnd",
]);

const isOver = () =>
  props.dragState?.sectionId === props.secId && props.dragState?.overId === props.cmp.id;

const isMenuOpen = () => props.openMenuKey === props.menuKeyFn(props.secId, props.cmp.id);

// ==============================
// ADVANCED GALLERY (preview UI)
// ==============================
const agActiveCatId = ref(null);

// normalized data from parent renderer
const agData = computed(() => {
  try {
    const r = props.renderAdvancedGallery?.(props.cmp) ?? {};
    return {
      previewCount: Math.max(1, Number(r.previewCount ?? 6)),
      categories: Array.isArray(r.categories) ? r.categories : [],
    };
  } catch {
    return { previewCount: 6, categories: [] };
  }
});

// keep selected category valid (when data changes)
watch(
  () => agData.value.categories.map((c) => String(c?.id ?? "")).join("|"),
  () => {
    const cats = agData.value.categories || [];
    if (!cats.length) {
      agActiveCatId.value = null;
      return;
    }

    if (
      !agActiveCatId.value ||
      !cats.find((c) => String(c.id) === String(agActiveCatId.value))
    ) {
      agActiveCatId.value = cats[0].id;
    }
  },
  { immediate: true }
);

const agActiveCategory = computed(() => {
  const cats = agData.value.categories || [];
  return cats.find((c) => String(c.id) === String(agActiveCatId.value)) || null;
});

const agShownGalleries = computed(() => {
  const cat = agActiveCategory.value;
  const gals = Array.isArray(cat?.galleries) ? cat.galleries : [];
  return gals.slice(0, agData.value.previewCount);
});
</script>

<template>
  <div
    class="relative bg-white"
    style="overflow: visible"
    :draggable="isPreview"
    @dragstart="emit('dragStart', secId, cmp.id)"
    @dragover="(e) => emit('dragOver', e, secId, cmp.id)"
    @drop="emit('drop', secId)"
    @dragend="emit('dragEnd')"
    :class="isOver() ? 'ring-2 ring-blue-400 rounded-xl' : ''"
  >
    <!-- RIGHT PADDING so buttons never overlap content -->
    <div class="p-6 pr-24">
      <!-- TEXT -->
      <div
        v-if="cmp.type === 'text'"
        @click="emit('edit')"
        class="cursor-pointer border rounded-xl p-4 hover:bg-gray-50 transition"
      >
        <div v-if="!renderText(cmp).html" class="mb-3 text-xs text-gray-500 italic">
          Pro editaci textu klikněte zde.
        </div>
        <div v-else class="prose prose-sm max-w-none">
          <div v-html="renderText(cmp).html"></div>
        </div>
      </div>

      <!-- TEXT + IMAGE -->
      <div v-else-if="cmp.type === 'text_image'">
        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch"
          :class="
            renderTextImage(cmp).layout === 'text_right'
              ? 'md:[&>div:first-child]:order-2'
              : ''
          "
        >
          <!-- TEXT BOX -->
          <div
            class="cursor-pointer border rounded-xl p-6 hover:bg-gray-50 transition prose prose-sm max-w-none h-full flex flex-col justify-center"
            @click="emit('edit')"
          >
            <div
              v-if="renderTextImage(cmp).html"
              v-html="renderTextImage(cmp).html"
            ></div>

            <div v-else class="text-xs text-gray-500 italic">
              Pro editaci textu a obrázku klikněte zde.
            </div>
          </div>

          <!-- IMAGE BOX -->
          <div
            class="cursor-pointer border rounded-xl p-6 hover:bg-gray-50 transition h-full flex items-center justify-center"
            @click="emit('edit')"
          >
            <div
              v-if="renderTextImage(cmp).image"
              class="w-full h-full rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center"
            >
              <img
                :src="renderTextImage(cmp).image"
                class="max-h-full max-w-full object-contain"
                alt=""
              />
            </div>

            <div v-else class="text-xs text-gray-500 italic">
              Pro editaci textu a obrázku klikněte zde.
            </div>
          </div>
        </div>
      </div>

      <!-- IMAGE -->
      <div v-else-if="cmp.type === 'image'">
        <div
          class="cursor-pointer border rounded-xl p-6 hover:bg-gray-50 transition flex items-center justify-center"
          @click="emit('edit')"
        >
          <div v-if="renderImage(cmp).image" class="w-full flex justify-center">
            <img
              :src="renderImage(cmp).image"
              alt=""
              :style="{
                width: (renderImage(cmp).widthPercent || 100) + '%',
                maxHeight: (renderImage(cmp).maxHeight || 420) + 'px',
                objectFit: renderImage(cmp).fit === 'cover' ? 'cover' : 'contain',
              }"
            />
          </div>

          <div v-else class="text-xs text-gray-500 italic">
            Pro editaci obrázku klikněte zde.
          </div>
        </div>

        <div class="mt-2 text-xs text-gray-500">
          Šířka: <b>{{ renderImage(cmp).widthPercent || 100 }}%</b> • Max výška:
          <b>{{ renderImage(cmp).maxHeight || 420 }}px</b>
          • Proklik:
          <b>{{
            renderImage(cmp).click?.enabled
              ? renderImage(cmp).click?.action === "link"
                ? "odkaz"
                : "modal"
              : "vypnuto"
          }}</b>
        </div>
      </div>

      <!-- GALLERY -->
      <div v-else-if="cmp.type === 'gallery'">
        <div class="text-sm text-gray-500 mb-3">
          Náhled na hlavní stránce: {{ renderGallery(cmp).shown.length }} ks
        </div>

        <div
          v-if="renderGallery(cmp).shown.length"
          class="grid grid-cols-2 md:grid-cols-3 gap-3"
        >
          <div
            v-for="(img, idx) in renderGallery(cmp).shown"
            :key="img + idx"
            class="rounded-xl overflow-hidden border bg-gray-50 cursor-pointer hover:bg-gray-100 transition"
            @click="emit('edit')"
          >
            <img :src="img" class="w-full h-36 object-cover" alt="" />
          </div>
        </div>

        <div
          v-else
          class="text-sm text-gray-400 italic cursor-pointer border rounded-xl p-4 hover:bg-gray-50 transition"
          @click="emit('edit')"
        >
          (Galerie je prázdná)
        </div>
      </div>

      <!-- CARDS -->
      <div v-else-if="cmp.type === 'cards'">
        <div class="text-sm text-gray-500 mb-3">
          Náhled na hlavní stránce: {{ renderCards(cmp).shown.length }} ks
        </div>

        <div
          v-if="renderCards(cmp).shown.length"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
        >
          <div
            v-for="c in renderCards(cmp).shown"
            :key="c.id"
            class="border rounded-xl p-4 bg-white hover:bg-gray-50 transition cursor-pointer"
            @click="emit('edit')"
          >
            <div class="flex items-start gap-3">
              <div
                class="w-14 h-14 rounded-lg border bg-gray-50 overflow-hidden flex items-center justify-center shrink-0"
              >
                <img
                  v-if="c.image"
                  :src="c.image"
                  class="w-full h-full object-cover"
                  alt=""
                />
                <span v-else class="text-xs text-gray-400">Bez</span>
              </div>

              <div class="min-w-0">
                <div class="font-semibold truncate">
                  {{ c.heading || "Bez nadpisu" }}
                </div>
                <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                  {{ c.text || "Bez textu" }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="text-sm text-gray-400 italic cursor-pointer border rounded-xl p-4 hover:bg-gray-50 transition"
          @click="emit('edit')"
        >
          (Karty jsou prázdné)
        </div>
      </div>

      <!-- ACCORDION -->
      <div v-else-if="cmp.type === 'accordion'">
        <div v-if="renderAccordion(cmp).title" class="text-lg font-semibold mb-3">
          {{ renderAccordion(cmp).title }}
        </div>

        <div v-if="renderAccordion(cmp).items.length" class="space-y-3">
          <div
            v-for="(it, idx) in renderAccordion(cmp).items"
            :key="it.id || idx"
            class="border rounded-xl bg-white cursor-pointer hover:bg-gray-50 transition"
            @click="emit('edit')"
          >
            <div class="flex items-center justify-between px-6 py-4">
              <div class="font-semibold truncate">
                {{ it.heading || "Bez nadpisu" }}
              </div>
              <div class="text-2xl text-gray-500 shrink-0">+</div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="text-sm text-gray-400 italic cursor-pointer border rounded-xl p-4 hover:bg-gray-50 transition"
          @click="emit('edit')"
        >
          (Rozbalovací blok je prázdný)
        </div>
      </div>

      <!-- FORM -->
      <div v-else-if="cmp.type === 'form'">
        <div class="text-sm text-gray-500 mb-3 flex items-center justify-between gap-3">
          <div>
            Formulář: {{ renderForm(cmp).total }} polí
            <span v-if="renderForm(cmp).hasMore" class="ml-1">(zobrazeno 6)</span>
          </div>

          <div v-if="renderForm(cmp).hasSideText" class="text-xs text-gray-400">
            Text: {{ renderForm(cmp).sideTextPosition === "left" ? "vlevo" : "vpravo" }}
          </div>
        </div>

        <div
          class="border rounded-xl p-5 bg-white hover:bg-gray-50 transition cursor-pointer"
          @click="emit('edit')"
        >
          <div v-if="!renderForm(cmp).total" class="text-sm text-gray-400 italic">
            (Formulář je prázdný – klikni pro přidání polí)
          </div>

          <div v-else>
            <div
              class="grid gap-6"
              :class="
                renderForm(cmp).hasSideText ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'
              "
            >
              <div
                v-if="
                  renderForm(cmp).hasSideText &&
                  renderForm(cmp).sideTextPosition === 'left'
                "
                class="prose prose-sm max-w-none text-gray-800"
                v-html="renderForm(cmp).sideText"
              />

              <div class="space-y-4">
                <div v-for="f in renderForm(cmp).shown" :key="f.id" class="space-y-1">
                  <label class="text-sm font-medium text-gray-800">
                    {{ f.label || "Bez popisku" }}
                    <span v-if="f.required" class="ml-2 text-xs text-red-600">*</span>
                  </label>

                  <textarea
                    v-if="f.type === 'textarea'"
                    class="w-full rounded-lg border px-3 py-2 bg-white"
                    :rows="Math.max(2, Number(f.rows || 4))"
                    :placeholder="f.placeholder || ''"
                    readonly
                    @mousedown.prevent
                  />

                  <select
                    v-else-if="f.type === 'select'"
                    class="w-full rounded-lg border px-3 py-2 bg-white"
                    disabled
                    @mousedown.prevent
                  >
                    <option value="">{{ f.placeholder || "Vyberte možnost…" }}</option>
                    <option
                      v-for="o in (f.options || []).slice(0, 8)"
                      :key="o.id"
                      :value="o.value"
                    >
                      {{ o.label || o.value }}
                    </option>
                  </select>

                  <div
                    v-else-if="f.type === 'radio'"
                    class="rounded-lg border p-3 bg-white space-y-2"
                    @mousedown.prevent
                  >
                    <div
                      v-for="o in (f.options || []).slice(0, 4)"
                      :key="o.id"
                      class="flex items-center gap-2 text-sm text-gray-700"
                    >
                      <input type="radio" class="w-4 h-4" disabled />
                      <span>{{ o.label || o.value || "Možnost" }}</span>
                    </div>

                    <div
                      v-if="(f.options || []).length > 4"
                      class="text-xs text-gray-400"
                    >
                      + další možnosti…
                    </div>
                  </div>

                  <div
                    v-else-if="f.type === 'checkbox'"
                    class="flex items-center gap-2 rounded-lg border px-3 py-2 bg-white"
                    @mousedown.prevent
                  >
                    <input type="checkbox" class="w-4 h-4" disabled />
                    <span class="text-sm text-gray-700">
                      {{ f.placeholder || "Souhlasím" }}
                    </span>
                  </div>

                  <input
                    v-else
                    class="w-full rounded-lg border px-3 py-2 bg-white"
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
                    :placeholder="f.placeholder || ''"
                    readonly
                    @mousedown.prevent
                  />

                  <div class="text-[11px] text-gray-400 flex items-center gap-2">
                    <span v-if="f.name" class="font-mono">ID: {{ f.name }}</span>
                    <span v-if="f.name" class="opacity-40">•</span>
                    <span class="uppercase">{{ f.type }}</span>
                  </div>
                </div>

                <div v-if="renderForm(cmp).hasMore" class="text-xs text-gray-400 pt-2">
                  + další pole ve formuláři…
                </div>
              </div>

              <div
                v-if="
                  renderForm(cmp).hasSideText &&
                  renderForm(cmp).sideTextPosition === 'right'
                "
                class="prose prose-sm max-w-none text-gray-800"
                v-html="renderForm(cmp).sideText"
              />
            </div>
          </div>
        </div>

        <div class="mt-2 text-xs text-gray-500">
          Klikni pro úpravu polí (drag&drop řešíš v editoru formuláře).
        </div>
      </div>

      <!-- LOCATIONS MAP -->
      <div v-else-if="cmp.type === 'locations_map'">
        <div
          class="border rounded-xl p-4 bg-white hover:bg-gray-50 transition cursor-pointer"
          @click="emit('edit')"
        >
          <div class="flex items-start justify-between gap-4 mb-3">
            <div>
              <div class="font-semibold text-lg">{{ renderLocationsMap(cmp).title }}</div>
              <div
                v-if="renderLocationsMap(cmp).subtitle"
                class="text-sm text-gray-500 mt-1"
              >
                {{ renderLocationsMap(cmp).subtitle }}
              </div>
            </div>

            <div class="text-xs text-gray-500 shrink-0">
              Poboček: {{ renderLocationsMap(cmp).locations.length }}
            </div>
          </div>

          <div
            v-if="renderLocationsMap(cmp).locations.length"
            class="flex gap-2 mb-4 flex-wrap"
          >
            <div
              v-for="l in renderLocationsMap(cmp).locations.slice(0, 6)"
              :key="l.id"
              class="px-3 py-1.5 rounded-lg border bg-gray-50 text-sm"
            >
              {{ l.tabLabel || "Pobočka" }}
            </div>
            <div
              v-if="renderLocationsMap(cmp).locations.length > 6"
              class="px-3 py-1.5 rounded-lg border bg-gray-50 text-sm text-gray-500"
            >
              +{{ renderLocationsMap(cmp).locations.length - 6 }}
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-stretch">
            <div class="rounded-xl overflow-hidden border bg-gray-50 p-5">
              <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">Mapa</div>
                <div class="text-xs text-gray-500">
                  Strana:
                  <b>
                    {{
                      (renderLocationsMap(cmp).first?.mapSide ||
                        renderLocationsMap(cmp).mapSide) === "right"
                        ? "vpravo"
                        : "vlevo"
                    }}
                  </b>
                </div>
              </div>

              <div class="mt-3 text-sm">
                <div
                  v-if="renderLocationsMap(cmp).first?.mapEmbedUrl"
                  class="text-green-700 font-semibold"
                >
                  ✓ Mapa nastavena
                </div>
                <div v-else class="text-gray-400 italic">
                  (Mapa není nastavena – klikni pro doplnění)
                </div>

                <div
                  v-if="renderLocationsMap(cmp).first?.mapEmbedUrl"
                  class="mt-3 text-xs text-gray-500"
                >
                  iframe src:
                  {{ renderLocationsMap(cmp).first.mapEmbedUrl.slice(0, 60) }}…
                </div>
              </div>
            </div>

            <div class="border rounded-xl p-5 bg-white">
              <div class="font-semibold mb-2">Karta pobočky</div>

              <div
                v-if="renderLocationsMap(cmp).first?.cardHtml"
                class="text-sm text-gray-700"
              >
                <div class="line-clamp-4">
                  {{ renderLocationsMap(cmp).firstCardText || "(prázdné)" }}
                </div>
                <div class="text-xs text-gray-400 mt-2">HTML karta (zkrácený náhled)</div>
              </div>

              <div v-else class="text-sm text-gray-400 italic">
                (Karta pobočky není vyplněná)
              </div>

              <div class="mt-4">
                <div class="text-sm font-semibold mb-2">Tlačítko</div>

                <div
                  v-if="renderLocationsMap(cmp).first?.button?.enabled"
                  class="space-y-2"
                >
                  <div
                    class="inline-flex rounded-lg bg-blue-700 text-white px-4 py-2 text-sm font-semibold"
                  >
                    {{ renderLocationsMap(cmp).first.button.text || "Tlačítko" }}
                  </div>

                  <div class="text-xs text-gray-500">
                    Akce:
                    <b>
                      {{
                        renderLocationsMap(cmp).first.button.action === "link"
                          ? "odkaz"
                          : "modal"
                      }}
                    </b>

                    <template
                      v-if="renderLocationsMap(cmp).first.button.action === 'modal'"
                    >
                      • obsah:
                      <b>
                        {{
                          renderLocationsMap(cmp).first.button.modalComponent?.type
                            ? renderLocationsMap(cmp).first.button.modalComponent.type
                            : "není vybrán"
                        }}
                      </b>
                    </template>

                    <template v-else>
                      • URL:
                      <b>
                        {{
                          renderLocationsMap(cmp).first.button.url
                            ? renderLocationsMap(cmp).first.button.url
                            : "není vyplněno"
                        }}
                      </b>
                    </template>
                  </div>
                </div>

                <div v-else class="text-sm text-gray-400 italic">
                  (Tlačítko je vypnuté)
                </div>
              </div>
            </div>
          </div>

          <div class="mt-3 text-xs text-gray-500">
            Klikni pro úpravu komponenty (preview je zjednodušený).
          </div>
        </div>
      </div>

      <!-- ADVANCED GALLERY (interactive preview) -->
      <div v-else-if="cmp.type === 'advanced_gallery'">
        <div class="text-sm text-gray-500 mb-3 flex items-center justify-between gap-3">
          <div>
            Kategorie: {{ agData.categories.length }}
            <span class="ml-2">•</span>
            Aktuálně:
            <b>{{ agActiveCategory?.label || "—" }}</b>
            <span class="ml-2">•</span>
            Galerie v kategorii:
            <b>{{ (agActiveCategory?.galleries || []).length }}</b>
            <span class="ml-2">•</span>
            Zobrazeno:
            <b>{{ agShownGalleries.length }}</b>
          </div>
        </div>

        <!-- menu chips (click to switch) -->
        <div v-if="agData.categories.length" class="flex flex-wrap gap-2 mb-4">
          <button
            v-for="c in agData.categories"
            :key="c.id"
            type="button"
            class="px-3 py-1.5 rounded-lg border text-sm transition"
            :class="
              String(c.id) === String(agActiveCatId)
                ? 'bg-blue-700 text-white border-blue-700'
                : 'bg-gray-50 hover:bg-gray-100'
            "
            @click="agActiveCatId = c.id"
          >
            {{ c.label || "Kategorie" }}
          </button>
        </div>

        <div
          v-if="agShownGalleries.length"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
        >
          <div
            v-for="g in agShownGalleries"
            :key="g.id"
            class="rounded-xl overflow-hidden border bg-gray-50 cursor-pointer hover:bg-gray-100 transition"
            @click="emit('edit')"
          >
            <div class="relative">
              <img
                v-if="g.cover"
                :src="g.cover"
                class="w-full h-40 object-cover"
                alt=""
              />
              <div
                v-else
                class="w-full h-40 flex items-center justify-center text-xs text-gray-400"
              >
                Bez cover
              </div>

              <div class="absolute inset-x-0 bottom-0 bg-black/40 text-white px-3 py-2">
                <div class="font-semibold truncate">
                  {{ g.title || "Bez názvu" }}
                </div>
                <div class="text-xs opacity-90">{{ (g.images || []).length }} fotek</div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="text-sm text-gray-400 italic cursor-pointer border rounded-xl p-4 hover:bg-gray-50 transition"
          @click="emit('edit')"
        >
          (V této kategorii zatím nejsou žádné galerie)
        </div>

        <div class="mt-2 text-xs text-gray-500">
          Klikni na kategorii nahoře pro přepnutí. Klik na kartu otevře editor komponenty.
        </div>
      </div>

      <!-- YOUTUBE -->
      <div v-else-if="cmp.type === 'youtube'">
        <div
          class="cursor-pointer border rounded-xl p-6 hover:bg-gray-50 transition"
          @click="emit('edit')"
        >
          <div class="text-sm text-gray-600 mb-2">
            YouTube: <b>{{ renderYouTube(cmp).videoId || "—" }}</b>
            <span class="mx-2">•</span>
            Zarovnání: <b>{{ renderYouTube(cmp).align }}</b>
          </div>

          <div
            class="w-full flex"
            :class="
              renderYouTube(cmp).align === 'left'
                ? 'justify-start'
                : renderYouTube(cmp).align === 'right'
                ? 'justify-end'
                : 'justify-center'
            "
          >
            <div
              class="bg-gray-100 rounded-xl overflow-hidden"
              :style="{
                width: '250px',
              }"
            >
              <div class="relative w-full" style="padding-top: 56.25%">
                <div class="absolute inset-0">
                  <img
                    v-if="renderYouTube(cmp).videoId"
                    :src="`https://i.ytimg.com/vi/${
                      renderYouTube(cmp).videoId
                    }/hqdefault.jpg`"
                    class="w-full h-full object-cover"
                    alt=""
                    loading="lazy"
                  />

                  <div
                    v-else
                    class="w-full h-full flex items-center justify-center text-xs text-gray-400"
                  >
                    (Neplatná URL)
                  </div>

                  <!-- play overlay -->
                  <div class="absolute inset-0 flex items-center justify-center">
                    <div
                      class="w-14 h-14 rounded-full bg-black/50 flex items-center justify-center"
                    >
                      <div
                        style="
                          width: 0;
                          height: 0;
                          border-top: 9px solid transparent;
                          border-bottom: 9px solid transparent;
                          border-left: 14px solid white;
                          margin-left: 3px;
                        "
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-2 text-xs text-gray-500">
            URL: {{ (renderYouTube(cmp).url || "").slice(0, 70)
            }}{{ (renderYouTube(cmp).url || "").length > 70 ? "…" : "" }}
          </div>
        </div>
      </div>

      <!-- OTHER -->
      <div v-else class="text-sm text-gray-600">
        Komponenta: <b>{{ typeLabel(cmp.type) }}</b> (zatím bez preview)
      </div>
    </div>

    <ComponentActionsMenu
      :isOpen="isMenuOpen()"
      @toggle="emit('toggleMenu', secId, cmp.id)"
      @edit="
        emit('edit');
        emit('closeMenus');
      "
      @changeType="
        emit('openChangeType');
        emit('closeMenus');
      "
      @delete="
        emit('openDeleteComponent');
        emit('closeMenus');
      "
    />
  </div>
</template>
