<script setup>
import { ref, reactive, computed, onMounted, nextTick } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue";

// ---------------------------
// Reactive States
// ---------------------------
const trainings = ref([]);
const organizations = ref([]);
const bookmarkedTrainings = ref([]);
const myRegistrations = ref(new Set());
const qrCountdowns = reactive({});
const qrIntervals = reactive({});
const qrCodeValue = ref(null);
const selectedTraining = ref(null);
const isModalOpen = ref(false);
const toasts = ref([]);

// ---------------------------
// Helper: Token Safe Getter
// ---------------------------
function getToken() {
  const token = localStorage.getItem("token");
  if (!token) {
    console.warn("⚠️ Token missing!");
    return null;
  }
  return token;
}

// ---------------------------
// Toast Notifications
// ---------------------------
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ---------------------------
// Fetch Trainings & Orgs
// ---------------------------


// ---------------------------
// Bookmarks
// ---------------------------

// ---------------------------
// Registrations
// ---------------------------
async function fetchMyRegistrations() {
  const token = getToken();
  if (!token) return;

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/registrations", {
      headers: { Authorization: `Bearer ${token}` },
    });

    myRegistrations.value.clear();
    res.data.forEach((r) => myRegistrations.value.add(r.trainingID));
    console.log("✅ Registrations:", Array.from(myRegistrations.value));
  } catch (err) {
    console.error("❌ Failed to fetch registrations:", err);
  }
}

onMounted(fetchMyRegistrations);

// 🔹 Toggle registration
async function toggleRegister(training) {
  const token = localStorage.getItem("token");
  if (!token) return alert("Please log in first.");

  // If already registered → unregister
  if (registeredPosts[training.trainingID]) {
    try {
      const registrationID =
        registeredPosts[training.trainingID].registrationID;

      await axios.delete(
        import.meta.env.VITE_API_BASE_URL +`/registrations/${registrationID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      delete registeredPosts[training.trainingID];
      myRegistrations.value.delete(training.trainingID); // 🧩 keep in sync
      console.log(`🗑 Unregistered from ${training.title}`);
    } catch (err) {
      console.error("❌ Failed to unregister:", err);
    }
  }
  // Else register
  else {
    try {
      const res = await axios.post(
        import.meta.env.VITE_API_BASE_URL+"/registrations",
        { trainingID: training.trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      registeredPosts[training.trainingID] = {
        registrationID: res.data.registrationID,
      };
      myRegistrations.value.add(training.trainingID); // 🧩 sync for QR

      console.log(`✅ Registered for ${training.title}`);
    } catch (err) {
      console.error("❌ Failed to register:", err);
    }
  }
}

// Date of PH
const PH_TIME_OFFSET = 8 * 60; // +8 hours in minutes

// Organizations
const isSidebarOpen = ref(false);
// Trainings data

// Modal + notifications
const isTrainingModalOpen = ref(false);

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

    const { data } = await axios.get(import.meta.env.VITE_API_BASE_URL +"/bookmarks", {
      headers: { Authorization: `Bearer ${token}` },
    });
    bookmarkedTrainings.value = data;
  } catch (error) {
    console.error("Error fetching bookmarks:", error);
  }
}

async function fetchOrganizations() {
  try {
    const token = localStorage.getItem("token"); // or however you store it

    const res = await axios.get(import.meta.env.VITE_API_BASE_URL +`/organization`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    organizations.value = res.data; // or res.data.data depending on your API
    console.log("✅ Organizations loaded:", organizations.value);

  } catch (error) {
    if (error.response) {
      console.error("❌ Server responded with:", error.response.status, error.response.data);
    } else if (error.request) {
      console.error("❌ No response received:", error.request);
    } else {
      console.error("❌ Axios error:", error.message);
    }
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
      await axios.delete(import.meta.env.VITE_API_BASE_URL +`/bookmarks/${trainingId}`, {
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
        import.meta.env.VITE_API_BASE_URL + "/bookmarks",
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
// 📝 Registration Function
// ============================

async function toggleRegistration(training) {
  const token = getToken();
  if (!token) {
    addToast("Please log in first", "accent");
    return;
  }

  // If registered → unregister
  if (myRegistrations.value.has(training.trainingID)) {
    try {
      const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/registrations", {
        headers: { Authorization: `Bearer ${token}` },
      });
      const reg = res.data.find((r) => r.trainingID === training.trainingID);
      if (!reg) {
        addToast("Registration not found", "error");
        return;
      }
      await axios.delete(import.meta.env.VITE_API_BASE_URL + `/registrations/${reg.registrationID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      myRegistrations.value.delete(training.trainingID);
      addToast("Unregistered successfully", "info");
    } catch (err) {
      console.error(err);
      addToast("Failed to unregister", "error");
    }
  }
  // If not registered → register
  else {
    try {
      await axios.post(import.meta.env.VITE_API_BASE_URL + "/registrations", { trainingID: training.trainingID }, {
        headers: { Authorization: `Bearer ${token}` },
      });
      myRegistrations.value.add(training.trainingID);
      addToast("Registered successfully!", "success");
    } catch (err) {
      console.error(err);
      if (err.response?.status === 409) addToast("Already registered", "accent");
      else addToast("Failed to register", "error");
    }
  }
}

// ============================
// 🔔 Toast Function
// ============================

// ============================
// 📡 API Fetch Functions
// ============================
async function fetchTrainings() {
  try {
    const token = getToken();
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings", {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    console.log("Trainings API response:", res.data);
    trainings.value = res.data;
  } catch (err) {
    console.error("Failed to fetch trainings:", err);
    addToast("Failed to load trainings", "error");
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
  qrCodeValue.value = `${import.meta.env.VITE_API_BASE_URL}/attendance/submit?trainingID=${training.trainingID}&key=${training.attendance_key}`;
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

// ---------------------------
// Lifecycle
// ---------------------------
onMounted(async () => {
  await fetchOrganizations();
  await fetchTrainings();
  await fetchBookmarks();
  await fetchMyRegistrations();
});

// Modal
const selectedPost = ref(null);
watch([trainings, myRegistrations], async () => {
  console.log("✅ Trainings updated:", trainings.value);

  buildEvents();
  await nextTick();
  setupCalendarDOM();
});


watch(trainings, () => {
  console.log("🔥 TRAININGS RECEIVED FROM API:", trainings.value);
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
          v-for="training in trainingsWithOrg"
          :key="training.trainingID"
          class="p-4 bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer flex justify-between items-center"
          @click="openTrainingListModal(training)"
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

    <!--Training List Modal-->
    <dialog v-if="selectedTrainingForList" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeTrainingListModal"
        >
          ✕
        </button>

        <!-- Safety check wrapper -->
        <template v-if="selectedTrainingForList">
          <!-- Training Details -->
          <h2 class="text-xl font-bold mb-2">
            {{ selectedTrainingForList.title || "Untitled Training" }}
          </h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization:
            {{
              selectedTrainingForList.organization?.name ||
              selectedTrainingForList.organizationName ||
              "Unknown Organization"
            }}
          </p>

          <!-- Buttons -->
          <div
            class="my-4 flex justify-end gap-2"
            v-if="selectedTrainingForList.trainingID"
          >
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedTrainingForList.trainingID)"
            >
              {{
                bookmarkedPosts?.[selectedTrainingForList.trainingID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Register / Unregister -->
            <button
              class="btn btn-sm text-white"
              :class="
                registeredPosts[selectedTrainingForList.trainingID]
                  ? 'bg-gray-500'
                  : 'bg-customButton'
              "
              @click.stop="toggleRegister(selectedTrainingForList)"
            >
              {{
                registeredPosts[selectedTrainingForList.trainingID]
                  ? "Unregister"
                  : "Register"
              }}
            </button>
          </div>

          <!-- Basic Info -->
          <p><strong>Mode:</strong> {{ selectedTrainingForList.mode }}</p>
          <p>
            <strong>Description:</strong>
            {{ selectedTrainingForList.description }}
          </p>

          <!-- Conditional Display -->
          <p v-if="selectedTrainingForList.mode?.toLowerCase() === 'online'">
            <strong>Link:</strong>
            <a
              :href="selectedTrainingForList.trainingLink"
              target="_blank"
              class="text-blue-500 underline"
            >
              {{ selectedTrainingForList.trainingLink }}
            </a>
          </p>

          <p
            v-else-if="
              selectedTrainingForList.mode?.toLowerCase() === 'on-site'
            "
          >
            <strong>Location:</strong> {{ selectedTrainingForList.location }}
          </p>

          <p>
            <strong>Schedule:</strong>
            {{ formatDate(selectedTrainingForList.schedule) }}
            <span v-if="selectedTrainingForList.schedule">
              at {{ formatTime(selectedTrainingForList.schedule) }}
            </span>
          </p>

          <!-- QR code if registered -->
          <div
            v-if="myRegistrations.has(selectedTrainingForList.trainingID)"
            class="mt-4 text-center"
          >
            <div
              v-if="
                selectedTrainingForList.attendance_key &&
                new Date(training.end_time) > new Date()
              "
              class="text-center flex flex-col items-center justify-center"
            >
              <qrcode-vue
                :value="selectedTrainingForList.attendance_key"
                :size="120"
              />
              <p class="text-sm text-gray-600 mt-1">
                Expires in:
                {{
                  qrCountdowns[selectedTrainingForList.trainingID] ||
                  formatDateTime(selectedTrainingForList.end_time)
                }}
              </p>
            </div>
            <div v-else>
              <p class="text-sm text-gray-500">
                QR code not yet generated or expired.
              </p>
            </div>
          </div>
        </template>
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
          'alert-accent': toast.type === 'accent',
        }"
      >
        {{ toast.message }}
      </div>
    </div>
  </main>
</template>
