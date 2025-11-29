<template>
  <div class="flex h-screen">
    <div class="flex flex-col flex-1">
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-4">
        <AdminTopBar v-if="isLoggedIn" @logout="handleLogout" />
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
import { authState, logout } from "@/stores/authState";
import { useRouter } from "vue-router";
import AdminTopBar from "@/components/Layout/AdminTopBar.vue";

const router = useRouter();

// Reactive login check
const isLoggedIn = computed(() => authState.user && authState.user.role === "admin");

// Logout handler
function handleLogout() {
  logout(); // clears user and token
  router.push({ name: "AdminLogin" }); // redirects automatically
}
</script>