<template>
  <div class="mt-4">
    <div class="py-1">
      <Button
        class="mr-2"
        label="Předchozí krok"
        severity="secondary"
        @click="$emit('goPrev')"
      />
    </div>

    <!-- CARD -->
    <div class="mt-6 w-full max-w-6xl bg-white p-6 shadow-sm rounded-xl">
      <h3 class="text-lg font-bold mb-6">Nastavení patičky</h3>

      <div class="space-y-8">
        <!-- SETTINGS -->
        <div class="rounded-xl border p-5">
          <h4 class="font-semibold mb-6">Vzhled patičky</h4>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
            <!-- Footer BG -->
            <div class="min-w-0">
              <label class="font-medium block mb-2">Pozadí patičky</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'fbg-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.footer_background === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('footer_background', k)"
                  :title="colorTitle(k)"
                />
              </div>
              <div class="mt-2 text-xs text-gray-500">
                Transparent = patička bude bez pozadí.
              </div>
            </div>

            <!-- Footer text -->
            <div class="min-w-0">
              <label class="font-medium block mb-2">Barva textu</label>
              <div class="flex items-center gap-3 flex-wrap">
                <button
                  v-for="k in colorKeys"
                  :key="'ftc-' + k"
                  type="button"
                  class="swatch"
                  :class="{ active: local.footer_color === k }"
                  :style="colorCircleStyle(k)"
                  @click="pickColor('footer_color', k)"
                  :title="colorTitle(k)"
                />
              </div>
              <div class="mt-2 text-xs text-gray-500">
                Text doporučuji kontrastní vůči pozadí.
              </div>
            </div>

            <!-- Columns -->
            <div class="min-w-0">
              <label class="font-medium block mb-3">Počet sloupců v patičce</label>

              <div class="flex items-center gap-2 flex-wrap">
                <button
                  v-for="n in 6"
                  :key="'cols-' + n"
                  type="button"
                  class="pill"
                  :class="{ active: local.footer_columns === n }"
                  @click="setColumns(n)"
                >
                  {{ n }}
                </button>
              </div>

              <div class="mt-2 text-xs text-gray-500">
                Na mobilu se sloupce automaticky skládají pod sebe.
              </div>

              <div class="mt-4 text-xs text-gray-500">
                Ukládá se automaticky při změně.
              </div>
            </div>
          </div>
        </div>

        <!-- PREVIEW -->
        <div class="rounded-xl border p-5">
          <div class="flex items-start justify-between gap-3 mb-4">
            <h4 class="font-semibold">Náhled</h4>
            <span class="text-xs text-gray-500">orientační</span>
          </div>

          <div class="preview-shell" :style="previewShellStyle">
            <div
              class="preview-grid"
              :style="{
                gridTemplateColumns: `repeat(${Math.max(
                  1,
                  local.footer_columns
                )}, minmax(0, 1fr))`,
              }"
            >
              <div
                v-for="i in local.footer_columns"
                :key="'col-' + i"
                class="preview-col"
              >
                <div class="preview-col-title">Sloupec {{ i }}</div>

                <!-- ✅ ONE COMPONENT PER COLUMN -->
                <template v-if="getColumnComponent(i)">
                  <!-- klik na kartu = edit obsahu -->
                  <div
                    class="cmp-card"
                    :style="{ borderColor: previewBorderCss }"
                    role="button"
                    tabindex="0"
                    @click="openContentEditor(i)"
                    @keydown.enter="openContentEditor(i)"
                    @keydown.space.prevent="openContentEditor(i)"
                  >
                    <div class="cmp-head" @click.stop>
                      <span class="cmp-badge">
                        {{ badgeLabel(getColumnComponent(i)?.type) }}
                      </span>

                      <!-- 3-dots dropdown -->
                      <div class="cmp-menu-wrap" @click.stop>
                        <button
                          type="button"
                          class="dots-btn"
                          :aria-expanded="openMenuCol === i ? 'true' : 'false'"
                          aria-label="Akce"
                          @click="toggleMenu(i)"
                        >
                          <svg
                            viewBox="0 0 24 24"
                            width="18"
                            height="18"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            aria-hidden="true"
                          >
                            <path d="M5 12h.01" />
                            <path d="M12 12h.01" />
                            <path d="M19 12h.01" />
                          </svg>
                        </button>

                        <div v-if="openMenuCol === i" class="dots-menu">
                          <button
                            type="button"
                            class="dots-item"
                            @click="
                              openComponentPicker(i);
                              closeMenu();
                            "
                          >
                            Změnit komponentu
                          </button>

                          <button
                            type="button"
                            class="dots-item danger"
                            @click="
                              deleteColumnComponent(i);
                              closeMenu();
                            "
                          >
                            Smazat
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- BODY PREVIEW (jen orientačně) -->
                    <div class="cmp-body">
                      <!-- MENU -->
                      <template v-if="getColumnComponent(i)?.type === 'menu'">
                        <div class="cmp-title">
                          {{ getColumnComponent(i)?.data?.title || "Menu" }}
                        </div>
                        <div class="cmp-muted">
                          Vybráno položek:
                          <b>{{ (getColumnComponent(i)?.data?.items || []).length }}</b>
                        </div>
                        <div class="cmp-muted">
                          Barva:
                          <b>{{
                            getColumnComponent(i)?.data?.link_color || "globální"
                          }}</b>
                          • Hover:
                          <b>{{
                            getColumnComponent(i)?.data?.link_hover_color || "globální"
                          }}</b>
                        </div>
                      </template>

                      <!-- TEXT -->
                      <template v-else-if="getColumnComponent(i)?.type === 'text'">
                        <div class="cmp-title">Text</div>
                        <div class="cmp-muted">
                          {{
                            truncate(
                              stripHtml(getColumnComponent(i)?.data?.html || ""),
                              80
                            )
                          }}
                        </div>
                      </template>

                      <!-- IMAGE -->
                      <template v-else-if="getColumnComponent(i)?.type === 'image'">
                        <div class="cmp-title">
                          {{ getColumnComponent(i)?.data?.alt || "Obrázek" }}
                        </div>
                        <div class="cmp-muted">
                          {{
                            getColumnComponent(i)?.data?.url
                              ? "Nahráno"
                              : "(zatím bez obrázku)"
                          }}
                          <span v-if="getColumnComponent(i)?.data?.width_percent">
                            • {{ getColumnComponent(i)?.data?.width_percent }}%
                          </span>
                        </div>
                        <div
                          v-if="getColumnComponent(i)?.data?.url"
                          class="cmp-image-wrap mt-2"
                        >
                          <img
                            :src="getColumnComponent(i).data.url"
                            alt=""
                            class="cmp-image"
                          />
                        </div>
                      </template>

                      <template v-else>
                        <div class="cmp-title">Komponenta</div>
                        <div class="cmp-muted">(neznámý typ)</div>
                      </template>

                      <div class="cmp-hint">Kliknutím upravíte obsah</div>
                    </div>
                  </div>
                </template>

                <!-- EMPTY -->
                <button
                  v-else
                  type="button"
                  class="add-tile"
                  @click="openComponentPicker(i)"
                  :style="{ borderColor: previewBorderCss }"
                >
                  <div class="add-plus" :style="{ borderColor: previewBorderCss }">
                    <svg
                      viewBox="0 0 24 24"
                      width="18"
                      height="18"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      aria-hidden="true"
                    >
                      <path d="M12 5v14" />
                      <path d="M5 12h14" />
                    </svg>
                  </div>

                  <div class="add-label">Přidat obsah sloupce</div>
                </button>
              </div>
            </div>

            <div class="preview-bottom" :style="{ color: previewTextCss }">
              © {{ new Date().getFullYear() }} – lukekriz.cz
            </div>
          </div>

          <div class="mt-3 text-xs text-gray-500">
            Náhled je orientační (reálný obsah se bude brát z patičky webu).
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== PICKER (TYPE) ===================== -->
    <Dialog
      v-model:visible="pickerOpen"
      modal
      header="Vyberte komponentu"
      :style="{ width: '520px', maxWidth: 'calc(100vw - 32px)' }"
    >
      <div class="text-sm text-gray-600 mb-4">
        Přidáte obsah do sloupce <b>{{ activeColumn }}</b
        >.
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <button class="pick-card" type="button" @click="chooseComponent('menu')">
          <div class="pick-icon">≡</div>
          <div class="pick-title">Menu</div>
          <div class="pick-sub">Seznam odkazů</div>
        </button>

        <button class="pick-card" type="button" @click="chooseComponent('text')">
          <div class="pick-icon">T</div>
          <div class="pick-title">Text</div>
          <div class="pick-sub">Nadpis, popis</div>
        </button>

        <button class="pick-card" type="button" @click="chooseComponent('image')">
          <div class="pick-icon">▦</div>
          <div class="pick-title">Fotka</div>
          <div class="pick-sub">Logo / obrázek</div>
        </button>
      </div>

      <template #footer>
        <Button label="Zavřít" severity="secondary" @click="pickerOpen = false" />
      </template>
    </Dialog>

    <!-- ===================== MENU EDITOR ===================== -->
    <Dialog
      v-model:visible="menuEditorOpen"
      modal
      header="Upravit menu"
      :style="{ width: '720px', maxWidth: 'calc(100vw - 32px)' }"
    >
      <div class="text-sm text-gray-600 mb-4">
        Sloupec <b>{{ editingColumn }}</b>
      </div>

      <div class="mb-4">
        <label class="text-sm font-bold block mb-2">Nadpis menu</label>
        <InputText v-model="menuForm.title" class="w-full" placeholder="Menu" />
      </div>

      <!-- ✅ MENU colors -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
        <div>
          <label class="text-sm font-bold block mb-2">Barva odkazu</label>
          <div class="flex items-center gap-3 flex-wrap">
            <button
              v-for="k in colorKeys"
              :key="'mlc-' + k"
              type="button"
              class="swatch"
              :class="{ active: menuForm.link_color === k }"
              :style="colorCircleStyle(k)"
              @click="menuForm.link_color = k"
              :title="colorTitle(k)"
            />
          </div>
          <div class="mt-2 text-xs text-gray-500">
            Necháte-li prázdné, použije se globální barva textu patičky.
          </div>
        </div>

        <div>
          <label class="text-sm font-bold block mb-2">Hover barva odkazu</label>
          <div class="flex items-center gap-3 flex-wrap">
            <button
              v-for="k in colorKeys"
              :key="'mlh-' + k"
              type="button"
              class="swatch"
              :class="{ active: menuForm.link_hover_color === k }"
              :style="colorCircleStyle(k)"
              @click="menuForm.link_hover_color = k"
              :title="colorTitle(k)"
            />
          </div>
          <div class="mt-2 text-xs text-gray-500">
            Necháte-li prázdné, hover se odvodí z globální barvy.
          </div>
        </div>
      </div>

      <div class="mb-3 flex items-center justify-between gap-3">
        <div class="text-sm font-bold">Položky (pages + subpages)</div>
        <div class="text-sm text-gray-500">
          Vybráno: <b>{{ menuSelectedKeys.length }}</b>
        </div>
      </div>

      <div class="menu-pick">
        <div v-for="opt in pageOptions" :key="opt.key" class="menu-row">
          <Checkbox v-model="menuSelectedKeys" :inputId="opt.key" :value="opt.key" />
          <label class="menu-label" :for="opt.key">
            <span class="menu-type">{{ opt.typeLabel }}</span>
            <span class="menu-title">{{ opt.label }}</span>
            <span class="menu-url">{{ opt.url }}</span>
          </label>
        </div>

        <div v-if="pageOptions.length === 0" class="text-sm text-gray-500 p-3">
          (Nejsou předané pages/subpages do komponenty StepFooter)
        </div>
      </div>

      <template #footer>
        <Button label="Zrušit" severity="secondary" @click="menuEditorOpen = false" />
        <Button label="Uložit" @click="saveMenu()" />
      </template>
    </Dialog>

    <!-- ===================== TEXT EDITOR ===================== -->
    <Dialog
      v-model:visible="textEditorOpen"
      modal
      header="Upravit text"
      :style="{ width: '900px', maxWidth: 'calc(100vw - 32px)' }"
    >
      <div class="text-sm text-gray-600 mb-4">
        Sloupec <b>{{ editingColumn }}</b>
      </div>

      <!-- ✅ bez inputu na nadpis -->
      <div>
        <label class="text-sm font-bold block mb-2">Obsah</label>

        <Editor v-model="textForm.html" editorStyle="height: 260px;">
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
              <option :value="mainDesign.font_type">{{ mainDesign.font_type }}</option>
              <option :value="mainDesign.font_type_2">
                {{ mainDesign.font_type_2 }}
              </option>
              <option :value="mainDesign.font_type_3">
                {{ mainDesign.font_type_3 }}
              </option>
            </select>
            <!-- ✅ FONT SIZE v PX (místo ql-header) -->
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
              <select class="ql-align">
                <option selected></option>
                <option value="center"></option>
                <option value="right"></option>
                <option value="justify"></option>
              </select>
              <button class="ql-code-block"></button>
              <button class="ql-link"></button>
            </span>
          </template>
        </Editor>
      </div>

      <template #footer>
        <Button label="Zrušit" severity="secondary" @click="textEditorOpen = false" />
        <Button label="Uložit" @click="saveText()" />
      </template>
    </Dialog>

    <!-- ===================== IMAGE EDITOR ===================== -->
    <Dialog
      v-model:visible="imageEditorOpen"
      modal
      header="Upravit obrázek"
      :style="{ width: '720px', maxWidth: 'calc(100vw - 32px)' }"
    >
      <div class="text-sm text-gray-600 mb-4">
        Sloupec <b>{{ editingColumn }}</b>
      </div>

      <div class="mb-4">
        <label class="text-sm font-bold block mb-2">Popisek (alt)</label>
        <InputText v-model="imageForm.alt" class="w-full" placeholder="Obrázek" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-bold block mb-2">Nahrát obrázek</label>

          <input type="file" accept="image/*" @change="onPickImageFile" />

          <div class="mt-3 text-xs text-gray-500">
            Po uložení se obrázek nahraje na server.
          </div>

          <div class="mt-4">
            <label class="text-sm font-bold block mb-2">Velikost (šířka v %)</label>
            <InputNumber
              v-model="imageForm.width_percent"
              :min="10"
              :max="100"
              :step="5"
              suffix="%"
              class="w-full"
            />
            <div class="mt-2 text-xs text-gray-500">
              Ovlivní pouze zobrazení v patičce (např. 40%).
            </div>
          </div>
        </div>

        <div>
          <label class="text-sm font-bold block mb-2">Náhled</label>
          <div class="img-preview">
            <img
              v-if="imagePreviewUrl"
              :src="imagePreviewUrl"
              alt=""
              class="img-preview-img"
              :style="{ width: (imageForm.width_percent || 60) + '%' }"
            />
            <div v-else class="text-sm text-gray-500">(zatím bez obrázku)</div>
          </div>
        </div>
      </div>

      <template #footer>
        <Button label="Zrušit" severity="secondary" @click="imageEditorOpen = false" />
        <Button label="Uložit" @click="saveImage()" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { reactive, computed, watch, ref, onBeforeUnmount } from "vue";
import axios from "axios";

import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Checkbox from "primevue/checkbox";
import Editor from "primevue/editor";
import InputNumber from "primevue/inputnumber";

/* ===================== PROPS/EMITS ===================== */
const props = defineProps({
  mainDesign: { type: Object, required: true },
  pages: { type: Array, default: () => [] },
  subpages: { type: Array, default: () => [] },
});

const emit = defineEmits(["goPrev", "goNext", "saveMainDesign"]);

/* ===================== FOOTER CONTENT (JSON) ===================== */
const footerContent = computed(() => {
  const raw = props.mainDesign?.footer_content;
  if (!raw) return {};
  if (typeof raw === "string") {
    try {
      return JSON.parse(raw) || {};
    } catch {
      return {};
    }
  }
  if (typeof raw === "object") return raw;
  return {};
});

const getColumnComponent = (col) => footerContent.value?.[String(col)] || null;

const setColumnComponent = (col, nextComponentOrNull) => {
  const next = { ...(footerContent.value || {}) };
  const key = String(col);

  if (!nextComponentOrNull) delete next[key];
  else next[key] = nextComponentOrNull;

  emit("saveMainDesign", { footer_content: next });
};

/* ===================== 3-dots MENU ===================== */
const openMenuCol = ref(null);
const closeMenu = () => (openMenuCol.value = null);
const toggleMenu = (col) => (openMenuCol.value = openMenuCol.value === col ? null : col);
const onDocClick = () => closeMenu();
document.addEventListener("click", onDocClick);
onBeforeUnmount(() => document.removeEventListener("click", onDocClick));

/* ===================== PICKER (TYPE) ===================== */
const pickerOpen = ref(false);
const activeColumn = ref(1);

const openComponentPicker = (col) => {
  activeColumn.value = Number(col) || 1;
  pickerOpen.value = true;
};

const defaultDataByType = (type) => {
  if (type === "menu")
    return {
      title: "Menu",
      items: [],
      link_color: null, // key z colorKeys nebo null (fallback globální)
      link_hover_color: null,
    };

  // ✅ text bez title
  if (type === "text") return { html: "" };

  if (type === "image") return { url: null, alt: "Obrázek", width_percent: 60 };
  return {};
};

const chooseComponent = (type) => {
  pickerOpen.value = false;

  setColumnComponent(activeColumn.value, {
    type,
    data: defaultDataByType(type),
  });

  // rovnou otevřít editor obsahu
  setTimeout(() => openContentEditor(activeColumn.value), 0);
};

const deleteColumnComponent = (col) => setColumnComponent(col, null);

/* ===================== EDIT CONTENT DIALOGS ===================== */
const editingColumn = ref(1);

/** MENU editor */
const menuEditorOpen = ref(false);
const menuForm = reactive({
  title: "Menu",
  link_color: null,
  link_hover_color: null,
});
const menuSelectedKeys = ref([]);

/** TEXT editor */
const textEditorOpen = ref(false);
const textForm = reactive({ html: "" });

/** IMAGE editor */
const imageEditorOpen = ref(false);
const imageForm = reactive({ alt: "Obrázek", url: null, width_percent: 60 });
const imageFile = ref(null);
const imagePreviewUrl = ref(null);

// options pro menu = pages + subpages
const pageOptions = computed(() => {
  const p = (props.pages || []).map((x) => ({
    key: `page:${x.id}`,
    type: "page",
    typeLabel: "Stránka",
    id: x.id,
    label: x.title ?? x.name ?? x.url ?? `Page ${x.id}`,
    url: x.url ?? "",
  }));

  const s = (props.subpages || []).map((x) => ({
    key: `subpage:${x.id}`,
    type: "subpage",
    typeLabel: "Podstránka",
    id: x.id,
    label: x.title ?? x.name ?? x.url ?? `Subpage ${x.id}`,
    url: x.url ?? "",
  }));

  return [...p, ...s];
});

const openContentEditor = (col) => {
  const cmp = getColumnComponent(col);
  if (!cmp) return;

  editingColumn.value = Number(col) || 1;

  if (cmp.type === "menu") {
    // hydrate
    menuForm.title = cmp.data?.title ?? "Menu";
    menuForm.link_color = cmp.data?.link_color ?? null;
    menuForm.link_hover_color = cmp.data?.link_hover_color ?? null;

    const items = Array.isArray(cmp.data?.items) ? cmp.data.items : [];
    menuSelectedKeys.value = items.map((it) => it.key).filter(Boolean);

    menuEditorOpen.value = true;
    return;
  }

  if (cmp.type === "text") {
    // ✅ jen editor (bez title inputu)
    textForm.html = cmp.data?.html ?? "";
    textEditorOpen.value = true;
    return;
  }

  if (cmp.type === "image") {
    imageForm.alt = cmp.data?.alt ?? "Obrázek";
    imageForm.url = cmp.data?.url ?? null;
    imageForm.width_percent = Number(cmp.data?.width_percent ?? 60);
    imageFile.value = null;

    // preview url: buď existující url, nebo lokální vybraný soubor
    imagePreviewUrl.value = imageForm.url;
    imageEditorOpen.value = true;
    return;
  }
};

/* ===== Save MENU ===== */
const saveMenu = () => {
  const selected = new Set(menuSelectedKeys.value || []);
  const items = pageOptions.value
    .filter((o) => selected.has(o.key))
    .map((o) => ({
      key: o.key,
      type: o.type,
      id: o.id,
      label: o.label,
      url: o.url,
    }));

  const current = getColumnComponent(editingColumn.value);

  setColumnComponent(editingColumn.value, {
    type: "menu",
    data: {
      ...(current?.data || {}),
      title: menuForm.title || "Menu",
      items,
      link_color: menuForm.link_color ?? null,
      link_hover_color: menuForm.link_hover_color ?? null,
    },
  });

  menuEditorOpen.value = false;
};

/* ===== Save TEXT ===== */
const saveText = () => {
  const current = getColumnComponent(editingColumn.value);

  setColumnComponent(editingColumn.value, {
    type: "text",
    data: {
      ...(current?.data || {}),
      html: textForm.html || "",
    },
  });

  textEditorOpen.value = false;
};

/* ===== Image pick/upload ===== */
const onPickImageFile = (e) => {
  const file = e.target?.files?.[0];
  if (!file) return;

  imageFile.value = file;

  if (imagePreviewUrl.value && imagePreviewUrl.value.startsWith("blob:")) {
    try {
      URL.revokeObjectURL(imagePreviewUrl.value);
    } catch {}
  }
  imagePreviewUrl.value = URL.createObjectURL(file);
};

// ✅ doplň si route v routes/web.php (POST admin/main-design/footer-image -> MainDesignController@uploadFooterImage)
const IMAGE_UPLOAD_ENDPOINT = "/admin/main-design/footer-image";

const uploadImageIfNeeded = async () => {
  if (!imageFile.value) return imageForm.url || null;

  const form = new FormData();
  form.append("image", imageFile.value);

  const { data } = await axios.post(IMAGE_UPLOAD_ENDPOINT, form, {
    headers: { "Content-Type": "multipart/form-data" },
  });

  return data?.url ?? null;
};

const saveImage = async () => {
  const current = getColumnComponent(editingColumn.value);

  let url = imageForm.url || null;
  try {
    url = await uploadImageIfNeeded();
  } catch (e) {
    console.error(e);
    alert("Nahrání obrázku se nepovedlo. Zkontrolujte endpoint.");
    return;
  }

  setColumnComponent(editingColumn.value, {
    type: "image",
    data: {
      ...(current?.data || {}),
      alt: imageForm.alt || "Obrázek",
      url,
      width_percent: Math.min(100, Math.max(10, Number(imageForm.width_percent || 60))),
    },
  });

  imageEditorOpen.value = false;
};

/* ===================== COLORS ===================== */
const colorKeys = [
  "primary",
  "secondary",
  "tertiary",
  "quaternary",
  "quinary",
  "transparent",
];
const colorLabels = {
  primary: "1. Hlavní",
  secondary: "2. Druhá",
  tertiary: "3. Třetí",
  quaternary: "4. Čtvrtá",
  quinary: "5. Pátá",
  transparent: "Transparent",
};

const palette = computed(() => ({
  primary: props.mainDesign.primary_color,
  secondary: props.mainDesign.secondary_color,
  tertiary: props.mainDesign.tertiary_color,
  quaternary: props.mainDesign.quaternary_color,
  quinary: props.mainDesign.quinary_color,
  transparent: "transparent",
}));

const checkerCss =
  "linear-gradient(45deg, #e5e7eb 25%, transparent 25%)," +
  "linear-gradient(-45deg, #e5e7eb 25%, transparent 25%)," +
  "linear-gradient(45deg, transparent 75%, #e5e7eb 75%)," +
  "linear-gradient(-45deg, transparent 75%, #e5e7eb 75%)";

const colorCircleStyle = (k) => {
  if (k === "transparent") {
    return {
      backgroundImage: checkerCss,
      backgroundSize: "10px 10px",
      backgroundPosition: "0 0, 0 5px, 5px -5px, -5px 0px",
      backgroundColor: "#fff",
    };
  }
  return { backgroundColor: palette.value[k] };
};

const colorTitle = (k) =>
  k === "transparent" ? "Transparent" : `${colorLabels[k]} ${palette.value[k]}`;

/* MAP DB -> KEY */
const normalizeHex = (v) => {
  if (v === null || v === undefined) return null;
  const s = String(v).trim().toLowerCase();
  if (s === "" || s === "transparent") return "transparent";
  if (s[0] !== "#") return s;
  if (s.length === 4) return "#" + s[1] + s[1] + s[2] + s[2] + s[3] + s[3];
  return s;
};

const mapHexToKey = (val, incoming) => {
  const s = normalizeHex(incoming);
  if (s === null || s === "transparent") return "transparent";
  if (colorKeys.includes(s)) return s;

  const p = {
    primary: normalizeHex(val.primary_color),
    secondary: normalizeHex(val.secondary_color),
    tertiary: normalizeHex(val.tertiary_color),
    quaternary: normalizeHex(val.quaternary_color),
    quinary: normalizeHex(val.quinary_color),
  };

  const found = Object.entries(p).find(([, hex]) => hex && hex === s);
  return found ? found[0] : null;
};

/* LOCAL */
const local = reactive({
  footer_background: "transparent",
  footer_color: "secondary",
  footer_columns: 3,
});

const isHydrating = ref(false);

watch(
  () => props.mainDesign,
  (val) => {
    if (!val) return;
    isHydrating.value = true;

    const bgKey = mapHexToKey(val, val.footer_background);
    const tcKey = mapHexToKey(val, val.footer_color);

    if (bgKey) local.footer_background = bgKey;
    if (tcKey) local.footer_color = tcKey;

    const cols = Number(val.footer_columns ?? 3);
    if (!Number.isNaN(cols)) local.footer_columns = Math.min(6, Math.max(1, cols));

    setTimeout(() => (isHydrating.value = false), 0);
  },
  { immediate: true, deep: true }
);

const keyToDbColor = (k) => (k === "transparent" ? null : palette.value[k]);

const emitSave = () => {
  emit("saveMainDesign", {
    footer_background: keyToDbColor(local.footer_background),
    footer_color: keyToDbColor(local.footer_color),
    footer_columns: local.footer_columns,
  });
};

const pickColor = (key, colorKey) => {
  local[key] = colorKey;
  if (!isHydrating.value) emitSave();
};

const setColumns = (n) => {
  local.footer_columns = Math.min(6, Math.max(1, Number(n) || 1));
  if (!isHydrating.value) emitSave();
};

/* PREVIEW */
const previewBgCss = computed(() =>
  local.footer_background === "transparent"
    ? "transparent"
    : palette.value[local.footer_background]
);

const previewTextCss = computed(() =>
  local.footer_color === "transparent" ? "#111827" : palette.value[local.footer_color]
);

const previewBorderCss = computed(() => "rgba(15, 23, 42, 0.18)");

const previewShellStyle = computed(() => ({
  background: previewBgCss.value,
}));

/* UI helpers */
const badgeLabel = (type) => {
  if (type === "menu") return "Menu";
  if (type === "text") return "Text";
  if (type === "image") return "Fotka";
  return "Komponenta";
};

const truncate = (s, n) => {
  const str = String(s ?? "");
  return str.length > n ? str.slice(0, n - 1) + "…" : str;
};

const stripHtml = (html) =>
  String(html || "")
    .replace(/<[^>]*>/g, "")
    .trim();
</script>

<style scoped>
/* swatches */
.swatch {
  width: 28px;
  height: 28px;
  border-radius: 9999px;
  border: 1px solid rgba(15, 23, 42, 0.18);
  display: inline-block;
  flex: 0 0 auto;
  padding: 0;
  line-height: 0;
}
.swatch.active {
  box-shadow: 0 0 0 2px #22c55e, 0 0 0 4px #ffffff;
}
.swatch:active {
  transform: scale(0.98);
}

/* pills */
.pill {
  height: 34px;
  min-width: 34px;
  padding: 0 12px;
  border-radius: 9999px;
  border: 1px solid rgba(15, 23, 42, 0.18);
  background: #fff;
  font-weight: 700;
  font-size: 14px;
}
.pill.active {
  border-color: #22c55e;
  box-shadow: 0 0 0 2px #22c55e, 0 0 0 4px #ffffff;
}

/* preview */
.preview-shell {
  border-radius: 16px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  overflow: hidden;
  padding: 18px;
}
.preview-grid {
  display: grid;
  gap: 14px;
  align-items: start;
}
.preview-col {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.65);
  padding: 14px;
  backdrop-filter: blur(1px);
}
.preview-col-title {
  font-weight: 800;
  font-size: 14px;
  margin-bottom: 10px;
}

/* empty add tile */
.add-tile {
  margin-top: 8px;
  width: 100%;
  border: 2px dashed rgba(15, 23, 42, 0.22);
  border-radius: 14px;
  padding: 28px 14px;
  background: rgba(255, 255, 255, 0.55);
  display: grid;
  place-items: center;
  gap: 12px;
  transition: transform 0.12s ease, background 0.12s ease;
}
.add-tile:hover {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.7);
}
.add-plus {
  width: 46px;
  height: 46px;
  border-radius: 9999px;
  border: 1px solid rgba(15, 23, 42, 0.18);
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.85);
}
.add-label {
  font-size: 13px;
  font-weight: 800;
  opacity: 0.9;
  text-align: center;
  line-height: 1.2;
  max-width: 140px;
}

.preview-bottom {
  margin-top: 16px;
  font-size: 12px;
  opacity: 0.85;
}

/* component card */
.cmp-card {
  width: 100%;
  border: 1px solid rgba(15, 23, 42, 0.14);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.75);
  padding: 12px;
  cursor: pointer;
  transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
}
.cmp-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  background: rgba(255, 255, 255, 0.85);
}
.cmp-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 10px;
}
.cmp-badge {
  font-size: 12px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 9999px;
  background: rgba(15, 23, 42, 0.06);
  border: 1px solid rgba(15, 23, 42, 0.08);
}
.cmp-body {
  font-size: 13px;
  line-height: 1.35;
}
.cmp-title {
  font-weight: 900;
  margin-bottom: 6px;
}
.cmp-muted {
  opacity: 0.75;
}
.cmp-hint {
  margin-top: 10px;
  font-size: 12px;
  opacity: 0.6;
}

/* 3-dots */
.cmp-menu-wrap {
  position: relative;
}
.dots-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: rgba(255, 255, 255, 0.9);
  display: grid;
  place-items: center;
}
.dots-btn:hover {
  background: #fff;
}
.dots-menu {
  position: absolute;
  top: 40px;
  right: 0;
  min-width: 190px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 12px;
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
  overflow: hidden;
  z-index: 50;
}
.dots-item {
  width: 100%;
  text-align: left;
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 800;
  background: #fff;
}
.dots-item:hover {
  background: rgba(15, 23, 42, 0.04);
}
.dots-item.danger {
  color: #dc2626;
}

/* image preview inside card */
.cmp-image-wrap {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: rgba(255, 255, 255, 0.9);
}
.cmp-image {
  width: 100%;
  height: 84px;
  object-fit: cover;
  display: block;
}

/* picker cards */
.pick-card {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 14px;
  padding: 14px 12px;
  background: #fff;
  text-align: left;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.pick-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
}
.pick-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-weight: 900;
  font-size: 16px;
  background: rgba(248, 250, 252, 1);
  border: 1px solid rgba(15, 23, 42, 0.08);
  margin-bottom: 10px;
}
.pick-title {
  font-weight: 800;
  font-size: 14px;
}
.pick-sub {
  margin-top: 2px;
  font-size: 12px;
  color: #6b7280;
}

/* menu picker list */
.menu-pick {
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 14px;
  overflow: hidden;
  max-height: 420px;
  overflow-y: auto;
  background: #fff;
}
.menu-row {
  display: grid;
  grid-template-columns: 18px 1fr;
  gap: 10px;
  padding: 10px 12px;
  border-bottom: 1px solid rgba(15, 23, 42, 0.06);
}
.menu-row:last-child {
  border-bottom: none;
}
.menu-label {
  display: grid;
  gap: 2px;
  cursor: pointer;
}
.menu-type {
  font-size: 11px;
  font-weight: 900;
  opacity: 0.65;
}
.menu-title {
  font-size: 13px;
  font-weight: 800;
}
.menu-url {
  font-size: 12px;
  opacity: 0.65;
}

/* image editor preview */
.img-preview {
  border: 1px dashed rgba(15, 23, 42, 0.18);
  border-radius: 14px;
  padding: 14px;
  min-height: 180px;
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.6);
}
.img-preview-img {
  height: auto;
  max-height: 260px;
  object-fit: contain;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: #fff;
}
</style>
