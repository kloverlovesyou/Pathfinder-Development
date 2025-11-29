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
import { ref, onMounted, onBeforeUnmount } from "vue";
import AdminTopBar from "@/components/Layout/AdminTopBar.vue";

// Reactive ref for login status
const isLoggedIn = ref(false);

function checkLogin() {
  const user = JSON.parse(localStorage.getItem("user"));
  isLoggedIn.value = user && user.role === "admin";
}

// Initial check on mount
onMounted(() => {
  checkLogin();
  window.addEventListener("storage", checkLogin); // detect logout in other tabs
});

// Cleanup listener
onBeforeUnmount(() => {
  window.removeEventListener("storage", checkLogin);
});
</script>