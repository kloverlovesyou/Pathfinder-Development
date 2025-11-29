<template>
  <div class="flex h-screen">
    <!-- Sidebar -->

    <!-- Main Area -->
    <div class="flex flex-col flex-1">
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-4">
        <!-- Only show topbar if admin is logged in -->
        <AdminTopBar v-if="isLoggedIn" />
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-100">
        <router-view v-slot="{ Component }">
          <keep-alive>
            <component :is="Component" />
          </keep-alive>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import AdminTopBar from "@/components/Layout/AdminTopBar.vue";

// Reactive check for admin login
const isLoggedIn = computed(() => {
  const user = JSON.parse(localStorage.getItem("user"));
  return user && user.role === "admin";
});
</script>