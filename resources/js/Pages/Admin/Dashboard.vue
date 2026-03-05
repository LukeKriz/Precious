<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
</script>

<template>
  <AdminLayout
    title="Nástěnka"
    description=""
    :home="{ icon: 'fas fa-home', to: '/admin' }"
    :breadcrumbItems="[{ label: 'Nástěnka', to: '/admin' }]"
  >
    <div>
      <h1 class="text-2xl font-bold">Vítejte v PRECIOUS</h1>
      <p>Administrační rozhraní pro Vaše internetové stránky</p>
    </div>
  </AdminLayout>
</template>
