<template>
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <SideBar />
    <!-- Main Area -->
    <div class="flex flex-col flex-1">
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-8">
        <TopBar />
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-x-auto">
        <div class="w-full max-w-full">
          <router-view v-slot="{ Component }">
            <keep-alive>
              <component :is="Component" />
            </keep-alive>
          </router-view>
        </div>
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
