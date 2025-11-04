<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue";

const registeredPosts = reactive({}); // stores registered trainings
const myRegistrations = ref(new Set()); // used for QR display

// 🔹 Fetch user's registered trainings
async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL+ "/registrations", {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Clear previous data
    Object.keys(registeredPosts).forEach((k) => delete registeredPosts[k]);
    myRegistrations.value.clear();

    // Fill both structures
    res.data.forEach((r) => {
      registeredPosts[r.trainingID] = {
        registrationID: r.registrationID,
      };
      myRegistrations.value.add(r.trainingID); // ✅ for QR logic
    });

    console.log("✅ Registered trainings loaded:", registeredPosts);
    console.log("✅ myRegistrations set:", Array.from(myRegistrations.value));
  } catch (err) {
    console.error("❌ Failed to fetch registrations:", err);
  }
}

onMounted(fetchMyRegistrations);

// Date of PH
const PH_TIME_OFFSET = 8 * 60; // +8 hours in minutes

// Organizations
const organizations = ref([]);
const isSidebarOpen = ref(false);
// Trainings data
const trainings = ref([]);
const qrCountdowns = reactive({});
let qrIntervals = {};
const qrCodeValue = ref(null);
const selectedTraining = ref(null);
const isModalOpen = ref(false);
const toasts = ref([]);
const selectedPost = ref(null);

// Bookmarked trainings (IDs)
const bookmarkedTrainings = ref([]);

// Computed trainings with org info
const trainingsWithOrg = computed(() =>
  trainings.value.map((t) => {
    const org = organizations.value.find(
      (o) => o.organizationID === t.organizationID
    ); // <-- fix here
    return {
      ...t,
      organizationName: org ? org.name : "Unknown",
      displaySchedule: t.schedule || "TBD",
    };
  })
);

// ============================
// 📌 Modal Controls
// ============================
function openTrainingModal(training) {
  // Format schedule nicely
  const formattedSchedule = training.schedule
    ? new Date(training.schedule).toLocaleString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      })
    : "N/A";

  // Build a clean, safe version of the training data
  selectedTraining.value = {
    ...training,
    organizationName:
      training.organizationName || training.organization?.name || "N/A",
    mode: training.mode || training.Mode || "N/A",
    location: training.location || "N/A",
    trainingLink: training.trainingLink || null,
    formattedSchedule,
  };

  // Show the modal
  selectedTraining.value = training;
  showModal.value = true;
  isModalOpen.value = true;

  console.log("✅ Opening training modal:", selectedTraining.value);
  console.log("Attendance key:", training.attendance_key);
  console.log("Expires at:", training.attendance_expires_at);

  // Start QR countdown if registered
  if (myRegistrations.has(training.trainingID)) {
    startModalQRCountdown(training);
  }

  if (!myRegistrations.has(training.trainingID) && training.registered) {
    myRegistrations.value.add(training.trainingID);
  }

  selectedTraining.value = training;
}

function closeModal() {
  isModalOpen.value = false;
  selectedTraining.value = null;
  selectedPost.value = null;
}

// ============================
// 🔖 Bookmark Functions
// ============================

// Fetch user's bookmarks
async function fetchBookmarks() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const { data } = await axios.get(import.meta.env.VITE_API_BASE_URL + "/bookmarks", {
      headers: { Authorization: `Bearer ${token}` },
    });
    bookmarkedTrainings.value = data;
  } catch (error) {
    console.error("Error fetching bookmarks:", error);
  }
}

async function fetchOrganizations() {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL+"/organization");
    organizations.value = res.data;
    console.log("✅ Organizations loaded:", organizations.value);
  } catch (error) {
    console.error("❌ Error fetching organizations:", error);
  }
}

// Toggle bookmark
async function toggleBookmark(trainingId) {
  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  // Remove bookmark
  if (isTrainingBookmarked(trainingId)) {
    try {
      await axios.delete(import.meta.env.VITE_API_BASE_URL+`/bookmarks/${trainingId}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedTrainings.value = bookmarkedTrainings.value.filter(
        (id) => id !== trainingId
      );
      addToast("Bookmark removed", "info");
    } catch (error) {
      console.error("Error removing bookmark:", error);
      addToast("Failed to remove bookmark", "error");
    }
  }
  // Add bookmark
  else {
    try {
      // <-- Added console.log here
      console.log("Bookmarking training ID:", trainingId);

      await axios.post(
        import.meta.env.VITE_API_BASE_URL+"/bookmarks",
        { trainingID: trainingId },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedTrainings.value.push(trainingId);
      addToast("Bookmarked!", "success");
    } catch (error) {
      if (error.response?.status === 409)
        addToast("Already bookmarked", "accent");
      else addToast("Failed to bookmark", "error");
      console.error("Failed to toggle bookmark:", error);
    }
  }
}
function isTrainingBookmarked(trainingId) {
  return bookmarkedTrainings.value.includes(trainingId);
}

function isTraining(post) {
  return post && (post.type === "training" || post.trainingID);
}

function closeTrainingModal() {
  selectedTraining.value = null;
}

// ============================
// 🔔 Toast Function
// ============================
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ---------------------------
// Fetch
// ---------------------------
async function fetchTrainings() {
  try {
    const token = getToken();
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings", {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    console.log("Trainings API response:", res.data);
    trainings.value = res.data;
  } catch {
    addToast("Failed to load trainings", "error");
  }
}

async function fetchBookmarks() {
  const token = getToken();
  if (!token) return;
  const { data } = await axios.get(import.meta.env.VITE_API_BASE_URL + "/bookmarks", {
    headers: { Authorization: `Bearer ${token}` },
  });
  bookmarkedTrainings.value = data;
}

async function fetchOrganizations() {
  try {
    const token = getToken();
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/organization", {
      headers: { Authorization: `Bearer ${token}` },
    });
    organizations.value = res.data;
  } catch {}
}

async function fetchMyRegistrations() {
  const token = getToken();
  if (!token) return;
  const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/registrations", {
    headers: { Authorization: `Bearer ${token}` },
  });

  myRegistrations.value.clear();
  res.data.forEach((r) => myRegistrations.value.add(r.trainingID));
}

// ---------------------------
// Bookmark
// ---------------------------
function isTrainingBookmarked(id) {
  return bookmarkedTrainings.value.includes(id);
}

// ---------------------------
// Training Modal
// ---------------------------
function openTrainingListModal(training) {
  selectedTraining.value = training;
}

function closeTrainingListModal() {
  selectedTraining.value = null;
}

// ---------------------------
// Computed
// ---------------------------
// ✅ FIXED — only *one* function
function startAllQRCountdowns() {
  Object.values(qrIntervals).forEach(clearInterval);
  qrIntervals = {};

  trainingsWithOrg.value.forEach((training) => {
    if (!myRegistrations.value.has(training.trainingID) || !training.attendance_expires_at) return;

    const id = training.trainingID;
    const expires = new Date(training.attendance_expires_at);

    const update = () => {
      const diff = expires - new Date();
      if (diff <= 0) {
        qrCountdowns[id] = "00:00";
        clearInterval(qrIntervals[id]);
      } else {
        const m = Math.floor(diff / 60000).toString().padStart(2, "0");
        const s = Math.floor((diff % 60000) / 1000).toString().padStart(2, "0");
        qrCountdowns[id] = `${m}:${s}`;
      }
    };

    qrCountdowns[id] = "loading...";
    update();
    qrIntervals[id] = setInterval(update, 1000);
  });
}

// ---------------------------
// Lifecycle
// ---------------------------
onMounted(async () => {
  await fetchOrganizations();
  await fetchTrainings();
  await fetchBookmarks();
  await fetchMyRegistrations();
});

const calendarOpen = ref(false);

function openModalCalendar(event) {
  // Handle modal opening for training/career
  console.log("Event clicked:", event);
}

import TrainingModal from "@/components/Layout/TrainingModal.vue";

const showModal = ref(false);

// Toggle registration
async function toggleRegister(training) {
  const token = localStorage.getItem("token");
  if (!token) return alert("Please log in first");

  if (myRegistrations.value.has(training.trainingID)) {
    await axios.delete(
      import.meta.env.VITE_API_BASE_URL + `/api/registrations/${training.trainingID}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    myRegistrations.value.delete(training.trainingID);
  } else {
    await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      { trainingID: training.trainingID },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    myRegistrations.value.add(training.trainingID);
  }
}
</script>

<template>
  <main class="font-poppins">
    <!-- Header -->

    <div class="bg-white m-3 p-4 rounded-lg">
      <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
        <h2 class="text-2xl font-bold mb-3 sticky top-0 bg-white z-10">
          Training
        </h2>
      </div>
      <!-- Training Cards -->
      <div class="space-y-4">
        <div
          v-for="training in trainings"
          :key="training.trainingID"
          class="p-4 bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer flex justify-between items-center"
          @click="openTrainingModal(training)"
        >
          <!-- Left: Training info -->
          <div>
            <h3 class="font-semibold text-lg">{{ training.title }}</h3>
            <p class="text-gray-700 font-medium">
              {{ training.organization?.name || training.organizationName }}
            </p>
          </div>

          <!-- Right: QR code if registered -->
          <div v-if="myRegistrations.has(training.trainingID)">
            <div
              v-if="
                training.attendance_key &&
                new Date(training.end_time) > new Date()
              "
            >
              <qrcode-vue :value="training.attendance_key" :size="80" />
            </div>
            <div v-else>
              <p class="text-sm text-gray-500 text-center">
                QR code not yet generated or expired.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <TrainingModal
      :isOpen="showModal"
      :training="selectedTraining"
      :isRegistered="myRegistrations.has(selectedTraining?.trainingID)"
      :isBookmarked="bookmarkedTrainings.includes(selectedTraining?.trainingID)"
      @close="showModal = false"
      @toggle-register="toggleRegister"
      @bookmark="toggleBookmark"
    />
    <!-- Toast Notifications -->
    <div class="toast toast-end toast-top z-50">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="alert"
        :class="{
          'alert-info': toast.type === 'info',
          'alert-success': toast.type === 'success',
          'alert-accent': toast.type === 'accent',
        }"
      >
        {{ toast.message }}
      </div>
    </div>
  </main>
</template>
