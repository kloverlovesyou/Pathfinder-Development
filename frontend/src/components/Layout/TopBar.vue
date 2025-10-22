<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import axios from "axios";

// --- State ---
const showDropdown = ref(false);
const activeMains = ref([]); // Training / Career / Organization
const activeSubs = ref([]); // Online / Onsite
const searchContainer = ref(null);
const mainFilters = ["Training", "Career", "Organization"];
const searchInput = ref(""); // User's search text
const results = ref([]); // Results from backend

// --- Toggle Filters ---
function toggleMainFilter(main) {
  if (activeMains.value.includes(main)) {
    // Deselect
    activeMains.value = activeMains.value.filter((m) => m !== main);
    if (main === "Training" || main === "Career") activeSubs.value = [];
  } else {
    if (main === "Training" || main === "Career") {
      // Mutually exclusive between Training and Career
      activeMains.value = activeMains.value.filter(
        (m) => m !== (main === "Training" ? "Career" : "Training")
      );
    }
    activeMains.value.push(main);
  }
}

// --- Toggle Subfilters (exclusive Online/Onsite) ---
function toggleExclusiveSub(sub) {
  if (activeSubs.value.includes(sub)) {
    activeSubs.value = [];
  } else {
    if (["Online", "Onsite"].includes(sub)) {
      activeSubs.value = activeSubs.value.filter(
        (s) => !["Online", "Onsite"].includes(s)
      );
    }
    activeSubs.value = [sub];
  }
}

// --- Click outside to close dropdown ---
function handleClickOutside(event) {
  if (searchContainer.value && !searchContainer.value.contains(event.target)) {
    showDropdown.value = false;
  }
}

onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() =>
  document.removeEventListener("click", handleClickOutside)
);

// --- Perform Search (calls Laravel backend + stored procedure) ---
async function performSearch() {
  // ✅ If no search term, clear results
  if (!searchInput.value.trim()) {
    results.value = [];
    return;
  }

  // Determine main and sub filters
  const filterType =
    activeMains.value.length > 0 ? activeMains.value[0].toLowerCase() : ""; // may be '', 'training', 'career', 'organization'

  const subFilter = activeSubs.value[0] || "";

  try {
    const response = axios
      .get("http://127.0.0.1:8000/api/search", {
        params: {
          search: searchInput.value,
          filterType: activeMains.value[0]?.toLowerCase() || "",
          subFilter: activeSubs.value[0] || "",
        },
      })
      .then((response) => {
        results.value = response.data.results || [];
      })
      .catch((error) => {
        console.error("Search failed:", error);
      });

    // Use backend structure (response.data.results)
    results.value = response.data.results || [];

    // Show "No results" even when empty array
    if (results.value.length === 0) {
      results.value = [];
    }
  } catch (error) {
    console.error("Search failed:", error);
    results.value = [];
  }
}

// --- Watchers ---
watch([searchInput, activeMains, activeSubs], performSearch);
</script>

<template>
  <div class="flex-grow flex items-center justify-between p-4">
    <!-- Logo -->
    <button
      class="text-xl font-bold font-poppins text-darkslategray whitespace-nowrap"
      @click="$router.push({ name: 'Homepage' })"
    >
      Pathfinder
    </button>

    <!-- Search Container -->
    <div
      ref="searchContainer"
      class="relative w-full max-w-md ml-4 font-poppins"
    >
      <!-- Search Input -->
      <label
        class="bg-blue-gray input w-full flex items-center gap-2 border-none rounded-full px-3 py-2 cursor-text"
        @click="showDropdown = true"
      >
        <!-- Search Icon -->
        <svg
          class="h-5 w-5 text-gray-500 flex-shrink-0"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
        >
          <g
            stroke-linejoin="round"
            stroke-linecap="round"
            stroke-width="2.5"
            fill="none"
            stroke="darkslategray"
          >
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
          </g>
        </svg>

        <input
          type="search"
          placeholder="Search"
          v-model="searchInput"
          class="flex-grow bg-transparent outline-none font-poppins"
          @focus="showDropdown = true"
        />
      </label>

      <!-- Dropdown -->
      <div
        v-if="showDropdown"
        class="absolute top-full left-0 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-md z-50 p-3 flex flex-col gap-3"
      >
        <!-- Main Filters -->
        <div class="flex flex-wrap gap-2 justify-start">
          <button
            v-for="main in mainFilters"
            :key="main"
            @mousedown.prevent="toggleMainFilter(main)"
            :class="[
              'px-4 py-1 rounded-full text-sm font-medium border transition-colors',
              activeMains.includes(main)
                ? 'bg-dark-slate text-white border-dark-slate'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-300',
            ]"
          >
            {{ main }}
          </button>
        </div>

        <!-- Subfilters -->
        <div
          v-if="activeMains.includes('Training')"
          class="flex flex-wrap gap-2 justify-start"
        >
          <button
            v-for="sub in ['Online', 'Onsite']"
            :key="sub"
            @mousedown.prevent="toggleExclusiveSub(sub)"
            :class="[
              'px-4 py-1 rounded-full text-sm font-medium border transition-colors',
              activeSubs.includes(sub)
                ? 'bg-dark-slate text-white border-dark-slate'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-300',
            ]"
          >
            {{ sub }}
          </button>
        </div>

        <div
          v-if="activeMains.includes('Career')"
          class="flex flex-wrap gap-2 justify-start"
        >
          <button
            v-for="sub in ['Position']"
            :key="sub"
            @mousedown.prevent="toggleExclusiveSub(sub)"
            :class="[
              'px-4 py-1 rounded-full text-sm font-medium border transition-colors',
              activeSubs.includes(sub)
                ? 'bg-dark-slate text-white border-dark-slate'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-300',
            ]"
          >
            {{ sub }}
          </button>
        </div>

        <!-- Results -->
        <div v-if="results.length" class="mt-2 max-h-60 overflow-y-auto">
          <div
            v-for="(item, index) in results"
            :key="index"
            class="p-2 hover:bg-gray-100 rounded cursor-pointer"
          >
            <div class="font-semibold text-gray-800">
              {{ item.Title || item.Position || item.Name }}
            </div>
            <div class="text-sm text-gray-500">
              {{ item.OrganizationName || item.Location || "" }}
            </div>
          </div>
        </div>

        <div
          v-else-if="searchInput && activeMains.length"
          class="text-center text-gray-500 text-sm mt-2"
        >
          No results found.
        </div>
      </div>
    </div>
  </div>
</template>
