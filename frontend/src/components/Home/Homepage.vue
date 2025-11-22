<script setup>
import { ref, reactive, onMounted, nextTick } from "vue";
import "cally"; // Calendar library
import axios from "axios";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

// ------------------ STATES ------------------
const calendarOpen = ref(false);
const isSidebarOpen = ref(false);

const registeredPosts = reactive({});
const appliedPosts = ref({});
const bookmarkedPosts = ref({});
const selectedCareerDetails = ref(null);
const recommendedTrainings = ref([]);
const allCareers = ref([]);
const selectedCareerId = ref(null);
const showCareerPopup = ref(false);
const showTrainingModal = ref(false);
const selectedTraining = ref(null);
const selectedPost = ref(null);
const posts = ref([]);
const myApplications = ref(new Set());
const myRegistrations = ref(new Set());
const toasts = ref([]);

const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);
const uploadedFilde = ref(null);
const uploadedFile = ref(null);
const applyModalOpen = ref(false);
const organizations = ref({});

// Build events only when posts are loaded
function buildEvents() {
  events.value = {};
  posts.value.forEach((post) => {
    const date = post.deadlineOfSubmission || (post.schedule ? post.schedule.split("T")[0] : null);
    if (date) {
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push(post);
    }
  });
}

// ------------------ TOAST ------------------
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ------------------ FETCH DATA ------------------
async function fetchAllCareers() {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/careers");
    allCareers.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error(err);
    addToast("Failed to fetch careers", "error");
  }
}

async function fetchMyApplications() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    myApplications.value = new Set(res.data.map((a) => a.careerID));
    res.data.forEach((a) => (appliedPosts.value[a.careerID] = true));
  } catch (err) {
    console.error(err);
  }
}

async function fetchMyRegistrations() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    myRegistrations.value = new Set(res.data.map((r) => r.trainingID));
    res.data.forEach((r) => (registeredPosts[r.trainingID] = true));
  } catch (err) {
    console.error(err);
  }
}


// ------------------ MODALS ------------------
async function openCareerModal(career) {
  try {
    console.log('Career Object:', career);

    const careerID = parseInt(career.careerID, 10);
    console.log('Parsed Career ID:', careerID);

    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + `/careers/${careerID}/details`);

    //store main career + recommended trainings
    selectedCareerDetails.value = res.data.career;
    recommendedTrainings.value = res.data.recommended_trainings || [];
    showCareerPopup.value = true;
  } catch (error) {
    console.error("Error loading career details:", error);
    alert("Failed to load career details.");
  }
}
function closeCareerModal() {
  showCareerPopup.value = false;
  selectedCareerDetails.value = null;
  recommendedTrainings.value = [];
}

// Open training modal
function openTrainingModal(training) {
  selectedTraining.value = training;
  showTrainingModal.value = true;
}

// Close training modal
function closeTrainingModal() {
  showTrainingModal.value = false;
  selectedTraining.value = null;
}

// Cancel application
function cancelApplication(career) {
  const id = career.careerID;
  appliedPosts.value[id] = false;
  myApplications.value.delete(id);
}

// Toggle bookmark for career
async function toggleCareerBookmark(career) {
  const token = localStorage.getItem('token');
  if (!token) {
    addToast('PLEASE LOG IN FIRST', 'accent');
    return;
  }

  const careerID = career.careerID;
  const isBookmarked = bookmarkedPosts.value[careerID];

  try {
    if (isBookmarked) {
      await axios.delete(import.meta.env.VITE_API_BASE_URL + `/career-bookmarks/${careerID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedPosts.value[careerID] = false;
      addToast('Bookmark removed', 'info');
    } else {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + '/career-bookmarks',
        { careerID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedPosts.value[careerID] = true;
      addToast('Bookmarked!', 'success');
    }
  } catch (error) {
    if (error.response?.status === 409) {
      addToast('Already bookmarked', 'accent');
    } else {
      addToast('Failed to bookmark', 'error');
    }
  }
}

// Register for training
async function registerForTraining(training) {
  if (!training) return;

  try {
    const token = localStorage.getItem('token');
    if (!token) {
      addToast('PLEASE LOG IN FIRST', 'accent');
      return;
    }

    await axios.post(
      import.meta.env.VITE_API_BASE_URL + '/registrations',
      { trainingID: training.trainingID },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    addToast('REGISTRATION SUCCESSFUL!!!', 'success');
    myRegistrations.value.add(training.trainingID);
    registeredPosts.value[training.trainingID] = true;
  } catch (error) {
    if (error.response?.status === 409) {
      addToast('YOU ALREADY REGISTERED FOR THIS TRAINING', 'accent');
    } else if (error.response?.status === 401) {
      addToast('UNAUTHORIZED. PLEASE LOG IN AGAIN', 'accent');
    } else {
      addToast('FAILED TO REGISTER', 'accent');
    }
  }
}

// Unregister from training
async function unregisterFromTraining(training) {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;

    // Find registration ID
    const registrationsRes = await axios.get(import.meta.env.VITE_API_BASE_URL + '/registrations', {
      headers: { Authorization: `Bearer ${token}` },
    });

    const registration = registrationsRes.data.find(r => r.trainingID === training.trainingID);
    if (registration) {
      await axios.delete(import.meta.env.VITE_API_BASE_URL + `/registrations/${registration.id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      myRegistrations.value.delete(training.trainingID);
      registeredPosts.value[training.trainingID] = false;
      addToast('Unregistered successfully', 'info');
    }
  } catch (error) {
    console.error("Error unregistering:", error);
    addToast('Failed to unregister', 'error');
  }
}


function openModal(post) {
  selectedPost.value = post;
}

function closeModal() {
  selectedPost.value = null;
}

// Show events on calendar click
function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}





function openApplyModal(career) {
  selectedPost.value = career;
  applyModalOpen.value = true;
}

function closeApplyModal() {
  applyModalOpen.value = false;
  uploadedFile.value = null;
  selectedPost.value = null;
}

function handleFileUpload(e) {
  uploadedFile.value = e.target.files[0];
}

async function fetchRecommendedCareers() {
  if (!selectedCareerId.value) {
    posts.value = [];
    return;
  }

  try {
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL +
      `/careers/recommend/${selectedCareerId.value}`
    );

    // API SHOULD RETURN LIST OF CAREERS
    posts.value = Array.isArray(res.data) ? res.data : [];

  } catch (err) {
    console.error("Error fetching recommended careers:", err);
    posts.value = [];
    addToast("Failed to load recommended careers", "error");
  }
}
// ------------------ ACTIONS ------------------


async function toggleTrainingBookmark(training) {
  const token = localStorage.getItem("token");
  if (!token) return addToast("Please log in first", "accent");

  const id = training.trainingID;
  const isBookmarked = bookmarkedPosts.value[id];

  try {
    if (isBookmarked) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/bookmarks/${id}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      bookmarkedPosts.value[id] = false;
      addToast("Bookmark removed", "info");
    } else {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/bookmarks",
        { trainingID: id },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedPosts.value[id] = true;
      addToast("Bookmarked!", "success");
    }
  } catch (err) {
    addToast("Failed to toggle bookmark", "error");
  }
}

// ------------------ HELPER ------------------
const isTraining = (post) => !!post.trainingID;
const isRegisteredOrApplied = (post) =>
  isTraining(post)
    ? myRegistrations.value.has(post.trainingID)
    : myApplications.value.has(post.careerID);

function formatDateTime(dt) {
  if (!dt) return "";
  return new Date(dt).toLocaleString("en-US", {
    dateStyle: "long",
    timeStyle: "short",
  });
}



function formatDate(d) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-US", { dateStyle: "long" });
}

// ------------------ LIFECYCLE ------------------
onMounted(async () => {
  await fetchAllCareers();
  await fetchMyApplications();
  await fetchMyRegistrations();
});
</script>

<template>
  <div class="font-poppins">
    <!-- Layout wrapper -->
    <div class="relative font-poppins min-h-screen flex">
      <!-- MAIN CONTENT -->
      <main class="flex-1 bg-white m-3 px-4 rounded-lg flex flex-col min-h-0 overflow-hidden">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
          <h2 class="text-lg font-bold">Career-Training</h2>
          <h2 class="text-2xl font-bold mb-2">Matching Engine</h2>

          <div class="mt-4 mb-4">
            <label for="career-select" class="block text-sm font-medium text-gray-700 mb-2">
              Select Your Target Career
            </label>
            <select id="career-select" v-model="selectedCareerId" @change="fetchRecommendedCareers"
              class="block w-full px-4 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md bg-gray-100">
              <option :value="null" disabled>Select a target career</option>
              <option v-for="career in allCareers" :key="career.careerID" :value="career.careerID">
                {{ career.position }}
              </option>
            </select>
          </div>
        </div>

        <!-- Scrollable Posts -->
        <div class="flex-1 overflow-y-auto space-y-4 pb-4 pt-4">
          <div v-if="posts.length === 0 && selectedCareerId" class="text-center text-gray-500 py-8">
            No recommended careers found.
          </div>
          <div v-else-if="posts.length === 0" class="text-center text-gray-500 py-8">
            Please select a target career to see recommendations.
          </div>
          <div v-for="post in posts" :key="post.careerID"
            class="p-4 bg-blue-gray rounded-lg relative cursor-pointer hover:bg-gray-300 transition" :class="{
              'ring-2 ring-blue-500': post.careerID === selectedCareerId,
            }" @click="openCareerModal(post)">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="font-semibold text-lg">{{ post.position }}</h3>
                <p class="text-gray-600 text-sm">
                  {{ post.organization || "Unknown Organization" }}
                </p>
              </div>
              <!-- ✅ Show indicator for target career -->
              <span v-if="post.careerID === selectedCareerId"
                class="ml-2 px-2 py-1 text-xs bg-blue-500 text-white rounded-full">
                Target
              </span>
            </div>
          </div>
        </div>
      </main>
    </div>

    <CalendarSidebar :isOpen="calendarOpen" @open="calendarOpen = true" @close="calendarOpen = false"
      @eventClick="openModalCalendar" />

    <!-- 🟦 Training Modal -->
    <dialog v-if="selectedTraining" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeTrainingModal">
          ✕
        </button>

        <!-- Training Details -->
        <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
        <p class="text-sm text-gray-600 mb-2">
          Organization: {{ selectedTraining.organization }}
        </p>

        <!-- Buttons -->
        <div class="my-4 flex justify-end gap-2">
          <!-- Bookmark -->
          <button class="btn btn-outline btn-sm" @click="toggleBookmark(selectedTraining)">
            {{
              bookmarkedPosts[selectedTraining.trainingID]
                ? "Bookmarked"
                : "Bookmark"
            }}
          </button>

          <!-- Events -->
          <div class="bg-white w-full max-w-[250px] h-72 overflow-y-auto">
            <h1 class="text-lg font-semibold">Upcoming Events</h1>
            <h2 class="mb-2">
              on <span>{{ selectedDate || "Select a date" }}</span>
            </h2>

            <div v-if="dayEvents.length === 0" class="text-gray-700">
              No events scheduled
            </div>

            <div v-else class="flex flex-col gap-2">
              <div v-for="(event, i) in dayEvents" :key="i" @click="openModal(event)"
                class="bg-gray-100 p-2 rounded-lg shadow-sm cursor-pointer hover:bg-gray-200 break-words flex justify-between items-center">
                <div>
                  <h3 class="font-semibold text-sm">
                    {{ isTraining(event) ? event.title : event.position }}
                  </h3>
                  <p class="text-xs text-gray-600">
                    {{ event.organization || "Unknown" }}
                  </p>
                </div>

                <!-- Badge -->
                <span v-if="isRegisteredOrApplied(event)" class="text-[10px] text-white px-2 py-1 rounded-full"
                  :class="isTraining(event) ? 'bg-blue-500' : 'bg-green-500'">
                  {{ isTraining(event) ? "Registered" : "Applied" }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <p><strong>Mode:</strong> {{ selectedTraining.Mode }}</p>
        <!-- Description -->
        <p><strong>Description: </strong>{{ selectedTraining.description }}</p>

        <button v-if="!isSidebarOpen"
          class="fixed bottom-6 right-6 bg-dark-slate text-white p-3 rounded-full shadow-lg z-50"
          @click="isSidebarOpen = true">
          <!-- Calendar SVG -->
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M16.75 3.56V2C16.75 1.59 16.41 1.25 16 1.25C15.59 1.25 15.25 1.59 15.25 2V3.5H8.74999V2C8.74999 1.59 8.40999 1.25 7.99999 1.25C7.58999 1.25 7.24999 1.59 7.24999 2V3.56C4.54999 3.81 3.23999 5.42 3.03999 7.81C3.01999 8.1 3.25999 8.34 3.53999 8.34H20.46C20.75 8.34 20.99 8.09 20.96 7.81C20.76 5.42 19.45 3.81 16.75 3.56Z"
              fill="white" />
            <path
              d="M20 9.84003H4C3.45 9.84003 3 10.29 3 10.84V17C3 20 4.5 22 8 22H16C19.5 22 21 20 21 17V10.84C21 10.29 20.55 9.84003 20 9.84003ZM9.21 18.21C9.16 18.25 9.11 18.3 9.06 18.33C9 18.37 8.94 18.4 8.88 18.42C8.82 18.45 8.76 18.47 8.7 18.48C8.63 18.49 8.57 18.5 8.5 18.5C8.37 18.5 8.24 18.47 8.12 18.42C7.99 18.37 7.89 18.3 7.79 18.21C7.61 18.02 7.5 17.76 7.5 17.5C7.5 17.24 7.61 16.98 7.79 16.79C7.89 16.7 7.99 16.63 8.12 16.58C8.3 16.5 8.5 16.48 8.7 16.52C8.76 16.53 8.82 16.55 8.88 16.58C8.94 16.6 9 16.63 9.06 16.67C9.11 16.71 9.16 16.75 9.21 16.79C9.39 16.98 9.5 17.24 9.5 17.5C9.5 17.76 9.39 18.02 9.21 18.21ZM9.21 14.71C9.02 14.89 8.76 15 8.5 15C8.24 15 7.98 14.89 7.79 14.71C7.61 14.52 7.5 14.26 7.5 14C7.5 13.74 7.61 13.48 7.79 13.29C8.07 13.01 8.51 12.92 8.88 13.08C9.01 13.13 9.12 13.2 9.21 13.29C9.39 13.48 9.5 13.74 9.5 14C9.5 14.26 9.39 14.52 9.21 14.71ZM12.71 18.21C12.52 18.39 12.26 18.5 12 18.5C11.74 18.5 11.48 18.39 11.29 18.21C11.11 18.02 11 17.76 11 17.5C11 17.24 11.11 16.98 11.29 16.79C11.66 16.42 12.34 16.42 12.71 16.79C12.89 16.98 13 17.24 13 17.5C13 17.76 12.89 18.02 12.71 18.21ZM12.71 14.71C12.66 14.75 12.61 14.79 12.56 14.83C12.5 14.87 12.44 14.9 12.38 14.92C12.32 14.95 12.26 14.97 12.2 14.98C12.13 14.99 12.07 15 12 15C11.74 15 11.48 14.89 11.29 14.71C11.11 14.52 11 14.26 11 14C11 13.74 11.11 13.48 11.29 13.29C11.38 13.2 11.49 13.13 11.62 13.08C11.99 12.92 12.43 13.01 12.71 13.29C12.89 13.48 13 13.74 13 14C13 14.26 12.89 14.52 12.71 14.71ZM16.21 18.21C16.02 18.39 15.76 18.5 15.5 18.5C15.24 18.5 14.98 18.39 14.79 18.21C14.61 18.02 14.5 17.76 14.5 17.5C14.5 17.24 14.61 16.98 14.79 16.79C15.16 16.42 15.84 16.42 16.21 16.79C16.39 16.98 16.5 17.24 16.5 17.5C16.5 17.76 16.39 18.02 16.21 18.21ZM16.21 14.71C16.16 14.75 16.11 14.79 16.06 14.83C16 14.87 15.94 14.9 15.88 14.92C15.82 14.95 15.76 14.97 15.7 14.98C15.63 14.99 15.56 15 15.5 15C15.24 15 14.98 14.89 14.79 14.71C14.61 14.52 14.5 14.26 14.5 14C14.5 13.74 14.61 13.48 14.79 13.29C14.89 13.2 14.99 13.13 15.12 13.08C15.3 13 15.5 12.98 15.7 13.02C15.76 13.03 15.82 13.05 15.88 13.08C15.94 13.1 16 13.13 16.06 13.17C16.11 13.21 16.16 13.25 16.21 13.29C16.39 13.48 16.5 13.74 16.5 14C16.5 14.26 16.39 14.52 16.21 14.71Z"
              fill="white" />
          </svg>
        </button>
      </div>
    </dialog>

    <!-- Career Details Modal -->
    <dialog v-if="showCareerPopup && selectedCareerDetails" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins bg-gray-800 text-white">
        <!-- Close button -->
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2 text-white"
          @click="closeCareerModal">
          ✕
        </button>

        <!-- Career Details -->
        <div>
          <h2 class="text-xl font-bold mb-2">
            {{ selectedCareerDetails.position }}
          </h2>
          <p class="text-sm text-gray-400 mb-2">
            Organization: {{ selectedCareerDetails.organization }}
          </p>

          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button class="btn btn-outline btn-sm border-white text-white hover:bg-white hover:text-gray-800"
              @click="toggleCareerBookmark(selectedCareerDetails)">
              {{
                bookmarkedPosts[selectedCareerDetails.careerID]
                  ? "BOOKMARKED"
                  : "BOOKMARK"
              }}
            </button>

            <!-- Apply / Cancel -->
            <button v-if="!myApplications.has(selectedCareerDetails.careerID)"
              class="btn btn-sm bg-blue-600 text-white hover:bg-blue-700"
              @click="openApplyModal(selectedCareerDetails)">
              APPLY
            </button>
            <button v-else class="btn btn-sm bg-gray-500 text-white" @click="cancelApplication(selectedCareerDetails)">
              Cancel Application
            </button>
          </div>

          <!-- Career Details -->
          <div class="space-y-2 text-sm">
            <p>
              <strong>Details:</strong>
              {{ selectedCareerDetails.detailsAndInstructions }}
            </p>
            <p>
              <strong>Qualifications:</strong>
              {{ selectedCareerDetails.qualifications }}
            </p>
            <p>
              <strong>Requirements:</strong>
              {{ selectedCareerDetails.requirements }}
            </p>
            <p>
              <strong>Application Address:</strong>
              {{ selectedCareerDetails.applicationLetterAddress }}
            </p>
            <p>
              <strong>Deadline:</strong>
              {{ formatDate(selectedCareerDetails.deadlineOfSubmission) }}
            </p>
          </div>

          <!-- Recommended Trainings -->
          <div class="mt-6">
            <h3 class="text-base font-semibold mb-3">Recommended Trainings</h3>
            <div v-if="recommendedTrainings.length === 0" class="text-gray-400 text-sm">
              No recommended trainings available.
            </div>
            <div v-else class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory" style="scrollbar-width: thin">
              <div v-for="training in recommendedTrainings" :key="training.trainingID"
                class="snap-start w-[180px] flex-shrink-0 p-3 bg-white text-gray-800 rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
                @click.stop="openTrainingModal(training)">
                <h4 class="font-semibold text-sm leading-snug mb-1">
                  {{ training.title }}
                </h4>
                <p class="text-[11px] text-gray-600 truncate">
                  {{
                    training.organizationName ||
                    training.provider ||
                    training.name ||
                    "Unknown"
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </dialog>

    <!-- Training Details Modal -->
    <dialog v-if="showTrainingModal && selectedTraining" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins bg-gray-800 text-white">
        <!-- Close button -->
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2 text-white"
          @click="closeTrainingModal">
          ✕
        </button>

        <!-- Training Details -->
        <div>
          <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
          <p class="text-sm text-gray-400 mb-2">
            Organization:
            {{
              selectedTraining.organizationName ||
              selectedTraining.provider ||
              selectedTraining.name ||
              "Unknown"
            }}
          </p>

          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm border-white text-white hover:bg-white hover:text-gray-800 flex items-center gap-2"
              @click="toggleTrainingBookmark(selectedTraining)">
              <svg v-if="!bookmarkedPosts[selectedTraining.trainingID]" width="20" height="20" viewBox="0 0 24 24"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12.89 5.87988H5.10999C3.39999 5.87988 2 7.27987 2 8.98987V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.23999 19.5199C8.65999 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.95 22.4199 15.99 21.7999 15.99 20.3499V8.98987C16 7.27987 14.6 5.87988 12.89 5.87988Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12.89 5.87988H5.11C3.4 5.87988 2 7.27988 2 8.98988V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.24 19.5199C8.66 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.96 22.4099 16 21.7999 16 20.3499V8.98988C16 7.27988 14.6 5.87988 12.89 5.87988Z"
                  fill="currentColor" />
              </svg>
            </button>
            <!-- Register -->
            <button v-if="!myRegistrations.has(selectedTraining.trainingID)"
              class="btn btn-sm bg-blue-600 text-white hover:bg-blue-700"
              @click="registerForTraining(selectedTraining)">
              REGISTER
            </button>
            <button v-else class="btn btn-sm bg-gray-500 text-white" @click="unregisterFromTraining(selectedTraining)">
              Unregister
            </button>
          </div>

          <!-- Divider -->
          <div class="divider my-4"></div>

          <!-- Training Details -->
          <div class="space-y-2 text-sm">
            <p>
              <strong>Mode:</strong>
              {{ selectedTraining.mode || "Not specified" }}
            </p>
            <p>
              <strong>Schedule:</strong>
              {{ formatDateTime(selectedTraining.schedule) }}
            </p>
            <p v-if="selectedTraining.location">
              <strong>Location:</strong> {{ selectedTraining.location }}
            </p>
            <p v-if="selectedTraining.trainingLink">
              <strong>Training Link:</strong>
              {{ selectedTraining.trainingLink }}
            </p>
            <p>
              <strong>Description:</strong> {{ selectedTraining.description }}
            </p>
          </div>
        </div>
      </div>
    </dialog>

    <!-- Apply Modal -->
    <dialog v-if="applyModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins">
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeApplyModal">
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4">
          Apply for {{ selectedPost?.position }}
        </h2>

        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Upload PDF Requirements</label>
            <input type="file" accept="application/pdf" @change="handleFileUpload" required
              class="file-input file-input-bordered w-full" />
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-outline btn-sm" @click="closeApplyModal">
              Cancel
            </button>
            <button type="submit" class="btn bg-customButton hover:bg-dark-slate text-white btn-sm">
              Submit
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <!-- Toast Notifications -->
    <div class="toast toast-end toast-top z-50">
      <div v-for="toast in toasts" :key="toast.id" class="alert" :class="{
        'alert-info': toast.type === 'info',
        'alert-success': toast.type === 'success',
        'alert-error': toast.type === 'error',
        'alert-warning': toast.type === 'accent',
      }">
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<style>
.event-day {
  background-color: #fde68a;
  border-radius: 9999px;
  font-weight: bold;
}
.selected-day {
  background-color: #3b82f6;
  color: white;
  border-radius: 9999px;
}

.today {
  border: 2px solid #4caf50;
  background-color: #e8f5e9;
  border-radius: 50%;
}
</style>
