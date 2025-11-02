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
async function fetchOrganizations() {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/organization");
    organizations.value = res.data;
  } catch (err) {
    console.error("❌ Failed to load organizations", err);
    addToast("Failed to load organizations", "error");
  }
}

async function fetchTrainings() {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings");
    trainings.value = res.data;
  } catch (err) {
    console.error("❌ Failed to load trainings", err);
    addToast("Failed to load trainings", "error");
  }
}

// ---------------------------
// Bookmarks
// ---------------------------
async function fetchBookmarks() {
  const token = getToken();
  if (!token) return;

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/bookmarks", {
      headers: { Authorization: `Bearer ${token}` },
    });
    bookmarkedTrainings.value = res.data;
  } catch (err) {
    console.error("❌ Failed to fetch bookmarks", err);
  }
}

function isTrainingBookmarked(trainingID) {
  return bookmarkedTrainings.value.includes(trainingID);
}

async function toggleBookmark(trainingID) {
  const token = getToken();
  if (!token) {
    addToast("Please log in first", "accent");
    return;
  }

  if (isTrainingBookmarked(trainingID)) {
    // Remove
    try {
      await axios.delete(import.meta.env.VITE_API_BASE_URL + `/bookmarks/${trainingID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedTrainings.value = bookmarkedTrainings.value.filter((id) => id !== trainingID);
      addToast("Bookmark removed", "info");
    } catch (err) {
      console.error("❌ Failed to remove bookmark", err);
      addToast("Failed to remove bookmark", "error");
    }
  } else {
    // Add
    try {
      await axios.post(import.meta.env.VITE_API_BASE_URL + "/bookmarks", { trainingID }, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedTrainings.value.push(trainingID);
      addToast("Bookmarked!", "success");
    } catch (err) {
      console.error("❌ Failed to add bookmark", err);
      addToast("Failed to bookmark", "error");
    }
  }
}

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

// ---------------------------
// QR Code Countdown
// ---------------------------
function startModalQRCountdown(training) {
  if (!training.attendance_key || !training.end_time) return;

  const trainingId = training.trainingID;
  const expiresAt = new Date(training.end_time);

  qrCodeValue.value = `${import.meta.env.VITE_API_BASE_URL}/attendance/submit?trainingID=${training.trainingID}&key=${training.attendance_key}`;

  // Clear existing interval
  if (qrIntervals[trainingId]) clearInterval(qrIntervals[trainingId]);

  const updateCountdown = () => {
    const diff = expiresAt - new Date();
    if (diff <= 0) {
      qrCountdowns[trainingId] = "00:00";
      qrCodeValue.value = null;
      clearInterval(qrIntervals[trainingId]);
    } else {
      const minutes = String(Math.floor(diff / 60000)).padStart(2, "0");
      const seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, "0");
      qrCountdowns[trainingId] = `${minutes}:${seconds}`;
    }
  };

  updateCountdown();
  qrIntervals[trainingId] = setInterval(updateCountdown, 1000);
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

    <!-- OVERLAY -->
    <div
      v-if="isSidebarOpen"
      class="fixed inset-0 bg-black/40 z-30"
      @click="isSidebarOpen = false"
    ></div>

    <!-- SIDEBAR -->
    <aside
      :class="[
        'bg-white shadow-lg rounded-l-lg p-4 transition-transform duration-300 fixed top-0 right-0 h-full z-40 w-80 flex flex-col',
        isSidebarOpen ? 'translate-x-0' : 'translate-x-full',
      ]"
    >
      <!-- Close Button -->
      <button
        class="absolute top-4 left-4 text-gray-600 hover:text-gray-900"
        @click="isSidebarOpen = false"
      >
        ✕
      </button>

      <!-- Sidebar Content -->
      <div class="flex flex-col items-center gap-4 mt-12 overflow-y-auto">
        <!-- Calendar -->
        <calendar-date
          ref="calendarRef"
          first-day-of-week="0"
          class="cally bg-base-100 border border-base-300 shadow rounded-box p-3 flex-shrink-0"
        >
          <calendar-month>
            <template #day="{ date, label }">
              <div
                class="w-8 h-8 flex items-center justify-center rounded-full"
                :data-date="date"
                :class="{
                  'bg-blue-100 text-blue-700 font-semibold': events[date],
                  'bg-gray-200 text-gray-600': !events[date],
                }"
                @click="showEvents(date)"
              >
                {{ label }}
              </div>
            </template>
          </calendar-month>
        </calendar-date>

        <!-- Events Panel -->
        <div class="bg-white w-full max-w-[250px] h-72 overflow-y-auto">
          <h1 class="text-lg font-semibold mb-1">Upcoming Events</h1>
          <h2 class="mb-2 text-sm text-gray-600">
            on
            <span class="font-medium">{{
              selectedDate || "Select a date"
            }}</span>
          </h2>

          <div v-if="dayEvents.length === 0" class="text-gray-500 text-sm">
            No events scheduled
          </div>

          <div v-else class="flex flex-col gap-2">
            <div
              v-for="(event, i) in dayEvents"
              :key="i"
              @click="openModal(event)"
              class="bg-gray-100 p-2 rounded-lg shadow-sm cursor-pointer hover:bg-gray-200 flex justify-between items-center"
            >
              <div>
                <h3 class="font-semibold text-sm">{{ event.title }}</h3>
                <p class="text-xs text-gray-600"></p>
                <p class="text-xs text-gray-600">
                  {{ event.organization }}
                </p>
              </div>
              <!-- ✅ Event Type Badge -->
              <span
                class="text-[10px] text-white px-2 py-1 rounded-full"
                :class="
                  event.type === 'training' ? 'bg-blue-500' : 'bg-green-500'
                "
              >
                {{ event.type === "training" ? "Training" : "Career" }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <button
      v-if="!isSidebarOpen"
      class="fixed bottom-6 right-6 bg-dark-slate text-white p-3 rounded-full shadow-lg z-50"
      @click="isSidebarOpen = true"
    >
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M16.75 3.56V2C16.75 1.59 16.41 1.25 16 1.25C15.59 1.25 15.25 1.59 15.25 2V3.5H8.74999V2C8.74999 1.59 8.40999 1.25 7.99999 1.25C7.58999 1.25 7.24999 1.59 7.24999 2V3.56C4.54999 3.81 3.23999 5.42 3.03999 7.81C3.01999 8.1 3.25999 8.34 3.53999 8.34H20.46C20.75 8.34 20.99 8.09 20.96 7.81C20.76 5.42 19.45 3.81 16.75 3.56Z"
          fill="white"
        />
        <path
          d="M20 9.84003H4C3.45 9.84003 3 10.29 3 10.84V17C3 20 4.5 22 8 22H16C19.5 22 21 20 21 17V10.84C21 10.29 20.55 9.84003 20 9.84003ZM9.21 18.21C9.16 18.25 9.11 18.3 9.06 18.33C9 18.37 8.94 18.4 8.88 18.42C8.82 18.45 8.76 18.47 8.7 18.48C8.63 18.49 8.57 18.5 8.5 18.5C8.37 18.5 8.24 18.47 8.12 18.42C7.99 18.37 7.89 18.3 7.79 18.21C7.61 18.02 7.5 17.76 7.5 17.5C7.5 17.24 7.61 16.98 7.79 16.79C7.89 16.7 7.99 16.63 8.12 16.58C8.3 16.5 8.5 16.48 8.7 16.52C8.76 16.53 8.82 16.55 8.88 16.58C8.94 16.6 9 16.63 9.06 16.67C9.11 16.71 9.16 16.75 9.21 16.79C9.39 16.98 9.5 17.24 9.5 17.5C9.5 17.76 9.39 18.02 9.21 18.21ZM9.21 14.71C9.02 14.89 8.76 15 8.5 15C8.24 15 7.98 14.89 7.79 14.71C7.61 14.52 7.5 14.26 7.5 14C7.5 13.74 7.61 13.48 7.79 13.29C8.07 13.01 8.51 12.92 8.88 13.08C9.01 13.13 9.12 13.2 9.21 13.29C9.39 13.48 9.5 13.74 9.5 14C9.5 14.26 9.39 14.52 9.21 14.71ZM12.71 18.21C12.52 18.39 12.26 18.5 12 18.5C11.74 18.5 11.48 18.39 11.29 18.21C11.11 18.02 11 17.76 11 17.5C11 17.24 11.11 16.98 11.29 16.79C11.66 16.42 12.34 16.42 12.71 16.79C12.89 16.98 13 17.24 13 17.5C13 17.76 12.89 18.02 12.71 18.21ZM12.71 14.71C12.66 14.75 12.61 14.79 12.56 14.83C12.5 14.87 12.44 14.9 12.38 14.92C12.32 14.95 12.26 14.97 12.2 14.98C12.13 14.99 12.07 15 12 15C11.74 15 11.48 14.89 11.29 14.71C11.11 14.52 11 14.26 11 14C11 13.74 11.11 13.48 11.29 13.29C11.38 13.2 11.49 13.13 11.62 13.08C11.99 12.92 12.43 13.01 12.71 13.29C12.89 13.48 13 13.74 13 14C13 14.26 12.89 14.52 12.71 14.71ZM16.21 18.21C16.02 18.39 15.76 18.5 15.5 18.5C15.24 18.5 14.98 18.39 14.79 18.21C14.61 18.02 14.5 17.76 14.5 17.5C14.5 17.24 14.61 16.98 14.79 16.79C15.16 16.42 15.84 16.42 16.21 16.79C16.39 16.98 16.5 17.24 16.5 17.5C16.5 17.76 16.39 18.02 16.21 18.21ZM16.21 14.71C16.16 14.75 16.11 14.79 16.06 14.83C16 14.87 15.94 14.9 15.88 14.92C15.82 14.95 15.76 14.97 15.7 14.98C15.63 14.99 15.56 15 15.5 15C15.24 15 14.98 14.89 14.79 14.71C14.61 14.52 14.5 14.26 14.5 14C14.5 13.74 14.61 13.48 14.79 13.29C14.89 13.2 14.99 13.13 15.12 13.08C15.3 13 15.5 12.98 15.7 13.02C15.76 13.03 15.82 13.05 15.88 13.08C15.94 13.1 16 13.13 16.06 13.17C16.11 13.21 16.16 13.25 16.21 13.29C16.39 13.48 16.5 13.74 16.5 14C16.5 14.26 16.39 14.52 16.21 14.71Z"
          fill="white"
        />
      </svg>
    </button>

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
          <p v-if="selectedTrainingForList.Mode?.toLowerCase() === 'online'">
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
              selectedTrainingForList.Mode?.toLowerCase() === 'on-site'
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
              v-if="selectedTrainingForList.attendance_key &&
              new Date(training.end_time) > new Date()"
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

    <!-- 🟦 Training Modal -->
    <dialog ref="trainingDialog" v-if="selectedTraining" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeTrainingModal"
        >
          ✕
        </button>

        <!-- Safety check wrapper -->
        <template v-if="selectedTraining">
          <!-- Training Details -->
          <h2 class="text-xl font-bold mb-2">
            {{ selectedTraining.title || "Untitled Training" }}
          </h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization:
            {{ selectedTraining.organization || "Unknown Organization" }}
          </p>

          <!-- Buttons -->
          <div
            class="my-4 flex justify-end gap-2"
            v-if="selectedTraining.trainingID"
          >
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedTraining)"
            >
              {{
                bookmarkedPosts?.[selectedTraining.trainingID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Register / Unregister -->
            <button
              class="btn btn-sm text-white"
              :class="
                registeredPosts[selectedTraining.trainingID]
                  ? 'bg-gray-500'
                  : 'bg-customButton'
              "
              @click.stop="toggleRegister(selectedTraining)"
            >
              {{
                registeredPosts[selectedTraining.trainingID]
                  ? "Unregister"
                  : "Register"
              }}
            </button>
          </div>

          <!-- Basic Info -->
          <p><strong>Mode:</strong> {{ selectedTraining.Mode || "N/A" }}</p>
          <p>
            <strong>Description:</strong> {{ selectedTraining.description }}
          </p>

          <!-- Conditional Display -->
          <p v-if="selectedTraining.Mode?.toLowerCase() === 'online'">
            <strong>Link:</strong>
            <a
              :href="selectedTraining.trainingLink"
              target="_blank"
              class="text-blue-500 underline"
            >
              {{ selectedTraining.trainingLink }}
            </a>
          </p>

          <p v-else-if="selectedTraining.Mode?.toLowerCase() === 'on-site'">
            <strong>Location:</strong> {{ selectedTraining.location }}
          </p>

          <p>
            <strong>Schedule:</strong>
            {{ formatDate(selectedTraining.date) }} at
            {{ selectedTraining.time }}
          </p>
          <!-- QR code if registered -->
          <div
            v-if="myRegistrations.has(selectedTraining)"
            class="mt-4 text-center"
          >
            <div
              v-if="selectedTraining.attendance_key"
              class="text-center flex flex-col items-center justify-center"
            >
              <qrcode-vue
                :value="selectedTraining.attendance_key"
                :size="120"
              />
              <p class="text-sm text-gray-600 mt-1">
                Expires in:
                {{
                  qrCountdowns[selectedTraining.trainingID] ||
                  formatDateTime(selectedTraining.attendance_expires_at)
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

    <!-- 🟩 Career Modal -->
    <dialog
      ref="careerDialog"
      v-if="selectedPost"
      open
      class="modal sm:modal-middle"
    >
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeModal"
        >
          ✕
        </button>

        <!-- Safety wrapper -->
        <template v-if="selectedPost">
          <!-- Career Details -->
          <h2 class="text-xl font-bold mb-2">
            {{ selectedPost.position || "Untitled Position" }}
          </h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization:
            {{ organizations?.[selectedPost.organizationID] || "Unknown" }}
          </p>

          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2" v-if="selectedPost.careerID">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedPost)"
            >
              {{
                bookmarkedPosts?.[selectedPost.careerID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Apply / Cancel -->
            <button
              v-if="!appliedPosts?.[selectedPost.careerID]"
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
              Cancel
            </button>
          </div>

          <!-- Career Info -->
          <p>
            <strong>Details:</strong>
            {{ selectedCareer?.detailsAndInstructions || "N/A" }}
          </p>
          <p>
            <strong>Qualifications:</strong>
            {{ selectedCareer?.qualifications || "N/A" }}
          </p>
          <p>
            <strong>Requirements:</strong>
            {{ selectedCareer?.requirements || "N/A" }}
          </p>
          <p>
            <strong>Application Address:</strong>
            {{ selectedCareer?.applicationLetterAddress || "N/A" }}
          </p>
          <p>
            <strong>Deadline:</strong>
            {{
              selectedCareer?.deadlineOfSubmission
                ? formatDate(selectedCareer.deadlineOfSubmission)
                : "No deadline set"
            }}
          </p>

          <!-- Interview Schedule -->
          <p v-if="selectedCareer?.date && selectedCareer?.time">
            <strong>Interview Schedule:</strong>
            {{ formatDate(selectedCareer.date) }} at
            {{ formatTime(selectedCareer.time) }}
          </p>

          <!-- Interview Mode -->
          <p v-if="selectedCareer?.mode">
            <strong>Mode:</strong> {{ selectedCareer.mode }}
          </p>

          <!-- Conditional display based on mode -->
          <p
            v-if="
              selectedCareer?.mode?.toLowerCase() === 'online' &&
              selectedCareer?.interviewLink
            "
          >
            <strong>Link:</strong>
            <a
              :href="selectedCareer.interviewLink"
              target="_blank"
              class="text-blue-500 underline"
            >
              {{ selectedCareer.interviewLink }}
            </a>
          </p>

          <p
            v-if="
              selectedCareer?.mode?.toLowerCase() === 'on-site' &&
              selectedCareer?.interviewLocation
            "
          >
            <strong>Location:</strong> {{ selectedCareer.interviewLocation }}
          </p>

          <!-- Recommended Trainings -->
          <div class="mt-6">
            <h3 class="text-base font-semibold mb-3">Recommended Trainings</h3>
            <div
              class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
              style="scrollbar-width: thin"
            >
              <div
                v-for="post in posts.filter((p) => p.type === 'training')"
                :key="post.trainingID"
                class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
                @click.stop="openModal(post)"
              >
                <h4 class="font-semibold text-sm leading-snug mb-1">
                  {{ post.title }}
                </h4>
                <p class="text-[11px] text-gray-600 truncate">
                  {{ organizations?.[post.organizationID] || "Unknown" }}
                </p>
              </div>
            </div>
          </div>
        </template>
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
            <label class="block text-sm font-medium mb-1"
              >Upload PDF Requirements</label
            >
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
          'alert-accent': toast.type === 'accent',
        }"
      >
        {{ toast.message }}
      </div>
    </div>
  </main>
</template>
