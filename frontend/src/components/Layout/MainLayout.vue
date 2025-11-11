<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <SideBar />
    <!-- Main Area -->
    <div class="flex flex-col flex-1">
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-4">
        <TopBar />
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
import { ref } from "vue";
import SideBar from "./SideBar.vue";
import TopBar from "./TopBar.vue";
const isExpanded = ref(true);
</script>
