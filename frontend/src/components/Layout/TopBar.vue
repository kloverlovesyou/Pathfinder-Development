<script setup>
import { ref, onMounted, onBeforeUnmount, watch, reactive } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";
import { useRegistrationStore } from "@/stores/registrationStore";

const qrCodeUrl = ref(null);

const route = useRoute();
const toasts = ref([]);
const regStore = useRegistrationStore(); // ✅ Pinia store

function showToast(message, type = "info") {
  toasts.value.push({ message, type });

  setTimeout(() => {
    toasts.value.shift();
  }, 3000);
}

// ✅ Use store instead of local registeredPosts
onMounted(() => {
  regStore.fetchMyRegistrations();
});

// ✅ Toggle using store
async function toggleRegister(training) {
  const token = localStorage.getItem("token");
  if (!token) {
    showToast("Please log in first.", "error");
    return;
  }

  try {
    await regStore.toggleRegister(training);

    const trainingID =
      training.trainingID ||
      training.TrainingID ||
      training.id ||
      training.ID;

    const isRegistered = regStore.registeredPosts[trainingID];

    showToast(
      isRegistered ? "Registered successfully!" : "Unregistered successfully!",
      "success"
    );
  } catch (err) {
    showToast("Action failed.", "error");
    console.error(err);
  }
}

// ✅ Everything else below remains **untouched**
const showDropdown = ref(false);
const activeMains = ref([]);
const activeSubs = ref([]);
const searchContainer = ref(null);
const mainFilters = ["Training", "Career", "Organization"];
const searchInput = ref("");
const results = ref([]);

const applyModalOpen = ref(false);
const isModalOpen = ref(false);

const bookmarkedPosts = ref({});
const appliedPosts = ref({});
const organizations = ref({});
const posts = ref([]);

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
  if (!searchInput.value.trim()) {
    results.value = [];
    return;
  }

  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/search",
      {
        params: {
          search: searchInput.value,
          filterType: activeMains.value[0]?.toLowerCase() || "",
          subFilter: activeSubs.value[0] || "",
        },
      }
    );

    results.value = response.data.results || [];
  } catch (error) {
    console.error("Search failed:", error);
    results.value = [];
  }
}
watch([searchInput, activeMains, activeSubs], performSearch);

function isTraining(post) {
  return post.Type?.toLowerCase().includes("training");
}
function isCareer(post) {
  return post.Type?.toLowerCase().includes("career");
}
function isOrganization(post) {
  return post.Type?.toLowerCase().includes("organization");
}

async function openOrganizationModal(orgItem) {
  resetModals();
  try {
    isModalOpen.value = true;
    selectedOrg.value = {
      name: orgItem.Name || orgItem.OrganizationName,
      location: orgItem.Location || "",
      website: orgItem.Website || "",
      logo: orgItem.Logo || "",
      careers: [],
      trainings: [],
    };

    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL +
        `/organization/${orgItem.ID}/posts`
    );
    selectedOrg.value.careers = response.data.careers || [];
    selectedOrg.value.trainings = response.data.trainings || [];
  } catch (error) {
    console.error("Failed to fetch organization details:", error);
  }
}

function resetModals() {
  selectedPost.value = null;
  isModalOpen.value = false;
  selectedOrg.value = null;
  applyModalOpen.value = false;
}

const isTrainingModalOpen = ref(false);
const isCareerModalOpen = ref(false);
const isOrgModalOpen = ref(false);

const selectedPost = ref({});
const selectedOrg = ref({});

async function openTrainingModal(post) {
  selectedPost.value = post;
  isTrainingModalOpen.value = true;

  try {
    const trainingID = post.trainingID || post.TrainingID || post.ID;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/training/${trainingID}/qrcode`
    );

    qrCodeUrl.value = res.data.qr_url; // ✅ backend should return a URL
  } catch (err) {
    console.error("Failed to fetch QR code:", err);
    qrCodeUrl.value = null;
  }
}
function closeTrainingModal() {
  selectedPost.value = {};
  isTrainingModalOpen.value = false;
}
function openCareerModal(post) {
  selectedPost.value = post;
  isCareerModalOpen.value = true;
}
function closeCareerModal() {
  selectedPost.value = {};
  isCareerModalOpen.value = false;
}
function openApplyModal(post) {
  selectedPost.value = post;
  applyModalOpen.value = true;
}
function closeApplyModal() {
  applyModalOpen.value = false;
  selectedPost.value = {};
}
function openOrgModal(org) {
  selectedOrg.value = org;
  isOrgModalOpen.value = true;
}
function closeOrgModal() {
  isOrgModalOpen.value = false;
  selectedOrg.value = {};
}

async function handleResultClick(item) {
  if (item.Type === "Training" || item.Type.includes("Training")) {
    selectedPost.value = item;
    isTrainingModalOpen.value = true;
  } else if (item.Type === "Career" || item.Type.includes("Career")) {
    selectedPost.value = item;
    isCareerModalOpen.value = true;
  } else if (item.Type === "Organization") {
    selectedOrg.value = {
      ...item,
      careers: [],
      trainings: [],
    };
    isOrgModalOpen.value = true;

    try {
      const response = await axios.get(
        `/api/search?search=${encodeURIComponent(
          item.Title
        )}&filterType=organization`
      );
      const results = response.data.results || [];

      selectedOrg.value.careers = results.filter((r) =>
        r.Type.includes("Career")
      );
      selectedOrg.value.trainings = results.filter((r) =>
        r.Type.includes("Training")
      );
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
            <button
              v-if="searchInput"
              type="button"
              @click="searchInput = ''"
              class="absolute right-2 text-gray-400 hover:text-black"
            >
              ✕
            </button>
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
    </div>

    <!-- TRAINING MODAL -->
    <dialog v-if="isTrainingModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <button
          class="btn btn-sm btn-circle absolute right-2 top-2"
          @click="closeTrainingModal"
        >
          ✕
        </button>

        <h2 class="text-xl font-bold mb-2">{{ selectedPost.Title }}</h2>
        <p class="text-sm text-gray-600 mb-2">
          Organization: {{ selectedPost.OrganizationName }}
        </p>

        <!-- Buttons -->
        <div class="my-4 flex justify-end gap-2">

          <!-- Bookmark -->
          <button
            class="btn btn-outline btn-sm"
            @click="toggleBookmark(selectedPost)"
          >
            {{
              bookmarkedPosts[selectedPost.TrainingID || selectedPost.CareerID]
                ? "Bookmarked"
                : "Bookmark"
            }}
          </button>

          <!-- Register -->
          <button
            v-if="isTraining(selectedPost)"
            class="btn btn-sm text-white"
            :class="
              regStore.registeredPosts[
                selectedPost.trainingID ??
                selectedPost.TrainingID ??
                selectedPost.id ??
                selectedPost.ID
              ]
                ? 'bg-gray-500'
                : 'bg-customButton'
            "
            @click="toggleRegister(selectedPost)"
          >
            {{
              regStore.registeredPosts[
                selectedPost.trainingID ??
                selectedPost.TrainingID ??
                selectedPost.id ??
                selectedPost.ID
              ]
                ? 'Unregister'
                : 'Register'
            }}
          </button>

        </div>

        <!-- ✅ ✅ QR CODE SECTION -->
        <div class="flex justify-center my-4">
          <template v-if="qrCodeUrl">
            <img
              :src="qrCodeUrl"
              alt="Training QR"
              class="w-40 h-40 border rounded shadow-md"
            />
          </template>

          <template v-else>
            <p class="text-center text-gray-500 text-sm">
              QR code will appear once registered.
            </p>
          </template>
        </div>
        <!-- ✅ ✅ END QR SECTION -->

        <!-- Training Info -->
        <p><strong>Mode:</strong> {{ selectedPost.Mode || "Not specified" }}</p>
        <p><strong>Description:</strong> {{ selectedPost.Description }}</p>
        <p><strong>Schedule:</strong> {{ selectedPost.Schedule }}</p>
        <p><strong>Location:</strong> {{ selectedPost.Location }}</p>
      </div>
    </dialog>

    <!-- CAREER MODAL -->
    <dialog v-if="isCareerModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <button
          class="btn btn-sm btn-circle absolute right-2 top-2"
          @click="closeCareerModal"
        >
          ✕
        </button>
        <h2 class="text-xl font-bold mb-2">
          {{ selectedPost.Title || selectedPost.Position }}
        </h2>

        <p class="text-sm text-gray-600 mb-2">
          Organization: {{ selectedPost.OrganizationName }}
        </p>

        <!-- Buttons -->
        <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedPost)"
            >
              {{
                bookmarkedPosts[selectedPost.TrainingID || selectedPost.CareerID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Register (training only) -->
            <button
                v-if="isTraining(selectedPost)"
                class="btn btn-sm text-white flex items-center justify-center gap-2"
                :class="regStore.registeredPosts[selectedPost.trainingID ?? selectedPost.TrainingID ?? selectedPost.id ?? selectedPost.ID] ? 'bg-gray-500' : 'bg-customButton'"
                :disabled="regStore.loadingPosts[selectedPost.trainingID ?? selectedPost.TrainingID ?? selectedPost.id ?? selectedPost.ID]"
                @click="toggleRegister(selectedPost)"
              >
                <template v-if="regStore.loadingPosts[selectedPost.trainingID ?? selectedPost.TrainingID ?? selectedPost.id ?? selectedPost.ID]">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>
                </template>
                <template v-else>
                  {{ regStore.registeredPosts[selectedPost.trainingID ?? selectedPost.TrainingID ?? selectedPost.id ?? selectedPost.ID] ? 'Unregister' : 'Register' }}
                </template>
              </button>
          </div>

        <!-- Career Info -->
        <p>
          <strong>Details:</strong>
          {{ selectedPost.Description || selectedPost.DetailsandInstructions }}
        </p>
        <p>
          <strong>Qualifications:</strong> {{ selectedPost.Qualifications }}
        </p>
        <p><strong>Requirements:</strong> {{ selectedPost.Requirements }}</p>
        <p>
          <strong>Application Address:</strong>
          {{ selectedPost.ApplicationLetterAddress }}
        </p>
        <p>
          <strong>Deadline:</strong> {{ selectedPost.DeadlineofSubmission }}
        </p>

        <!-- Recommended Trainings -->
        <div class="mt-6">
          <h3 class="text-base font-semibold mb-3">Recommended Trainings</h3>
          <div
            class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
            style="scrollbar-width: thin"
          >
            <div
              v-for="post in posts.filter((p) => isTraining(p))"
              :key="post.TrainingID"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
              @click.stop="openTrainingModal(post)"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ post.Title }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                {{ organizations[post.OrganizationID] }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </dialog>

    <!-- APPLY MODAL -->
    <dialog v-if="applyModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins">
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeApplyModal"
        >
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4">
          Apply for {{ selectedPost?.Position || selectedPost?.Title }}
        </h2>

        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
              Upload PDF Requirements
            </label>
            <input
              type="file"
              accept="application/pdf"
              @change="handleFileUpload"
              required
              class="file-input file-input-bordered w-full"
            />
          </div>

          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="btn btn-outline btn-sm"
              @click="closeApplyModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn bg-customButton hover:bg-dark-slate text-white btn-sm"
            >
              Submit
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <div class="fixed top-4 right-4 space-y-2 z-50">
      <div
        v-for="(t, i) in toasts"
        :key="i"
        class="px-4 py-2 rounded text-white shadow-md"
        :class="{
          'bg-green-600': t.type === 'success',
          'bg-red-600': t.type === 'error',
          'bg-blue-600': t.type === 'info'
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
              :key="career.ID"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
              @click="handleResultClick(career)"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ career.Title }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                Deadline: {{ career.DeadlineOfSubmission || "N/A" }}
              </p>
            </div>
          </div>
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
              :key="training.ID"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
              @click="handleResultClick(training)"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ training.Title }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                Schedule: {{ training.Schedule || "Not specified" }}
              </p>
            </div>
          </div>
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
