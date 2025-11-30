<template>
  <div class="h-screen bg-gray-100 overflow-hidden">
    <!-- Sidebar -->
    <SideBar
      :expanded="isSidebarExpanded"
      @update:expanded="isSidebarExpanded = $event"
    />

    <!-- Overlay for mobile when sidebar is open -->
    <div
      v-if="isSidebarExpanded && windowWidth < 1024"
      class="fixed inset-0 bg-black/50 z-40 md:hidden"
      @click="isSidebarExpanded = false"
    ></div>

    <!-- Main Area -->
    <div
      class="flex flex-col h-screen transition-all duration-300 overflow-hidden"
      :style="contentOffsetStyle"
      :class="{
        // On mobile, no margin needed since sidebar is hidden/overlay
        // On desktop, use computed margin from contentOffsetStyle
        'ml-0': windowWidth < 1024,
      }"
    >
      <!-- Header -->
      <header class="h-16 text-dark-slate flex items-center px-4 sm:px-6 md:px-8 lg:px-12 flex-shrink-0 relative">
        <!-- Mobile Menu Button - Always visible on mobile -->
        <button
          v-if="windowWidth < 1024"
          class="md:hidden absolute left-4 z-[60] p-2 hover:bg-gray-200 rounded-lg transition-colors touch-manipulation"
          @click.stop.prevent="toggleSidebar"
          @touchstart.stop.prevent="toggleSidebar"
          aria-label="Toggle menu"
          type="button"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 text-dark-slate"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>
        <!-- TopBar with margin on mobile to account for menu button -->
        <div :class="{ 'ml-12 md:ml-0': windowWidth < 1024 }" class="flex-1">
          <TopBar />
        </div>
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
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import SideBar from "./SideBar.vue";
import TopBar from "./TopBar.vue";

const isSidebarExpanded = ref(true);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

// On mobile, start with sidebar collapsed
onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateWindowWidth);
    updateWindowWidth();
    // On mobile, start with sidebar collapsed
    if (window.innerWidth < 1024) {
      isSidebarExpanded.value = false;
    }
  }
});

const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth;
};

// Update sidebar state when window resizes
watch(windowWidth, (newWidth, oldWidth) => {
  // On mobile, collapse sidebar; on desktop, keep current state or expand
  if (newWidth < 1024) {
    // If switching to mobile, collapse sidebar
    if (oldWidth >= 1024) {
      isSidebarExpanded.value = false;
    }
  } else {
    // If switching to desktop, expand sidebar
    if (oldWidth < 1024) {
      isSidebarExpanded.value = true;
    }
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateWindowWidth);
  }
});

const toggleSidebar = () => {
  console.log('Toggle clicked, current state:', isSidebarExpanded.value);
  isSidebarExpanded.value = !isSidebarExpanded.value;
  console.log('Sidebar toggled to:', isSidebarExpanded.value);
};

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
