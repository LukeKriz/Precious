<template>
  <div class="flex min-h-screen">
    <aside class="w-64 bg-gray-800 text-white p-4 flex flex-col md:justify-between p-4">
      <Link href="/admin">
        <h1 class="mb-10 mt-4 ml-2 text-3xl">PRECIOUS</h1>
      </Link>

      <div style="height: 80%">
        <h2 class="mb-4 mt-4 ml-2 text-md font-bold text-gray-100">Stránka</h2>
        <nav class="mb-20">
          <ul>
            <li>
              <Link href="/admin/pages">Struktura</Link>
            </li>

            <li>
              <Link href="/admin/layout">Rozložení a design</Link>
            </li>

            <li>
              <Link href="">Obsah stránek</Link>
            </li>

            <li>
              <Link href="">Nastavení stránky a SEO</Link>
            </li>
          </ul>
        </nav>

        <h2 class="mb-4 mt-4 ml-2 text-md font-bold text-gray-100">E-shop</h2>
        <nav>
          <ul>
            <li>
              <Link href="">Kategorie</Link>
            </li>
            <li>
              <Link href="">Zboží</Link>
            </li>
            <li>
              <Link href="">Objednávky</Link>
            </li>
            <li>
              <Link href="">Dopravci</Link>
            </li>
            <li>
              <Link href="">Správa financí</Link>
            </li>
          </ul>
        </nav>
      </div>
      <div class="text-right"></div>
    </aside>
    <div class="w-full relative">
      <div
        class="w-full bg-gray-600 h-[50px] flex justify-end items-center p-4 font-bold"
      >
        <button @click="logout" class="text-white">Odhlásit se</button>
      </div>
      <div class="p-6 pb-0 border-b-2 border-b-gray-100">
        <div>
          <h1 class="text-2xl font-bold">{{ title }}</h1>
          <p>{{ description }}</p>
        </div>
        <Breadcrumb
          :home="home"
          :model="breadcrumbItems"
          style="padding-left: 0 !important"
        />
      </div>
      <div
        v-if="globalStore.globalSuccess"
        class="flex justify-between items-center absolute w-5/6 top-[175px] left-1/2 transform -translate-x-1/2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"
      >
        <p>{{ globalStore.globalSuccess }}</p>
        <button @click="globalStore.globalSuccess = null">
          <i class="fa-solid fa-circle-xmark"></i>
        </button>
      </div>

      <div
        v-if="globalStore.globalError"
        class="flex justify-between items-center absolute w-5/6 top-[175px] left-1/2 transform -translate-x-1/2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      >
        <p>{{ globalStore.globalError }}</p>

        <button @click="globalStore.globalError = null">
          <i class="fa-solid fa-circle-xmark"></i>
        </button>
      </div>
      <main class="flex-1 p-6 mt-5">
        <Toast position="bottom-right" group="br" />
        <slot />
      </main>
      <footer
        class="bg-gray-300 h-7 text-xs text-gray-600 flex justify-center items-center"
      >
        <p>lukekriz.cz ©2025</p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { defineProps } from "vue";

import { useGlobalStore } from "@/stores/globalStore";

const globalStore = useGlobalStore();

const props = defineProps({
  title: String,
  description: String,
  breadcrumbItems: Array,
  home: Object,
});

const logout = () => {
  router.post("/logout");
};
</script>

<style scoped>
li {
  font-weight: 600;
  margin-bottom: 0.8rem;
  margin-left: 1rem;
}
</style>
