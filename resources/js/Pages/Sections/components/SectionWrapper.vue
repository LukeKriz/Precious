<script setup>
defineProps({
  sec: { type: Object, required: true },
});
</script>

<template>
  <section
    class="w-full flex flex-col relative"
    :class="
      sec?.va === 'top'
        ? 'justify-start'
        : sec?.va === 'bottom'
        ? 'justify-end'
        : 'justify-center'
    "
    :style="{
      minHeight: (sec.height || 0) + 'px',
      paddingTop: (sec.pt || 0) + 'px',
      paddingBottom: (sec.pb || 0) + 'px',
    }"
  >
    <!-- BG full width -->
    <div
      class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-screen -z-2"
      :style="
        sec?.bgImage
          ? {
              backgroundImage: `url(${sec.bgImage})`,
              backgroundSize: 'cover',
              backgroundPosition: 'center',
              backgroundRepeat: 'no-repeat',
            }
          : { backgroundColor: sec?.bg ? sec.bg : 'transparent' }
      "
      aria-hidden="true"
    />

    <!-- Overlay full width -->
    <div
      v-if="sec?.overlayColor && Number(sec?.overlayOpacity || 0) > 0"
      class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-screen -z-1 pointer-events-none"
      :style="{
        backgroundColor: sec.overlayColor,
        opacity: (Number(sec.overlayOpacity) / 100).toString(),
      }"
      aria-hidden="true"
    />

    <slot />
  </section>
</template>
