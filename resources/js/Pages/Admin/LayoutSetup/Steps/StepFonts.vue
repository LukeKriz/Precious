<template>
  <div>
    <!-- navigace -->
    <div class="py-1">
      <Button
        class="mr-2"
        label="Předchozí krok"
        severity="secondary"
        @click="$emit('goPrev')"
      />
    </div>

    <!-- obsah -->
    <div class="mt-6">
      <div class="card flex justify-start">
        <div class="w-full max-w-4xl bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold">Výběr fontu webu</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <!-- FONT 1 -->
              <label class="font-medium block mb-2">Primární font</label>

              <select
                v-model="font1"
                @change="saveFonts"
                class="w-full rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300 px-3 py-2"
              >
                <option v-for="f in fonts" :key="f" :value="f">
                  {{ f }}
                </option>
              </select>

              <!-- + BUTTON -->
              <button
                v-if="!showFont2"
                class="mt-3 text-sm text-green-700 hover:underline"
                @click="addFont2"
                type="button"
              >
                + Přidat další font
              </button>

              <!-- FONT 2 -->
              <div v-if="showFont2" class="mt-6">
                <div class="flex items-center justify-between mb-2">
                  <label class="font-medium block">Sekundární font</label>

                  <!-- REMOVE FONT 2 -->
                  <button
                    type="button"
                    class="items-center justify-center rounded-lg bg-red-500 px-1 text-sm font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700"
                    title="Odebrat sekundární font"
                    @click="confirmRemoveFont2"
                  >
                    ×
                  </button>
                </div>

                <select
                  v-model="font2"
                  @change="saveFonts"
                  class="w-full rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300 px-3 py-2"
                >
                  <option value="">— žádný —</option>
                  <option v-for="f in fonts" :key="'f2-' + f" :value="f">
                    {{ f }}
                  </option>
                </select>

                <button
                  v-if="!showFont3"
                  class="mt-3 text-sm text-green-700 hover:underline"
                  @click="addFont3"
                  type="button"
                >
                  + Přidat třetí font
                </button>
              </div>

              <!-- FONT 3 -->
              <div v-if="showFont3" class="mt-6">
                <div class="flex items-center justify-between mb-2">
                  <label class="font-medium block">Třetí font</label>

                  <!-- REMOVE FONT 3 -->
                  <button
                    type="button"
                    class="items-center justify-center rounded-lg bg-red-500 px-1 text-sm font-semibold text-white shadow-sm hover:bg-red-600 active:bg-red-700"
                    title="Odebrat třetí font"
                    @click="confirmRemoveFont3"
                  >
                    ×
                  </button>
                </div>

                <select
                  v-model="font3"
                  @change="saveFonts"
                  class="w-full rounded border-gray-200 focus:outline-none focus:ring-1 focus:ring-green-300 focus:border-green-300 px-3 py-2"
                >
                  <option value="">— žádný —</option>
                  <option v-for="f in fonts" :key="'f3-' + f" :value="f">
                    {{ f }}
                  </option>
                </select>
              </div>

              <div v-if="isSaving" class="text-xs text-gray-500 mt-2">Ukládám…</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- další krok -->
    <div class="py-6">
      <Button label="Další krok" @click="$emit('goNext')" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import axios from "axios";
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const props = defineProps({
  mainDesign: { type: Object, required: true },
});

defineEmits(["goPrev", "goNext"]);

const fonts = [
  "Inter",
  "Roboto",
  "Roboto Condensed",
  "Open Sans",
  "Lato",
  "Montserrat",
  "Poppins",
  "Nunito",
  "Source Sans 3",
  "DM Sans",
  "Raleway",
  "Ubuntu",
  "Merriweather",
  "Playfair Display",
  "PT Sans",
  "PT Serif",
  "Work Sans",
  "Fira Sans",
  "Rubik",
  "Cabin",
  "Manrope",
  "IBM Plex Sans",
  "IBM Plex Serif",
  "Quicksand",
  "Josefin Sans",
  "Karla",
  "Mulish",
  "Hind",
  "Titillium Web",
  "Barlow",
  "Exo 2",
  "Arimo",
  "Heebo",
  "Archivo",
  "Overpass",
  "Varela Round",
  "Asap",
  "Assistant",
  "Red Hat Display",
  "Space Grotesk",
  "Urbanist",
];

const font1 = ref("");
const font2 = ref("");
const font3 = ref("");

const showFont2 = ref(false);
const showFont3 = ref(false);

const isSaving = ref(false);

watch(
  () => [
    props.mainDesign?.font_type,
    props.mainDesign?.font_type_2,
    props.mainDesign?.font_type_3,
  ],
  ([f1, f2, f3]) => {
    font1.value = f1 || "";
    font2.value = f2 || "";
    font3.value = f3 || "";

    showFont2.value = !!f2;
    showFont3.value = !!f3;
  },
  { immediate: true }
);

const bumpThemeCss = () => {
  const link = document.getElementById("theme-css");
  if (!link) return;
  link.setAttribute("href", `/theme.css?v=${Date.now()}`);
};

const saveFonts = async () => {
  isSaving.value = true;

  try {
    await axios.post(
      "/admin/main-design",
      {
        font_type: font1.value || null,
        font_type_2: showFont2.value ? font2.value || null : null,
        font_type_3: showFont3.value ? font3.value || null : null,
      },
      { headers: { Accept: "application/json" } }
    );

    bumpThemeCss();
  } catch (e) {
    console.error(e?.response?.data || e);
  } finally {
    isSaving.value = false;
  }
};

// ---- add/remove helpers ----
const addFont2 = () => {
  showFont2.value = true;
};

const addFont3 = () => {
  showFont3.value = true;
};

const removeFont3 = async () => {
  showFont3.value = false;
  font3.value = "";
  await saveFonts();
};

const removeFont2 = async () => {
  // odstranění font2 zruší i font3
  showFont2.value = false;
  showFont3.value = false;

  font2.value = "";
  font3.value = "";

  await saveFonts();
};

// ---- confirm wrappers ----
const confirmRemoveFont3 = () => {
  confirm.require({
    message: "Opravdu chcete odebrat třetí font?",
    header: "Odebrat font",
    icon: "fa-regular fa-circle-question",
    acceptLabel: "Ano, odebrat",
    rejectLabel: "Ne",
    acceptClass: "p-button-danger",
    rejectClass: "p-button-secondary",
    accept: async () => {
      await removeFont3();
    },
  });
};

const confirmRemoveFont2 = () => {
  confirm.require({
    message: "Opravdu chcete odebrat sekundární font? (Odebere se zároveň i třetí font.)",
    header: "Odebrat font",
    icon: "fa-regular fa-circle-question",
    acceptLabel: "Ano, odebrat",
    rejectLabel: "Ne",
    acceptClass: "p-button-danger",
    rejectClass: "p-button-secondary",
    accept: async () => {
      await removeFont2();
    },
  });
};
</script>
