<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue";
import { useRegistrationStore } from "@/stores/registrationStore";
const regStore = useRegistrationStore();

// Remove this local reactive store
// const registeredPosts = reactive({});
const myRegistrations = computed(() =>
  new Set(Object.keys(regStore.registeredPosts).map(Number))
); // still used for QR display
const bookmarkLoading = reactive({});
const registerLoading = reactive({});

// ---------------------------
// 🔹 Use store for registrations
// ---------------------------
onMounted(() => {
  regStore.loadMyRegistrations().then(() => {
    // Update local QR logic set
    myRegistrations.value = new Set(Object.keys(regStore.registeredPosts).map(Number));
  });
});

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
    );
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
  selectedTraining.value = training;
  showModal.value = true;
  isModalOpen.value = true;

  // Start QR countdown if registered
  if (regStore.registeredPosts[training.trainingID]) {
    startModalQRCountdown(training);
  }
}

function closeModal() {
  isModalOpen.value = false;
  selectedTraining.value = null;
  selectedPost.value = null;
}

// ============================
// 🔖 Bookmark Functions
// ============================

async function toggleBookmark(trainingId) {
  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  bookmarkLoading[trainingId] = true;

  try {
    if (isTrainingBookmarked(trainingId)) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/bookmarks/${trainingId}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      addToast("Bookmark removed", "info");
    } else {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/bookmarks",
        { trainingID: trainingId },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      addToast("Bookmarked!", "success");
    }
    await fetchBookmarks();
  } catch (error) {
    console.error("Failed to toggle bookmark:", error);
    addToast("Failed to toggle bookmark", "error");
  } finally {
    bookmarkLoading[trainingId] = false;
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
// Fetch APIs
// ---------------------------
async function fetchTrainings() {
  try {
    const token = localStorage.getItem("token");
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings", {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    trainings.value = res.data;
  } catch (err) {
    console.error("❌ fetchTrainings error:", err.response?.data || err);
    addToast("Failed to load trainings", "error");
  }
}

async function fetchBookmarks() {
  const token = localStorage.getItem("token");
  if (!token) return;
  const { data } = await axios.get(import.meta.env.VITE_API_BASE_URL + "/bookmarks", {
    headers: { Authorization: `Bearer ${token}` },
  });
  bookmarkedTrainings.value = data;
}

async function fetchOrganizations() {
  try {
    const token = localStorage.getItem("token");
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/organization", {
      headers: { Authorization: `Bearer ${token}` },
    });
    organizations.value = res.data;
  } catch {}
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
// Toggle Registration using store
// ---------------------------
async function toggleRegister(training) {
  await regStore.toggleRegister(training.trainingID);

}

// ---------------------------
// Lifecycle
// ---------------------------
onMounted(async () => {
  await fetchOrganizations();
  await fetchTrainings();
  await fetchBookmarks();

  // Load registrations via store
  await regStore.loadMyRegistrations();
  myRegistrations.value = new Set(Object.keys(regStore.registeredPosts).map(Number));
});

const calendarOpen = ref(false);
function openModalCalendar(event) {
  console.log("Event clicked:", event);
}

import TrainingModal from "@/components/Layout/TrainingModal.vue";
const showModal = ref(false);

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
              <qrcode-vue :value="training.attendance_link" :size="80" />
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
    :bookmarkLoading="bookmarkLoading[selectedTraining?.trainingID]"
    :registerLoading="regStore.loading[selectedTraining?.trainingID]" 
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
