<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
  pages: { type: Array, default: () => [] },
  subpages: { type: Array, default: () => [] }, // ✅ přidáno
  layouts: { type: Array, default: () => [] },

  selectedLayoutId: [Number, String, null],
  selectedPageUrl: { type: String, default: "/" },

  visibleDialogs: { type: Object, default: () => ({}) },
  visibleEditDialogs: { type: Object, default: () => ({}) },
  pageDesignData: { type: Object, required: true },

  menuPosition: { type: Array, default: () => [] },
  logoPosition: { type: Array, default: () => [] },
});

defineEmits([
  "goPrev",
  "goNext",
  "selectPage",
  "requireConfirmation",
  "showDialog",
  "showEditDialog",
  "saveLayout",
]);

// poslední validní hodnoty
const lastLogo = ref(null);
const lastMenu = ref(null);

watch(
  () => [props.pageDesignData?.logo_position, props.pageDesignData?.menu_position],
  ([logo, menu]) => {
    lastLogo.value = logo;
    lastMenu.value = menu;
  },
  { immediate: true }
);

// disable logika
const isLogoOptionDisabled = (val) => val === props.pageDesignData?.menu_position;
const isMenuOptionDisabled = (val) => val === props.pageDesignData?.logo_position;

// zastavit event před PrimeVue
const onOptionMouseDown = (e, disabled) => {
  if (!disabled) return;
  e.preventDefault();
  e.stopPropagation();
};

const onOptionClick = (e, disabled) => {
  if (!disabled) return;
  e.preventDefault();
  e.stopPropagation();
};

// pojistky při update
const onLogoChange = (val) => {
  if (val === props.pageDesignData.menu_position) {
    props.pageDesignData.logo_position = lastLogo.value;
    return;
  }
  lastLogo.value = val;
};

const onMenuChange = (val) => {
  if (val === props.pageDesignData.logo_position) {
    props.pageDesignData.menu_position = lastMenu.value;
    return;
  }
  lastMenu.value = val;
};

/**
 * ✅ Seskupení: page a hned pod ní její subpages
 * - bez zásahu do backendu
 * - jen přeskupení pro Tabs
 */
const allTargets = computed(() => {
  const PARENT_KEY = "page_id"; // ← pokud máš jinak, přepiš (např. parent_id)

  const pages = (props.pages || []).map((p) => ({
    key: `page:${p.url}`,
    kind: "page",
    title: p.title,
    payload: { ...p, _type: "page" },
  }));

  const subpages = (props.subpages || []).map((s) => ({
    key: `subpage:${s.url ?? s.id}`,
    kind: "subpage",
    title: s.title,
    payload: { ...s, _type: "subpage" },
    _parentId: s?.[PARENT_KEY] ?? null,
  }));

  const subsByParent = new Map();
  for (const sp of subpages) {
    const pid = sp._parentId;
    if (!pid) continue;
    if (!subsByParent.has(pid)) subsByParent.set(pid, []);
    subsByParent.get(pid).push(sp);
  }

  const ordered = [];
  for (const p of pages) {
    ordered.push(p);

    const children = subsByParent.get(p.payload.id) || [];
    children.sort((a, b) => String(a.title).localeCompare(String(b.title), "cs"));
    ordered.push(...children);
  }

  // subpages bez parenta (když data nemají vazbu)
  const orphans = subpages.filter((sp) => !sp._parentId);
  if (orphans.length) {
    orphans.sort((a, b) => String(a.title).localeCompare(String(b.title), "cs"));
    ordered.push(...orphans);
  }

  return ordered;
});

/**
 * ✅ vybraný tab: podle selectedPageUrl najdeme page nebo subpage
 * (protože url může být stejná logika, ale klíče se liší)
 */
const selectedKey = computed(() => {
  const byPage = `page:${props.selectedPageUrl}`;
  if (allTargets.value.some((x) => x.key === byPage)) return byPage;

  const bySub = `subpage:${props.selectedPageUrl}`;
  if (allTargets.value.some((x) => x.key === bySub)) return bySub;

  return allTargets.value[0]?.key ?? byPage;
});
</script>

<template>
  <div>
    <div class="py-1">
      <Button
        class="mr-2"
        label="Předchozí krok"
        severity="secondary"
        @click="$emit('goPrev')"
      />
    </div>

    <div class="mt-3">
      <div class="card flex justify-center flex-wrap">
        <!-- ✅ Tabs používají unikátní selectedKey -->
        <Tabs :value="selectedKey" scrollable>
          <TabList class="mb-5">
            <Tab
              v-for="t in allTargets"
              :key="t.key"
              :value="t.key"
              @click="$emit('selectPage', t.payload)"
              class="!px-4 !py-2"
            >
              <!-- ✅ design: page vs subpage -->
              <div class="flex flex-col leading-tight">
                <div class="flex items-center gap-2">
                  <span v-if="t.kind === 'subpage'" class="text-gray-400">↳</span>
                  <span :class="t.kind === 'subpage' ? 'font-semibold' : 'font-bold'">
                    {{ t.title }}
                  </span>
                </div>

                <div v-if="t.kind === 'subpage'" class="text-[11px] text-gray-500 ml-5">
                  Podstránka
                </div>
              </div>
            </Tab>
          </TabList>

          <TabPanels>
            <!-- ✅ pozor: value musí být t.key, ne url -->
            <TabPanel
              v-for="t in allTargets"
              :key="t.key"
              :value="t.key"
              class="flex justify-start items-center"
            >
              <div
                v-for="layout in layouts"
                :key="layout.id"
                @click="$emit('requireConfirmation', layout.id)"
                class="relative flex flex-col justify-center items-center w-1/5 h-w border rounded-lg m-2 bg-gray-100 shadow-lg tile"
                :class="{ 'tile-taken': String(selectedLayoutId) === String(layout.id) }"
              >
                <div class="flex justify-between">
                  <button
                    @click.stop="$emit('showDialog', layout.id)"
                    v-tooltip.top="'Více informací'"
                    class="absolute top-0 right-0 p-2 rounded-bl-lg rounded-tr-2xl text-gray-600 font-bold w-10 hover:text-blue-500 hover:bg-blue-100 hover:shadow-lg"
                  >
                    <i class="fa-solid fa-circle-info text-sx"></i>
                  </button>

                  <button
                    v-if="String(selectedLayoutId) === String(layout.id)"
                    @click.stop="$emit('showEditDialog', layout.id)"
                    v-tooltip.top="'Upravit rozměry'"
                    class="absolute top-0 left-0 p-2 rounded-br-lg rounded-tl-2xl text-gray-600 font-bold w-10 hover:text-orange-500 hover:bg-orange-100 hover:shadow-lg"
                  >
                    <i class="fas fa-pen text-sx"></i>
                  </button>
                </div>

                <!-- info dialog -->
                <Dialog
                  v-model:visible="visibleDialogs[layout.id]"
                  modal
                  header="Detail layoutu"
                  :style="{ width: '50vw' }"
                >
                  <template #header></template>
                  <template #default>
                    <div class="flex flex-col">
                      <div class="flex justify-center items-center">
                        <img
                          :src="`/img/layouts/${layout.sketch_url}`"
                          class="w-1/2 mb-5"
                          alt=""
                        />
                      </div>
                      <h3 class="text-left text-lg font-bold">
                        {{ layout.title }}
                      </h3>
                      <p>{{ layout.description }}</p>
                    </div>
                  </template>
                </Dialog>

                <!-- edit dialog -->
                <Dialog
                  v-model:visible="visibleEditDialogs[layout.id]"
                  modal
                  header="Upravit rozměry jednotlivých komponent"
                  :style="{ width: '40vw' }"
                >
                  <template #header></template>
                  <template #default>
                    <div class="flex flex-col justify-center items-center">
                      <div class="flex flex-col justify-center items-center pt-3">
                        <div class="flex gap-2 justify-center items-center mb-4">
                          <label for="header-height">
                            Výška hlavičky <span class="text-xs">(px)</span>
                          </label>
                          <input
                            v-model="pageDesignData.header_height"
                            class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                            type="number"
                            inputId="header-height"
                          />
                        </div>

                        <div class="flex gap-2 justify-center items-center mb-4">
                          <label for="banner-height">
                            Výška banneru <span class="text-xs">(px)</span>
                          </label>
                          <input
                            v-model="pageDesignData.banner_height"
                            class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                            type="number"
                            inputId="banner-height"
                          />
                        </div>

                        <!-- ✅ LOGO POSITION -->
                        <div class="flex gap-2 justify-center items-center mb-4">
                          <label for="logo-position">Pozice Loga</label>

                          <SelectButton
                            id="logo-position"
                            v-model="pageDesignData.logo_position"
                            :options="logoPosition"
                            optionLabel="label"
                            optionValue="value"
                            @update:modelValue="onLogoChange"
                          >
                            <template #option="slotProps">
                              <button
                                type="button"
                                class="px-2 py-1"
                                :class="[
                                  isLogoOptionDisabled(slotProps.option.value)
                                    ? 'opacity-40 cursor-not-allowed'
                                    : '',
                                ]"
                                :tabindex="
                                  isLogoOptionDisabled(slotProps.option.value) ? -1 : 0
                                "
                                @mousedown="
                                  onOptionMouseDown(
                                    $event,
                                    isLogoOptionDisabled(slotProps.option.value)
                                  )
                                "
                                @click="
                                  onOptionClick(
                                    $event,
                                    isLogoOptionDisabled(slotProps.option.value)
                                  )
                                "
                              >
                                {{ slotProps.option.label }}
                              </button>
                            </template>
                          </SelectButton>
                        </div>

                        <!-- ✅ MENU POSITION -->
                        <div class="flex gap-2 justify-center items-center mb-4">
                          <label for="menu-position">Pozice Menu</label>

                          <SelectButton
                            id="menu-position"
                            v-model="pageDesignData.menu_position"
                            :options="menuPosition"
                            optionLabel="label"
                            optionValue="value"
                            @update:modelValue="onMenuChange"
                          >
                            <template #option="slotProps">
                              <button
                                type="button"
                                class="px-2 py-1"
                                :class="[
                                  isMenuOptionDisabled(slotProps.option.value)
                                    ? 'opacity-40 cursor-not-allowed'
                                    : '',
                                ]"
                                :tabindex="
                                  isMenuOptionDisabled(slotProps.option.value) ? -1 : 0
                                "
                                @mousedown="
                                  onOptionMouseDown(
                                    $event,
                                    isMenuOptionDisabled(slotProps.option.value)
                                  )
                                "
                                @click="
                                  onOptionClick(
                                    $event,
                                    isMenuOptionDisabled(slotProps.option.value)
                                  )
                                "
                              >
                                {{ slotProps.option.label }}
                              </button>
                            </template>
                          </SelectButton>
                        </div>

                        <!-- ✅ CATEGORY SETTINGS (jen pro layout 2 / eShop) -->
                        <div
                          v-if="String(layout.id) === '2'"
                          class="w-full mt-2 pt-2 border-t border-gray-200"
                        >
                          <div class="flex gap-2 justify-center items-center mb-4">
                            <label for="category-weight">
                              Šířka kategorie <span class="text-xs">(px)</span>
                            </label>
                            <input
                              v-model="pageDesignData.category_weight"
                              class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                              type="number"
                              id="category-weight"
                              min="0"
                              placeholder="např. 280"
                            />
                          </div>

                          <div class="flex gap-2 justify-center items-center mb-4">
                            <label for="category-height">
                              Výška kategorie <span class="text-xs">(px)</span>
                            </label>
                            <input
                              v-model="pageDesignData.category_height"
                              class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                              type="number"
                              id="category-height"
                              min="0"
                              placeholder="Prázdné = plná výška"
                            />
                          </div>
                        </div>

                        <div class="flex gap-2 justify-center items-center mb-4">
                          <label for="footer-height">
                            Výška patičky <span class="text-xs">(px)</span>
                          </label>
                          <input
                            v-model="pageDesignData.footer_height"
                            class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                            type="number"
                            id="footer-height"
                          />
                        </div>
                      </div>

                      <Button class="w-1/3 mt-10" @click="$emit('saveLayout', layout.id)">
                        Uložit
                      </Button>
                    </div>
                  </template>
                </Dialog>

                <img
                  :src="`/img/layouts/${layout.sketch_url}`"
                  alt=""
                  class="w-5/6 h-5/6 mb-2"
                />
                <span class="text-sm">{{ layout.title }}</span>
              </div>
            </TabPanel>
          </TabPanels>
        </Tabs>
      </div>
    </div>

    <div class="py-6">
      <Button label="Další krok" @click="$emit('goNext')" />
    </div>
  </div>
</template>
