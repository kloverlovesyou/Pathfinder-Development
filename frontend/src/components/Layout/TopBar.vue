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

// --- Modal States ---

const applyModalOpen = ref(false);
const isModalOpen = ref(false); // For org modal

// --- Misc States ---
const bookmarkedPosts = ref({});
const appliedPosts = ref({});
const registeredPosts = ref({});
const organizations = ref({});
const posts = ref([]);

// --- Filter Toggles ---
function toggleMainFilter(main) {
  if (activeMains.value.includes(main)) {
    activeMains.value = activeMains.value.filter((m) => m !== main);
    if (main === "Training" || main === "Career") activeSubs.value = [];
  } else {
    if (main === "Training" || main === "Career") {
      // Mutually exclusive
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
  if (!searchInput.value.trim()) {
    results.value = [];
    return;
  }

  try {
    const response = await axios.get("http://127.0.0.1:8000/api/search", {
      params: {
        search: searchInput.value,
        filterType: activeMains.value[0]?.toLowerCase() || "",
        subFilter: activeSubs.value[0] || "",
      },
    });

    results.value = response.data.results || [];
  } catch (error) {
    console.error("Search failed:", error);
    results.value = [];
  }
}
watch([searchInput, activeMains, activeSubs], performSearch);

// --- Type Checkers ---
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

    // Fetch related posts dynamically
    const response = await axios.get(`/api/organization/${orgItem.ID}/posts`);
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

// 🟦 TRAINING MODAL
function openTrainingModal(post) {
  selectedPost.value = post;
  isTrainingModalOpen.value = true;
}

function closeTrainingModal() {
  selectedPost.value = {};
  isTrainingModalOpen.value = false;
}

// 🟩 CAREER MODAL
function openCareerModal(post) {
  selectedPost.value = post;
  isCareerModalOpen.value = true;
}

function closeCareerModal() {
  selectedPost.value = {};
  isCareerModalOpen.value = false;
}

// 🧩 APPLY MODAL
function openApplyModal(post) {
  selectedPost.value = post;
  applyModalOpen.value = true;
}

function closeApplyModal() {
  applyModalOpen.value = false;
  selectedPost.value = {};
}

// 🟧 ORG MODAL
function openOrgModal(org) {
  selectedOrg.value = org;
  isOrgModalOpen.value = true;
}

function closeOrgModal() {
  isOrgModalOpen.value = false;
  selectedOrg.value = {};
}

// Handles result click depending on Type
async function handleResultClick(item) {
  if (item.Type === "Training" || item.Type.includes("Training")) {
    // 🎯 Training modal
    selectedPost.value = item;
    isTrainingModalOpen.value = true;
  } else if (item.Type === "Career" || item.Type.includes("Career")) {
    // 🎯 Career modal
    selectedPost.value = item;
    isCareerModalOpen.value = true;
  } else if (item.Type === "Organization") {
    // 🎯 Organization modal (fetch org info + its posts)
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
    <!-- Logo -->
    <button
      class="text-xl font-bold font-poppins text-darkslategray whitespace-nowrap"
      @click="$router.push({ name: 'Homepage' })"
    >
      Pathfinder
    </button>

    <!-- Search Container -->
    <div class="flex justify-end w-full">
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

          <!--  Search Results -->
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

      <div>
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
                  bookmarkedPosts[
                    selectedPost.TrainingID || selectedPost.CareerID
                  ]
                    ? "Bookmarked"
                    : "Bookmark"
                }}
              </button>

              <!-- Register (training only) -->
              <button
                v-if="isTraining(selectedPost)"
                class="btn btn-sm text-white"
                :class="
                  registeredPosts[selectedPost.TrainingID]
                    ? 'bg-gray-500'
                    : 'bg-customButton'
                "
                @click="toggleRegister(selectedPost)"
              >
                {{
                  registeredPosts[selectedPost.TrainingID]
                    ? "Unregister"
                    : "Register"
                }}
              </button>
            </div>

            <!-- Training Info -->
            <p>
              <strong>Mode:</strong> {{ selectedPost.Mode || "Not specified" }}
            </p>
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
                  bookmarkedPosts[
                    selectedPost.TrainingID || selectedPost.CareerID
                  ]
                    ? "Bookmarked"
                    : "Bookmark"
                }}
              </button>

              <!-- Apply / Cancel -->
              <button
                v-if="!appliedPosts[selectedPost.ID]"
                class="btn btn-sm bg-customButton text-white"
                @click="openApplyModal(selectedPost)"
              >
                Apply
              </button>

              <button
                v-else
                class="btn btn-sm bg-gray-500 text-white"
                @click="cancelApplication(selectedPost)"
              >
                Cancel Application
              </button>
            </div>

            <!-- Career Info -->
            <p>
              <strong>Details:</strong>
              {{
                selectedPost.Description || selectedPost.DetailsandInstructions
              }}
            </p>
            <p>
              <strong>Qualifications:</strong> {{ selectedPost.Qualifications }}
            </p>
            <p>
              <strong>Requirements:</strong> {{ selectedPost.Requirements }}
            </p>
            <p>
              <strong>Application Address:</strong>
              {{ selectedPost.ApplicationLetterAddress }}
            </p>
            <p>
              <strong>Deadline:</strong> {{ selectedPost.DeadlineofSubmission }}
            </p>

            <!-- Recommended Trainings -->
            <div class="mt-6">
              <h3 class="text-base font-semibold mb-3">
                Recommended Trainings
              </h3>
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
                  {{
                    selectedOrg.Description || selectedOrg.OrganizationLocation
                  }}
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
    </div>
  </div>
</template>
<style scoped>
input[type="search"]::-webkit-search-cancel-button {
  -webkit-appearance: none;
  appearance: none;
}
</style>
