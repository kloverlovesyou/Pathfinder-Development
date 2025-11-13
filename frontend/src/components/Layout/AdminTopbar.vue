<script setup>
import { ref, onMounted, onBeforeUnmount, watch, reactive } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

const route = useRoute();
const toasts = ref([]);

function showToast(message, type = "info") {
  toasts.value.push({ message, type });

  setTimeout(() => {
    toasts.value.shift();
  }, 3000);
}

const showDropdown = ref(false);
const searchContainer = ref(null);
const searchInput = ref("");
const results = ref([]);
const selectedOrg = ref(null);
const showApplicantModal = ref(false);
const applicants = ref([]);
const selectedApplicant = ref(null);

// 🧹 Clear search
function clearSearch() {
  searchInput.value = "";
  results.value = [];
  showDropdown.value = false;
}

// 🖱 Handle click on search result
function handleResultClick(item) {
  showDropdown.value = false;
  searchInput.value = "";

  if (item.type === "organization") {
    selectedOrg.value = item;
  } else if (item.type === "applicant") {
    // Load all applicants

    selectedApplicant.value = item;
    showApplicantModal.value = true;
    console.log("Selected applicant:", item);
  }
}

async function performSearch() {
  console.log("Searching for:", searchInput.value);
  if (!searchInput.value) {
    results.value = [];
    return;
  }

  try {
    const res = await fetch(
      `http://127.0.0.1:8000/api/admin/search?query=${encodeURIComponent(
        searchInput.value
      )}`
    );
    const contentType = res.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      const text = await res.text();
      console.error("❌ Expected JSON, got:", text);
      return;
    }

    const data = await res.json();

    // Normalize API keys to lowercase for template
    results.value = data.map((item) => ({
      id: item.ID,
      name: item.Name,
      location: item.Location,
      email: item.EmailAddress,
      type: item.Type.toLowerCase(),
    }));
  } catch (err) {
    console.error("Search failed:", err);
  }
}

// Emit events to parent
const emit = defineEmits(["open-organization-modal", "open-applicant-modal"]);

function openModal(item) {
  if (item.type === "organization") {
    emit("open-organization-modal", item);
  } else if (item.type === "applicant") {
    emit("open-applicant-modal", item);
  }

  // Clear search
  results.value = [];
  searchInput.value = "";
}

function acceptOrg(id) {
  console.log("Accepted org:", id);
}

function rejectOrg(id) {
  console.log("Rejected org:", id);
}

function closeApplicantModal() {
  showApplicantModal.value = false;
  selectedApplicant.value = null;
}

// Modal states
const showApplicantsModal = ref(false);
const showApplicantDetailModal = ref(false);

const highlightedApplicant = ref(null); // The one matching the search

function openApplicantsModal(item) {
  // Use the search results for highlighting
  highlightedApplicant.value = item;

  // Optionally fetch all applicants or use your search API
  allApplicants.value = results.value
    .filter((i) => i.type === "applicant")
    .sort((a) => (a.id === item.id ? -1 : 0)); // Put searched applicant on top

  showApplicantsModal.value = true;
}

const allApplicants = ref([
  // Example data; replace with your search results or API call
  {
    id: 1,
    name: "Juan Dela Cruz",
    email: "juan@email.com",
    location: "Manila",
  },
  { id: 2, name: "Maria Santos", email: "maria@email.com", location: "Cebu" },
]);

function deleteApplicant(id) {
  // Replace with your API delete call
  allApplicants.value = allApplicants.value.filter((a) => a.id !== id);
  console.log("Deleted applicant id:", id);
}
</script>

<template>
  <div class="flex-grow flex items-center justify-between p-4 font-poppins">
    <div class="flex items-center justify-between p-4 w-full">
      <!-- LEFT: Logo -->
      <button
        class="text-xl font-bold font-poppins text-darkslategray whitespace-nowrap"
        @click="$router.push({ name: 'AdminHomePage' })"
      >
        Pathfinder
      </button>

      <!-- CENTER: Admin Buttons (only on AdminHomePage) -->
      <div class="flex gap-8 flex-grow font-poppins pt-4 pb-3 pl-8">
        <!-- Home Button -->
        <button
          @click="$router.push({ name: 'AdminHomePage' })"
          :class="[
            'flex flex-col items-center justify-center transition-all duration-200',
            route.name === 'AdminHomePage'
              ? 'text-dark-slate border-b-4 border-dark-slate'
              : 'text-gray-400 hover:text-dark-slate border-b-4 border-transparent ',
          ]"
          title="Account Verification"
        >
          <svg
            class="w-8 h-8 mb-1"
            viewBox="0 0 24 24"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect width="24" height="24" fill="white" />
            <path
              d="M17.1 2H12.9C9.81693 2 8.37099 3.09409 8.06975 5.73901C8.00673 6.29235 8.465 6.75 9.02191 6.75H11.1C15.3 6.75 17.25 8.7 17.25 12.9V14.9781C17.25 15.535 17.7077 15.9933 18.261 15.9303C20.9059 15.629 22 14.1831 22 11.1V6.9C22 3.4 20.6 2 17.1 2Z"
            />
            <path
              d="M11.1 8H6.9C3.4 8 2 9.4 2 12.9V17.1C2 20.6 3.4 22 6.9 22H11.1C14.6 22 16 20.6 16 17.1V12.9C16 9.4 14.6 8 11.1 8ZM12.29 13.65L8.58 17.36C8.44 17.5 8.26 17.57 8.07 17.57C7.88 17.57 7.7 17.5 7.56 17.36L5.7 15.5C5.42 15.22 5.42 14.77 5.7 14.49C5.98 14.21 6.43 14.21 6.71 14.49L8.06 15.84L11.27 12.63C11.55 12.35 12 12.35 12.28 12.63C12.56 12.91 12.57 13.37 12.29 13.65Z"
            />
          </svg>
          <span class="text-xs whitespace-nowrap">Account Verification</span>
        </button>

        <!-- Account Settings Button -->
        <button
          @click="$router.push({ name: 'AdminUpdateDelete' })"
          :class="[
            'flex flex-col items-center justify-center transition-all duration-200',
            route.name === 'AdminUpdateDelete'
              ? 'text-dark-slate border-b-4 border-dark-slate'
              : 'text-gray-400 hover:text-dark-slate border-b-4 border-transparent hover:dark-slate',
          ]"
          title="Account Settings"
        >
          <svg
            class="w-8 h-8 mb-1"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M20.1 9.21994C18.29 9.21994 17.55 7.93994 18.45 6.36994C18.97 5.45994 18.66 4.29994 17.75 3.77994L16.02 2.78994C15.23 2.31994 14.21 2.59994 13.74 3.38994L13.63 3.57994C12.73 5.14994 11.25 5.14994 10.34 3.57994L10.23 3.38994C9.78 2.59994 8.76 2.31994 7.97 2.78994L6.24 3.77994C5.33 4.29994 5.02 5.46994 5.54 6.37994C6.45 7.93994 5.71 9.21994 3.9 9.21994C2.86 9.21994 2 10.0699 2 11.1199V12.8799C2 13.9199 2.85 14.7799 3.9 14.7799C5.71 14.7799 6.45 16.0599 5.54 17.6299C5.02 18.5399 5.33 19.6999 6.24 20.2199L7.97 21.2099C8.76 21.6799 9.78 21.3999 10.25 20.6099L10.36 20.4199C11.26 18.8499 12.74 18.8499 13.65 20.4199L13.76 20.6099C14.23 21.3999 15.25 21.6799 16.04 21.2099L17.77 20.2199C18.68 19.6999 18.99 18.5299 18.47 17.6299C17.56 16.0599 18.3 14.7799 20.11 14.7799C21.15 14.7799 22.01 13.9299 22.01 12.8799V11.1199C22 10.0799 21.15 9.21994 20.1 9.21994ZM12 15.2499C10.21 15.2499 8.75 13.7899 8.75 11.9999C8.75 10.2099 10.21 8.74994 12 8.74994C13.79 8.74994 15.25 10.2099 15.25 11.9999C15.25 13.7899 13.79 15.2499 12 15.2499Z"
              fill="currentColor"
            />
          </svg>
          <span class="text-xs whitespace-nowrap">Account Setting</span>
        </button>
      </div>

      <!-- RIGHT: Logout button (on AdminHomePage only) -->
      <div class="absolute top-2 right-6">
        <button
          @click="logout"
          :class="[
            'flex flex-col items-center justify-center transition-all duration-200',
            route.name === 'Logout'
              ? 'text-dark-slate border-b-4 border-dark-slate'
              : 'text-gray-400 hover:text-dark-slate border-b-4 border-transparent hover:border-dark-slate',
          ]"
          title="Logout"
        >
          <svg
            class="w-8 h-8 mb-1"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M21.4999 13V15.26C21.4999 19.73 19.7099 21.52 15.2399 21.52H15.1099C11.0899 21.52 9.23991 20.07 8.90991 16.53"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M8.8999 7.55999C9.2099 3.95999 11.0599 2.48999 15.1099 2.48999H15.2399C19.7099 2.48999 21.4999 4.27999 21.4999 8.74999"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M15.0001 12H3.62012"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M5.85 8.65002L2.5 12L5.85 15.35"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <span class="text-xs">Logout</span>
        </button>
      </div>

      <div class="flex justify-center w-full">
        <div
          ref="searchContainer"
          class="relative w-full max-w-md ml-4 font-poppins"
        >
          <!-- 🔍 Search Input -->
          <label
            class="bg-blue-gray input w-full flex items-center gap-2 border-none rounded-full px-3 py-2 cursor-text"
            @click="showDropdown = true"
          >
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
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
              </g>
            </svg>

            <input
              type="search"
              placeholder="Search Applicants or Organizations..."
              v-model="searchInput"
              class="flex-grow bg-transparent outline-none font-poppins"
              @focus="showDropdown = true"
              @input="performSearch"
            />
            <button
              v-if="searchInput"
              type="button"
              @click="clearSearch"
              class="absolute right-2 text-gray-400 hover:text-black"
            >
              ✕
            </button>
          </label>

          <!-- 🔽 Dropdown Results -->
          <div
            v-if="showDropdown && (results.length || searchInput)"
            class="absolute top-full left-0 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-md z-50 p-3 flex flex-col gap-3"
          >
            <!-- No results -->
            <div
              v-if="!results.length && searchInput"
              class="text-center text-gray-500 text-sm mt-2"
            >
              No results found.
            </div>

            <!-- Results -->
            <div v-else class="max-h-60 overflow-y-auto">
              <div
                v-for="(item, index) in results"
                :key="index"
                class="p-2 hover:bg-gray-100 rounded cursor-pointer transition-all"
                @mousedown.prevent="handleResultClick(item)"
              >
                <div class="font-semibold text-gray-800">
                  {{ item.name }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ item.email || item.location }}
                </div>
                <div class="text-xs italic text-gray-400">
                  {{
                    item.type === "organization" ? "Organization" : "Applicant"
                  }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- 🧭 Applicants Table Modal (Minimalist) -->
      <div
        v-if="showApplicantModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
        @click.self="closeApplicantModal"
      >
        <div
          class="bg-white rounded-xl shadow-lg w-full max-w-4xl p-6 relative max-h-[80vh] overflow-auto"
        >
          <!-- Close button -->
          <button
            @click="closeApplicantModal"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-light"
          >
            &times;
          </button>

          <h2 class="text-2xl font-semibold mb-6 text-gray-800">
            All Applicants
          </h2>

          <!-- Applicants Table -->
          <table class="w-full text-left border-separate border-spacing-y-2">
            <thead>
              <tr>
                <th class="text-left text-gray-500 font-medium px-4 py-2">
                  Name
                </th>
                <th class="text-left text-gray-500 font-medium px-4 py-2">
                  Email
                </th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="applicant in allApplicants"
                :key="applicant.id"
                class="bg-gray-50 hover:bg-gray-100 rounded-lg transition"
              >
                <td class="px-4 py-3 text-gray-800">{{ applicant.name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ applicant.email }}</td>
                <td class="px-4 py-3 flex justify-center">
                  <button
                    @click="deleteApplicant(applicant.id)"
                    class="bg-red-500 text-white text-sm px-3 py-1 rounded-md hover:bg-red-600 transition"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="fixed top-4 right-4 space-y-2 z-50">
      <div
        v-for="(t, i) in toasts"
        :key="i"
        class="px-4 py-2 rounded text-white shadow-md"
        :class="{
          'bg-green-600': t.type === 'success',
          'bg-red-600': t.type === 'error',
          'bg-blue-600': t.type === 'info',
        }"
      >
        {{ t.message }}
      </div>
    </div>
  </div>
</template>
<style scoped>
input[type="search"]::-webkit-search-cancel-button {
  -webkit-appearance: none;
  appearance: none;
}
</style>
