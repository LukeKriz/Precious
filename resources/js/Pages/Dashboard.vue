<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
  <AdminLayout  title="Struktura stránek"
        description="Zde si prosím vytvořte strukturu Vaší internetové stránky."
        :home="{ icon: 'fas fa-home', to: '/' }"
        :breadcrumbItems="[{ label: 'Struktura stránek', to: '/admin/pages' }] ">
    <div>
      <h1 class="text-2xl font-bold">Dashboard</h1>
    </div>
  </AdminLayout>
</template>