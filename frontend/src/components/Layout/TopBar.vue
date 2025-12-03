<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";
import { useRegistrationStore } from "@/stores/registrationStore";
import TrainingModal from "@/components/Layout/TrainingModal.vue";
import CareerModal from "@/components/Layout/careermodal.vue";

const route = useRoute();
const toasts = ref([]);
const regStore = useRegistrationStore(); // ✅ Pinia store
const isSearching = ref(false);

function showToast(message, type = "info") {
  toasts.value.push({ message, type });

  setTimeout(() => {
    toasts.value.shift();
  }, 3000);
}

// ✅ Use store instead of local registeredPosts
onMounted(() => {
  regStore.fetchMyRegistrations();
  fetchMyApplications();
});

// ✅ Everything else below remains **untouched**
const showDropdown = ref(false);
const activeMains = ref([]);
const activeSubs = ref([]);
const searchContainer = ref(null);
const mainFilters = ["Training", "Career", "Organization"];
const searchInput = ref("");
const results = ref([]);

const selectedTrainingModalData = ref(null);
const selectedCareerModalData = ref(null);
const myApplications = ref(new Set());

async function fetchMyApplications() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const ids = (response.data || [])
      .map(
        (app) =>
          app.careerID ?? app.career?.careerID ?? app?.CareerID ?? app?.career?.id
      )
      .filter((id) => id !== null && id !== undefined);
    myApplications.value = new Set(ids);
  } catch (error) {
    console.error("Failed to fetch applications:", error);
  }
}

function toggleMainFilter(main) {
  if (activeMains.value.includes(main)) {
    activeMains.value = activeMains.value.filter((m) => m !== main);
    if (main === "Training" || main === "Career") activeSubs.value = [];
  } else {
    if (main === "Training" || main === "Career") {
      activeMains.value = activeMains.value.filter(
        (m) => m !== (main === "Training" ? "Career" : "Training")
      );
    }
    activeMains.value.push(main);
  }
}

function toggleExclusiveSub(sub) {
  if (activeSubs.value.includes(sub)) {
    activeSubs.value = [];
  } else {
    if (["Online", "On-site"].includes(sub)) {
      activeSubs.value = activeSubs.value.filter(
        (s) => !["Online", "On-site"].includes(s)
      );
    }
    activeSubs.value = [sub];
  }
}

function handleClickOutside(event) {
  if (searchContainer.value && !searchContainer.value.contains(event.target)) {
    showDropdown.value = false;
  }
}
onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() =>
  document.removeEventListener("click", handleClickOutside)
);

async function performSearch() {
  const query = searchInput.value.trim();
  if (!query) {
    results.value = [];
    return;
  }

  try {
    isSearching.value = true; // ✅ start loading

    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/search",
      {
        params: {
          search: query,
          filterType: activeMains.value[0]?.toLowerCase() || "all",
          subFilter: activeSubs.value[0] || "",
        },
      }
    );

    const candidateResults = response.data?.results ?? response.data ?? [];
    const data = Array.isArray(candidateResults) ? candidateResults : [];
    results.value = mergeTrainingResults(data);
  } catch (error) {
    console.error("Search failed:", error);
    results.value = [];
  } finally {
    isSearching.value = false; // ✅ stop loading
  }
}

watch(
  [searchInput, activeMains, activeSubs],
  () => {
    performSearch();
  },
  { immediate: true }
);

function isTraining(post) {
  return post.Type?.toLowerCase().includes("training");
}
function isCareer(post) {
  return post.Type?.toLowerCase().includes("career");
}
function isOrganization(post) {
  return post.Type?.toLowerCase().includes("organization");
}

function resetModals() {
  selectedTrainingModalData.value = null;
  selectedCareerModalData.value = null;
  selectedOrg.value = null;
  isTrainingModalOpen.value = false;
  isCareerModalOpen.value = false;
  isOrgModalOpen.value = false;
}

const isTrainingModalOpen = ref(false);
const isCareerModalOpen = ref(false);
const isOrgModalOpen = ref(false);

const selectedOrg = ref({});

function closeTrainingModal() {
  isTrainingModalOpen.value = false;
  selectedTrainingModalData.value = null;
}

function closeCareerModal() {
  isCareerModalOpen.value = false;
  selectedCareerModalData.value = null;
}

function closeOrgModal() {
  isOrgModalOpen.value = false;
  selectedOrg.value = {};
}

function buildScheduleEntry(item) {
  return {
    trainingScheduleID:
      item.trainingScheduleID || item.TrainingScheduleID || null,
    schedule: item.Schedule || item.schedule || null,
    end_time: item.end_time || item.End_time || item.EndTime || item.endTime || null,
    mode: item.Mode || item.mode || null,
    location: item.Location || item.location || null,
    trainingLink: item.TrainingLink || item.trainingLink || null,
  };
}

function mapTrainingResult(item) {
  if (!item) return null;
  const trainingID =
    item.trainingID ?? item.TrainingID ?? item.ID ?? item.id ?? null;
  const organizationName =
    item.OrganizationName ||
    item.organizationName ||
    item.organization ||
    item.Name ||
    "Unknown";

  const schedules =
    Array.isArray(item.schedules) && item.schedules.length
      ? item.schedules
      : (item.Schedule ||
          item.schedule ||
          item.Mode ||
          item.mode ||
          item.Location ||
          item.location)
        ? [buildScheduleEntry(item)]
        : [];

  return {
    trainingID,
    title: item.Title || item.title || "Untitled Training",
    description: item.Description || item.description || "No description provided.",
    organizationName,
    organization: { name: organizationName },
    schedules,
    Mode: item.Mode || item.mode || schedules[0]?.mode || null,
  };
}

function mapCareerResult(item) {
  if (!item) return null;
  const careerID =
    item.careerID ?? item.CareerID ?? item.ID ?? item.id ?? null;
  const organizationName =
    item.OrganizationName ||
    item.organizationName ||
    item.organization ||
    "Unknown";

  return {
    careerID,
    position:
      item.Title || item.Position || item.position || "Career Opportunity",
    details:
      item.Description ||
      item.details ||
      item.Details ||
      item.detailsAndInstructions ||
      "Not specified",
    placeOfAssignment:
      item.placeOfAssignment ||
      item.PlaceOfAssignment ||
      item.Location ||
      item.location ||
      "",
    qualificationStandard:
      item.Qualifications ||
      item.qualificationStandard ||
      item.qualificationstandard ||
      "",
    postingDate: item.postingDate || item.PostingDate || null,
    closingDate:
      item.closingDate ||
      item.ClosingDate ||
      item.DeadlineofSubmission ||
      null,
    pdf_directory: item.pdf_directory || item.pdfDirectory || item.PDF || null,
    organizationName,
  };
}

function hasScheduleData(entry) {
  if (!entry) return false;
  return !!(
    entry.schedule ||
    entry.end_time ||
    entry.mode ||
    entry.location ||
    entry.trainingLink
  );
}

function extractScheduleEntries(item) {
  if (!item) return [];
  if (Array.isArray(item.schedules) && item.schedules.length) {
    return item.schedules.filter((sched) => hasScheduleData(sched));
  }
  const entry = buildScheduleEntry(item);
  return hasScheduleData(entry) ? [entry] : [];
}

function mergeTrainingResults(data = []) {
  const trainingMap = new Map();
  const finalResults = [];

  data.forEach((item) => {
    const type = (item?.Type || "").toLowerCase();
    if (type.includes("training")) {
      const trainingID = resolveTrainingId(item) ?? Symbol("training");
      const scheduleEntries = extractScheduleEntries(item);

      if (!trainingMap.has(trainingID)) {
        const baseItem = {
          ...item,
          schedules: [...scheduleEntries],
        };
        trainingMap.set(trainingID, baseItem);
        finalResults.push(baseItem);
      } else {
        const existing = trainingMap.get(trainingID);
        existing.schedules = existing.schedules || [];
        scheduleEntries.forEach((entry) => {
          const isDuplicate = existing.schedules.some(
            (sched) =>
              sched.schedule === entry.schedule &&
              sched.mode === entry.mode &&
              sched.location === entry.location
          );
          if (!isDuplicate) {
            existing.schedules.push(entry);
          }
        });
      }
    } else {
      finalResults.push(item);
    }
  });

  return finalResults;
}

function resolveTrainingId(training) {
  if (!training) return null;
  return (
    training.trainingID ??
    training.TrainingID ??
    training.ID ??
    training.id ??
    null
  );
}

function handleTrainingModalRegister(payload) {
  if (!payload) return;

  if (payload.error) {
    showToast("Action failed.", "error");
    console.error("Failed to toggle registration:", payload.error);
    return;
  }

  const success = payload.isRegistered;
  showToast(
    success ? "Registered successfully!" : "Unregistered successfully!",
    success ? "success" : "info"
  );
}

function handleApplicationsUpdate(updatedSet) {
  myApplications.value = new Set(updatedSet ?? []);
}

async function handleResultClick(item) {
  if (!item) return;
  resetModals();
  const type = (item.Type || "").toLowerCase();

  if (type.includes("training")) {
    selectedTrainingModalData.value = mapTrainingResult(item);
    isTrainingModalOpen.value = true;
    return;
  }

  if (type.includes("career")) {
    selectedCareerModalData.value = mapCareerResult(item);
    isCareerModalOpen.value = true;
    return;
  }

  if (type.includes("organization")) {
    // Base info from the search result
    selectedOrg.value = {
      ...item,
      careers: [],
      trainings: [],
    };
    isOrgModalOpen.value = true;

    try {
      // Try to use the organizationID from search result if available
      const orgID =
        item.organizationID ||
        item.OrganizationID ||
        item.ID ||
        item.id ||
        null;

      // 1) Try to fetch the full organization (with relations) if we have an ID
      if (orgID) {
        try {
          const orgRes = await axios.get(
            import.meta.env.VITE_API_BASE_URL + `/organizations/${orgID}`
          );
          const org = orgRes.data;

          selectedOrg.value = {
            ...selectedOrg.value,
            Title: org.name || org.Name || selectedOrg.value.Title,
            OrganizationName:
              org.name || org.Name || selectedOrg.value.OrganizationName,
            Description:
              org.location ||
              org.Location ||
              selectedOrg.value.Description,
            OrganizationLocation:
              org.location ||
              org.Location ||
              selectedOrg.value.OrganizationLocation,
            Website:
              org.websiteURL ||
              org.WebsiteURL ||
              selectedOrg.value.Website,
            Logo:
              org.logoPath ||
              org.logo_directory ||
              selectedOrg.value.Logo,
            careers: Array.isArray(org.careers) ? org.careers : [],
            trainings: Array.isArray(org.trainings) ? org.trainings : [],
          };
        } catch (orgError) {
          console.warn("Could not fetch organization details via API:", orgError.response?.data || orgError.message);
          // Continue to use search API as fallback
        }
      }

      // 2) Use the search endpoint to gather all posts by organization name
      const orgName =
        selectedOrg.value.Title ||
        selectedOrg.value.OrganizationName ||
        item.Name ||
        item.OrganizationName ||
        item.Title ||
        "";

      if (orgName) {
        try {
          // Search for all content (not just organization) to get careers and trainings
          const searchRes = await axios.get(
            import.meta.env.VITE_API_BASE_URL +
              `/search?search=${encodeURIComponent(orgName)}&filterType=all`
          );

          const searchResults = searchRes.data.results || [];

          // Filter careers that belong to this organization
          const careersFromSearch = searchResults.filter((r) => {
            const t = (r.Type || r.type || "").toLowerCase();
            const orgMatch = 
              (r.OrganizationName || "").toLowerCase() === orgName.toLowerCase() ||
              (r.organizationName || "").toLowerCase() === orgName.toLowerCase();
            return t.includes("career") && orgMatch;
          });

          // Filter trainings that belong to this organization
          const trainingsFromSearch = searchResults.filter((r) => {
            const t = (r.Type || r.type || "").toLowerCase();
            const orgMatch = 
              (r.OrganizationName || "").toLowerCase() === orgName.toLowerCase() ||
              (r.organizationName || "").toLowerCase() === orgName.toLowerCase();
            return t.includes("training") && orgMatch;
          });

        // Merge API careers/trainings with search-based ones (avoid duplicates by ID)
        const byId = (list, idKeys) => {
          const map = new Map();
          for (const item of list) {
            const id =
              item.careerID ||
              item.CareerID ||
              item.trainingID ||
              item.TrainingID ||
              item.ID ||
              item.id;
            if (!id) continue;
            if (!map.has(id)) map.set(id, item);
          }
          return Array.from(map.values());
        };

          const mergedCareers = byId(
            [...(selectedOrg.value.careers || []), ...careersFromSearch],
            ["careerID", "CareerID", "ID", "id"]
          );

          const mergedTrainings = byId(
            [...(selectedOrg.value.trainings || []), ...trainingsFromSearch],
            ["trainingID", "TrainingID", "ID", "id"]
          );

          selectedOrg.value.careers = mergedCareers;
          selectedOrg.value.trainings = mergedTrainings;
        } catch (searchError) {
          console.warn("Could not fetch careers/trainings via search:", searchError.response?.data || searchError.message);
        }
      }
    } catch (error) {
      console.error("Error fetching organization posts:", error);
    }
  }
}
</script>

<template>
  <div class="flex-grow flex items-center justify-between p-4">
    <div class="flex items-center justify-between p-4 w-full">
      <!-- LEFT: Logo -->
      <button
        class="text-xl font-bold font-poppins text-darkslategray whitespace-nowrap"
        @click="$router.push({ name: 'Homepage' })"
      >
        Pathfinder
      </button>

      <!-- CENTER: Admin Buttons (only on AdminHomePage) -->
      <div
        v-if="
          route.path === '/admin' ||
          route.path === '/admin/adminhomepage' ||
          route.name === 'AdminUpdateDelete'
        "
        class="flex items-center justify-center gap-8 flex-grow font-poppins pt-4 pb-3"
      >
        <!-- Home Button -->
        <button
          @click="$router.push({ name: 'AdminHomePage' })"
          :class="[
            'flex flex-col items-center justify-center transition-all duration-200',
            route.name === 'AdminHomePage'
              ? 'text-dark-slate border-b-4 border-dark-slate'
              : 'text-gray-400 hover:text-customButton border-b-4 border-transparent hover:customButton',
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
          <span class="text-xs">Account Verification</span>
        </button>

        <!-- Account Settings Button -->
        <button
          @click="$router.push({ name: 'AdminUpdateDelete' })"
          :class="[
            'flex flex-col items-center justify-center transition-all duration-200',
            route.name === 'AdminUpdateDelete'
              ? 'text-dark-slate border-b-4 border-dark-slate'
              : 'text-gray-400 hover:text-customButton border-b-4 border-transparent hover:customButton',
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
          <span class="text-xs">Account Setting</span>
        </button>
      </div>

      <!-- RIGHT: Logout button (on AdminHomePage only) -->
      <div
        v-if="
          route.path === '/admin' ||
          route.path === '/admin/adminhomepage' ||
          route.name === 'AdminUpdateDelete'
        "
        class="absolute top-2 right-6"
      >
        <button
          @click="logout"
          :class="[
            'flex flex-col items-center justify-center transition-all duration-200',
            route.name === 'Logout'
              ? 'text-dark-slate border-b-4 border-dark-slate'
              : 'text-gray-400 hover:text-customButton border-b-4 border-transparent hover:border-customButton',
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

      <!-- RIGHT: Search bar (non-admin pages) -->
      <div v-else class="flex justify-end w-full">
        <div
          ref="searchContainer"
          class="relative w-full max-w-md ml-4 font-poppins"
        >
          <!-- Search Input -->
          <label
            class="bg-white input w-full flex items-center gap-2 border-none rounded-full px-3 py-2 cursor-text relative"
            @click="showDropdown = true"
          >
            <!-- Search Icon -->
            <svg
              class="h-5 w-5 text-gray-500 flex-shrink-0"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
            >
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="darkslategray">
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

            <!-- Clear Button -->
            <button
              v-if="searchInput && !isSearching"
              type="button"
              @click="searchInput = ''"
              class="absolute right-2 text-gray-400 hover:text-black"
            >
              ✕
            </button>

            <!-- Loading Spinner -->
            <svg
              v-if="isSearching"
              class="absolute right-2 h-4 w-4 animate-spin text-gray-500"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 018 8h-4l3 3-3 3h4a8 8 0 01-8 8v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
            </svg>
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
                v-for="sub in ['Online', 'On-site']"
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

            <!-- Search Results -->
            <div v-if="results.length" class="mt-2 max-h-60 overflow-y-auto">
              <div
                v-for="(item, index) in results"
                :key="index"
                class="p-2 hover:bg-gray-100 rounded cursor-pointer"
                @click="handleResultClick(item)"
              >
                <div class="font-semibold text-gray-800">
                  {{ item.Title || item.Position || item.Name }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ item.OrganizationName || item.location || "" }}
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
    </div>

    <TrainingModal
      :isOpen="isTrainingModalOpen"
      :training="selectedTrainingModalData"
      @close="closeTrainingModal"
      @toggle-register="handleTrainingModalRegister"
    />

    <CareerModal
      :show="isCareerModalOpen"
      :career="selectedCareerModalData"
      :myApplications="myApplications"
      @close="closeCareerModal"
      @update-applications="handleApplicationsUpdate"
    />

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
    <!--  ORGANIZATION MODAL -->
    <dialog v-if="isOrgModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-2xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeOrgModal"
        >
          ✕
        </button>

        <!-- Organization Info -->
        <div class="flex items-center gap-3 mb-3">
          <img
            v-if="selectedOrg.Logo"
            :src="selectedOrg.Logo"
            alt="Organization Logo"
            class="w-14 h-14 rounded-full object-cover"
          />
          <div>
            <h2 class="text-xl font-bold">
              {{ selectedOrg.Title || selectedOrg.OrganizationName }}
            </h2>
            <p class="text-sm text-gray-600">
              {{ selectedOrg.Description || selectedOrg.OrganizationLocation }}
            </p>
          </div>
        </div>

        <!-- Website -->
        <p v-if="selectedOrg.Website" class="text-sm mb-5">
          <a
            :href="selectedOrg.Website"
            target="_blank"
            class="text-blue-600 underline break-all"
          >
            {{ selectedOrg.Website }}
          </a>
        </p>

        <!--  Career Posts -->
        <div
          v-if="selectedOrg.careers && selectedOrg.careers.length"
          class="mt-4"
        >
          <h3 class="text-base font-semibold mb-3">Career Posts</h3>
          <div
            class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
            style="scrollbar-width: thin"
          >
            <div
              v-for="career in selectedOrg.careers"
              :key="career.careerID || career.ID || career.id"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
              @click="handleResultClick({
                ...career,
                Type: 'Career',
                Title: career.position || career.Position || career.Title,
                ID: career.careerID || career.ID || career.id
              })"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ career.position || career.Position || career.Title || "Career Position" }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                Deadline: {{ career.closingDate || career.DeadlineOfSubmission || career.closing_date || "N/A" }}
              </p>
            </div>
          </div>
        </div>
        <div v-else-if="selectedOrg.careers && selectedOrg.careers.length === 0" class="mt-4">
          <p class="text-sm text-gray-500 italic">No career posts available.</p>
        </div>

        <!--  Training Posts -->
        <div
          v-if="selectedOrg.trainings && selectedOrg.trainings.length"
          class="mt-6"
        >
          <h3 class="text-base font-semibold mb-3">Training Posts</h3>
          <div
            class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
            style="scrollbar-width: thin"
          >
            <div
              v-for="training in selectedOrg.trainings"
              :key="training.trainingID || training.ID || training.id"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
              @click="handleResultClick({
                ...training,
                Type: 'Training',
                Title: training.title || training.Title,
                ID: training.trainingID || training.ID || training.id
              })"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ training.title || training.Title || "Training" }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                Schedule: {{ training.schedule || training.Schedule || "Not specified" }}
              </p>
            </div>
          </div>
        </div>
        <div v-else-if="selectedOrg.trainings && selectedOrg.trainings.length === 0" class="mt-6">
          <p class="text-sm text-gray-500 italic">No training posts available.</p>
        </div>
      </div>
    </dialog>
  </div>
</template>
<style scoped>
input[type="search"]::-webkit-search-cancel-button {
  -webkit-appearance: none;
  appearance: none;
}
</style>
