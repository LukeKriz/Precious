<script setup>
import { computed } from "vue";

const props = defineProps({
  mainDesign: { type: Object, required: true },
  pages: { type: Array, default: () => [] },
  subpages: { type: Array, default: () => [] },
  footerHeight: { type: String, default: "60px" },
});

/* ✅ stejné klíče jako v adminu (StepFooter ukládá footer_background + footer_color) */
const footerBg = computed(() => props.mainDesign?.footer_background ?? "transparent");
const footerText = computed(() => props.mainDesign?.footer_color ?? "#111827");

const footerCols = computed(() => {
  const n = Number(props.mainDesign?.footer_columns ?? 3);
  return Math.min(6, Math.max(1, Number.isFinite(n) ? n : 3));
});

/* paleta (stejné key jako v adminu) */
const palette = computed(() => ({
  primary: props.mainDesign?.primary_color,
  secondary: props.mainDesign?.secondary_color,
  tertiary: props.mainDesign?.tertiary_color,
  quaternary: props.mainDesign?.quaternary_color,
  quinary: props.mainDesign?.quinary_color,
  transparent: "transparent",
}));

const cssColorFromKey = (k, fallback) => {
  if (!k) return fallback;
  if (k === "transparent") return "transparent";

  // když přijde přímo hex / css barva, nech ji
  if (
    typeof k === "string" &&
    (k.startsWith("#") || k.startsWith("rgb") || k.startsWith("hsl"))
  ) {
    return k;
  }

  // jinak to ber jako key do palety
  return palette.value?.[k] ?? fallback;
};

/** ---------------- footer_content parser ----------------
 *  A) flat map: { "1": {type,data}, "2": {...} }
 *  B) legacy:   { columns: { "1": [ {type,data}, ... ] } }
 *  někdy oboje -> preferuj A
 */
const parsedFooterContent = computed(() => {
  const raw = props.mainDesign?.footer_content;

  let obj = null;
  if (!raw) obj = null;
  else if (typeof raw === "string") {
    try {
      obj = JSON.parse(raw);
    } catch {
      obj = null;
    }
  } else if (typeof raw === "object") {
    obj = raw;
  }

  if (!obj || typeof obj !== "object") {
    return { mode: "empty", flat: {}, columns: {} };
  }

  // flat keys "1","2"... (ignore "columns")
  const flat = {};
  Object.keys(obj).forEach((k) => {
    if (k === "columns") return;
    if (/^\d+$/.test(k) && obj[k] && typeof obj[k] === "object") {
      flat[k] = obj[k];
    }
  });

  // legacy columns
  const columns = obj.columns && typeof obj.columns === "object" ? obj.columns : {};

  return {
    mode: Object.keys(flat).length
      ? "flat"
      : Object.keys(columns).length
      ? "columns"
      : "empty",
    flat,
    columns,
  };
});

const getCol = (i) => {
  const key = String(i);
  const fc = parsedFooterContent.value;

  // prefer flat map
  if (fc.mode === "flat") return fc.flat[key] || null;

  // legacy: columns["1"] is array -> take first item
  if (fc.mode === "columns") {
    const arr = fc.columns?.[key];
    if (Array.isArray(arr) && arr.length) return arr[0];
    return null;
  }

  return null;
};

const safeWidthPercent = (v) => {
  const n = Number(v);
  if (!Number.isFinite(n)) return 100;
  return Math.min(100, Math.max(5, n));
};

/* ✅ zarovnání: 1. sloupec start, poslední end, ostatní center */
const colAlignClass = (i) => {
  if (i === 1) return "flex flex-col justify-center items-start text-left";
  if (i === footerCols.value) return "flex flex-col justify-center items-end text-right";
  return "flex flex-col justify-center items-center text-center";
};

/* ✅ CSS vars pro link / hover per sloupec (menu) */
const menuVars = (col) => {
  const linkKey = col?.data?.link_color ?? null;
  const hoverKey = col?.data?.link_hover_color ?? null;

  const base = cssColorFromKey(linkKey, footerText.value);
  const hover = cssColorFromKey(hoverKey, base);

  return {
    "--menu-link": base,
    "--menu-hover": hover,

    // vyrovnání obsahu editoru
    "padding-top": "10px",
  };
};
</script>

<template>
  <footer
    class="shrink-0"
    :style="{ height: footerHeight, background: footerBg, color: footerText }"
  >
    <div class="mx-auto max-w-6xl h-full flex flex-col justify-around px-4">
      <div
        class="grid gap-8 py-6 items-start"
        :style="{ gridTemplateColumns: `repeat(${footerCols}, minmax(0, 1fr))` }"
      >
        <div v-for="i in footerCols" :key="i" class="min-w-0">
          <!-- empty -->
          <div v-if="!getCol(i)" class="text-sm opacity-70">
            (sloupec {{ i }} je prázdný)
          </div>

          <!-- MENU -->
          <div
            v-else-if="getCol(i)?.type === 'menu'"
            class="footer-menu"
            :class="colAlignClass(i)"
            :style="menuVars(getCol(i))"
          >
            <div class="font-extrabold mb-3">
              {{ getCol(i)?.data?.title || "Menu" }}
            </div>

            <ul class="space-y-2 text-sm">
              <li v-for="(it, idx) in getCol(i)?.data?.items || []" :key="it?.key || idx">
                <a class="footer-link" :href="it?.url || '#'" rel="noopener">
                  {{ it?.label || "Položka" }}
                </a>
              </li>

              <li v-if="(getCol(i)?.data?.items || []).length === 0" class="opacity-70">
                (zatím bez položek)
              </li>
            </ul>
          </div>

          <!-- TEXT (✅ render Quill HTML correctly) -->
          <div v-else-if="getCol(i)?.type === 'text'" :class="colAlignClass(i)">
            <div
              class="text-sm opacity-90 max-w-none ql-editor"
              v-html="getCol(i)?.data?.html || ''"
            />
          </div>

          <!-- IMAGE (✅ bez textu a bez rámečku) -->
          <div v-else-if="getCol(i)?.type === 'image'" :class="colAlignClass(i)">
            <img
              v-if="getCol(i)?.data?.url"
              :src="getCol(i).data.url"
              :alt="getCol(i)?.data?.alt || ''"
              class="footer-image-img"
              :style="{ width: safeWidthPercent(getCol(i)?.data?.width_percent) + '%' }"
              draggable="false"
            />
          </div>

          <!-- unknown -->
          <div v-else class="text-sm opacity-70">(neznámý typ komponenty)</div>
        </div>
      </div>
      <div class="footer-bottom">
        <p class="footer-copy">
          © {{ new Date().getFullYear() }}
          <span class="font-semibold"
            ><a
              href="https://lukekriz.cz"
              target="_blank"
              rel="noopener"
              class="footer-author"
            >
              lukekriz.cz
            </a></span
          >
        </p>
      </div>
    </div>
  </footer>
</template>

<style>
.footer-bottom {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;

  font-size: 11px;
  opacity: 0.4;
}

.footer-copy {
  margin: 0;
}

.footer-author {
  font-size: 12px;
  opacity: 0.8;
  text-decoration: none;
  transition: opacity 0.15s ease;
}

.footer-author:hover {
  opacity: 1;
  text-decoration: underline;
}

/* ========= MENU links přes CSS vars ========= */
.footer-link {
  color: var(--menu-link);
  text-decoration: none;
  transition: color 0.12s ease, text-decoration-color 0.12s ease;
}
.footer-link:hover {
  color: var(--menu-hover);
}

/* ========= image bez rámu ========= */
.footer-image-img {
  display: block;
  height: auto;
  max-width: 100%;
  object-fit: contain;
}

/* ========= MINIMUM Quill/PrimeVue Editor output styling =========
   Pokud importuješ globálně: `import "quill/dist/quill.snow.css";`
   tak tohle klidně můžeš smazat. Tohle je "minimal" varianta.
*/
.ql-editor {
  padding: 0;
  line-height: 1.35;
}

/* zarovnání */
.ql-align-center {
  text-align: center;
}
.ql-align-right {
  text-align: right;
}
.ql-align-justify {
  text-align: justify;
}

/* velikosti */
.ql-size-small {
  font-size: 0.75em;
}
.ql-size-large {
  font-size: 1.5em;
}
.ql-size-huge {
  font-size: 2.5em;
}

/* základní typografie */
.ql-editor p {
  margin: 0.25em 0;
}
.ql-editor h1 {
  font-size: 1.6em;
  font-weight: 800;
  margin: 0.35em 0;
}
.ql-editor h2 {
  font-size: 1.3em;
  font-weight: 800;
  margin: 0.3em 0;
}
.ql-editor strong {
  font-weight: 800;
}
.ql-editor em {
  font-style: italic;
}
.ql-editor u {
  text-decoration: underline;
}
.ql-editor a {
  text-decoration: underline;
}
</style>
