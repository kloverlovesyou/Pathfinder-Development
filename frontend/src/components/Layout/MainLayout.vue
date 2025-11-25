<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <SideBar
      :expanded="isSidebarExpanded"
      @update:expanded="isSidebarExpanded = $event"
    />

    <!-- Main Area -->
    <div
      class="flex flex-col min-h-screen transition-all duration-300"
      :style="contentOffsetStyle"
    >
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-12">
        <TopBar />
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-x-auto px-6 pb-6">
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
import { ref, computed } from "vue";
import SideBar from "./SideBar.vue";
import TopBar from "./TopBar.vue";

const isSidebarExpanded = ref(true);

const contentOffsetStyle = computed(() => ({
  marginLeft: isSidebarExpanded.value ? "18rem" : "4rem",
}));
</script>
