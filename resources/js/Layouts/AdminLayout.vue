<template>
  <div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-gray-800 text-white flex flex-col justify-between p-4">
      <!-- LOGO -->
      <Link href="/admin" class="mb-6">
        <img src="/storage/img/logo-white.png" alt="PRECIOUS" />
      </Link>

      <!-- NAVIGATION -->
      <div class="flex-1 overflow-y-auto">
        <!-- ===== STRÁNKA ===== -->
        <h2 class="mb-4 mt-4 ml-2 text-md font-bold text-gray-100">Stránka</h2>
        <nav class="mb-8">
          <ul>
            <li>
              <Link
                href="/admin/pages"
                class="menu-link"
                :class="isActive('/admin/pages')"
              >
                Struktura webu
              </Link>
            </li>

            <li>
              <Link
                href="/admin/layout"
                class="menu-link"
                :class="isActive('/admin/layout')"
              >
                Rozložení a design
              </Link>
            </li>

            <li>
              <Link
                href="/admin/content"
                class="menu-link"
                :class="isActive('/admin/content')"
              >
                Obsah stránek
              </Link>
            </li>
          </ul>
        </nav>

        <!-- ===== NASTAVENÍ ===== -->
        <h2 class="mb-4 mt-6 ml-2 text-md font-bold">Nastavení</h2>

        <nav class="mb-20">
          <ul>
            <li>
              <Link
                href="/admin/settings"
                class="menu-link"
                :class="isActive('/admin/settings', { exclude: ['/admin/settings/seo'] })"
              >
                Základní nastavení
              </Link>
            </li>

            <li>
              <Link
                href="/admin/settings/seo"
                class="menu-link"
                :class="isActive('/admin/settings/seo')"
              >
                Pokročilé SEO
              </Link>
            </li>
          </ul>
        </nav>
      </div>

      <!-- FOOTER -->
      <div class="text-xs text-gray-400 text-right">PRECIOUS Admin</div>
    </aside>

    <!-- CONTENT -->
    <div class="w-full relative">
      <!-- TOP BAR -->
      <div class="w-full bg-gray-600 h-[50px] flex justify-end items-center px-4">
        <button @click="logout" class="text-white font-semibold">Odhlásit se</button>
      </div>

      <!-- PAGE HEADER -->
      <div class="p-6 pb-4 border-b border-gray-200">
        <h1 class="text-2xl font-bold">{{ title }}</h1>
        <p class="text-gray-600">{{ description }}</p>

        <Breadcrumb :home="home" :model="breadcrumbItems" class="mt-2" />
      </div>

      <!-- FLASH MESSAGES -->
      <div v-if="globalStore.globalSuccess" class="flash success">
        <span>{{ globalStore.globalSuccess }}</span>
        <button @click="globalStore.globalSuccess = null">
          <i class="fa-solid fa-circle-xmark"></i>
        </button>
      </div>

      <div v-if="globalStore.globalError" class="flash error">
        <span>{{ globalStore.globalError }}</span>
        <button @click="globalStore.globalError = null">
          <i class="fa-solid fa-circle-xmark"></i>
        </button>
      </div>

      <!-- MAIN -->
      <main class="flex-1 p-6 mt-5">
        <Toast position="bottom-right" group="br" />
        <slot />
      </main>

      <!-- FOOTER -->
      <footer
        class="bg-gray-300 h-7 text-xs text-gray-600 flex justify-center items-center"
      >
        <p><a href="https://lukekriz.cz" target="_blank">lukekriz.cz</a> © 2026</p>
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

const isActive = (path, opts = {}) => {
  const url = router.page.url;

  // exact nebo /path/...
  const isMatch = url === path || url.startsWith(path + "/");
  if (!isMatch) return "";

  // možnost vyloučit prefixy (např. /admin/settings/seo)
  const exclude = opts.exclude || [];
  for (const ex of exclude) {
    if (url === ex || url.startsWith(ex + "/")) return "";
  }

  return "bg-gray-700 text-green-400 font-semibold";
};
</script>

<style scoped>
.menu-link {
  display: block;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-weight: 600;
  margin-bottom: 5px;
}

.menu-link:hover {
  background-color: #374151;
  color: #4ade80;
}

.flash {
  position: absolute;
  top: 175px;
  left: 50%;
  transform: translateX(-50%);
  width: 83%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  z-index: 50;
}

.flash.success {
  background-color: #dcfce7;
  border: 1px solid #4ade80;
  color: #166534;
}

.flash.error {
  background-color: #fee2e2;
  border: 1px solid #f87171;
  color: #7f1d1d;
}
</style>
