<template>
  <Head title="Welcome" />

  <div
    class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
  >
    <div v-if="canLogin" class="sm:fixed sm:top-0 sm:end-0 p-6 text-end z-10">
      <div class="flex">
        <div class="relative" v-for="page in pages">
          <div class="flex justify-center items-center m-3">
            <Link
              v-if="page.url !== '/' && page.active !== 0"
              :key="page.id"
              :href="page.url"
              class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
              >{{ page.title }}</Link
            >

            <div
              v-if="
                subpages.some(
                  (subpage) =>
                    page.url !== '/' && page.id === subpage.page_id && page.active !== 0
                )
              "
            >
              <button @click="toggleMenu(page.id)" class="ml-2">
                <i class="fa-solid fa-sort-down"></i>
              </button>
            </div>
          </div>

          <div class="absolute">
            <div
              v-if="openMenu[page.id]"
              class="flex flex-col mb-2"
              v-for="subpage in subpages"
            >
              <Link
                v-if="page.id === subpage.page_id && subpage.active !== 0"
                :key="subpage.id"
                :href="subpage.url"
                class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >{{ subpage.title }}</Link
              >
            </div>
          </div>
        </div>

        <Link
          v-if="$page.props.auth.user"
          :href="route('admin.dashboard')"
          class="flex justify-content items-center font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
          ><i class="fa-solid fa-user"></i
        ></Link>

        <template v-else>
          <Link
            :href="route('login')"
            class="flex justify-content items-center font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
            ><i class="fa-solid fa-user"></i
          ></Link>
        </template>
      </div>
    </div>

    <div class="flex justify-center align-middle">
      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">
          Vítejte v Precious!
        </h1>

        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          Pro pokračování se prosím přihlašte. Pokud nemáte přihlášeni kontaktujte prosím
          Admina.
        </p>
      </div>
    </div>

    <div
      class="absolute bottom-0 left-0 right-0 p-4 text-center text-gray-500 dark:text-gray-400"
    >
      <p>Powered by <Link href="https://www.lukekriz.cz">Luke Kříž.</Link></p>
    </div>
  </div>
</template>
<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, defineProps } from "vue";
import { useToast } from "primevue/usetoast";
defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  pages: Array,
  subpages: Array,
});

defineExpose({ showToast });

const toast = useToast();

const openMenu = ref({});

const toggleMenu = (pageId) => {
  openMenu.value[pageId] = !openMenu.value[pageId];
};

const showToast = (severity, summary, detail) => {
  toast.add({
    severity: severity,
    summary: summary,
    detail: detail,
    life: 3000,
  });
};
</script>
<style>
.bg-dots-darker {
  background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
  .dark\:bg-dots-lighter {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
  }
}
</style>
