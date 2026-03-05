<template>
  <div>
    <!--  <div class="py-1">
      <Button
        class="mr-2"
        label="Předchozí krok"
        severity="secondary"
        @click="$emit('goPrev')"
      />
    </div> -->

    <div class="mt-1">
      <div class="card flex justify-start">
        <div class="w-half max-w-3xl bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold">Základní barvy webu</h3>
          </div>

          <div class="space-y-4">
            <!-- PRIMARY -->
            <div class="flex flex-col items-start justify-between gap-1">
              <label class="font-medium">Hlavní barva</label>
              <div class="flex items-center gap-3">
                <div class="relative w-10 h-10">
                  <div
                    class="w-10 h-10 rounded-full border shadow-sm"
                    :style="{ background: mainDesign.primary_color }"
                  ></div>

                  <input
                    type="color"
                    v-model="mainDesign.primary_color"
                    @change="$emit('saveMainDesign')"
                    class="absolute inset-0 w-10 h-10 opacity-0 cursor-pointer"
                  />
                </div>

                <input
                  v-model="mainDesign.primary_color"
                  class="w-28 rounded border px-2 py-1"
                  @change="$emit('saveMainDesign')"
                />
              </div>
            </div>

            <!-- SECONDARY -->
            <div class="flex flex-col items-start justify-between gap-1">
              <label class="font-medium">Druhá barva</label>
              <div class="flex items-center gap-3">
                <div class="relative w-10 h-10">
                  <div
                    class="w-10 h-10 rounded-full border shadow-sm"
                    :style="{ background: mainDesign.secondary_color }"
                  ></div>

                  <input
                    type="color"
                    v-model="mainDesign.secondary_color"
                    @change="$emit('saveMainDesign')"
                    class="absolute inset-0 w-10 h-10 opacity-0 cursor-pointer"
                  />
                </div>

                <input
                  v-model="mainDesign.secondary_color"
                  class="w-28 rounded border px-2 py-1"
                  @change="$emit('saveMainDesign')"
                />
              </div>
            </div>

            <!-- TERTIARY -->
            <div class="flex flex-col items-start justify-between gap-1">
              <label class="font-medium">Třetí barva</label>
              <div class="flex items-center gap-3">
                <div class="relative w-10 h-10">
                  <div
                    class="w-10 h-10 rounded-full border shadow-sm"
                    :style="{ background: mainDesign.tertiary_color }"
                  ></div>

                  <input
                    type="color"
                    v-model="mainDesign.tertiary_color"
                    @change="$emit('saveMainDesign')"
                    class="absolute inset-0 w-10 h-10 opacity-0 cursor-pointer"
                  />
                </div>

                <input
                  v-model="mainDesign.tertiary_color"
                  class="w-28 rounded border px-2 py-1"
                  @change="$emit('saveMainDesign')"
                />
              </div>
            </div>

            <!-- QUATERNARY -->
            <div class="flex flex-col items-start justify-between gap-1">
              <label class="font-medium">Čtvrtá barva</label>
              <div class="flex items-center gap-3">
                <div class="relative w-10 h-10">
                  <div
                    class="w-10 h-10 rounded-full border shadow-sm"
                    :style="{ background: mainDesign.quaternary_color }"
                  ></div>

                  <input
                    type="color"
                    v-model="mainDesign.quaternary_color"
                    @change="$emit('saveMainDesign')"
                    class="absolute inset-0 w-10 h-10 opacity-0 cursor-pointer"
                  />
                </div>

                <input
                  v-model="mainDesign.quaternary_color"
                  class="w-28 rounded border px-2 py-1"
                  @change="$emit('saveMainDesign')"
                />
              </div>
            </div>

            <!-- QUINARY -->
            <div class="flex flex-col items-start justify-between gap-1">
              <label class="font-medium">Pátá barva</label>
              <div class="flex items-center gap-3">
                <div class="relative w-10 h-10">
                  <div
                    class="w-10 h-10 rounded-full border shadow-sm"
                    :style="{ background: mainDesign.quinary_color }"
                  ></div>

                  <input
                    type="color"
                    v-model="mainDesign.quinary_color"
                    @change="$emit('saveMainDesign')"
                    class="absolute inset-0 w-10 h-10 opacity-0 cursor-pointer"
                  />
                </div>

                <input
                  v-model="mainDesign.quinary_color"
                  class="w-28 rounded border px-2 py-1"
                  @change="$emit('saveMainDesign')"
                />
              </div>
            </div>
          </div>
          <!-- /space-y-4 -->
        </div>
      </div>
    </div>

    <div class="py-6">
      <Button label="Další krok" @click="$emit('goNext')" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  mainDesign: {
    type: Object,
    required: true,
  },
});

defineEmits(["goPrev", "goNext", "saveMainDesign"]);
</script>
