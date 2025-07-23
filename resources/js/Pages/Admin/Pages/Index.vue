<template>
  <AdminLayout
    title="Struktura stránek"
    description="Zde si prosím vytvořte strukturu Vaší internetové stránky."
    :home="{ icon: 'fas fa-home', route: '/admin' }"
    :breadcrumbItems="[{ label: 'Struktura stránek', route: '/admin/pages' }]"
  >
    <div>
      <PrimaryButton
        @click="addRow"
        class="mb-6 rounded-full"
        :class="editingIndex !== null ? 'bg-gray-600 hover:bg-gray-500' : ''"
        ><i class="fa-regular fa-file mr-2"></i>Nová stránka</PrimaryButton
      >

      <div class="space-y-4 w-2/3">
        <div
          v-for="(page, pageIndex) in props.pages"
          :key="page.id || pageIndex"
          class="p-6 border border-1 rounded-lg bg-gray-100 flex flex-col space-y-2"
        >
          <!-- Hlavní stránka -->
          <div
            v-if="editingIndex !== pageIndex"
            class="flex items-center justify-between space-x-1"
          >
            <div class="flex space-x-2">
              <div
                class="flex space-x-1 items-center p-2 pt-1 pb-1 rounded-full"
                :class="page.active === 0 ? 'bg-red-200' : 'bg-green-200'"
              >
                <i
                  class="text-sm text-gray-500"
                  :class="
                    page.active !== 0
                      ? 'fas fa-check text-green-600'
                      : 'fas fa-times text-red-600'
                  "
                ></i>
                <p
                  class="text-sm font-bold"
                  :class="page.active === 0 ? 'text-red-600' : 'text-green-600'"
                >
                  {{ page.active === 0 ? "Neaktivní" : "Aktivní" }}
                </p>
              </div>
              <div
                v-if="page.url === '/'"
                class="flex space-x-1 items-center bg-orange-100 p-2 pt-1 pb-1 rounded-full text-[#e9a514]"
              >
                <i class="fa-solid fa-web-awesome"></i>
                <p class="text-sm font-bold">Úvodní stránka</p>
              </div>
            </div>
            <div class="flex space-x-2 items-center bg-gray-200 p-2 rounded-full">
              <div v-if="menuActive[pageIndex]" class="flex space-x-2">
                <button
                  v-tooltip.top="'Upravit'"
                  @click="editRow(pageIndex)"
                  class="bg-green-600 flex space-x-1 justify-center items-center w-6 h-6 rounded-full text-gray-200 hover:bg-green-500"
                >
                  <i class="fas fa-pen text-xs"></i>
                </button>
                <button
                  @click="addSubpage(pageIndex)"
                  v-tooltip.top="'Přidat podstránku'"
                  class="bg-gray-300 flex space-x-1 justify-center items-center w-6 h-6 rounded-full hover:bg-gray-400"
                >
                  <i class="fas fa-plus text-xs"></i>
                </button>
                <button
                  @click="deleteRow(page.id, pageIndex)"
                  v-tooltip.top="'Smazat'"
                  class="bg-red-600 flex space-x-1 justify-center items-center w-6 h-6 rounded-full text-gray-200 text-xs hover:bg-red-500"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <button
                @click="menuOpen(pageIndex)"
                class="flex space-x-1 justify-center items-center w-6 h-6 rounded-full hover:scale-125 transition duration-200 delay-100"
              >
                <i
                  :class="
                    menuActive[pageIndex] === true
                      ? 'fa-solid fa-xmark'
                      : 'fas fa-ellipsis-v'
                  "
                ></i>
              </button>
            </div>
          </div>
          <div v-else class="flex items-center space-x-2 justify-between">
            <div class="flex justify-center items-center space-x-2 mb-4">
              <span class="text-sm text-gray-500">Stav</span>
              <ToggleSwitch
                v-model="page.active"
                :true-value="1"
                :false-value="0"
                value=""
                inputId="active"
              >
                <template #handle="{ checked }">
                  <i
                    :class="[
                      '!text-xs',
                      { 'fas fa-check': checked, 'fas fa-times': !checked },
                    ]"
                  />
                </template>
              </ToggleSwitch>
            </div>
            <div class="flex space-x-2 items-center">
              <button
                @click="savePage(pageIndex)"
                v-tooltip.top="'Uložit'"
                class="bg-green-600 flex space-x-1 justify-center items-center w-8 h-8 rounded-full text-gray-200 hover:bg-green-500"
              >
                <i class="fas fa-check"></i>
              </button>
              <button
                @click="cancelEdit"
                v-tooltip.top="'Zrušit'"
                class="bg-gray-300 flex space-x-1 justify-center items-center w-8 h-8 rounded-full hover:bg-gray-400"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex-1 flex space-x-5">
              <template v-if="editingIndex === pageIndex">
                <FloatLabel variant="on" class="w-1/3">
                  <InputText id="title" v-model="page.title" class="w-full" />
                  <label for="title">Zadejte název stránky</label>
                </FloatLabel>
                <FloatLabel variant="on" class="w-1/3">
                  <InputText id="url" v-model="page.url" class="w-full" />
                  <label for="url">Zadejte URL</label>
                </FloatLabel>
                <div class="flex items-center gap-2">
                  <Checkbox
                    v-model="page.sellable"
                    :true-value="1"
                    :false-value="0"
                    value=""
                    inputId="sellable"
                    binary
                  />
                  <label for="sellable">Prodejní sekce</label>
                </div>
              </template>
              <template v-else>
                <div class="flex flex-col w-1/3">
                  <span class="text-sm text-gray-500">Název stránky:</span>
                  <h2 class="text-lg font-bold">{{ page.title }}</h2>
                </div>
                <div class="flex flex-col w-1/3">
                  <span class="text-sm text-gray-500">URL stránky:</span>
                  <p class="text-sm text-gray-700">{{ page.url || "/" }}</p>
                </div>
                <div class="flex flex-col w-1/3">
                  <span class="text-sm text-gray-500">Prodejní sekce:</span>
                  <p class="text-sm text-gray-700">
                    {{ page.sellable === 1 ? "Ano" : "Ne" }}
                  </p>
                </div>
              </template>
            </div>
          </div>

          <!-- Subpages -->
          <div v-if="page.subpages && page.subpages.length" class="ml-6 mt-0">
            <span class="text-sm text-gray-500">Podstránky</span>
            <div
              v-for="(subpage, subIndex) in page.subpages"
              :key="subpage.id || subIndex"
              class="p-2 border rounded-lg bg-gray-200 flex items-center justify-between mb-3"
            >
              <div v-if="subpage.editing" class="flex items-center space-x-2 mr-2">
                <div class="flex justify-center items-center space-x-2">
                  <span class="text-sm text-gray-500">Stav</span>
                  <ToggleSwitch
                    v-tooltip.top="'Aktivní/neaktivní stránka'"
                    v-model="subpage.active"
                    :true-value="1"
                    :false-value="0"
                    value=""
                    inputId="active"
                  >
                    <template #handle="{ checked }">
                      <i
                        :class="[
                          '!text-xs',
                          { 'fas fa-check': checked, 'fas fa-times': !checked },
                        ]"
                      />
                    </template>
                  </ToggleSwitch>
                </div>
              </div>

              <div v-else class="flex items-center space-x-1 mr-2">
                <div
                  class="flex space-x-1 justify-center items-center w-6 h-6 rounded-full"
                  :class="subpage.active === 0 ? 'bg-red-200' : 'bg-green-200'"
                >
                  <i
                    class="text-sm text-gray-500"
                    :class="
                      subpage.active !== 0
                        ? 'fas fa-check text-green-600'
                        : 'fas fa-times text-red-600'
                    "
                  ></i>
                </div>
              </div>

              <div class="flex-1 flex space-x-4 w-3/4">
                <template v-if="subpage.editing">
                  <FloatLabel variant="on" class="w-1/3">
                    <InputText id="sub-title" v-model="subpage.title" />
                    <label for="sub-title">Název podstránky</label>
                  </FloatLabel>
                  <FloatLabel variant="on" class="w-1/3">
                    <InputText id="sub-url" v-model="subpage.url" />
                    <label for="sub-url">URL podstránky</label>
                  </FloatLabel>
                  <div class="flex items-center gap-2">
                    <Checkbox
                      v-model="subpage.sellable"
                      inputId="sellableSub"
                      :true-value="1"
                      :false-value="0"
                      value=""
                      binary
                    />
                    <label for="sellableSub">Prodejní sekce</label>
                  </div>
                </template>
                <template v-else>
                  <div class="flex flex-col w-1/3">
                    <span class="text-sm text-gray-500">Název podstránky:</span>
                    <h3 class="font-semibold">{{ subpage.title }}</h3>
                  </div>
                  <div class="flex flex-col w-1/3">
                    <span class="text-sm text-gray-500">URL podstránky:</span>
                    <p class="text-sm text-gray-700">{{ subpage.url }}</p>
                  </div>
                  <div class="flex flex-col w-1/3">
                    <span class="text-sm text-gray-500">Prodejní sekce:</span>
                    <p class="text-sm text-gray-700">
                      {{ subpage.sellable === 1 ? "Ano" : "Ne" }}
                    </p>
                  </div>
                </template>
              </div>
              <div class="flex items-center space-x-2 h-[60px]">
                <template v-if="subpage.editing">
                  <button
                    @click="saveSubpage(pageIndex, subIndex)"
                    v-tooltip.top="'Uložit'"
                    class="bg-green-600 flex space-x-1 justify-center items-center w-6 h-6 text-xs rounded-full text-gray-200 hover:bg-green-500"
                  >
                    <i class="fas fa-check"></i>
                  </button>
                  <button
                    @click="cancelSubpageEdit(pageIndex, subIndex)"
                    v-tooltip.top="'Zrušit'"
                    class="bg-gray-300 flex space-x-1 justify-center items-center w-6 h-6 text-xs rounded-full hover:bg-gray-400"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </template>
                <template v-else>
                  <div
                    class="flex space-x-2 items-center bg-gray-100 transition-bg-gray-100 duration-100 p-2 rounded-full"
                    :class="
                      menuSubActive[subIndex] ||
                      'bg-gray-200 transition-bg-gray-200 duration-100'
                    "
                  >
                    <div class="flex space-x-2">
                      <button
                        @click="editSubpage(pageIndex, subIndex)"
                        v-tooltip.top="'Upravit'"
                        class="bg-green-600 flex space-x-1 justify-center items-center w-6 h-6 text-xs rounded-full text-gray-200 hover:bg-green-500"
                        :class="
                          menuSubActive[subIndex] ||
                          'opacity-0 transition-opacity duration-100'
                        "
                        :disabled="!menuSubActive[subIndex]"
                      >
                        <i class="fas fa-pen"></i>
                      </button>
                      <button
                        @click="deleteSubpage(subpage.id, index, subIndex)"
                        v-tooltip.top="'Smazat'"
                        class="bg-red-600 flex space-x-1 justify-center items-center w-6 h-6 text-xs rounded-full text-gray-200 hover:bg-red-500"
                        :class="
                          menuSubActive[subIndex] ||
                          'opacity-0 transition-opacity duration-100'
                        "
                        :disabled="!menuSubActive[subIndex]"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                    <button
                      @click="menuSubOpen(subIndex)"
                      class="flex space-x-1 justify-center items-center w-6 h-6 rounded-full hover:scale-125 transition duration-200 delay-100"
                    >
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { reactive, ref, defineProps } from "vue";
import { useGlobalStore } from "@/stores/globalStore";

const globalStore = useGlobalStore();

const props = defineProps({
  pages: Array,
  subpages: Array,
});

const editingIndex = ref(null);
const originalPages = reactive([]);
const editingSubIndex = ref(null);
const menuActive = reactive({});
const menuSubActive = reactive({});
const currentPageIndex = ref(null); // Ukládá aktuální index stránky

const menuOpen = (pageIndex) => {
  if (menuActive[pageIndex] === true) {
    menuActive[pageIndex] = false;
    return;
  }
  menuActive[pageIndex] = true;
};

const menuSubOpen = (subIndex) => {
  if (menuSubActive[subIndex] === true) {
    menuSubActive[subIndex] = false;
    return;
  }
  menuSubActive[subIndex] = true;
};

const addRow = () => {
  props.pages.push({
    id: null,
    title: "",
    url: "",
    sellable: 0,
    subpages: [],
    active: 1,
  });
  editingIndex.value = props.pages.length - 1;
};

const addSubpage = (pageIndex) => {
  const page = props.pages[pageIndex];

  if (!page.subpages) page.subpages = [];

  page.subpages.push({
    id: null,
    title: "",
    url: "",
    sellable: 0,
    editing: true,
    page_id: page.id,
    active: 1,
  });

  editingSubIndex.value = page.subpages.length;
};

const editRow = (index) => {
  if (editingIndex.value !== null) {
    globalStore.setGlobalError("Nejprve uložte nebo zrušte editaci aktuální stránky!");
    return;
  }

  if (editingSubIndex.value !== null) {
    globalStore.setGlobalError("Nejprve uložte nebo zrušte editaci aktuální podstránky!");
    return;
  }

  editingIndex.value = index;
  originalPages[index] = { ...props.pages[index] };
};

const editSubpage = (pageIndex, subIndex) => {
  if (editingIndex.value !== null) {
    globalStore.setGlobalError("Nejprve uložte nebo zrušte editaci aktuální stránky!");
    return;
  }

  if (editingSubIndex.value !== null) {
    globalStore.setGlobalError("Nejprve uložte nebo zrušte editaci aktuální podstránky!");
    return;
  }

  editingSubIndex.value = subIndex;
  const subpage = props.pages[pageIndex].subpages[subIndex];
  subpage.editing = true;
};

const savePage = (index) => {
  if (!validateUrl(index)) {
    return;
  }

  const page = props.pages[index];
  if (page.title === "") {
    page.title = "Nová stránka";
  }
  if (page.id) {
    router.put(`/admin/pages/${page.id}`, page, {
      onSuccess: () => {
        globalStore.setGlobalSuccess("Stránka upravena!");
      },
    });
  } else {
    router.post("/admin/pages", page, {
      onSuccess: () => {
        globalStore.setGlobalSuccess("Stránka vytvořena!");
      },
    });
  }
  editingIndex.value = null;
  editingSubIndex.value = null;
};

const saveSubpage = (pageIndex, subIndex) => {
  const page = props.pages[pageIndex];
  const subpage = page.subpages[subIndex];

  if (!validateUrl(pageIndex, subIndex)) {
    return;
  }

  if (subpage.id) {
    router.put(`/admin/subpage/${subpage.id}`, subpage, {
      onSuccess: () => {
        globalStore.setGlobalSuccess("Podstránka upravena!");
      },
      onError: (errors) => {
        globalStore.setGlobalError("Chyba při ukládání podstránky!");
      },
    });
  } else {
    router.post("/admin/subpage", subpage, {
      onSuccess: () => {
        globalStore.setGlobalSuccess("Podstránka vytvořena!");
      },
      onError: (errors) => {
        console.error(errors);
        globalStore.setGlobalError("Chyba při vytváření podstránky!");
      },
    });
  }
  editingIndex.value = null;
  editingSubIndex.value = null;
};

const cancelEdit = () => {
  if (props.pages[editingIndex.value].id === null) {
    props.pages.pop();
  } else {
    props.pages[editingIndex.value] = originalPages[editingIndex.value];
  }
  editingIndex.value = null;
};

const cancelSubpageEdit = (pageIndex, subIndex) => {
  const subpage = props.pages[pageIndex].subpages[subIndex];
  if (subpage.id === null) {
    props.pages[pageIndex].subpages.pop();
  } else {
    subpage.editing = false;
  }
  editingSubIndex.value = null;
};

const deleteRow = (id, index) => {
  if (confirm("Opravdu chcete tuto stránku smazat?")) {
    if (id) {
      router.delete(`/admin/pages/${id}`);
      globalStore.setGlobalSuccess("Stránka smazána!");
    }
    props.pages.splice(index, 1);
  }
};

const deleteSubpage = (id, index, subIndex) => {
  if (confirm("Opravdu chcete tuto stránku smazat?")) {
    if (id) {
      router.delete(`/admin/subpage/${id}`);
      globalStore.setGlobalSuccess("Podstránka smazána!");
    }
  }
};

const validateUrl = (pageIndex, subIndex = null) => {
  let urlToValidate;

  // Rozhodneme, jestli validujeme stránku nebo podstránku
  if (subIndex === null) {
    urlToValidate = props.pages[pageIndex].url;
  } else {
    urlToValidate = props.pages[pageIndex].subpages[subIndex].url;
  }

  // Pokud `urlToValidate` je null nebo undefined, nastavíme ho na prázdný řetězec
  if (!urlToValidate) {
    urlToValidate = "";
  }

  // Přidáme `/`, pokud chybí
  if (!urlToValidate.startsWith("/")) {
    urlToValidate = "/" + urlToValidate;
    if (subIndex === null) {
      props.pages[pageIndex].url = urlToValidate;
    } else {
      props.pages[pageIndex].subpages[subIndex].url = urlToValidate;
    }
  }

  // Zkontrolujeme duplicity mezi stránkami a podstránkami
  const allUrls = new Set();

  props.pages.forEach((page, idx) => {
    // Přidáme URL stránky
    if (idx !== pageIndex && page.url) {
      allUrls.add(page.url);
    }

    // Přidáme URL podstránek
    if (page.subpages && page.subpages.length > 0) {
      page.subpages.forEach((subpage, subIdx) => {
        if ((idx !== pageIndex || subIdx !== subIndex) && subpage.url) {
          allUrls.add(subpage.url);
        }
      });
    }
  });

  if (allUrls.has(urlToValidate)) {
    globalStore.setGlobalError("URL musí být unikátní!");
    return false;
  }

  return true;
};
</script>

<style scoped>
.bubble {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid #d1d5db;
  background-color: #e0f2fe;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.bubble-title {
  font-size: 1.25rem;
  font-weight: bold;
}
</style>
