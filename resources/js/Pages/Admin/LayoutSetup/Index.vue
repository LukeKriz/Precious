<template>
  <div>
    <ConfirmDialog />
    <AdminLayout
      title="Rozložení a design"
      description="Zde si prosím vyberte základní rozložení stránky a základní design jednolivých komponent."
      :home="{ icon: 'fas fa-home', route: '/admin' }"
      :breadcrumbItems="[{ label: 'Rozložení a design', route: '/admin/layout' }]"
    >
      <Stepper v-model:value="activeStep">
        <StepItem value="1">
          <Step><span class="font-bold">Vyberte barevné schéma stránky</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepColors
              :mainDesign="mainDesign"
              @goNext="
                activeStep = '2';
                activateCallback('2');
              "
              @saveMainDesign="saveMainDesign"
            />
          </StepPanel>
        </StepItem>

        <StepItem value="2">
          <Step><span class="font-bold">Vyberte font webu</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepFonts
              :mainDesign="mainDesign"
              @goPrev="
                activeStep = '1';
                activateCallback('1');
              "
              @goNext="
                activeStep = '3';
                activateCallback('3');
              "
            />
          </StepPanel>
        </StepItem>

        <StepItem value="3">
          <Step><span class="font-bold">Vyberte rozložení pro každou stránku</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepLayouts
              :pages="pages"
              :subpages="subpages"
              :layouts="props.layouts"
              :selectedLayoutId="selectedLayoutId"
              :visibleDialogs="visibleDialogs"
              :visibleEditDialogs="visibleEditDialogs"
              :pageDesignData="pageDesignData"
              :menuPosition="menuPosition"
              :logoPosition="logoPosition"
              :selectedPageUrl="selectedPageUrl"
              @goPrev="
                activeStep = '2';
                activateCallback('2');
              "
              @goNext="
                activeStep = '4';
                activateCallback('4');
              "
              @selectPage="selectPage"
              @requireConfirmation="requireConfirmation"
              @showDialog="showDialog"
              @showEditDialog="showEditDialog"
              @saveLayout="saveLayout"
            />
          </StepPanel>
        </StepItem>

        <StepItem value="4">
          <Step><span class="font-bold">Nastavení hlavičky</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepHeader
              :mainDesign="mainDesign"
              @onLogoSelected="onLogoSelected"
              @deleteLogo="deleteLogo"
              @updateHeader="saveHeader"
              @goPrev="
                activeStep = '3';
                activateCallback('3');
              "
              @goNext="
                activeStep = '5';
                activateCallback('5');
              "
            />
          </StepPanel>
        </StepItem>

        <StepItem value="5">
          <Step><span class="font-bold">Nastavení banneru pro stránku</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepBanner
              :pages="props.pages"
              :subpages="props.subpages"
              :selectedPageUrl="selectedPageUrl"
              :selectedPageId="selectedPageId"
              :selectedPageType="selectedPageType"
              :pageDesignData="pageDesignData"
              :mainDesign="mainDesign"
              @goPrev="
                activeStep = '4';
                activateCallback('4');
              "
              @goNext="
                activeStep = '6';
                activateCallback('6');
              "
              @selectPage="selectPage"
              @deleteBannerItem="deleteBannerItem"
            />
          </StepPanel>
        </StepItem>

        <StepItem value="6">
          <Step><span class="font-bold">Vyberte vzhled inputů</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepInputs
              :mainDesign="mainDesign"
              @goPrev="
                activeStep = '5';
                activateCallback('5');
              "
              @goNext="
                activeStep = '7';
                activateCallback('7');
              "
            />
          </StepPanel>
        </StepItem>

        <StepItem value="7">
          <Step><span class="font-bold">Vyberte vzhled jednotlivých tlačítek</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepButtons
              :mainDesign="mainDesign"
              @goPrev="
                activeStep = '6';
                activateCallback('6');
              "
              @goNext="
                activeStep = '8';
                activateCallback('8');
              "
            />
          </StepPanel>
        </StepItem>

        <StepItem value="8">
          <Step><span class="font-bold">Pozadí stránky</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepBackground
              :mainDesign="mainDesign"
              @goPrev="
                activeStep = '7';
                activateCallback('7');
              "
              @goNext="
                activeStep = '9';
                activateCallback('9');
              "
              @saveMainDesign="saveMainDesign"
            />
          </StepPanel>
        </StepItem>

        <StepItem value="9">
          <Step><span class="font-bold">Nastavení patičky</span></Step>
          <StepPanel v-slot="{ activateCallback }">
            <StepFooter
              :mainDesign="mainDesign"
              @goPrev="
                activeStep = '8';
                activateCallback('8');
              "
              @goNext="
                activeStep = '10';
                activateCallback('10');
              "
              @saveMainDesign="saveMainDesign"
              :pages="props.pages"
              :subpages="props.subpages"
            />
          </StepPanel>
        </StepItem>
      </Stepper>
    </AdminLayout>
  </div>
</template>

<script setup>
import { router, useRemember } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { reactive, ref, defineProps, onMounted, watch, computed } from "vue";
import { useToastStore } from "@/stores/globalStore";
import { useConfirm } from "primevue/useconfirm";
import axios from "axios";

import StepHeader from "./Steps/StepHeader.vue";
import StepLayouts from "./Steps/StepLayouts.vue";
import StepBanner from "./Steps/StepBanner.vue";
import StepColors from "./Steps/StepColors.vue";
import StepButtons from "./Steps/StepButtons.vue";
import StepFonts from "./Steps/StepFonts.vue";
import StepInputs from "./Steps/StepInputs.vue";
import StepBackground from "./Steps/StepBackground.vue";
import StepFooter from "./Steps/StepFooter.vue";

const props = defineProps({
  layouts: Array,
  pageDesign: Object,
  pages: Array,
  subpages: Array,
  mainDesign: Object,
});

const toast = useToastStore();
const confirm = useConfirm();

/** ✅ 1) zapamatování kroku */
const activeStep = useRemember("1", "admin.layoutSetup.activeStep");

/** ✅ 2) zapamatování vybrané položky (page/subpage) */
const selectedPageRemember = useRemember(
  { id: null, url: "/", type: "page" },
  "admin.layoutSetup.selectedPage"
);

/**
 * ✅ spojení pages + subpages do jednoho seznamu do Tabů
 * - _type slouží pro backend target
 */
const allPages = computed(() => {
  const p = (props.pages || []).map((x) => ({ ...x, _type: "page" }));
  const s = (props.subpages || []).map((x) => ({
    ...x,
    _type: "subpage",
    title: x.title,
  }));
  return [...p, ...s];
});

const mainDesign = reactive({
  // background
  page_background: null,

  // colors
  primary_color: "#ffffff",
  secondary_color: "#000000",
  tertiary_color: "#ffffff",
  quaternary_color: "#ffffff",
  quinary_color: "#ffffff",

  // header
  logo_url: null,
  logo_width: null,
  logo_height: null,
  header_background: null,
  header_color: null,
  header_color_hover: null,
  submenu_background: null,
  submenu_color: null,
  submenu_hover: null,

  // fonts
  font_type: null,
  font_type_2: null,
  font_type_3: null,

  // buttons
  button_type: null,
  button_color: null,
  button_text_color: null,
  button_hover_color: null,
  button_hover_text_color: null,
  button_border_width: null,
  button_border_color: null,
  button_border_hover_color: null,
  button_font_weight: null,
  button_border_enabled: null,

  // inputs
  input_background_color: null,
  input_text_color: null,
  input_border_enabled: null,
  input_border_width: null,
  input_border_color: null,
  input_radius: null,
  input_font_weight: null,
  input_hover_border_color: null,
  input_focus_ring_enabled: null,
  input_focus_ring_color: null,
  input_focus_ring_width: null,

  // ✅ footer
  footer_background: null,
  footer_color: null,
  footer_columns: null,
  footer_content: null,
});

const pageDesignData = reactive({
  ...props.pageDesign,
  banner_id: null,
  banner_url: null,
  banner_type: "static",
  banner_count: 1,
  banners: [],
});

const selectedLayoutId = ref(null);
const visibleDialogs = ref({});
const visibleEditDialogs = ref({});

const selectedPageId = ref(null);
const selectedPageUrl = ref("/");
const selectedPageType = ref("page"); // ✅ page|subpage

const withTarget = (url) => {
  const t = selectedPageType.value || "page";
  return url.includes("?") ? `${url}&target=${t}` : `${url}?target=${t}`;
};

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

const resetBannerState = () => {
  pageDesignData.banner_id = null;
  pageDesignData.banner_url = null;
  pageDesignData.banner_type = "static";
  pageDesignData.banner_count = 1;
  pageDesignData.banners = [];
  pageDesignData.id = null;
  pageDesignData.page_id = null;
  pageDesignData.subpage_id = null;
};

const hydrateFromShow = (data) => {
  pageDesignData.header_height = data?.header_height ?? null;
  pageDesignData.banner_height = data?.banner_height ?? null;
  pageDesignData.category_weight = data?.category_weight ?? null;
  pageDesignData.category_height = data?.category_height ?? null;
  pageDesignData.footer_height = data?.footer_height ?? null;
  pageDesignData.logo_position = data?.logo_position ?? null;
  pageDesignData.menu_position = data?.menu_position ?? null;
  pageDesignData.id = data?.id ?? null;
  pageDesignData.page_id = data?.page_id ?? null;
  pageDesignData.subpage_id = data?.subpage_id ?? null;
  pageDesignData.banner_id = data?.banner_id ?? null;

  pageDesignData.banner_url = data?.banner?.banner_url ?? null;
  pageDesignData.banner_type = data?.banner?.banner_type ?? "static";
  pageDesignData.banner_count =
    data?.banner?.banner_type === "slider" ? Number(data?.banner?.banner_count ?? 3) : 1;

  pageDesignData.banners = Array.isArray(data?.banners) ? data.banners : [];
};

const selectPage = async (page) => {
  selectedPageId.value = page.id;
  selectedPageUrl.value = page.url;
  selectedPageType.value = page._type || "page";

  selectedPageRemember.id = page.id;
  selectedPageRemember.url = page.url;
  selectedPageRemember.type = selectedPageType.value;

  try {
    const response = await axios.get(
      withTarget(`/admin/page-design/${selectedPageId.value}`)
    );

    if (response.data) {
      hydrateFromShow(response.data);
      selectedLayoutId.value = response.data.layout_id ?? null;
    } else {
      selectedLayoutId.value = null;
      resetBannerState();
    }
  } catch (e) {
    if (e?.response?.status === 404) {
      selectedLayoutId.value = null;
      resetBannerState();
      return;
    }
    toast.showToast("error", "Chyba", e?.response?.data?.message || "Načtení selhalo");
    selectedLayoutId.value = null;
    resetBannerState();
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
    onSuccess: () => (selectedLayoutId.value = null),
    onError: () => toast.showToast("error", "Chyba", "Nepodařilo se odstranit layout!"),
  });
};

const requireConfirmation = (layoutId) => {
  if (!selectedPageId.value) {
    toast.showToast("info", "Vyberte stránku", "Nejprve vyberte stránku v tabech.");
    return;
  }

  if (String(selectedLayoutId.value) === String(layoutId)) {
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
      reject: () => toast.showToast("info", "Zachováno", "Layout zůstal vybraný."),
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
      reject: () => toast.showToast("error", "Zrušeno", "Výběr byl zrušen."),
    });
  }
};

const saveLayout = (layoutId) => {
  const payload = {
    ...pageDesignData,
    layout_id: layoutId,
    page_id: selectedPageType.value === "page" ? selectedPageId.value : null,
    subpage_id: selectedPageType.value === "subpage" ? selectedPageId.value : null,
  };

  router.post(withTarget("/admin/page-design"), payload, {
    onSuccess: () => {
      toast.showToast("success", "Uloženo", "Rozložení bylo úspěšně uloženo!");
      visibleEditDialogs.value[layoutId] = false;
    },
    onError: () => toast.showToast("error", "Chyba", "Došlo k chybě při ukládání!"),
  });
};

/** ✅ slider delete item */
const deleteBannerItem = (bannerId) => {
  if (!selectedPageId.value) return;

  confirm.require({
    message: "Opravdu chcete smazat tento banner ze slideru?",
    header: "Smazat banner",
    icon: "fa-regular fa-circle-question",
    acceptLabel: "Ano, smazat",
    rejectLabel: "Ne",
    acceptClass: "p-button-danger",
    rejectClass: "p-button-secondary",
    accept: async () => {
      try {
        await axios.delete(
          withTarget(
            `/admin/page-design/${selectedPageId.value}/banner-items/${bannerId}`
          )
        );
        await selectPage({
          id: selectedPageId.value,
          url: selectedPageUrl.value,
          _type: selectedPageType.value,
        });
        toast.showToast("success", "Smazáno", "Banner byl odstraněn");
      } catch (e) {
        toast.showToast(
          "error",
          "Chyba",
          e?.response?.data?.message || "Nepodařilo se smazat banner"
        );
      }
    },
  });
};

/** LOGO */
/** LOGO */
const onLogoSelected = async (payload) => {
  // payload: { file, logo_width, logo_height }
  const file = payload?.file;
  if (!file) return;

  const form = new FormData();
  form.append("logo", file);

  // ✅ přidat rozměry do requestu
  if (payload.logo_width) form.append("logo_width", String(payload.logo_width));
  if (payload.logo_height) form.append("logo_height", String(payload.logo_height));

  try {
    const { data } = await axios.post("/admin/main-design/logo", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (data?.logo_url) {
      mainDesign.logo_url = data.logo_url;

      // ✅ uložit i reálné rozměry vrácené z backendu (po resize)
      if (data.logo_width) mainDesign.logo_width = data.logo_width;
      if (data.logo_height) mainDesign.logo_height = data.logo_height;

      toast.showToast("success", "Uloženo", "Logo nahráno");
    }
  } catch (err) {
    toast.showToast("error", "Chyba", "Logo se nepodařilo nahrát");
  }
};

const deleteLogo = () => {
  confirm.require({
    message: "Opravdu chcete smazat logo? Tato akce je nevratná.",
    header: "Smazat logo",
    icon: "fa-regular fa-circle-question",
    acceptLabel: "Ano, smazat",
    rejectLabel: "Ne",
    acceptClass: "p-button-danger",
    rejectClass: "p-button-secondary",
    accept: async () => {
      try {
        await axios.delete("/admin/main-design/logo");
        mainDesign.logo_url = null;
        toast.showToast("success", "Smazáno", "Logo bylo odstraněno");
      } catch (e) {
        toast.showToast("error", "Chyba", "Nepodařilo se smazat logo");
      }
    },
  });
};

const saveHeader = async (payload) => {
  try {
    await axios.post("/admin/main-design", payload);

    const { data } = await axios.get("/admin/main-design", {
      headers: { Accept: "application/json" },
    });

    if (data) {
      Object.keys(mainDesign).forEach((k) => {
        if (k in data) mainDesign[k] = data[k];
      });
    }
  } catch (e) {
    toast.showToast("error", "Chyba", "Hlavičku se nepodařilo uložit");
  }
};

/** ✅ SAVE MAIN DESIGN (full or patch) */
const saveMainDesign = (patch = null) => {
  if (patch && typeof patch === "object") {
    Object.keys(patch).forEach((k) => {
      mainDesign[k] = patch[k];
    });
  }

  const payload = patch && typeof patch === "object" ? patch : { ...mainDesign };

  router.post("/admin/main-design", payload, {
    preserveScroll: true,
    onSuccess: async () => {
      try {
        const { data } = await axios.get("/admin/main-design", {
          headers: { Accept: "application/json" },
        });
        if (data) {
          Object.keys(mainDesign).forEach((k) => {
            if (k in data) mainDesign[k] = data[k];
          });
        }
      } catch (e) {
        console.error(e?.response?.data || e);
      }
    },
    onError: () => toast.showToast("error", "Chyba", "Nelze uložit"),
  });
};

/* ===================== FOOTER CONTENT HELPERS ===================== */
const ensureFooterContentShape = (val) => {
  if (!val) return { columns: {} };

  if (typeof val === "string") {
    try {
      const parsed = JSON.parse(val);
      if (parsed && typeof parsed === "object") return parsed;
    } catch {}
    return { columns: {} };
  }

  if (typeof val === "object") {
    if (!val.columns || typeof val.columns !== "object") return { ...val, columns: {} };
    return val;
  }

  return { columns: {} };
};

const createDefaultComponent = (type) => {
  const id =
    crypto?.randomUUID?.() || `cmp_${Date.now()}_${Math.random().toString(16).slice(2)}`;

  if (type === "menu") {
    return {
      id,
      type: "menu",
      data: {
        title: "Menu",
        items: [
          { label: "Položka 1", url: "/" },
          { label: "Položka 2", url: "/" },
        ],
      },
    };
  }

  if (type === "text") {
    return {
      id,
      type: "text",
      data: { title: "Nadpis", text: "Text v patičce…" },
    };
  }

  if (type === "image") {
    return {
      id,
      type: "image",
      data: { url: null, alt: "" },
    };
  }

  return { id, type, data: {} };
};

onMounted(() => {
  // restore selected page
  if (selectedPageRemember?.id) {
    const found = allPages.value.find(
      (x) =>
        x.id === selectedPageRemember.id &&
        (x._type || "page") === (selectedPageRemember.type || "page")
    );
    if (found) {
      selectPage(found);
    } else {
      const def = allPages.value.find((x) => x.url === "/" && x._type === "page");
      if (def) selectPage(def);
    }
  } else {
    const defaultPage = allPages.value.find(
      (page) => page.url === "/" && page._type === "page"
    );
    if (defaultPage) selectPage(defaultPage);
  }

  // hydrate mainDesign
  if (props.mainDesign) {
    const keys = [
      // colors
      "primary_color",
      "secondary_color",
      "tertiary_color",
      "quaternary_color",
      "quinary_color",

      // header
      "logo_url",
      "logo_width",
      "logo_height",
      "header_background",
      "header_color",
      "header_color_hover",
      "submenu_background",
      "submenu_color",
      "submenu_hover",

      // fonts
      "font_type",
      "font_type_2",
      "font_type_3",

      // buttons
      "button_type",
      "button_color",
      "button_text_color",
      "button_hover_color",
      "button_hover_text_color",
      "button_border_width",
      "button_border_color",
      "button_border_hover_color",
      "button_font_weight",
      "button_border_enabled",

      // inputs
      "input_background_color",
      "input_text_color",
      "input_border_enabled",
      "input_border_width",
      "input_border_color",
      "input_radius",
      "input_font_weight",
      "input_hover_border_color",
      "input_focus_ring_enabled",
      "input_focus_ring_color",
      "input_focus_ring_width",

      // background
      "page_background",

      // ✅ footer
      "footer_background",
      "footer_color",
      "footer_columns",
      "footer_content",
    ];

    keys.forEach((k) => {
      if (Object.prototype.hasOwnProperty.call(props.mainDesign, k)) {
        mainDesign[k] = props.mainDesign[k];
      }
    });

    // ✅ normalize footer_content to object (so addFooter works even if backend sends string)
    mainDesign.footer_content = ensureFooterContentShape(mainDesign.footer_content);
  }

  const savedStep = localStorage.getItem("admin.layoutSetup.activeStep");
  if (savedStep) {
    try {
      activeStep.value = savedStep;
    } catch (e) {}
  }
});

watch(
  () => {
    try {
      return activeStep.value;
    } catch {
      return activeStep;
    }
  },
  (v) => {
    if (v) localStorage.setItem("admin.layoutSetup.activeStep", String(v));
  }
);
</script>

<style scoped>
:deep(.tile) {
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

:deep(.tile:hover) {
  box-shadow: -8px -8px 16px rgba(255, 255, 255, 1), 8px 8px 16px rgba(0, 0, 0, 0.3);
  transform: translateY(-4px);
}

:deep(.tile-taken) {
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
