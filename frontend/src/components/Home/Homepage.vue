<script setup>
import { ref, onMounted, nextTick, reactive } from "vue";
import axios from "axios";
import "cally";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const calendarOpen = ref(false);

function openModalCalendar(event) {
  // Handle modal opening for training/career
  console.log("Event clicked:", event);
}

const registeredPosts = reactive({}); // stores registered trainings

async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL +"/registrations", {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Fill registeredPosts (for quick lookup)
    res.data.forEach((r) => {
      registeredPosts[r.trainingID] = {
        registrationID: r.registrationID,
      };
    });

    console.log("✅ Registered trainings loaded:", registeredPosts);
  } catch (err) {
    console.error("❌ Failed to fetch registrations:", err);
  }
}

onMounted(fetchMyRegistrations);

// Toggle registration
async function toggleRegister(training) {
  const token = localStorage.getItem("token");
  if (!token) return alert("Please log in first.");

  // If already registered -> unregister
  if (registeredPosts[training.trainingID]) {
    try {
      const registrationID =
        registeredPosts[training.trainingID].registrationID;
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/registrations/${registrationID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      delete registeredPosts[training.trainingID];
      console.log(`🗑 Unregistered from ${training.title}`);
    } catch (err) {
      console.error("❌ Failed to unregister:", err);
    }
  }
  // Else register
  else {
    try {
      const res = await axios.post(
        import.meta.env.VITE_API_BASE_URL +"/registrations",
        { trainingID: training.trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      registeredPosts[training.trainingID] = {
        registrationID: res.data.registrationID,
      };

      console.log(`✅ Registered for ${training.title}`);
    } catch (err) {
      console.error("❌ Failed to register:", err);
    }
  }
}

const appliedPosts = ref({});
const isSidebarOpen = ref(false);
const bookmarkedPosts = ref({});
const registeredPosts = ref({});
const selectedCareerDetails = ref(null);
const recommendedTrainings = ref([]);
const allCareers = ref([]);
const selectedCareerId = ref(null);
const showCareerPopup = ref(false);
const showTrainingModal = ref(false);
const posts = ref([]);
const myApplications = ref(new Set());
const myRegistrations = ref(new Set());
const toasts = ref([]);

// ✅ Toast notifications
function addToast(message, type = 'info') {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  }, 3000);
}

// ✅ Fetch user's applications and registrations
async function fetchMyApplications() {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;
    const res = await axios.get('http://127.0.0.1:8000/api/applications', {
      headers: { Authorization: `Bearer ${token}` },
    });
    myApplications.value = new Set(res.data.map(a => a.careerID));
    // Also update appliedPosts for UI state
    res.data.forEach(a => {
      appliedPosts.value[a.careerID] = true;
    });
  } catch (error) {
    console.error("Error fetching applications:", error);
  }
}

async function fetchMyRegistrations() {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;
    const res = await axios.get('http://127.0.0.1:8000/api/registrations', {
      headers: { Authorization: `Bearer ${token}` },
    });
    myRegistrations.value = new Set(res.data.map(r => r.trainingID));
    // Also update registeredPosts for UI state
    res.data.forEach(r => {
      registeredPosts.value[r.trainingID] = true;
    });
  } catch (error) {
    console.error("Error fetching registrations:", error);
  }
}

// Fetch all careers for dropdown
async function fetchAllCareers() {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/careers');
    if (response.data && Array.isArray(response.data)) {
      allCareers.value = response.data;
    } else {
      allCareers.value = [];
    }
  } catch (error) {
    console.error("Error fetching careers:", error);
    addToast("Failed to fetch careers", "error");
  }
}

// Fetch recommended careers based on selected target career
async function fetchRecommendedCareers() {
  if (!selectedCareerId.value) {
    posts.value = [];
    return;
  }
  try {
    const response = await axios.get(`http://127.0.0.1:8000/api/careers/${selectedCareerId.value}/recommended`);
    if (response.data && Array.isArray(response.data)) {
      posts.value = response.data;
      buildEvents();
    } else {
      posts.value = [];
    }
  } catch (error) {
    console.error("Error fetching recommended careers:", error);
    addToast("Failed to fetch recommended careers", "error");
  }
}

// Open career modal and fetch details + recommended trainings
async function openCareerModal(career) {
  try {
    const careerID = parseInt(career.careerID, 10);
    if (isNaN(careerID)) {
      throw new Error('Invalid career ID');
    }

    const res = await axios.get(`http://127.0.0.1:8000/api/careers/${careerID}/details`);
    
    if (res.data && res.data.career) {
      selectedCareerDetails.value = res.data.career;
      recommendedTrainings.value = res.data.recommended_trainings || [];
      showCareerPopup.value = true;
    } else {
      throw new Error('Invalid response format');
    }
  } catch (error) {
    console.error("Error loading career details:", error);
    addToast("Failed to load career details", "error");
  }
}

// Close career modal
function closeCareerModal() {
  showCareerPopup.value = false;
  selectedCareerDetails.value = null;
  recommendedTrainings.value = [];
}

// ✅ Open training modal
function openTrainingModal(training) {
  selectedTraining.value = training;
  showTrainingModal.value = true;
}

// ✅ Close training modal
function closeTrainingModal() {
  showTrainingModal.value = false;
  selectedTraining.value = null;
}

// ✅ Cancel application
function cancelApplication(career) {
  const id = career.careerID;
  appliedPosts.value[id] = false;
  myApplications.value.delete(id);
}

// ✅ Toggle bookmark for career
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
      await axios.delete(`http://127.0.0.1:8000/api/career-bookmarks/${careerID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedPosts.value[careerID] = false;
      addToast('Bookmark removed', 'info');
    } else {
      await axios.post(
        'http://127.0.0.1:8000/api/career-bookmarks',
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

// ✅ Toggle bookmark for training
async function toggleTrainingBookmark(training) {
  const token = localStorage.getItem('token');
  if (!token) {
    addToast('PLEASE LOG IN FIRST', 'accent');
    return;
  }

  const trainingID = training.trainingID;
  const isBookmarked = bookmarkedPosts.value[trainingID];

  try {
    if (isBookmarked) {
      await axios.delete(`http://127.0.0.1:8000/api/bookmarks/${trainingID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedPosts.value[trainingID] = false;
      addToast('Bookmark removed', 'info');
    } else {
      await axios.post(
        'http://127.0.0.1:8000/api/bookmarks',
        { trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedPosts.value[trainingID] = true;
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

// ✅ Register for training
async function registerForTraining(training) {
  if (!training) return;

  try {
    const token = localStorage.getItem('token');
    if (!token) {
      addToast('PLEASE LOG IN FIRST', 'accent');
      return;
    }

    await axios.post(
      'http://127.0.0.1:8000/api/registrations',
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

// ✅ Unregister from training
async function unregisterFromTraining(training) {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;

    // Find registration ID
    const registrationsRes = await axios.get('http://127.0.0.1:8000/api/registrations', {
      headers: { Authorization: `Bearer ${token}` },
    });
    
    const registration = registrationsRes.data.find(r => r.trainingID === training.trainingID);
    if (registration) {
      await axios.delete(`http://127.0.0.1:8000/api/registrations/${registration.id}`, {
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

const selectedTraining = ref(null);

// Modal
const selectedPost = ref(null);
function openModal(post) {
  console.log("Opening modal for:", post); // ✅ debug line

  if (post.type === "training") {
    selectedTraining.value = post; // 🟦 training modal
    selectedPost.value = null;
  } else if (post.type === "career") {
    selectedPost.value = post; // 🟩 career modal
    selectedTraining.value = null;
  } else {
    console.warn("Unknown post type:", post);
  }
}

function closeModal() {
  selectedPost.value = null;
}

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

// Show events on calendar click
function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}

// Calendar + events
const events = ref({});
const dayEvents = ref([]);

// Apply modal

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

// ✅ Submit application with file upload
async function submitApplication() {
  if (!uploadedFile.value) {
    addToast("Please upload a PDF file first.", "accent");
    return;
  }

  if (!selectedPost.value) {
    addToast("No career selected", "error");
    return;
  }

  try {
    const token = localStorage.getItem('token');
    if (!token) {
      addToast('PLEASE LOG IN FIRST', 'accent');
      return;
    }

    const form = new FormData();
    form.append('careerID', selectedPost.value.careerID);
    form.append('requirements', uploadedFile.value);

    await axios.post('http://127.0.0.1:8000/api/applications', form, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    addToast('APPLICATION SUBMITTED SUCCESSFULLY', 'success');
    const id = selectedPost.value.careerID;
    appliedPosts.value[id] = true;
    myApplications.value.add(id);

    closeApplyModal();
  } catch (error) {
    if (error.response?.status === 409) {
      addToast('YOU ALREADY APPLIED TO THIS CAREER', 'accent');
    } else if (error.response?.status === 401) {
      addToast('UNAUTHORIZED. PLEASE LOG IN AGAIN', 'accent');
    } else if (error.response?.status === 422) {
      addToast('INVALID INPUT. ONLY PDF UP TO 5MB', 'accent');
    } else {
      addToast('FAILED TO SUBMIT APPLICATION', 'accent');
    }
  }
}

// Helpers
const isTraining = (post) => !!post.trainingID;

const isRegisteredOrApplied = (post) => {
  if (isTraining(post)) {
    return myRegistrations.value.has(post.trainingID);
  } else {
    return myApplications.value.has(post.careerID);
  }
};

onMounted(async () => {
  await fetchAllCareers();
  await fetchMyApplications();
  await fetchMyRegistrations();
  await nextTick();

  setTimeout(() => {
    const calendar = calendarRef.value;
    if (!calendar) {
      console.warn('Calendar ref is not available');
      return;
    }

    const today = new Date().toISOString().split("T")[0];

    calendar.addEventListener("render", () => {
      calendar.querySelectorAll("[data-date]").forEach((el) => {
        const dateStr = el.getAttribute("data-date");
        el.classList.remove("event-day", "today");

        if (events.value[dateStr]) el.classList.add("event-day");
        if (dateStr === today) el.classList.add("today");
      });
    });

    calendar.addEventListener("change", (e) => {
      const pickedDate = e.target.value;
      showEvents(pickedDate);

      calendar
        .querySelectorAll("[data-date]")
        .forEach((el) => el.classList.remove("selected-day"));
      const selectedEl = calendar.querySelector(`[data-date="${pickedDate}"]`);
      if (selectedEl) selectedEl.classList.add("selected-day");
    });

    calendar.value = today;
    showEvents(today);

    const event = new Event("change", { bubbles: true });
    calendar.dispatchEvent(event);
  }, 100);
});

function formatDateTime(dt) {
  if (!dt) return "";
  return new Date(dt).toLocaleString("en-US", {
    dateStyle: "long",
    timeStyle: "short",
  });
}

function formatDate(d) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-US", {
    dateStyle: "long",
  });
}

const uploadedFile = ref(null);
const applyModalOpen = ref(false);
function openApplyModal(post) {
  selectedPost.value = post;
  applyModalOpen.value = true;
}

function closeApplyModal() {
  applyModalOpen.value = false;
  uploadedFile.value = null;
}

function handleFileUpload(e) {
  uploadedFile.value = e.target.files[0];
}

function submitApplication() {
  if (!uploadedFile.value) {
    alert("Please upload a PDF file first.");
    return;
  }

  // ✅ Safety check: make sure a post is selected
  if (!selectedPost.value) return;

  // Log info (for debugging)
  console.log("Submitting application for:", selectedPost.value);
  console.log("Uploaded file:", uploadedFile.value);

  const id = selectedPost.value.careerID;
  appliedPosts.value[id] = true;

  alert("Application submitted successfully!");
  closeApplyModal();
}
// --- CALENDAR LOGIC ---
const calendarRef = ref(null);
const selectedDate = ref("");


// --- Handle day click ---
function onDayClick(date) {
  selectedDate.value = toISODate(date);
  showEvents(selectedDate.value);
}

// --- Initialize calendar: fetch events & select today ---
onMounted(async () => {
  await fetchEvents();
  onDayClick(new Date()); // select today automatically
});
// --- Check if a date is today ---
function isToday(date) {
  const d = new Date(date);
  const now = new Date();
  return (
    d.getFullYear() === now.getFullYear() &&
    d.getMonth() === now.getMonth() &&
    d.getDate() === now.getDate()
  );
}
// --- EVENTS LOGIC ---

async function fetchEvents() {
  try {
    const user = JSON.parse(localStorage.getItem("user"));
    const token = localStorage.getItem("token");

    if (!user || !token) {
      console.warn("⚠️ No user or token found in localStorage");
      return;
    }

    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL +`/calendar/${user.applicantID}`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const eventList = response.data.events || [];
    console.log("✅ API Events Fetched:", eventList);

    // Normalize all dates (ensure YYYY-MM-DD)
    eventList.forEach((e) => {
      e.date = new Date(e.date).toISOString().split("T")[0];
    });

    // Build events map
    events.value = {};
    eventList.forEach((event) => {
      if (!events.value[event.date]) events.value[event.date] = [];
      events.value[event.date].push(event);
    });

    console.log("📅 Events Map:", events.value);
  } catch (error) {
    console.error("❌ Error fetching events:", error);
  }
}

function showEvents(date) {
  selectedDate.value = date;
  dayEvents.value = events.value[date] || [];
  console.log("📅 Events for", date, ":", dayEvents.value);
}

// --- INITIALIZE CALENDAR ---
onMounted(async () => {
  await nextTick();
  await fetchEvents();

  const calendar = calendarRef.value;
  if (!calendar) return;

  const today = new Date().toISOString().split("T")[0];
  showEvents(today);

  // Highlight event days and today on render
  const highlightDays = () => {
    calendar.querySelectorAll("[data-date]").forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("event-day", "today");

      if (events.value[dateStr]) {
        el.classList.add("event-day");
      }

      if (dateStr === today) {
        el.classList.add("today"); // <-- Highlight today
      }
    });
  };

  const highlightToday = () => {
    // Select all day elements
    const dayEls = calendar.querySelectorAll("[data-date]");
    if (!dayEls.length) return;

    dayEls.forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("today");

      if (dateStr === today) {
        el.classList.add("today"); // Add highlight to today
      }
    });
  };

  // Initial highlight
  highlightDays();

  // Optional: re-highlight on calendar render if your calendar library triggers it
  calendar.addEventListener("render", highlightDays);

  // Handle day clicks
  calendar.addEventListener("change", (e) => {
    const pickedDate = e.target.value;
    showEvents(pickedDate);
  });
});

// Format for comparisons / mapping
function toISODate(d) {
  if (!d) return "";
  return new Date(d).toISOString().split("T")[0]; // YYYY-MM-DD
}
</script>

<template>
  <div class="font-poppins">
    <!-- Layout wrapper -->
    <div class="relative font-poppins min-h-screen flex">
      <!-- MAIN CONTENT -->
      <main class="flex-1 bg-white m-3 px-4 rounded-lg flex flex-col min-h-0 overflow-hidden">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
          <h2 class="text-lg font-bold">Career</h2>
          <h2 class="text-2xl font-bold mb-2">Match Recommendation</h2>

          <div class="mt-4 mb-4">
            <h3 class="text-xl font-bold mb-2">Career-Training Matching Engine</h3>
            <label for="career-select" class="block text-sm font-medium text-gray-700 mb-2">
              Select Your Target Career
            </label>
            <select 
              id="career-select" 
              v-model="selectedCareerId" 
              @change="fetchRecommendedCareers" 
              class="block w-full px-4 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md bg-gray-100"
            >
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
          <div
            v-for="post in posts"
            :key="post.careerID"
            class="p-4 bg-blue-gray rounded-lg relative cursor-pointer hover:bg-gray-300 transition"
            :class="{ 'ring-2 ring-blue-500': post.careerID === selectedCareerId }" 
            @click="openCareerModal(post)"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="font-semibold text-lg">{{ post.position }}</h3>
                <p class="text-gray-600 text-sm">{{ post.organization || 'Unknown Organization' }}</p>
              </div>
              <!-- ✅ Show indicator for target career -->
              <span 
                v-if="post.careerID === selectedCareerId" 
                class="ml-2 px-2 py-1 text-xs bg-blue-500 text-white rounded-full"
              >
                Target
              </span>
            </div>
          </div>
        </div>
      </main>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <!-- 🟦 Training Modal -->
    <dialog v-if="selectedTraining" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeTrainingModal"
        >
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
          <button
            class="btn btn-outline btn-sm"
            @click="toggleBookmark(selectedTraining)"
          >
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
              <div
                v-for="(event, i) in dayEvents"
                :key="i"
                @click="openModal(event)"
                class="bg-gray-100 p-2 rounded-lg shadow-sm cursor-pointer hover:bg-gray-200 break-words flex justify-between items-center"
              >
                <div>
                  <h3 class="font-semibold text-sm">
                    {{ isTraining(event) ? event.title : event.position }}
                  </h3>
                  <p class="text-xs text-gray-600">
                    {{ event.organization || 'Unknown' }}
                  </p>
                </div>

                <!-- Badge -->
                <span
                  v-if="isRegisteredOrApplied(event)"
                  class="text-[10px] text-white px-2 py-1 rounded-full"
                  :class="isTraining(event) ? 'bg-blue-500' : 'bg-green-500'"
                >
                  {{ isTraining(event) ? "Registered" : "Applied" }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <p><strong>Mode:</strong> {{ selectedTraining.Mode }}</p>
        <!-- Description -->
        <p><strong>Description: </strong>{{ selectedTraining.description }}</p>

        <!-- Conditional display: Online or On-site -->
        <p v-if="selectedTraining.Mode.toLowerCase() === 'online'">
          <strong>Link:</strong>
          <a
            :href="selectedTraining.trainingLink"
            target="_blank"
            class="text-blue-500 underline"
          >
            {{ selectedTraining.trainingLink }}
          </a>
        </p>
        <p v-else-if="selectedTraining.Mode.toLowerCase() === 'on-site'">
          <strong>Location:</strong> {{ selectedTraining.location }}
        </p>

        <p>
          <strong>Schedule:</strong>
          {{ formatDate(selectedTraining.date) }} at {{ selectedTraining.time }}
        </p>
      </div>
    </dialog>

    <!-- 🟩 Career Modal -->
    <dialog v-if="selectedPost" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2 text-white"
          @click="closeCareerModal"
        >
          ✕
        </button>

        <!-- Career Details -->
        <div>
          <h2 class="text-xl font-bold mb-2">{{ selectedCareerDetails.position }}</h2>
          <p class="text-sm text-gray-400 mb-2">
            Organization: {{ selectedCareerDetails.organizationName }}
          </p>

          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm border-white text-white hover:bg-white hover:text-gray-800"
              @click="toggleCareerBookmark(selectedCareerDetails)"
            >
              {{ bookmarkedPosts[selectedCareerDetails.careerID] ? "BOOKMARKED" : "BOOKMARK" }}
            </button>
            <!-- Apply -->
            <button
              v-if="!myApplications.has(selectedCareerDetails.careerID)"
              class="btn btn-sm bg-blue-600 text-white hover:bg-blue-700"
              @click="openApplyModal(selectedCareerDetails)"
            >
              APPLY
            </button>
            <button
              v-else
              class="btn btn-sm bg-gray-500 text-white"
              @click="cancelApplication(selectedCareerDetails)"
            >
              Cancel Application
            </button>
          </div>

          <!-- Career Details -->
          <div class="space-y-2 text-sm">
            <p><strong>Details:</strong> {{ selectedCareerDetails.detailsAndInstructions }}</p>
            <p><strong>Qualifications:</strong> {{ selectedCareerDetails.qualifications }}</p>
            <p><strong>Requirements:</strong> {{ selectedCareerDetails.requirements }}</p>
            <p><strong>Application Address:</strong> {{ selectedCareerDetails.applicationLetterAddress }}</p>
            <p><strong>Deadline:</strong> {{ formatDate(selectedCareerDetails.deadlineOfSubmission) }}</p>
          </div>

          <!-- Recommended Trainings -->
          <div class="mt-6">
            <h3 class="text-base font-semibold mb-3">Recommended Trainings</h3>
            <div v-if="recommendedTrainings.length === 0" class="text-gray-400 text-sm">
              No recommended trainings available.
            </div>
            <div
              v-else
              class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
              style="scrollbar-width: thin"
            >
              <div
                v-for="training in recommendedTrainings"
                :key="training.trainingID"
                class="snap-start w-[180px] flex-shrink-0 p-3 bg-white text-gray-800 rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
                @click.stop="openTrainingModal(training)"
              >
                <h4 class="font-semibold text-sm leading-snug mb-1">{{ training.title }}</h4>
                <p class="text-[11px] text-gray-600 truncate">
                  {{ training.organizationName || training.provider || training.name || 'Unknown' }}
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
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2 text-white"
          @click="closeTrainingModal"
        >
          ✕
        </button>

        <!-- Training Details -->
        <div>
          <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
          <p class="text-sm text-gray-400 mb-2">
            Organization: {{ selectedTraining.organizationName || selectedTraining.provider || selectedTraining.name || 'Unknown' }}
          </p>

          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm border-white text-white hover:bg-white hover:text-gray-800 flex items-center gap-2"
              @click="toggleTrainingBookmark(selectedTraining)"
            >
              <svg
                v-if="!bookmarkedPosts[selectedTraining.trainingID]"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M12.89 5.87988H5.10999C3.39999 5.87988 2 7.27987 2 8.98987V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.23999 19.5199C8.65999 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.95 22.4199 15.99 21.7999 15.99 20.3499V8.98987C16 7.27987 14.6 5.87988 12.89 5.87988Z"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <svg
                v-else
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M12.89 5.87988H5.11C3.4 5.87988 2 7.27988 2 8.98988V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.24 19.5199C8.66 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.96 22.4099 16 21.7999 16 20.3499V8.98988C16 7.27988 14.6 5.87988 12.89 5.87988Z"
                  fill="currentColor"
                />
              </svg>
            </button>
            <!-- Register -->
            <button
              v-if="!myRegistrations.has(selectedTraining.trainingID)"
              class="btn btn-sm bg-blue-600 text-white hover:bg-blue-700"
              @click="registerForTraining(selectedTraining)"
            >
              REGISTER
            </button>
            <button
              v-else
              class="btn btn-sm bg-gray-500 text-white"
              @click="unregisterFromTraining(selectedTraining)"
            >
              Unregister
            </button>
          </div>

          <!-- Divider -->
          <div class="divider my-4"></div>

          <!-- Training Details -->
          <div class="space-y-2 text-sm">
            <p><strong>Mode:</strong> {{ selectedTraining.mode || "Not specified" }}</p>
            <p><strong>Schedule:</strong> {{ formatDateTime(selectedTraining.schedule) }}</p>
            <p v-if="selectedTraining.location"><strong>Location:</strong> {{ selectedTraining.location }}</p>
            <p v-if="selectedTraining.trainingLink"><strong>Training Link:</strong> {{ selectedTraining.trainingLink }}</p>
            <p><strong>Description:</strong> {{ selectedTraining.description }}</p>
          </div>
        </div>
      </div>
    </dialog>

    <!-- Apply Modal -->
    <dialog v-if="applyModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins">
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeApplyModal"
        >
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4">
          Apply for {{ selectedPost?.position }}
        </h2>

        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Upload PDF Requirements</label>
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

    <!-- Toast Notifications -->
    <div class="toast toast-end toast-top z-50">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="alert"
        :class="{
          'alert-info': toast.type === 'info',
          'alert-success': toast.type === 'success',
          'alert-error': toast.type === 'error',
          'alert-warning': toast.type === 'accent',
        }"
      >
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
