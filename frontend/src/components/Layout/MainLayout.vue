<template>
  <div class="h-screen bg-gray-100 overflow-hidden">
    <!-- Sidebar -->
    <SideBar
      :expanded="isSidebarExpanded"
      @update:expanded="isSidebarExpanded = $event"
    />

    <!-- Main Area -->
    <div
      class="flex flex-col h-screen transition-all duration-300 overflow-hidden"
      :style="contentOffsetStyle"
      :class="{
        'ml-0 md:ml-16': !isSidebarExpanded && windowWidth < 1024,
        'ml-0 md:ml-64': isSidebarExpanded && windowWidth < 1024
      }"
    >
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-4 sm:px-6 md:px-8 lg:px-12 flex-shrink-0">
        <TopBar />
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto overflow-x-hidden px-3 sm:px-4 md:px-6 pb-4 sm:pb-6">
        <div class="w-full max-w-full mx-auto">
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
import { ref, computed, onMounted, onUnmounted } from "vue";
import SideBar from "./SideBar.vue";
import TopBar from "./TopBar.vue";

const isSidebarExpanded = ref(true);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth;
};

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateWindowWidth);
    updateWindowWidth();
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateWindowWidth);
  }
});

const contentOffsetStyle = computed(() => {
  // On mobile/tablet (< 1024px), don't apply margin via style - let Tailwind classes handle it
  if (windowWidth.value < 1024) {
    return {};
  }
  // On desktop (>= 1024px), use computed margin
  return {
    marginLeft: isSidebarExpanded.value ? "18rem" : "4rem",
  };
});
</script>
