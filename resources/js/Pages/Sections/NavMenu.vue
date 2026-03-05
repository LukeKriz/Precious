<script setup>
import { computed, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
  pages: { type: Array, default: () => [] },
  subpages: { type: Array, default: () => [] },
  mainDesign: { type: Object, default: () => ({}) },
  pageDesign: { type: Object, default: () => ({}) },
});

const page = usePage();
const currentUrl = computed(() => page.url || window.location.pathname);

const isOpen = ref(false);
const openMobileDropdownFor = ref(null);

// ===== COLORS (main_design_header accessors via mainDesign) =====
const headerBg = computed(
  () =>
    props.mainDesign?.header_background ?? props.mainDesign?.primary_color ?? "#ffffff"
);
const headerColor = computed(
  () => props.mainDesign?.header_color ?? props.mainDesign?.secondary_color ?? "#111827"
);
const headerHoverColor = computed(
  () =>
    props.mainDesign?.header_color_hover ?? props.mainDesign?.tertiary_color ?? "#6366f1"
);

const submenuBg = computed(() => props.mainDesign?.submenu_background ?? headerBg.value);
const submenuColor = computed(() => props.mainDesign?.submenu_color ?? headerColor.value);
const submenuHover = computed(
  () => props.mainDesign?.submenu_hover ?? headerHoverColor.value
);

const logoUrl = computed(() => props.mainDesign?.logo_url || null);

const logoWidthPx = computed(() => {
  const w = props.mainDesign?.logo_width;
  if (w === null || w === undefined || w === "" || Number(w) <= 0) return null;
  return `${Number(w)}px`;
});

const logoHeightPx = computed(() => {
  const h = props.mainDesign?.logo_height;
  if (h === null || h === undefined || h === "" || Number(h) <= 0) return null;
  return `${Number(h)}px`;
});

// když jsou rozměry nastavené, chceme použít explicitní width/height,
// jinak fallback na původní tailwind h-10 w-auto
const hasLogoSize = computed(() => !!(logoWidthPx.value || logoHeightPx.value));

// ✅ vždy string typu "65px"
const headerHeightPx = computed(() => {
  const h = props.pageDesign?.header_height;
  if (h === null || h === undefined || h === "" || Number(h) <= 0) return "65px";
  return `${Number(h)}px`;
});

// --- normalizace pozic (CZ/EN) ---
const normPos = (v) => {
  const x = String(v || "").toLowerCase();
  if (["left", "vlevo"].includes(x)) return "left";
  if (["center", "uprostred", "uprostřed", "middle"].includes(x)) return "center";
  if (["right", "vpravo"].includes(x)) return "right";
  return "left";
};

const logoPosition = computed(() => normPos(props.pageDesign?.logo_position));

const menuPosition = computed(() => {
  const desired = normPos(props.pageDesign?.menu_position);
  const lp = logoPosition.value;

  if (desired === lp) {
    if (desired === "center") return "right";
    if (desired === "left") return "center";
    if (desired === "right") return "center";
  }
  return desired;
});

// jen aktivní stránky
const menuPages = computed(() =>
  (props.pages || []).filter((p) => Number(p.active) !== 0)
);

const childrenOf = (pageId) =>
  (props.subpages || []).filter(
    (s) => Number(s.active) !== 0 && String(s.page_id) === String(pageId)
  );

const isActive = (url) => {
  if (!url) return false;
  const cur = (currentUrl.value || "/").replace(/\/+$/, "") || "/";
  const target = (url || "/").replace(/\/+$/, "") || "/";
  if (target === "/") return cur === "/";
  return cur === target || cur.startsWith(target + "/") || cur.startsWith(target);
};

// ✅ active = hover barva
const linkColor = (url) => (isActive(url) ? headerHoverColor.value : headerColor.value);

const toggleMobileDropdown = (pageId) => {
  openMobileDropdownFor.value = openMobileDropdownFor.value === pageId ? null : pageId;
};

// helper pozice
const posClass = (pos) => {
  if (pos === "left") return "left-4";
  if (pos === "right") return "right-4";
  return "left-1/2 -translate-x-1/2";
};
</script>

<template>
  <header
    class="relative z-[9999]"
    :style="{ backgroundColor: headerBg, color: headerColor }"
  >
    <!-- DESKTOP -->
    <div class="hidden md:block" :style="{ height: headerHeightPx }">
      <div class="relative mx-auto max-w-6xl px-4 h-full">
        <!-- LOGO -->
        <div class="absolute inset-y-0 flex items-center" :class="posClass(logoPosition)">
          <Link href="/" class="inline-flex items-center gap-3">
            <img
              v-if="logoUrl"
              :src="logoUrl"
              alt="Logo"
              class="object-contain"
              :class="hasLogoSize ? '' : 'h-10 w-auto'"
              :style="{
                width: logoWidthPx || 'auto',
                height: logoHeightPx || '40px',
              }"
            />
            <span v-else class="font-bold text-lg">Precious</span>
          </Link>
        </div>

        <!-- MENU -->
        <nav
          class="absolute inset-y-0 flex items-center gap-6 flex-nowrap"
          :class="posClass(menuPosition)"
        >
          <div
            v-for="p in menuPages"
            :key="p.id"
            class="relative group flex items-center h-full"
          >
            <Link
              :href="p.url"
              class="font-medium inline-flex items-center gap-1 whitespace-nowrap transition-colors"
              :style="{ color: linkColor(p.url) }"
            >
              <span
                class="nav-link"
                :style="{ '--nav-color': headerColor, '--nav-hover': headerHoverColor }"
              >
                {{ p.title }}
              </span>

              <span
                v-if="childrenOf(p.id).length"
                class="text-xs"
                :style="{ color: linkColor(p.url) }"
              >
                <i class="fa-solid fa-angle-down"></i>
              </span>
            </Link>

            <!-- dropdown (roll down, not bubble) -->
            <div
              v-if="childrenOf(p.id).length"
              class="submenu absolute left-0 top-full w-64 overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible"
              :style="{ backgroundColor: submenuBg }"
            >
              <div class="py-2">
                <Link
                  v-for="s in childrenOf(p.id)"
                  :key="s.id"
                  :href="s.url"
                  class="submenu-item block px-5 py-3 text-sm font-semibold whitespace-nowrap"
                  :style="{
                    '--submenu-color': submenuColor,
                    '--submenu-hover': submenuHover,
                    color: isActive(s.url) ? submenuHover : submenuColor,
                  }"
                >
                  {{ s.title }}
                </Link>
              </div>
            </div>
          </div>

          <Link
            v-if="$page.props.auth?.user"
            href="/admin"
            class="ml-2 inline-flex h-10 w-10 items-center justify-center bg-white/20 hover:bg-white/30 transition"
            title="Admin"
          >
            <i class="fa-solid fa-user"></i>
          </Link>
          <Link
            v-else
            href="/login"
            class="ml-2 inline-flex h-10 w-10 items-center justify-center bg-white/20 hover:bg-white/30 transition"
            title="Přihlásit"
          >
            <i class="fa-solid fa-user"></i>
          </Link>
        </nav>
      </div>
    </div>

    <!-- MOBILE HEADER (stays headerBg/headerColor) -->
    <div
      class="md:hidden mx-auto max-w-6xl px-4 flex items-center justify-between"
      :style="{ height: headerHeightPx }"
    >
      <Link href="/" class="inline-flex items-center gap-3">
        <img
          v-if="logoUrl"
          :src="logoUrl"
          alt="Logo"
          class="h-10 w-auto object-contain"
        />
        <span v-else class="font-bold text-lg">Precious</span>
      </Link>

      <button
        class="burger-btn"
        :class="{ open: isOpen }"
        @click="isOpen = !isOpen"
        aria-label="Menu"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>

    <!-- ✅ MOBILE MENU: background = submenuBg, NO borders, NO extra spacing -->
    <div v-if="isOpen" class="md:hidden" :style="{ backgroundColor: submenuBg }">
      <div class="mx-auto max-w-6xl px-4 pb-4">
        <div class="flex flex-col">
          <div
            v-for="p in menuPages"
            :key="'m-' + p.id"
            class="py-3"
            :style="{ borderBottom: '1px solid rgba(255,255,255,0.18)' }"
          >
            <div class="flex items-center justify-between">
              <Link
                :href="p.url"
                class="font-semibold whitespace-nowrap"
                :style="{ color: isActive(p.url) ? submenuHover : submenuColor }"
              >
                {{ p.title }}
              </Link>

              <button
                v-if="childrenOf(p.id).length"
                type="button"
                class="ml-3 inline-flex h-9 w-9 items-center justify-center rounded-lg"
                :style="{
                  color: submenuColor,
                  backgroundColor: 'rgba(255,255,255,0.14)',
                }"
                @click="toggleMobileDropdown(p.id)"
                aria-label="Submenu"
              >
                <i
                  class="fa-solid fa-angle-down transition-transform"
                  :style="{
                    transform:
                      openMobileDropdownFor === p.id ? 'rotate(180deg)' : 'rotate(0deg)',
                  }"
                ></i>
              </button>
            </div>

            <!-- submenu items -->
            <div v-if="openMobileDropdownFor === p.id" class="pt-3">
              <div class="flex flex-col gap-2">
                <Link
                  v-for="s in childrenOf(p.id)"
                  :key="'ms-' + s.id"
                  :href="s.url"
                  class="font-medium"
                  :style="{ color: isActive(s.url) ? submenuHover : submenuColor }"
                >
                  {{ s.title }}
                </Link>
              </div>
            </div>
          </div>

          <!-- auth -->
          <div class="pt-4">
            <Link
              v-if="$page.props.auth?.user"
              href="/admin"
              class="inline-flex items-center gap-2 font-semibold"
              :style="{ color: submenuColor }"
            >
              <i class="fa-solid fa-user"></i> Admin
            </Link>
            <Link
              v-else
              href="/login"
              class="inline-flex items-center gap-2 font-semibold"
              :style="{ color: submenuColor }"
            >
              <i class="fa-solid fa-user"></i> Přihlásit
            </Link>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* header link hover */
.nav-link {
  transition: color 0.15s ease;
  color: var(--nav-color);
}
.group:hover .nav-link {
  color: var(--nav-hover) !important;
}

/* dropdown “roll down from bar” look */
.submenu {
  border-top: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 0;
  box-shadow: none;
  transform: translateY(-6px);
  transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
}
.group:hover .submenu {
  transform: translateY(0);
}

/* submenu items hover */
.submenu-item {
  transition: color 0.15s ease;
  color: var(--submenu-color);
}
.submenu-item:hover {
  color: var(--submenu-hover) !important;
}

.burger-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;

  background: rgba(255, 255, 255, 0.15);

  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 5px;

  cursor: pointer;
  padding: 0;
}

/* čárky */
.burger-btn span {
  width: 25px;
  height: 2px;
  background-color: currentColor;
  display: block;
  transition: transform 0.25s ease, opacity 0.2s ease;
}

/* ===== OPEN STATE → X ===== */
.burger-btn.open span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.burger-btn.open span:nth-child(2) {
  opacity: 0;
}

.burger-btn.open span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}
</style>
