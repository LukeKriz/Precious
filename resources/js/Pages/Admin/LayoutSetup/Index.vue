<template>
  <div>
    <ConfirmDialog />
    <AdminLayout
      title="Rozložení a design"
      description="Zde si prosím vyberte základní rozložení stránky a základní design jednolivých komponent."
      :home="{ icon: 'fas fa-home', route: '/admin' }"
      :breadcrumbItems="[{ label: 'Rozložení a design', route: '/admin/layout' }]"
    >
      <Stepper value="1">
        <StepItem value="1">
          <Step><span class="font-bold">Vyberte rozložení pro každou stránku</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <div class="mt-3">
              <div class="card flex justify-center flex-wrap">
                <Tabs value="/" scrollable>
                  <TabList class="mb-5">
                    <Tab
                      v-for="page in pages"
                      :key="page.id"
                      :value="page.url"
                      @click="selectPage(page)"
                    >
                      {{ page.title }}
                    </Tab>
                  </TabList>
                  <TabList class="mb-5" v-if="filteredSubpages.length">
                    <Tab
                      v-for="subpage in filteredSubpages"
                      :key="subpage.id"
                      :value="subpage.url"
                    >
                      {{ subpage.title }}
                    </Tab>
                  </TabList>
                  <TabPanels>
                    <TabPanel
                      v-for="page in pages"
                      :key="page.id"
                      :value="page.url"
                      class="flex justify-center items-center"
                    >
                      <div
                        v-for="layout in layouts"
                        :key="layout.id"
                        @click="requireConfirmation(layout.id)"
                        class="relative flex flex-col justify-center items-center w-1/5 h-w border rounded-lg m-2 bg-gray-100 shadow-lg tile"
                        :class="{ 'tile-taken': selectedLayoutId === layout.id }"
                      >
                        <div class="flex justify-between">
                          <button
                            label="show"
                            @click.stop="showDialog(layout.id)"
                            v-tooltip.top="'Více informací'"
                            class="absolute top-0 right-0 p-2 rounded-bl-lg rounded-tr-2xl text-gray-600 font-bold w-10 hover:text-blue-500 hover:bg-blue-100 hover:shadow-lg"
                          >
                            <i class="fa-solid fa-circle-info text-sx"></i>
                          </button>

                          <button
                            label="show"
                            v-if="selectedLayoutId === layout.id"
                            @click.stop="showEditDialog(layout.id)"
                            v-tooltip.top="'Upravit rozměry'"
                            class="absolute top-0 left-0 p-2 rounded-br-lg rounded-tl-2xl text-gray-600 font-bold w-10 hover:text-orange-500 hover:bg-orange-100 hover:shadow-lg"
                          >
                            <i class="fas fa-pen text-sx"></i>
                          </button>
                        </div>
                        <Dialog
                          v-model:visible="visibleDialogs[layout.id]"
                          modal
                          header="Detail layoutu"
                          :style="{ width: '50vw' }"
                        >
                          <template #header> </template>
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

                        <Dialog
                          v-model:visible="visibleEditDialogs[layout.id]"
                          modal
                          header="Upravit rozměry jednotlivých komponent"
                          :style="{ width: '40vw' }"
                        >
                          <template #header> </template>
                          <template #default>
                            <div class="flex flex-col justify-center items-center">
                              <div class="flex flex-col justify-center items-center pt-3">
                                <div class="flex gap-2 justify-center items-center mb-4">
                                  <label for="header-height"
                                    >Šířka hlavičky
                                    <span class="text-xs">(px)</span></label
                                  >
                                  <input
                                    v-model="pageDesignData.header_height"
                                    class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                                    type="number"
                                    inputId="header-height"
                                  />
                                </div>
                                <div class="flex gap-2 justify-center items-center mb-4">
                                  <label for="banner-height"
                                    >Šířka banneru
                                    <span class="text-xs">(px)</span></label
                                  >
                                  <input
                                    v-model="pageDesignData.banner_height"
                                    class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                                    type="number"
                                    inputId="banner-height"
                                  />
                                </div>
                                <div class="flex gap-2 justify-center items-center mb-4">
                                  <label for="logo-position">Pozice Loga</label>
                                  <SelectButton
                                    id="logo-position"
                                    v-model="pageDesignData.logo_position"
                                    :options="logoPosition"
                                    optionLabel="label"
                                    optionValue="value"
                                  />
                                </div>
                                <div class="flex gap-2 justify-center items-center mb-4">
                                  <label for="menu-position">Pozice Menu</label>
                                  <SelectButton
                                    id="menu-position"
                                    v-model="pageDesignData.menu_position"
                                    :options="menuPosition"
                                    optionLabel="label"
                                    optionValue="value"
                                  />
                                </div>
                                <div class="flex gap-2 justify-center items-center mb-4">
                                  <label for="footer-height"
                                    >Šířka patičky
                                    <span class="text-xs">(px)</span></label
                                  >
                                  <input
                                    v-model="pageDesignData.footer_height"
                                    class="rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300"
                                    type="number"
                                    id="footer-height"
                                  />
                                </div>
                              </div>
                              <Button class="w-1/3 mt-10" @click="saveLayout(layout.id)"
                                >Uložit</Button
                              >
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
              <Button label="Další krok" @click="activateCallback('2')" />
            </div>
          </StepPanel>
        </StepItem>

        <StepItem value="2">
          <Step><span class="font-bold">Vyberte barevné schéma stránky</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <div class="mt-6">
              <div class="card flex justify-center flex-wrap"></div>
            </div>
            <div class="py-6">
              <Button
                class="mr-2"
                label="Předchozí krok"
                @click="activateCallback('1')"
                severity="secondary"
              />
              <Button label="Další krok" @click="activateCallback('3')" />
            </div>
          </StepPanel>
        </StepItem>

        <StepItem value="3">
          <Step><span class="font-bold">Nahrejte logo</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <div class="mt-6">
              <div class="card flex justify-center flex-wrap"></div>
            </div>
            <div class="py-6">
              <Button
                class="mr-2"
                label="Předchozí krok"
                @click="activateCallback('2')"
                severity="secondary"
              />
              <Button label="Další krok" @click="activateCallback('4')" />
            </div>
          </StepPanel>
        </StepItem>

        <StepItem value="4">
          <Step><span class="font-bold">Nahrejte banner</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <div class="mt-6">
              <div class="card flex justify-center flex-wrap"></div>
            </div>
            <div class="py-6">
              <Button
                class="mr-2"
                label="Předchozí krok"
                @click="activateCallback('3')"
                severity="secondary"
              />
              <Button label="Další krok" @click="activateCallback('5')" />
            </div>
          </StepPanel>
        </StepItem>
      </Stepper>
    </AdminLayout>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { reactive, ref, defineProps } from "vue";
import { useGlobalStore, useToastStore } from "@/stores/globalStore";
import { useConfirm } from "primevue/useconfirm";
import axios from "axios";
import { onMounted } from "vue";
import { computed } from "vue";

const props = defineProps({
  layouts: Array,
  pageDesign: Object,
  pages: Array,
  subpages: Array,
});

const filteredSubpages = computed(() => {
  if (!selectedPageId.value) return [];
  return props.subpages.filter((subpage) => subpage.page_id === selectedPageId.value);
});

const globalStore = useGlobalStore();
const toast = useToastStore();
const confirm = useConfirm();

const pageDesignData = reactive({ ...props.pageDesign });
const selectedLayoutId = ref(null);
const visibleDialogs = ref({});
const visibleEditDialogs = ref({});
const menuPosition = ref([
  { label: "Vlevo", value: "left" },
  { label: "Uprostřed", value: "middle" },
  { label: "Vpravo", value: "right" },
]);

const logoPosition = ref([
  { label: "Vlevo", value: "left" },
  { label: "Uprostřed", value: "middle" },
  { label: "Vpravo", value: "right" },
]);

const selectedPageId = ref(null);

const selectPage = async (page) => {
  selectedPageId.value = page.id;

  try {
    const response = await axios.get(`/admin/page-design/${selectedPageId.value}`);

    if (response.data !== null) {
      pageDesignData.header_height = response.data.header_height;
      pageDesignData.banner_height = response.data.banner_height;
      pageDesignData.footer_height = response.data.footer_height;
      pageDesignData.logo_position = response.data.logo_position;
      pageDesignData.menu_position = response.data.menu_position;
      selectedLayoutId.value = response.data.layout_id;
    }
  } catch (error) {
    toast.showToast("info", "Doplňte hodnoty", "Prosím dopňte hodnoty pro tuto stránku.");
    selectedLayoutId.value = null;
  }
};

const showDialog = (layoutId) => {
  visibleDialogs.value[layoutId] = true;
};

const showEditDialog = (layoutId) => {
  visibleEditDialogs.value[layoutId] = true;
};

const deleteLayout = (pageId) => {
  router.delete(`/admin/page-design/${pageId}`, {
    preserveState: true,
    onSuccess: () => {
      selectedLayoutId.value = null;
    },
    onError: () => {
      toast.showToast("error", "Chyba", "Nepodařilo se odstranit layout!");
    },
  });
};

const requireConfirmation = (layoutId) => {
  if (selectedLayoutId.value === layoutId) {
    confirm.require({
      message: "Opravdu chcete zrušit výběr tohoto layoutu?",
      header: "Zrušení výběru",
      icon: "fa-regular fa-circle-question",
      acceptLabel: "Ano",
      rejectLabel: "Ne",
      acceptClass: "p-button-warning",
      rejectClass: "p-button-secondary",
      accept: async () => {
        deleteLayout(selectedPageId.value);
        selectedLayoutId.value = null;
        visibleEditDialogs.value[layoutId] = false;
        toast.showToast("success", "Uloženo", "Výběr byl úspěšně zrušen!");
      },
      reject: () => {
        toast.showToast("info", "Zachováno", "Layout zůstal vybraný.");
      },
    });
  } else {
    confirm.require({
      message: "Opravdu chcete vybrat tento layout?",
      header: "Potvrzení výběru",
      icon: "fa-regular fa-circle-question",
      acceptLabel: "Ano",
      rejectLabel: "Ne",
      acceptClass: "p-button-success",
      rejectClass: "p-button-danger",
      accept: () => {
        selectedLayoutId.value = layoutId;
        visibleEditDialogs.value[layoutId] = true;
      },
      reject: () => {
        toast.showToast("error", "Zrušeno", "Výběr byl zrušen.");
      },
    });
  }
};

const saveLayout = (layoutId) => {
  router.post(
    "/admin/page-design",
    {
      layout_id: layoutId,
      page_id: selectedPageId.value,

      ...pageDesignData,
    },
    {
      onSuccess: () => {
        toast.showToast("success", "Uloženo", "Rozložení bylo úspěšně uloženo!");
        visibleEditDialogs.value[layoutId] = false;
      },
      onError: () => {
        toast.showToast("error", "Chyba", "Došlo k chybě při ukládání!");
      },
    }
  );
};

onMounted(() => {
  const defaultPage = props.pages.find((page) => page.url === "/");
  if (defaultPage) {
    selectPage(defaultPage);
  }
});
</script>

<style scoped>
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  padding: 20px;
  background: #f0f0f3;
}

.tile {
  cursor: pointer;
  background: linear-gradient(to bottom right, #ffffff, #e6e6e6);
  border-radius: 24px;
  box-shadow: -6px -6px 12px rgba(255, 255, 255, 0.9), 6px 6px 12px rgba(0, 0, 0, 0.2);
  padding: 20px;
  text-align: center;
  font-size: 16px;
  font-weight: bold;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.tile:hover {
  box-shadow: -8px -8px 16px rgba(255, 255, 255, 1), 8px 8px 16px rgba(0, 0, 0, 0.3);
  transform: translateY(-4px);
}

.tile-taken {
  cursor: pointer;
  background: linear-gradient(to bottom right, #ffffff, #00ff5570);
  border-radius: 24px;
  box-shadow: -6px -6px 12px #6cfc4f49, 6px 6px 12px #18e2224b;
  padding: 20px;
  text-align: center;
  font-size: 16px;
  font-weight: bold;

  background-color: #f0f0f3;
  transform: translateY(-4px);
}
</style>
