<script setup>
import { ref, onMounted, computed, onActivated, reactive } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue";
import { watch, nextTick } from "vue";

const registeredPosts = reactive({}); // stores registered trainings
const myRegistrations = ref(new Set()); // used for QR display

// 🔹 Fetch user's registered trainings
async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/registrations", {
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

// Modal + notifications
const selectedTraining = ref(null);
const isTrainingModalOpen = ref(false);

const isModalOpen = ref(false);
const toasts = ref([]);

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

    const { data } = await axios.get("http://127.0.0.1:8000/api/bookmarks", {
      headers: { Authorization: `Bearer ${token}` },
    });
    bookmarkedTrainings.value = data;
  } catch (error) {
    console.error("Error fetching bookmarks:", error);
  }
}

async function fetchOrganizations() {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/organization");
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
      await axios.delete(`http://127.0.0.1:8000/api/bookmarks/${trainingId}`, {
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
        "http://127.0.0.1:8000/api/bookmarks",
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
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
  }, 3000);
}

// ============================
// 📡 API Fetch Functions
// ============================
async function fetchTrainings() {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/trainings");
    trainings.value = response.data;
  } catch (error) {
    console.error("Error fetching trainings:", error);
    addToast("FAILED TO LOAD TRAININGS", "error");
  }
}

// Check if QR should be visible
function isTrainingActive(training) {
  if (!training.schedule || !training.end_time) return false;

  const now = new Date();
  const start = new Date(training.schedule);
  const end = new Date(training.end_time);

  return now >= start && now <= end;
}

const registeredTrainingsWithQR = computed(() => {
  return trainingsWithOrg.value
    .filter((t) => myRegistrations.value.has(t.trainingID))
    .map((t) => {
      const isActiveQR = qrActiveTrainingId.value === t.trainingID;
      return {
        ...t,
        qrKey: t.attendance_key,
        qrExpires: t.end_Time,
        isActiveQR,
        qrCountdown: isActiveQR ? qrCountdown.value : null,
      };
    });
});

//QR Countdown
function startQRCountdown(training) {
  // Stop any running countdown interval
  if (qrCountdownInterval) clearInterval(qrCountdownInterval);

  // 📌 Use the training.end_time as expiration reference
  const endTime = new Date(training.end_time || training.end_Time); // supports both snakeCase or camelCase

  // Generate QR (you can change this to your full URL if needed)
  qrCodeValue.value = `http://192.168.1.247:8000/attendance/submit?trainingID=${training.trainingID}&key=${training.attendance_key}`;
  qrExpiresAt.value = endTime;
  qrActiveTrainingId.value = training.trainingID;

  // ⏳ Countdown function
  const updateCountdown = () => {
    const now = new Date();
    const diff = endTime - now;

    if (diff <= 0) {
      // ⛔ QR expired
      clearInterval(qrCountdownInterval);
      qrCountdown.value = "00:00";
      qrCodeValue.value = "expired";
      qrActiveTrainingId.value = null;
      qrExpiresAt.value = null;
    } else {
      const minutes = Math.floor(diff / 60000)
        .toString()
        .padStart(2, "0");
      const seconds = Math.floor((diff % 60000) / 1000)
        .toString()
        .padStart(2, "0");
      qrCountdown.value = `${minutes}:${seconds}`;
    }
  };

  // Start immediately and every second
  updateCountdown();
  qrCountdownInterval = setInterval(updateCountdown, 1000);
}

// Reactive object to hold countdowns for all trainings

// Start countdowns for all registered trainings with QR
function startAllQRCountdowns() {
  Object.values(qrIntervals).forEach(clearInterval);
  qrIntervals = {};

  //start all countdowns for existing QR codes
  function startAllQRCountdowns() {
    // Clear any previous intervals (This assumes qrIntervals is defined, see fix 1)
    Object.values(qrIntervals).forEach(clearInterval);
    qrIntervals = {};

    trainingsWithOrg.value.forEach((training) => {
      if (
        myRegistrations.value.has(training.trainingID) &&
        training.attendance_expires_at
      ) {
        const trainingId = training.trainingID;

        const updateCountdown = () => {
          const now = new Date();
          const expires = new Date(training.attendance_expires_at);
          const diff = expires - now;

          if (diff <= 0) {
            qrCountdowns[trainingId] = "00:00";
            clearInterval(qrIntervals[trainingId]);
          } else {
            const minutes = Math.floor(diff / 60000)
              .toString()
              .padStart(2, "0");
            const seconds = Math.floor((diff % 60000) / 1000)
              .toString()
              .padStart(2, "0");
            qrCountdowns[trainingId] = `${minutes}:${seconds}`;
          }
        }; // Initialize the key so template reacts immediately

        qrCountdowns[trainingId] = "loading...";

        updateCountdown(); // initial call
        qrIntervals[trainingId] = setInterval(updateCountdown, 1000);
      }
    });
  }
  const modalQR = reactive({
    value: null,
    expiresAt: null,
    countdown: "00:00",
    interval: null,
  });

  //ModalQrCountdown
  // Modal QR Countdown
  function startModalQRCountdown(training) {
    if (!training.attendance_key || !training.end_time) return;

    qrCodeValue.value = training.attendance_key;
    qrCountdown.value = "";

    const expiresAt = new Date(training.end_time);

    if (qrInterval) clearInterval(qrInterval);

    const updateCountdown = () => {
      const now = new Date();
      const diff = expiresAt - now;

      if (diff <= 0) {
        // ⛔ Hide QR and stop countdown
        qrCountdown.value = "00:00";
        qrCodeValue.value = null; // hide QR code
        clearInterval(qrInterval);

        // Optional: if you’re showing a modal or section, hide it too
        if (isModalOpen.value) isModalOpen.value = false;
      } else {
        const minutes = Math.floor(diff / 60000)
          .toString()
          .padStart(2, "0");
        const seconds = Math.floor((diff % 60000) / 1000)
          .toString()
          .padStart(2, "0");
        qrCountdown.value = `${minutes}:${seconds}`;
      }
    };

    updateCountdown();
    qrInterval = setInterval(updateCountdown, 1000);
  }
}

// ============================
// 🚀 Lifecycle Hooks
// ============================
onMounted(async () => {
  await fetchOrganizations();
  console.log("✅ Organizations loaded:", organizations.value); // <-- here

  const orgID = 1; // replace with dynamic value
  await fetchTrainings(orgID);
  console.log("✅ Trainings loaded:", trainings.value); // <-- and here

  await fetchMyRegistrations();
  await fetchBookmarks();
  startAllQRCountdowns();

  buildEvents();
  setupCalendarDOM();

  setInterval(() => fetchTrainings(orgID), 30000);
});

// Modal
const selectedPost = ref(null);
watch([trainings, myRegistrations], async () => {
  buildEvents();
  await nextTick();
  setupCalendarDOM();
});

function formatDateTime(dt) {
  if (!dt) return "";
  const date = new Date(dt);

  // Let JS handle the correct Philippine timezone automatically
  return date.toLocaleString("en-PH", {
    timeZone: "Asia/Manila",
    dateStyle: "long",
    timeStyle: "short",
  });
}

const selectedTrainingForList = ref(null);

function openTrainingListModal(training) {
  selectedTrainingForList.value = training;
  console.log("🟩 Selected training data:", training); // ✅ Logs the clicked card’s full data
  selectedTraining.value = training;
}

function closeTrainingListModal() {
  selectedTrainingForList.value = null;
}

function formatDate(datetime, options = { detailed: true }) {
  if (!datetime) return options.detailed ? "N/A" : "";

  const date = new Date(datetime);

  if (options.detailed) {
    // Full detailed format with weekday
    return date.toLocaleDateString("en-US", {
      weekday: "long",
      month: "long",
      day: "numeric",
      year: "numeric",
    });
  } else {
    // Simple long format
    return date.toLocaleDateString("en-US", { dateStyle: "long" });
  }
}

function formatTime(datetime) {
  if (!datetime) return "N/A";
  const date = new Date(datetime);
  return date.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
}

import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

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
      `http://127.0.0.1:8000/api/registrations/${training.trainingID}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    myRegistrations.value.delete(training.trainingID);
  } else {
    await axios.post(
      "http://127.0.0.1:8000/api/registrations",
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
